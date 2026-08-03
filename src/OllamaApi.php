<?php
namespace App;

use App\Exceptions\TimeoutException;
use ArdaGnsrn\Ollama\Ollama;
use Psr\Log\LoggerInterface;

class OllamaApi
{
    private object $client;
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger, ?object $client = null)
    {
        $this->client = $client ?? Ollama::client();
        $this->logger = $logger;
    }

    /**
     * Get a translation response from the Ollama API with retries and exponential backoff.
     *
     * @param string $text The text to translate
     * @param string $locale The target locale
     * @param string $model The model to use
     * @param float $temperature The temperature for generation
     * @param int $maxTokens Maximum tokens to generate
     * @param int $timeout Timeout in seconds
     * @param int $maxRetries Maximum number of retry attempts
     * @param int $initialBackoff Initial backoff time in seconds
     * @return string|null The translated text or null on failure
     */
    public function getResponse(
        string $text,
        string $locale,
        string $model = "gemma3:1b",
        float $temperature = 0.0,
        int $maxTokens = 300,
        int $timeout = 20,
        int $maxRetries = 3,
        int $initialBackoff = 1
    ): ?string {
        $prompt = $this->buildTranslationPrompt($text, $locale);

        for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
            try {
                $response = $this->client->completions()->create([
                    'model'       => $model,
                    'prompt'      => $prompt,
                    'temperature' => $temperature,
                    'max_tokens'  => $maxTokens,
                    'timeout'     => $timeout,
                    'stream'      => false,
                ]);

                $translation = trim($response->response);

                if (! empty($translation)) {
                    $cleaned = $this->cleanTranslationOutput($text, $translation);
                    $cleaned = $this->sanitizeJoomlaStringValue($text, $cleaned, $locale);
                    if (! empty($cleaned)) {
                        return $cleaned;
                    }
                }

                $this->logger->warning("Empty translation response received", [
                    'text'   => $text,
                    'locale' => $locale,
                    'model'  => $model,
                ]);

                return null;

            } catch (TimeoutException $e) {
                $this->handleTimeoutException($e, $attempt, $maxRetries, $initialBackoff);
            } catch (\Exception $e) {
                $this->handleGenericException($e, $attempt, $maxRetries, $initialBackoff);
            }
        }

        return null;
    }

    /**
     * Get batch translation response from Ollama API for multiple key-value items.
     *
     * @param array $items Key-value pairs where value is English text
     * @param string $locale Target locale
     * @param string $model Model name
     * @param float $temperature Generation temperature
     * @param int $maxTokens Max response tokens
     * @param int $timeout Request timeout
     * @param int $maxRetries Max retries
     * @param int $initialBackoff Initial backoff in seconds
     * @return array Translated key-value pairs
     */
    public function getBatchResponse(
        array $items,
        string $locale,
        string $model = "gemma3:1b",
        float $temperature = 0.0,
        int $maxTokens = 2000,
        int $timeout = 60,
        int $maxRetries = 2,
        int $initialBackoff = 1
    ): array {
        if (empty($items)) {
            return [];
        }

        $prompt = $this->buildBatchTranslationPrompt($items, $locale);

        for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
            try {
                $response = $this->client->completions()->create([
                    'model'       => $model,
                    'prompt'      => $prompt,
                    'temperature' => $temperature,
                    'max_tokens'  => $maxTokens,
                    'timeout'     => $timeout,
                    'stream'      => false,
                ]);

                $rawResponse = trim($response->response);

                // Strip markdown code block wrappers if present (e.g. ```json ... ```)
                $cleanJson = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $rawResponse);
                $cleanJson = trim($cleanJson);

                $decoded = json_decode($cleanJson, true);
                if (is_array($decoded) && ! empty($decoded)) {
                    $results = [];
                    foreach ($items as $key => $origValue) {
                        if (isset($decoded[$key]) && is_string($decoded[$key]) && ! empty(trim($decoded[$key]))) {
                            $val = $this->cleanTranslationOutput($origValue, $decoded[$key]);
                            $results[$key] = $this->sanitizeJoomlaStringValue($origValue, $val, $locale);
                        }
                    }
                    if (! empty($results)) {
                        return $results;
                    }
                }

                $this->logger->warning("Batch translation JSON parsing failed on attempt " . ($attempt + 1), [
                    'raw' => $rawResponse
                ]);

            } catch (TimeoutException $e) {
                $this->handleTimeoutException($e, $attempt, $maxRetries, $initialBackoff);
            } catch (\Exception $e) {
                $this->handleGenericException($e, $attempt, $maxRetries, $initialBackoff);
            }
        }

        // Fallback: Translate individually if batch LLM response fails
        $this->logger->warning("Batch translation failed, falling back to individual translations", ['count' => count($items)]);
        $results = [];
        foreach ($items as $key => $value) {
            $trans = $this->getResponse($value, $locale, $model, $temperature, 300, $timeout, $maxRetries, $initialBackoff);
            if ($trans !== null) {
                $results[$key] = $trans;
            }
        }
        return $results;
    }

    /**
     * Clean and sanitize LLM translation output to remove chatty preambles,
     * markdown formatting, multiline repetitions, footnotes, and wrapping quotes.
     * Ensures the result is always a valid single-line plain-text INI string value.
     *
     * Handles all known small-LLM bad output patterns:
     *  - '%s plugin is disabled" translates to: "Het plugins..."  → take text after "translates to:"
     *  - 'Order (%s items) nl-NL: Bestellingssummantie (%s items)' → take text after locale label
     *  - 'Translation followed by \'Edit Your Profile\''           → strip "followed by '...'" suffix
     *  - 'Voer uw bericht.** nl-NL: Voewerk uw bericht.*'         → locale + trailing * residue
     */
    public function cleanTranslationOutput(string $originalText, string $rawResponse): string
    {
        $text = trim($rawResponse);

        // 1. Strip markdown code fences  (```json ... ``` or ``` ... ```)
        $text = preg_replace('/^```[a-z]*\s*/i', '', $text);
        $text = preg_replace('/\s*```$/i', '', $text);
        $text = trim($text);

        // 2. If "translates to:" / "translated as:" appears ANYWHERE in the string,
        //    take only the text that follows it.
        //    Catches: '%s plugin is disabled" translates to: "Het plugins..."'
        if (preg_match('/(?:translates? to|translated as)\s*:\s*(.+)$/isu', $text, $m)) {
            $text = trim($m[1]);
        }

        // 3. Strip leading standalone preamble labels (Translation:, Output:, Result:, Here is:)
        $text = preg_replace('/^(?:translation|output|result|here is|translated text)\s*:\s*/iu', '', $text);
        $text = trim($text);

        // 4. If a BCP-47 locale label appears anywhere (e.g. "nl-NL:" or "fr_FR:"),
        //    take only the text that follows it — the LLM may emit both a "first attempt"
        //    and then a second with the locale prefix.
        //    Catches: 'Order summisatie (%s items) nl-NL: Bestellingssummantie (%s items)'
        //    Catches: 'Voer uw bericht.** nl-NL: Voewerk uw bericht.*'
        if (preg_match('/\b[a-z]{2,3}[-_][A-Z]{2,4}\s*:\s*(.+)$/u', $text, $m)) {
            $text = trim($m[1]);
        }

        // 5. Split into lines; discard trailing footnotes like "Note: ...", "PS: ..."
        $lines = array_values(array_filter(array_map('trim', explode("\n", $text))));
        $cleaned = [];
        foreach ($lines as $line) {
            if (preg_match('/^(?:note|ps|p\.s\.|please|also|keep in mind|remember)\b/i', $line)) {
                break; // everything from here is a footnote
            }
            $cleaned[] = $line;
        }
        $text = implode(' ', $cleaned);

        // 6. Strip "followed by '...'" / "and then '...'" English explanatory appendages.
        //    Catches: 'Wijkeldens Uw Profil In Schrijven followed by \'Edit Your Profile\''
        $text = preg_replace("/\s+(?:followed by|and then|puis|or also)\s+['\"].*?['\"]/iu", '', $text);

        // 7. Strip markdown bold/italic/underline  (**x** → x, *x* → x, _x_ → x)
        $text = preg_replace('/\*\*([^*]+)\*\*/', '$1', $text);
        $text = preg_replace('/\*([^*]+)\*/',     '$1', $text);
        $text = preg_replace('/_([^_]+)_/',       '$1', $text);

        // 8. Strip trailing asterisk(s) — markdown residue like "Voewerk uw bericht.*"
        $text = preg_replace('/\*+$/', '', $text);
        $text = trim($text);

        // 9. Collapse repeated identical phrases the LLM may echo (e.g. "min  min")
        $text = preg_replace('/\b(.{3,})\s+\1\b/u', '$1', $text);

        // 10. Normalise internal whitespace
        $text = preg_replace('/[\t ]+/', ' ', $text);
        $text = trim($text);

        // 11. Strip any remaining outer quote characters (single, double, or backtick)
        $text = trim($text, "\"'`");

        // 12. Final safety: if result is empty, fall back to the original
        if ($text === '') {
            $text = $originalText;
        }

        return $text;
    }

    /**
     * Sanitize a translated string to conform to Joomla INI string value standards.
     *
     * Enforces:
     * - All original printf-style and named placeholders are present in translated output.
     * - No HTML tags left in value (LLMs sometimes emit <b>, <em>).
     * - Result is a flat single line.
     * - International/untranslatable tokens (URLs, emails, version numbers) are preserved as-is.
     *
     * @param string $original  The source English string.
     * @param string $translated The cleaned LLM output.
     * @return string  The validated/repaired value.
     */
    public function sanitizeJoomlaStringValue(string $original, string $translated, string $locale = ''): string
    {
        // 1. Extract all format tokens from the ORIGINAL that must survive unchanged.
        $tokens = $this->extractFormatTokens($original);

        // 2. For each token missing from translated output, append it back in position order.
        if (! empty($tokens)) {
            $translated = $this->restoreMissingTokens($translated, $tokens);
        }

        // 3. Strip any stray HTML tags the LLM may have introduced.
        $translated = strip_tags($translated);

        // 4. Collapse any internal whitespace runs.
        $translated = preg_replace('/\s+/', ' ', $translated);
        $translated = trim($translated);

        // 5. Reject output that contains characters from the wrong script for this locale.
        //    E.g. Arabic chars in nl-NL output → fall back to original.
        if ($locale !== '' && $this->detectScriptContamination($locale, $translated)) {
            $this->logger->warning(
                "Script contamination detected in translation for locale '$locale' — falling back to original.",
                ['original' => $original, 'contaminated' => $translated]
            );
            return $original;
        }

        // 6. Safety: never return an empty string.
        return $translated !== '' ? $translated : $original;
    }

    /**
     * Detect whether $text contains characters from a Unicode script that does NOT
     * belong in the expected script family for $locale.
     *
     * Latin-script locales (most European languages): reject Arabic, Hebrew, CJK,
     * Cyrillic, Thai, etc.
     * Cyrillic locales (ru-RU, uk-UA, bg-BG): reject Arabic, CJK, etc.
     * Arabic locales (ar-SA): reject CJK, Cyrillic, etc.
     * CJK locales (zh-CN, ja-JP, ko-KR): broad acceptance, only reject Arabic/Cyrillic.
     *
     * @param string $locale   BCP-47 locale code, e.g. "nl-NL", "ar-SA".
     * @param string $text     Translated text to validate.
     * @return bool  True if contamination is detected (output should be rejected).
     */
    public function detectScriptContamination(string $locale, string $text): bool
    {
        // Nothing to check on empty strings.
        if (trim($text) === '') {
            return false;
        }

        // Derive the language subtag (first part before '-').
        $lang = strtolower(explode('-', $locale)[0]);

        // Script families mapped to PCRE Unicode property patterns.
        // Each entry: [expected_scripts, forbidden_pattern]
        $arabicScript    = '\p{Arabic}';
        $cyrillicScript  = '\p{Cyrillic}';
        $hebrewScript    = '\p{Hebrew}';
        $cjkBlock        = '[\x{4E00}-\x{9FFF}\x{3040}-\x{30FF}\x{AC00}-\x{D7AF}]';
        $thaiScript      = '\p{Thai}';
        $devanagariScript = '\p{Devanagari}';

        // Latin-script language codes (non-exhaustive but covers all common Joomla locales).
        $latinLangs = [
            'af','ca','cs','cy','da','de','en','es','et','eu','fi','fr','gl','hr',
            'hu','id','is','it','lb','lt','lv','ms','mt','nl','no','pl','pt','ro',
            'sk','sl','sq','sr','sv','tr','vi'
        ];

        $cyrillicLangs = ['bg', 'mk', 'ru', 'uk'];
        $arabicLangs   = ['ar', 'fa', 'ur'];
        $cjkLangs      = ['ja', 'ko', 'zh'];

        if (in_array($lang, $latinLangs, true)) {
            // Latin-script locales must not contain Arabic, Cyrillic, Hebrew, CJK, Thai, Devanagari.
            $forbidden = "/$arabicScript|$cyrillicScript|$hebrewScript|$cjkBlock|$thaiScript|$devanagariScript/u";
            return (bool) preg_match($forbidden, $text);
        }

        if (in_array($lang, $cyrillicLangs, true)) {
            // Cyrillic locales must not contain Arabic, Hebrew, CJK, Thai.
            $forbidden = "/$arabicScript|$hebrewScript|$cjkBlock|$thaiScript/u";
            return (bool) preg_match($forbidden, $text);
        }

        if (in_array($lang, $arabicLangs, true)) {
            // Arabic-script locales must not contain CJK or Cyrillic.
            $forbidden = "/$cyrillicScript|$cjkBlock/u";
            return (bool) preg_match($forbidden, $text);
        }

        if (in_array($lang, $cjkLangs, true)) {
            // CJK locales: reject obvious wrong-script contamination.
            $forbidden = "/$arabicScript|$cyrillicScript|$thaiScript/u";
            return (bool) preg_match($forbidden, $text);
        }

        // Unknown locale — no rejection.
        return false;
    }

    /**
     * Extract all Joomla / printf format tokens from a string.
     * Tokens that must NEVER be translated:
     *   - %s  %d  %f  %1$s  %2$d  (printf-style)
     *   - {PLACEHOLDER}  {ORDER_LINK}  (Joomla named tokens)
     *   - [ITEM]  [[BLOCK]]  (Joomla template tags)
     *   - URLs (http:// https://)
     *   - Email addresses
     *   - HTML entities (&amp; &lt; etc.)
     *
     * @return string[]  Ordered list of tokens found.
     */
    private function extractFormatTokens(string $text): array
    {
        $tokens = [];

        // printf-style: %s %d %f %1$s %2$d etc.
        preg_match_all('/%(?:\d+\$)?[sdfe]/u', $text, $m);
        $tokens = array_merge($tokens, $m[0]);

        // Named Joomla tokens: {ORDER_LINK}  {SITE_NAME}
        preg_match_all('/\{[A-Z_]+\}/u', $text, $m);
        $tokens = array_merge($tokens, $m[0]);

        // Joomla template tags: [ITEM]  [[BLOCK]]
        preg_match_all('/\[\[?[A-Z_]+\]?\]/u', $text, $m);
        $tokens = array_merge($tokens, $m[0]);

        // URLs
        preg_match_all('/https?:\/\/\S+/u', $text, $m);
        $tokens = array_merge($tokens, $m[0]);

        // HTML entities (&amp; etc.)
        preg_match_all('/&[a-z]+;/u', $text, $m);
        $tokens = array_merge($tokens, $m[0]);

        return $tokens;
    }

    /**
     * Re-append any tokens that the LLM dropped or mutated in the translated string.
     *
     * @param string   $translated  Output from LLM (already cleaned).
     * @param string[] $tokens      Tokens extracted from the original.
     * @return string
     */
    private function restoreMissingTokens(string $translated, array $tokens): string
    {
        foreach ($tokens as $token) {
            // If token is missing, append it (position guessing for simple cases)
            if (mb_strpos($translated, $token) === false) {
                $translated .= ' ' . $token;
            }
        }
        return trim($translated);
    }

    /**
     * Build the translation prompt for a single string.
     * The prompt is intentionally strict to prevent small LLMs from outputting
     * preambles, markdown, footnotes, or multi-line repetitions.
     */
    private function buildTranslationPrompt(string $text, string $locale): string
    {
        // Extract tokens that must survive unchanged so we can list them in the prompt.
        $tokens = $this->extractFormatTokens($text);
        $tokenNote = ! empty($tokens)
            ? '- DO NOT TRANSLATE these tokens (copy exactly): ' . implode('  ', $tokens)
            : '';

        return <<<PROMPT
You are a professional UI software localisation engine.
Your sole task is to translate the given text into the locale "$locale".

OUTPUT FORMAT — STRICT:
- Return exactly ONE line of plain text.
- No preamble, no label, no explanation (e.g. do NOT write "translates to:", "Translation:", "Output:", "Here is:", "In $locale:").
- No markdown (no **, no *, no _, no backticks, no code fences).
- No surrounding quotes of any kind.
- No trailing notes, comments, or "Note:" paragraphs.
- No HTML tags.

DO NOT TRANSLATE — copy these token types exactly as they appear:
- Printf placeholders: %s  %d  %f  %1\$s  %2\$d
- Named tokens: {ORDER_LINK}  {SITE_NAME}  (anything in curly braces)
- Joomla template tags: [ITEM]  [[BLOCK]]
- URLs (http:// https://)
- HTML entities (&amp; &lt; &gt;)
- Brand/technical terms: CSS, HTML, ID, URL, OK, PDF, API, CMS, PHP, JSON
$tokenNote

EXAMPLES (locale nl-NL):
  Input:  Add to cart
  Output: Toevoegen aan winkelwagen

  Input:  %d item(s) in your order
  Output: %d artikel(en) in uw bestelling

  Input:  Order {ORDER_LINK} placed
  Output: Bestelling {ORDER_LINK} geplaatst

  Input:  Remove
  Output: Verwijderen

NOW TRANSLATE:
$text
PROMPT;
    }

    /**
     * Build prompt for batch key-value translations.
     * Returns strict JSON-only output.
     */
    private function buildBatchTranslationPrompt(array $items, string $locale): string
    {
        $jsonItems = json_encode($items, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        return <<<PROMPT
You are a professional UI software localisation engine.
Translate all values in the JSON object into the locale "$locale".

OUTPUT FORMAT — STRICT:
- Return ONLY a raw valid JSON object.
- Use EXACTLY the same keys as the input.
- Each value must be a plain translated string — no markdown, no quotes inside values, no newlines inside values.
- No surrounding text, no explanation, no code fences (do NOT wrap output in ```).
- No HTML tags in any value.

DO NOT TRANSLATE — copy these token types exactly as they appear in each value:
- Printf placeholders: %s  %d  %f  %1\$s  %2\$d
- Named tokens: {ORDER_LINK}  {SITE_NAME}  (anything in curly braces)
- Joomla template tags: [ITEM]  [[BLOCK]]
- URLs (http:// https://)
- HTML entities (&amp; &lt; &gt;)
- Brand/technical terms: CSS, HTML, ID, URL, OK, PDF, API, CMS, PHP, JSON

EXAMPLE (locale nl-NL):
  Input:  {"ADD_TO_CART": "Add to cart", "ORDER_MSG": "Your order {ORDER_LINK} is ready", "REMOVE": "Remove"}
  Output: {"ADD_TO_CART": "Toevoegen aan winkelwagen", "ORDER_MSG": "Uw bestelling {ORDER_LINK} is gereed", "REMOVE": "Verwijderen"}

INPUT JSON:
$jsonItems
PROMPT;
    }

    /**
     * Handle timeout exception with logging and backoff.
     *
     * @param TimeoutException $e The exception
     * @param int $attempt Current attempt number
     * @param int $maxRetries Maximum retries
     * @param int $initialBackoff Initial backoff time
     */
    private function handleTimeoutException(
        TimeoutException $e,
        int $attempt,
        int $maxRetries,
        int $initialBackoff
    ): void {
        $this->logger->warning("API Timeout: Attempt {$attempt}/{$maxRetries}", [
            'message' => $e->getMessage(),
            'timeout' => $e->getTimeout(),
        ]);

        if ($attempt < $maxRetries - 1) {
            $waitTime = $initialBackoff * (2 ** $attempt);
            sleep($waitTime);
        }
    }

    /**
     * Handle generic exception with logging and backoff.
     *
     * @param \Exception $e The exception
     * @param int $attempt Current attempt number
     * @param int $maxRetries Maximum retries
     * @param int $initialBackoff Initial backoff time
     */
    private function handleGenericException(
        \Exception $e,
        int $attempt,
        int $maxRetries,
        int $initialBackoff
    ): void {
        $this->logger->error("API Error: Attempt {$attempt}/{$maxRetries}", [
            'message' => $e->getMessage(),
            'code'    => $e->getCode(),
        ]);

        if ($attempt < $maxRetries - 1) {
            $waitTime = $initialBackoff * (2 ** $attempt);
            sleep($waitTime);
        }
    }
}
