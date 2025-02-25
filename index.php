<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Translate Language - Ollama Translate Language</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f1f1f1;
        }

        .container {
            width: 50%;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            margin-top: 20px;
            margin-left: 10px;
            text-align: center;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .form-group {
            width: 100%;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
        }

        label {
            font-weight: bold;
            margin-bottom: 5px;
        }

        input[type="text"], select {
            width: 100%;
            padding: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        input[type="submit"] {
            width: 100%;
            padding: 10px 0;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .error {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Translate Language - Ollama Translate Language</h1>
        <form action="main.php" method="post" id="translationForm">
            <div class="form-group">
                <label for="code">Code:</label>
                <input type="text" id="code" name="code" value="">
            </div>
            <div class="form-group">
                <label for="file">File:</label>
                <select id="file" name="file">
                    <option value="">Select</option>
                    <option value="site">Site</option>
                    <option value="admin">Admin</option>
                    <option value="sys">Admin Sys</option>
                </select>
            </div>
            <div class="form-group">
                <input type="submit" value="Submit">
            </div>
        </form>
    </div>
    <div class="container">
        <h3>Result</h3>
        <div id="result"></div>
    </div>
    <script>
        document.getElementById('translationForm').addEventListener('submit', async function (event) {
            event.preventDefault(); // Prevent form submission

            const formData = {
                code: document.getElementById('code').value,
                file: document.getElementById('file').value,
            };

            try {
                // Make the fetch API call
                const response = await fetch('main.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData),
                });

                const result = await response.json();

                // Display the result to the user
                const resultDiv = document.getElementById('result');
                resultDiv.innerHTML = ''; // Clear previous content

                if (result.status === 'success') {
                    resultDiv.innerHTML = `
                        <p class="success">${result.message}</p>
                    `;
                } else {
                    resultDiv.innerHTML = `<p class="error">${result.message}</p>`;
                }
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('result').innerHTML = `<p class="error">An unexpected error occurred.</p>`;
            }
        });
    </script>
</body>
</html>
