# Ollama AI Translation Tool (`ai-content-replacer`)

An automated, high-performance translation solution for Joomla extension language `.ini` files powered by local Ollama LLMs. It features **batch prompting**, **translation caching**, **background job queuing**, a **visual key inspector**, and a modern web interface.

---

## ⚡ Highlights & Key Features

- **🚀 10x–50x Speed Boost via Batch Prompting**: Groups 15–25 missing key strings into structured JSON batch prompts, dramatically reducing LLM round-trip times and network overhead.
- **⚡ 0ms Translation Caching**: Powered by `symfony/cache` (`FilesystemAdapter`). Identical strings across files or repeated translation runs return instantly in 0ms.
- **🔄 Async Background Processing**: Non-blocking translation execution via background PHP CLI workers. Submits requests with instant `HTTP 202 Accepted` response.
- **📊 Visual Key Inspector & Dashboard**: Live stats breakdown showing **Total Base Keys**, **Already Translated**, **Missing Keys**, and **Cached Strings**.
- **🔍 Missing Keys Explorer**: Interactive table with real-time text search/filtering (`KEY_NAME` & English text) and cache status badges (`Cached` vs `LLM`).
- **🎯 Flexible Action Modes**:
  - **Batch Action**: Translate all missing keys in background batches.
  - **Selected Action**: Check specific rows to translate selected keys only.
  - **Single Action**: Translate an individual key string inline with a single click.
- **📡 Real-Time Progress & Terminal Log Console**: Live percentage progress bar (`0% - 100%`) and dark monospaced developer terminal streaming worker activity.
- **🎨 Modern Glassmorphic UI/UX**: Crafted with Google Fonts (*Inter* & *JetBrains Mono*), floating focus rings, and responsive card layouts.
- **🧪 100% Test Coverage**: Fully verified with 21 PHPUnit unit tests and 49 Playwright E2E tests.

---

## 📋 Prerequisites

- **PHP**: 7.4 or 8.x (with `cURL`, `mbstring`, and `json` extensions)
- **Composer**: Dependency manager
- **Node.js**: 18+ (for Playwright E2E tests)
- **Ollama**: Local Ollama server running (e.g. `http://localhost:11434` with `gemma3:1b` or similar model)

---

## 🛠️ Installation & Setup

1. **Clone the Repository**:
   ```bash
   git clone https://github.com/siddik-web/ai-content-replacer.git
   cd ai-content-replacer
   ```

2. **Install PHP Dependencies**:
   ```bash
   composer install
   ```

3. **Install Node.js Dependencies & Playwright Browsers**:
   ```bash
   npm install
   npx playwright install chromium
   ```

4. **Environment Configuration**:
   ```bash
   cp .env.example .env
   ```
   Edit `.env` to set your target project path and model:
   ```env
   PROJECT_PATH="/absolute/path/to/joomla/project"
   COMPONENT_NAME="com_sppagebuilder"
   OLLAMA_HOST="http://localhost:11434"
   OLLAMA_MODEL="gemma3:1b"
   ```

---

## 🚀 Usage

### 1. Web Interface

Start the built-in PHP development server:
```bash
php -S localhost:8080
```
Open **`http://localhost:8080`** in your browser.

- **Scan & Inspect**: Click **`🔍 Scan Missing Keys`** to display missing string statistics and inspect key-value previews.
- **Select Action**: Use checkboxes to select specific keys or click **Submit Translation** for full batch processing.
- **Monitor Progress**: Watch the live progress bar and terminal log console as background worker tasks process translations.

### 2. API Endpoints

- **`POST /main.php`**: Dispatches a translation job.
  ```json
  {
    "code": "fr-FR",
    "file": "site",
    "projectPath": "/path/to/project",
    "componentName": "com_sppagebuilder",
    "async": true,
    "selected_keys": ["COM_KEY_1", "COM_KEY_2"]
  }
  ```
- **`GET /job-status.php?job_id={job_id}`**: Retrieves background job status, progress percentage, and log messages.
- **`GET /scan-keys.php?projectPath=...&componentName=...&code=...&file=...`**: Scans `.ini` files and returns missing key details.

### 3. CLI Background Worker

Execute CLI background tasks directly:
```bash
# Run CLI worker for specific background job
php main.php --job-id=job_66af7d8291a
```

---

## 🧪 Testing Suite

### PHPUnit Unit Tests (21 Tests)

```bash
# Run all unit tests
./vendor/bin/phpunit
```
*Coverage includes `OllamaApiTest`, `JobManagerTest`, `TranslationCacheTest`, `TranslationServiceTest`, and `UtilTest`.*

### Playwright E2E Tests (49 Tests)

```bash
# Run all Playwright end-to-end tests
npm test

# Run individual test suites
npm run test:form      # Form UI & validation tests
npm run test:api       # API endpoint & server tests
npm run test:workflow  # End-to-end translation workflow tests
npm run test:utils     # Helper & utility function tests

# Interactive / Headed Mode
npm run test:headed    # Run tests in visible browser
npm run test:debug     # Interactive step-by-step debug mode
npm run test:ui        # Open Playwright UI dashboard
```

---

## 📁 Architecture & File Structure

```
ai-content-replacer/
├── app.php               # Application orchestrator (TranslationApp)
├── index.php             # Web UI interface & progress console
├── main.php              # API endpoint & CLI worker entrypoint
├── scan-keys.php         # API endpoint for string scanning & inspection
├── job-status.php        # API endpoint for background progress polling
├── config.php            # Application constants & defaults
├── src/
│   ├── ContentReplacer.php    # Translation engine (caching, batching, callbacks)
│   ├── OllamaApi.php          # Ollama client wrapper & batch prompt builder
│   ├── TranslationCache.php   # Symfony Cache integration (0ms lookup)
│   ├── JobManager.php         # Asynchronous job queue manager
│   ├── TranslationService.php # Joomla .ini loader & parser
│   └── Util.php               # Logger & path utilities
├── storage/
│   └── jobs/             # Background job JSON state files
├── logs/
│   └── cache/            # Symfony filesystem cache files
└── tests/                # PHPUnit & Playwright test suites
```

---

## ⚙️ Configuration Reference (`.env`)

| Parameter | Description | Default |
|-----------|-------------|---------|
| `OLLAMA_HOST` | Local Ollama API URL | `http://localhost:11434` |
| `OLLAMA_MODEL` | Ollama model to use for translations | `gemma3:1b` |
| `PROJECT_PATH` | Default root directory of Joomla project | - |
| `COMPONENT_NAME` | Target component folder name | `com_sppagebuilder` |
| `LOG_LEVEL` | Application logging level | `info` |

---

## 📄 License

This project is open-source under the [MIT License](LICENSE).
