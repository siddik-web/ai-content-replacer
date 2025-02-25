<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Translate Language - SP Page Builder</title>
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
    <div class="container">
        <h1>Translate Language - SP Page Builder</h1>
        <form action="main.php" method="post" id="translationForm">
            <div>
                <label for="code">Language Code:</label>
                <select name="code" id="code">
                    <option value="">Please Select Language Code</option>
                    <option value="bg-BG">Bulgarian (bg-BG)</option>
                    <option value="cs-CZ">Czech (cs-CZ)</option>
                    <option value="es-ES">Spanish (es-ES)</option>
                    <option value="fi-FI">Finnish (fi-FI)</option>
                    <option value="fr-FR">French (fr-FR)</option>
                    <option value="it-IT">Italian (it-IT)</option>
                    <option value="nl-NL">Dutch (nl-NL)</option>
                    <option value="pt-BR">Portuguese Brazil (pt-BR)</option>
                    <option value="pt-PT">Portuguese (pt-PT)</option>
                    <option value="ru-RU">Russian (ru-RU)</option>
                    <option value="th-TH">Thai (th-TH)</option>
                    <option value="uk-UA">Ukrainian (uk-UA)</option>
                    <option value="de-DE">German (de-DE)</option>
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
        document.getElementById('translationForm').addEventListener('submit', async function (event) {
            event.preventDefault();

            // Clear previous errors
            document.getElementById('code-error').textContent = '';
            document.getElementById('file-error').textContent = '';

            // Validate inputs
            const code = document.getElementById('code').value.trim();
            const file = document.getElementById('file').value.trim();
            let isValid = true;

            if (!code) {
                document.getElementById('code-error').textContent = 'Please enter a valid language code.';
                isValid = false;
            }
            if (!file) {
                document.getElementById('file-error').textContent = 'Please select a file type.';
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
                const formData = { code, file };

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