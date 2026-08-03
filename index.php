<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Translate Language - JoomShaper Extensions</title>
    <style>
        /* General Styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9fafb;
            color: #374151;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            font-size: 24px;
            margin-bottom: 20px;
            color: #1e3a8a;
        }
        form {
            display: grid;
            gap: 15px;
        }
        label {
            font-weight: 600;
            margin-bottom: 5px;
            display: block;
        }
        input[type="text"], select {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        input[type="text"]:focus, select:focus {
            border-color: #3b82f6;
            outline: none;
        }
        input[type="submit"] {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            background-color: #3b82f6;
            color: #fff;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        input[type="submit"]:hover {
            background-color: #2563eb;
        }
        input[type="submit"][disabled] {
            background-color: #cbd5e1;
            cursor: not-allowed;
        }
        .error {
            color: #ef4444;
            font-size: 14px;
            margin-top: 5px;
        }
        .loading-spinner {
            display: none;
            border: 4px solid #e5e7eb;
            border-top: 4px solid #3b82f6;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .loading-text {
            display: none;
            text-align: center;
            margin-top: 10px;
            font-size: 14px;
            color: #3b82f6;
        }
        .result-container {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            background-color: #f3f4f6;
        }
        .success {
            color: #16a34a;
        }
        .error-message {
            color: #ef4444;
        }
    </style>
</head>
<body>
<?php
    require_once 'vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();

    $defaultProjectPath   = $_ENV['PROJECT_PATH'] ?? '';
    $defaultComponentName = $_ENV['COMPONENT_NAME'] ?? '';
?>
    <div class="container">
        <h1>Translate Language - JoomShaper Extensions</h1>
        <form action="main.php" method="post" id="translationForm">
            <div>
                <label for="projectPath">Project Path:</label>
                <input type="text" name="projectPath" id="projectPath" value="<?php echo htmlspecialchars($defaultProjectPath); ?>" placeholder="/path/to/project">
                <div id="projectPath-error" class="error"></div>
            </div>
            <div>
                <label for="componentName">Component Name:</label>
                <input type="text" name="componentName" id="componentName" value="<?php echo htmlspecialchars($defaultComponentName); ?>" placeholder="com_example">
                <div id="componentName-error" class="error"></div>
            </div>
            <div>
                <label for="code">Language Code:</label>
                <select name="code" id="code">
                    <option value="">Please Select Language Code</option>
                    <option value="af-ZA">Afrikaans (af-ZA)</option>
                    <option value="sq-AL">Albanian (sq-AL)</option>
                    <option value="ar-AA">Arabic (ar-AA)</option>
                    <option value="hy-AM">Armenian (hy-AM)</option>
                    <option value="az-AZ">Azerbaijani (az-AZ)</option>
                    <option value="eu-ES">Basque (eu-ES)</option>
                    <option value="be-BY">Belarusian (be-BY)</option>
                    <option value="bn-BD">Bengali (bn-BD)</option>
                    <option value="bs-BA">Bosnian (bs-BA)</option>
                    <option value="bg-BG">Bulgarian (bg-BG)</option>
                    <option value="ca-ES">Catalan (ca-ES)</option>
                    <option value="zh-CN">Chinese Simplified (zh-CN)</option>
                    <option value="zh-TW">Chinese Traditional (zh-TW)</option>
                    <option value="hr-HR">Croatian (hr-HR)</option>
                    <option value="cs-CZ">Czech (cs-CZ)</option>
                    <option value="da-DK">Danish (da-DK)</option>
                    <option value="nl-NL">Dutch (nl-NL)</option>
                    <option value="en-GB">English UK (en-GB)</option>
                    <option value="en-US">English US (en-US)</option>
                    <option value="eo-XX">Esperanto (eo-XX)</option>
                    <option value="et-EE">Estonian (et-EE)</option>
                    <option value="fi-FI">Finnish (fi-FI)</option>
                    <option value="fr-FR">French (fr-FR)</option>
                    <option value="gl-ES">Galician (gl-ES)</option>
                    <option value="ka-GE">Georgian (ka-GE)</option>
                    <option value="de-DE">German (de-DE)</option>
                    <option value="el-GR">Greek (el-GR)</option>
                    <option value="he-IL">Hebrew (he-IL)</option>
                    <option value="hi-IN">Hindi (hi-IN)</option>
                    <option value="hu-HU">Hungarian (hu-HU)</option>
                    <option value="is-IS">Icelandic (is-IS)</option>
                    <option value="id-ID">Indonesian (id-ID)</option>
                    <option value="it-IT">Italian (it-IT)</option>
                    <option value="ja-JP">Japanese (ja-JP)</option>
                    <option value="km-KH">Khmer (km-KH)</option>
                    <option value="ko-KR">Korean (ko-KR)</option>
                    <option value="lv-LV">Latvian (lv-LV)</option>
                    <option value="lt-LT">Lithuanian (lt-LT)</option>
                    <option value="mk-MK">Macedonian (mk-MK)</option>
                    <option value="ms-MY">Malay (ms-MY)</option>
                    <option value="nb-NO">Norwegian Bokmål (nb-NO)</option>
                    <option value="nn-NO">Norwegian Nynorsk (nn-NO)</option>
                    <option value="fa-IR">Persian (fa-IR)</option>
                    <option value="pl-PL">Polish (pl-PL)</option>
                    <option value="pt-BR">Portuguese Brazil (pt-BR)</option>
                    <option value="pt-PT">Portuguese (pt-PT)</option>
                    <option value="ro-RO">Romanian (ro-RO)</option>
                    <option value="ru-RU">Russian (ru-RU)</option>
                    <option value="sr-RS">Serbian Cyrillic (sr-RS)</option>
                    <option value="sk-SK">Slovak (sk-SK)</option>
                    <option value="sl-SI">Slovenian (sl-SI)</option>
                    <option value="es-ES">Spanish (es-ES)</option>
                    <option value="sv-SE">Swedish (sv-SE)</option>
                    <option value="ta-IN">Tamil (ta-IN)</option>
                    <option value="th-TH">Thai (th-TH)</option>
                    <option value="tr-TR">Turkish (tr-TR)</option>
                    <option value="uk-UA">Ukrainian (uk-UA)</option>
                    <option value="ur-PK">Urdu (ur-PK)</option>
                    <option value="vi-VN">Vietnamese (vi-VN)</option>
                    <option value="cy-GB">Welsh (cy-GB)</option>
                </select>
                <div id="code-error" class="error"></div>
            </div>
            <div>
                <label for="file">Language File:</label>
                <select id="file" name="file">
                    <option value="">Select</option>
                    <option value="site">Site</option>
                    <option value="admin">Admin</option>
                    <option value="sys">Admin Sys</option>
                </select>
                <div id="file-error" class="error"></div>
            </div>
            <div>
                <input type="submit" value="Submit" id="submitButton">
                <div class="loading-spinner" id="loadingSpinner"></div>
                <div class="loading-text" id="loadingText">Processing...</div>
            </div>
        </form>
        <div class="result-container" id="result"></div>
    </div>

    <script>
        const SP_PAGEBUILDER_LANGS = [
            'bg-BG', 'cs-CZ', 'de-DE', 'es-ES', 'fi-FI', 'fr-FR',
            'it-IT', 'nl-NL', 'pt-BR', 'pt-PT', 'ru-RU', 'th-TH', 'uk-UA'
        ];

        const ALL_LANGUAGES = [
            { value: 'af-ZA', label: 'Afrikaans (af-ZA)' },
            { value: 'sq-AL', label: 'Albanian (sq-AL)' },
            { value: 'ar-AA', label: 'Arabic (ar-AA)' },
            { value: 'hy-AM', label: 'Armenian (hy-AM)' },
            { value: 'az-AZ', label: 'Azerbaijani (az-AZ)' },
            { value: 'eu-ES', label: 'Basque (eu-ES)' },
            { value: 'be-BY', label: 'Belarusian (be-BY)' },
            { value: 'bn-BD', label: 'Bengali (bn-BD)' },
            { value: 'bs-BA', label: 'Bosnian (bs-BA)' },
            { value: 'bg-BG', label: 'Bulgarian (bg-BG)' },
            { value: 'ca-ES', label: 'Catalan (ca-ES)' },
            { value: 'zh-CN', label: 'Chinese Simplified (zh-CN)' },
            { value: 'zh-TW', label: 'Chinese Traditional (zh-TW)' },
            { value: 'hr-HR', label: 'Croatian (hr-HR)' },
            { value: 'cs-CZ', label: 'Czech (cs-CZ)' },
            { value: 'da-DK', label: 'Danish (da-DK)' },
            { value: 'nl-NL', label: 'Dutch (nl-NL)' },
            { value: 'en-GB', label: 'English UK (en-GB)' },
            { value: 'en-US', label: 'English US (en-US)' },
            { value: 'eo-XX', label: 'Esperanto (eo-XX)' },
            { value: 'et-EE', label: 'Estonian (et-EE)' },
            { value: 'fi-FI', label: 'Finnish (fi-FI)' },
            { value: 'fr-FR', label: 'French (fr-FR)' },
            { value: 'gl-ES', label: 'Galician (gl-ES)' },
            { value: 'ka-GE', label: 'Georgian (ka-GE)' },
            { value: 'de-DE', label: 'German (de-DE)' },
            { value: 'el-GR', label: 'Greek (el-GR)' },
            { value: 'he-IL', label: 'Hebrew (he-IL)' },
            { value: 'hi-IN', label: 'Hindi (hi-IN)' },
            { value: 'hu-HU', label: 'Hungarian (hu-HU)' },
            { value: 'is-IS', label: 'Icelandic (is-IS)' },
            { value: 'id-ID', label: 'Indonesian (id-ID)' },
            { value: 'it-IT', label: 'Italian (it-IT)' },
            { value: 'ja-JP', label: 'Japanese (ja-JP)' },
            { value: 'km-KH', label: 'Khmer (km-KH)' },
            { value: 'ko-KR', label: 'Korean (ko-KR)' },
            { value: 'lv-LV', label: 'Latvian (lv-LV)' },
            { value: 'lt-LT', label: 'Lithuanian (lt-LT)' },
            { value: 'mk-MK', label: 'Macedonian (mk-MK)' },
            { value: 'ms-MY', label: 'Malay (ms-MY)' },
            { value: 'nb-NO', label: 'Norwegian Bokmål (nb-NO)' },
            { value: 'nn-NO', label: 'Norwegian Nynorsk (nn-NO)' },
            { value: 'fa-IR', label: 'Persian (fa-IR)' },
            { value: 'pl-PL', label: 'Polish (pl-PL)' },
            { value: 'pt-BR', label: 'Portuguese Brazil (pt-BR)' },
            { value: 'pt-PT', label: 'Portuguese (pt-PT)' },
            { value: 'ro-RO', label: 'Romanian (ro-RO)' },
            { value: 'ru-RU', label: 'Russian (ru-RU)' },
            { value: 'sr-RS', label: 'Serbian Cyrillic (sr-RS)' },
            { value: 'sk-SK', label: 'Slovak (sk-SK)' },
            { value: 'sl-SI', label: 'Slovenian (sl-SI)' },
            { value: 'es-ES', label: 'Spanish (es-ES)' },
            { value: 'sv-SE', label: 'Swedish (sv-SE)' },
            { value: 'ta-IN', label: 'Tamil (ta-IN)' },
            { value: 'th-TH', label: 'Thai (th-TH)' },
            { value: 'tr-TR', label: 'Turkish (tr-TR)' },
            { value: 'uk-UA', label: 'Ukrainian (uk-UA)' },
            { value: 'ur-PK', label: 'Urdu (ur-PK)' },
            { value: 'vi-VN', label: 'Vietnamese (vi-VN)' },
            { value: 'cy-GB', label: 'Welsh (cy-GB)' }
        ];

        function filterLanguagesByComponent() {
            const componentNameInput = document.getElementById('componentName');
            const codeSelect = document.getElementById('code');
            const componentVal = componentNameInput.value.trim().toLowerCase();
            const currentSelected = codeSelect.value;

            const isSpPageBuilder = componentVal === 'com_sppagebuilder';

            // Re-populate options
            codeSelect.innerHTML = '<option value="">Please Select Language Code</option>';

            ALL_LANGUAGES.forEach(lang => {
                if (!isSpPageBuilder || SP_PAGEBUILDER_LANGS.includes(lang.value)) {
                    const option = document.createElement('option');
                    option.value = lang.value;
                    option.textContent = lang.label;
                    if (lang.value === currentSelected) {
                        option.selected = true;
                    }
                    codeSelect.appendChild(option);
                }
            });
        }

        document.getElementById('componentName').addEventListener('input', filterLanguagesByComponent);
        document.getElementById('componentName').addEventListener('change', filterLanguagesByComponent);

        document.getElementById('translationForm').addEventListener('submit', async function (event) {
            event.preventDefault();

            // Clear previous errors
            document.getElementById('code-error').textContent = '';
            document.getElementById('file-error').textContent = '';
            document.getElementById('projectPath-error').textContent = '';
            document.getElementById('componentName-error').textContent = '';

            // Validate inputs
            const code = document.getElementById('code').value.trim();
            const file = document.getElementById('file').value.trim();
            const projectPath = document.getElementById('projectPath').value.trim();
            const componentName = document.getElementById('componentName').value.trim();
            let isValid = true;

            if (!code) {
                document.getElementById('code-error').textContent = 'Please enter a valid language code.';
                isValid = false;
            }
            if (!file) {
                document.getElementById('file-error').textContent = 'Please select a file type.';
                isValid = false;
            }
            if (!projectPath) {
                document.getElementById('projectPath-error').textContent = 'Please enter a project path.';
                isValid = false;
            }
            if (!componentName) {
                document.getElementById('componentName-error').textContent = 'Please enter a component name.';
                isValid = false;
            }

            if (!isValid) return;

            // Display the result to the user
            const resultDiv = document.getElementById('result');
            resultDiv.innerHTML = ''; // Clear previous content

            // Disable the submit button and show the loading state
            const submitButton = document.getElementById('submitButton');
            const loadingSpinner = document.getElementById('loadingSpinner');
            const loadingText = document.getElementById('loadingText');

            submitButton.disabled = true;
            loadingSpinner.style.display = 'block';
            loadingText.style.display = 'block';

            try {
                const formData = { code, file, projectPath, componentName };

                // Make the fetch API call
                const response = await fetch('main.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData),
                });

                const result = await response.json();

                if (result.status === 'success') {
                    resultDiv.innerHTML = `<p class="success">${result.message}</p>`;
                } else {
                    resultDiv.innerHTML = `<p class="error-message">${result.message}</p>`;
                }
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('result').innerHTML = `<p class="error-message">An unexpected error occurred.</p>`;
            } finally {
                // Re-enable the submit button and hide the loading state
                submitButton.disabled = false;
                loadingSpinner.style.display = 'none';
                loadingText.style.display = 'none';
            }
        });
    </script>
</body>
</html>