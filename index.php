<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Translate Language - SP Page Builder</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --primary-light: #eff6ff;
            --secondary: #7c3aed;
            --success: #16a34a;
            --success-light: #f0fdf4;
            --warning: #d97706;
            --warning-light: #fffbeb;
            --bg-main: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --radius-lg: 20px;
            --radius-md: 12px;
            --shadow-lg: 0 20px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.03);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            margin: 0;
            padding: 24px 32px;
            background: radial-gradient(circle at 50% 0%, #eff6ff 0%, #f8fafc 70%);
            color: var(--text-main);
            min-height: 100vh;
        }

        @media (max-width: 768px) {
            body {
                padding: 16px;
            }
        }

        .container {
            width: 100%;
            max-width: 1560px;
            margin: 0 auto;
            background: var(--card-bg);
            border-radius: var(--radius-lg);
            padding: 36px 40px;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-color);
            position: relative;
            min-height: calc(100vh - 48px);
        }

        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #2563eb, #7c3aed, #3b82f6);
        }

        .header-bar {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            background: var(--primary-light);
            color: var(--primary);
            font-size: 12px;
            font-weight: 600;
            border-radius: 999px;
            margin-bottom: 8px;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }

        h1 {
            font-size: 28px;
            font-weight: 800;
            margin: 0 0 6px 0;
            color: var(--text-main);
            letter-spacing: -0.02em;
        }

        .subtitle {
            color: var(--text-muted);
            font-size: 14px;
            margin: 0;
            line-height: 1.5;
        }

        /* Preset Chips Bar for Quick Preset Switch */
        .preset-bar {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 24px;
            padding: 12px 18px;
            background: #f1f5f9;
            border-radius: var(--radius-md);
            font-size: 13px;
        }

        .preset-label {
            font-weight: 700;
            color: #475569;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .preset-chip {
            padding: 6px 14px;
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 999px;
            font-weight: 600;
            color: #334155;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .preset-chip:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--primary-light);
        }

        form {
            display: grid;
            gap: 20px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        label {
            font-size: 13.5px;
            font-weight: 600;
            margin-bottom: 6px;
            color: #334155;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        input[type="text"], select {
            width: 100%;
            padding: 13px 18px;
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: 15px;
            font-family: inherit;
            color: var(--text-main);
            background-color: #f8fafc;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        input[type="text"]:hover, select:hover {
            border-color: #cbd5e1;
            background-color: #ffffff;
        }

        input[type="text"]:focus, select:focus {
            border-color: var(--primary);
            background-color: #ffffff;
            outline: none;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }

        select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%252364748b' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            background-size: 16px;
            padding-right: 40px;
        }

        .btn-group {
            display: flex;
            gap: 14px;
        }

        .btn {
            padding: 13px 24px;
            border: none;
            border-radius: var(--radius-md);
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .scan-btn {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
        }

        .scan-btn:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        .submit-btn {
            flex: 1;
            padding: 14px 24px;
            border: none;
            border-radius: var(--radius-md);
            font-size: 15px;
            font-weight: 700;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .submit-btn:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.35);
            transform: translateY(-1px);
        }

        .submit-btn[disabled], .btn[disabled] {
            background: #cbd5e1;
            box-shadow: none;
            cursor: not-allowed;
            transform: none;
            color: #94a3b8;
        }

        .error {
            color: #ef4444;
            font-size: 13px;
            font-weight: 500;
            margin-top: 6px;
        }

        .loading-spinner {
            display: none;
            border: 3px solid #e2e8f0;
            border-top: 3px solid var(--primary);
            border-radius: 50%;
            width: 24px;
            height: 24px;
            animation: spin 0.8s linear infinite;
            margin: 12px auto 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loading-text {
            display: none;
            text-align: center;
            margin-top: 8px;
            font-size: 13.5px;
            font-weight: 600;
            color: var(--primary);
        }

        /* Visual Stats Dashboard Card */
        .stats-dashboard {
            display: none;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-top: 28px;
            background: #f8fafc;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 20px;
        }

        @media (max-width: 768px) {
            .stats-dashboard {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .stat-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 16px 20px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .stat-card:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .stat-label {
            font-size: 12px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 800;
            color: var(--text-main);
            margin-top: 4px;
            transition: color 0.3s ease;
        }

        .stat-value.missing { color: var(--warning); }
        .stat-value.translated { color: var(--success); }

        /* Missing & Existing Keys Tabbed Explorer (Full Width) */
        .keys-explorer {
            display: none;
            margin-top: 28px;
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 24px;
        }

        .tab-nav {
            display: flex;
            gap: 12px;
            border-bottom: 1.5px solid var(--border-color);
            margin-bottom: 20px;
        }

        .tab-btn {
            padding: 12px 20px;
            font-size: 15px;
            font-weight: 700;
            color: var(--text-muted);
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .tab-btn.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .explorer-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
            gap: 16px;
            flex-wrap: wrap;
        }

        /* Filter Pills & Search Input */
        .filter-pills {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .filter-pill {
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 12.5px;
            font-weight: 600;
            background: #f1f5f9;
            color: #475569;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .filter-pill.active {
            background: var(--primary-light);
            color: var(--primary);
            border-color: #bfdbfe;
        }

        .search-input {
            padding: 10px 16px;
            font-size: 14px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            width: 320px;
        }

        .action-toolbar {
            display: flex;
            gap: 12px;
            align-items: center;
            margin-bottom: 16px;
            padding: 12px 18px;
            background: #f1f5f9;
            border-radius: 10px;
        }

        .btn-sm {
            padding: 8px 16px;
            font-size: 13.5px;
            border-radius: 8px;
            font-weight: 600;
        }

        .btn-primary-sm {
            background: var(--primary);
            color: #ffffff;
            border: none;
        }

        .btn-primary-sm:hover { background: var(--primary-hover); }

        .keys-table-container {
            max-height: 480px;
            overflow-y: auto;
            border: 1px solid var(--border-color);
            border-radius: 10px;
        }

        table.keys-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13.5px;
            text-align: left;
        }

        table.keys-table th {
            position: sticky;
            top: 0;
            background: #f8fafc;
            padding: 12px 16px;
            font-weight: 700;
            color: #475569;
            border-bottom: 1px solid var(--border-color);
            z-index: 10;
        }

        table.keys-table td {
            padding: 12px 16px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            transition: background-color 0.3s ease;
        }

        table.keys-table tr.row-just-translated {
            background-color: #f0fdf4 !important;
            animation: highlightGreen 2s ease-out;
        }

        @keyframes highlightGreen {
            0% { background-color: #bbf7d0; }
            100% { background-color: #f0fdf4; }
        }

        table.keys-table tr:hover {
            background-color: #f8fafc;
        }

        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 11.5px;
            font-weight: 600;
        }

        .badge-cached {
            background: #dcfce7;
            color: #15803d;
        }

        .badge-uncached {
            background: #fef3c7;
            color: #b45309;
        }

        .badge-live {
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 12.5px;
            padding: 5px 12px;
        }

        /* Job Status Badges */
        .badge-pending {
            background: #f1f5f9;
            color: #475569;
        }

        .badge-processing {
            background: #dbeafe;
            color: #1d4ed8;
            animation: pulseBg 1.5s infinite;
        }

        .badge-completed {
            background: #dcfce7;
            color: #15803d;
        }

        .badge-failed {
            background: #fee2e2;
            color: #b91c1c;
        }

        @keyframes pulseBg {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal-card {
            background: #ffffff;
            border-radius: 12px;
            width: 90%;
            max-width: 680px;
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
            padding: 24px;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 22px;
            font-weight: 700;
            cursor: pointer;
            color: #94a3b8;
            line-height: 1;
        }

        .modal-close:hover { color: #0f172a; }

        .mini-progress-bar {
            width: 100%;
            height: 6px;
            background: #e2e8f0;
            border-radius: 3px;
            overflow: hidden;
            margin-top: 4px;
        }

        .mini-progress-fill {
            height: 100%;
            background: var(--primary);
            transition: width 0.3s ease;
        }


        /* Copy Key Button */
        .btn-copy {
            background: none;
            border: none;
            cursor: pointer;
            color: #94a3b8;
            padding: 2px 6px;
            border-radius: 4px;
            transition: color 0.15s ease;
        }

        .btn-copy:hover {
            color: var(--primary);
            background: #eff6ff;
        }

        /* Result Alert Container */
        .result-container {
            margin-top: 28px;
            min-height: 1px;
            display: block;
        }

        .result-container p {
            margin: 0;
            padding: 16px 20px;
            border-radius: var(--radius-md);
            font-size: 14.5px;
            font-weight: 600;
        }

        .success {
            background-color: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .error-message {
            background-color: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        /* Floating Toast Popup Notification */
        .toast-notification {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: #0f172a;
            color: #ffffff;
            padding: 14px 22px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            display: none;
            z-index: 1000;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Progress Card & Realtime Counter Console Styles */
        .progress-box {
            display: none;
            margin-top: 28px;
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 24px;
            box-shadow: var(--shadow-md);
        }

        .progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            font-size: 14.5px;
            font-weight: 700;
            color: #1e293b;
        }

        .progress-bar-bg {
            background: #f1f5f9;
            border-radius: 999px;
            height: 12px;
            width: 100%;
            overflow: hidden;
            margin-bottom: 18px;
        }

        .progress-bar-fill {
            background: linear-gradient(90deg, #2563eb, #7c3aed);
            height: 100%;
            width: 0%;
            transition: width 0.4s ease-out;
            border-radius: 999px;
        }

        .job-log-box {
            background: #0f172a;
            color: #38bdf8;
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
            line-height: 1.6;
            padding: 16px;
            border-radius: 10px;
            max-height: 200px;
            overflow-y: auto;
            white-space: pre-wrap;
            border: 1px solid #1e293b;
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
    $defaultProvider      = $_ENV['LLM_PROVIDER'] ?? 'gemini';
    $defaultGeminiApiKey  = $_ENV['GEMINI_API_KEY'] ?? '';
    $defaultGeminiModel   = $_ENV['GEMINI_MODEL'] ?? 'gemini-flash-lite-latest';
?>
    <div class="container">
        <div class="header-bar">
            <div>
                <div class="brand-badge">⚡ Automated AI Translation</div>
                <h1>Translate Language - SP Page Builder</h1>
                <div class="subtitle">Fast, AI-powered automated translation suite for Joomla extension language `.ini` files.</div>
            </div>
        </div>

        <!-- Quick Presets Toolbar -->
        <div class="preset-bar">
            <span class="preset-label">⚡ Quick Switch Presets:</span>
            <button type="button" class="preset-chip" onclick="applyPreset('com_sppagebuilder')">SP Page Builder (com_sppagebuilder)</button>
            <button type="button" class="preset-chip" onclick="applyPreset('com_easystore')">EasyStore (com_easystore)</button>
        </div>

        <form action="main.php" method="post" id="translationForm">
            <div class="form-row">
                <div class="form-group">
                    <label for="provider">LLM Provider</label>
                    <select name="provider" id="provider" onchange="toggleProviderSettings()">
                        <option value="gemini" <?php echo ($defaultProvider === 'gemini') ? 'selected' : ''; ?>>Google Gemini API (Free Key - Recommended)</option>
                        <option value="ollama" <?php echo ($defaultProvider === 'ollama') ? 'selected' : ''; ?>>Ollama (Local LLM)</option>
                    </select>
                </div>

                <div class="form-group" id="geminiApiKeyGroup">
                    <label for="apiKey">Gemini API Key <small style="color: var(--text-muted); font-weight: normal;">(Free from AI Studio)</small></label>
                    <input type="text" name="apiKey" id="apiKey" value="<?php echo htmlspecialchars($defaultGeminiApiKey); ?>" placeholder="AIzaSy...">
                    <div id="apiKey-error" class="error"></div>
                </div>

                <div class="form-group">
                    <label for="model">LLM Model</label>
                    <select name="model" id="model">
                        <!-- Populated via JS toggleProviderSettings() -->
                    </select>
                    <div id="model-error" class="error"></div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="projectPath">Project Root Path</label>
                    <input type="text" name="projectPath" id="projectPath" value="<?php echo htmlspecialchars($defaultProjectPath); ?>" placeholder="/absolute/path/to/project">
                    <div id="projectPath-error" class="error"></div>
                </div>

                <div class="form-group">
                    <label for="componentName">Component Name</label>
                    <input type="text" name="componentName" id="componentName" value="<?php echo htmlspecialchars($defaultComponentName); ?>" placeholder="com_sppagebuilder">
                    <div id="componentName-error" class="error"></div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="code">Target Language Code</label>
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

                <div class="form-group">
                    <label for="file">Language File Type</label>
                    <select id="file" name="file">
                        <option value="">Select</option>
                        <option value="site">Site</option>
                        <option value="admin">Admin</option>
                        <option value="sys">Admin Sys</option>
                    </select>
                    <div id="file-error" class="error"></div>
                </div>
            </div>

            <div class="btn-group">
                <button type="button" id="scanBtn" class="btn scan-btn">🔍 Scan & Inspect File Keys</button>
                <input type="submit" value="Submit Translation" id="submitButton" class="submit-btn">
            </div>

            <div class="loading-spinner" id="loadingSpinner"></div>
            <div class="loading-text" id="loadingText">Processing translations...</div>
        </form>

        <!-- Visual Stats Dashboard Card -->
        <div class="stats-dashboard" id="statsDashboard">
            <div class="stat-card" id="cardTotalBase">
                <div class="stat-label">Total Base</div>
                <div class="stat-value" id="statTotalBase">0</div>
            </div>
            <div class="stat-card" id="cardTranslated">
                <div class="stat-label">Translated</div>
                <div class="stat-value translated" id="statTranslated">0</div>
            </div>
            <div class="stat-card" id="cardMissing">
                <div class="stat-label">Missing</div>
                <div class="stat-value missing" id="statMissing">0</div>
            </div>
            <div class="stat-card" id="cardCached">
                <div class="stat-label">Cached</div>
                <div class="stat-value" id="statCached">0</div>
            </div>
        </div>

        <!-- Missing & Existing Keys Tabbed Explorer -->
        <div class="keys-explorer" id="keysExplorer">
            <div class="tab-nav">
                <button type="button" class="tab-btn active" id="tabMissingBtn">⚠️ Missing Keys (<span id="countTabMissing">0</span>)</button>
                <button type="button" class="tab-btn" id="tabTranslatedBtn">✅ Already Translated (<span id="countTabTranslated">0</span>)</button>
                <button type="button" class="tab-btn" id="tabJobsBtn">📋 Background Jobs (<span id="countTabJobs">0</span>)</button>
            </div>

            <div class="explorer-header">
                <div class="filter-pills">
                    <button type="button" class="filter-pill active" onclick="setFilterType('all')">All</button>
                    <button type="button" class="filter-pill" onclick="setFilterType('cached')">💾 Cached Only</button>
                    <button type="button" class="filter-pill" onclick="setFilterType('llm')">⚡ LLM Needed</button>
                </div>

                <input type="text" id="searchKeys" class="search-input" placeholder="🔍 Filter keys or text...">
            </div>

            <!-- View 1: Missing Keys Table & Actions -->
            <div id="viewMissing">
                <div class="action-toolbar">
                    <label style="margin: 0; font-size: 13px; font-weight: 600;">
                        <input type="checkbox" id="selectAllKeys"> Select All
                    </label>
                    <div style="flex: 1;"></div>
                    <button type="button" id="btnTranslateSelected" class="btn btn-sm btn-primary-sm" disabled>⚡ Translate Selected (<span id="selectedCount">0</span>)</button>
                </div>

                <div class="keys-table-container">
                    <table class="keys-table">
                        <thead>
                            <tr>
                                <th style="width: 36px;"></th>
                                <th>Key String</th>
                                <th>English Base Text</th>
                                <th style="width: 80px;">Cache</th>
                                <th style="width: 100px;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="keysTableBody">
                            <!-- Populated dynamically via JS -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- View 2: Already Translated Keys Table -->
            <div id="viewTranslated" style="display: none;">
                <div class="keys-table-container">
                    <table class="keys-table">
                        <thead>
                            <tr>
                                <th>Key String</th>
                                <th>English Base Text</th>
                                <th>Current Translated Output</th>
                                <th style="width: 110px;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="translatedTableBody">
                            <!-- Populated dynamically via JS -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- View 3: Background Jobs Explorer -->
            <div id="viewJobs" style="display: none;">
                <div class="action-toolbar">
                    <span style="font-size: 13px; font-weight: 600; color: #475569;">📊 Job History & Status Monitor</span>
                    <div style="flex: 1;"></div>
                    <button type="button" id="btnRefreshJobs" class="btn btn-sm" style="background: #e2e8f0; color: #334155; border: none; cursor: pointer; margin-right: 6px;">🔄 Refresh Jobs</button>
                    <button type="button" id="btnClearJobs" class="btn btn-sm" style="background: #fee2e2; color: #b91c1c; border: none; cursor: pointer;">🗑️ Clear History</button>
                </div>

                <div class="keys-table-container">
                    <table class="keys-table">
                        <thead>
                            <tr>
                                <th>Job ID</th>
                                <th>Status</th>
                                <th>Target Locale / Component</th>
                                <th>Progress</th>
                                <th>Created</th>
                                <th style="width: 100px;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="jobsTableBody">
                            <!-- Populated dynamically via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="progress-box" id="progressBox">
            <div class="progress-header">
                <span id="progressMessage">Initializing worker job...</span>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="badge badge-live" id="liveRemainingBadge">⏳ <span id="liveRemainingText">0 left</span></span>
                    <span id="progressPercent">0%</span>
                </div>
            </div>
            <div class="progress-bar-bg">
                <div class="progress-bar-fill" id="progressBarFill"></div>
            </div>
            <div class="job-log-box" id="jobLogBox">Logs will appear here...</div>
        </div>

        <div class="result-container" id="result"></div>
    </div>

    <!-- Job Log Modal -->
    <div id="jobModal" class="modal-overlay" style="display: none;">
        <div class="modal-card">
            <div class="modal-header">
                <h3 id="modalJobTitle" style="margin: 0; font-size: 16px; color: var(--text-main);">Job Details</h3>
                <button type="button" class="modal-close" id="modalCloseBtn">&times;</button>
            </div>
            <div class="modal-body">
                <div id="modalJobDetails" style="font-size: 13px; color: #475569; margin-bottom: 12px; line-height: 1.6;"></div>
                <div style="font-weight: 600; margin-top: 12px; margin-bottom: 6px; font-size: 13px;">Execution Logs:</div>
                <div class="job-log-box" id="modalJobLogs" style="height: 220px;"></div>
                <div style="margin-top: 16px; display: flex; justify-content: flex-end; gap: 8px;">
                    <button type="button" id="modalRerunBtn" class="btn btn-sm btn-primary-sm">🔄 Re-run Job</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Floating Toast Notification -->
    <div class="toast-notification" id="toastNotification">Notification text</div>


    <script>
        const DEFAULT_PROJECT_PATH = <?php echo json_encode($defaultProjectPath); ?>;
        const DEFAULT_COMPONENT_NAME = <?php echo json_encode($defaultComponentName); ?>;

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

        const GEMINI_MODELS = [
            { value: 'gemini-flash-lite-latest', label: 'Gemini 2.5 / Flash Lite (Recommended - Free Tier)', selected: true },
            { value: 'gemini-2.0-flash', label: 'Gemini 2.0 Flash (Free Tier)' },
            { value: 'gemini-1.5-flash', label: 'Gemini 1.5 Flash' },
            { value: 'gemini-1.5-pro', label: 'Gemini 1.5 Pro' }
        ];

        const OLLAMA_MODELS = [
            { value: 'gemma3:1b', label: 'Gemma 3 (1B)', selected: true },
            { value: 'gemma3', label: 'Gemma 3 (4B)' },
            { value: 'llama3', label: 'Llama 3' },
            { value: 'mistral', label: 'Mistral' }
        ];

        function toggleProviderSettings() {
            const providerSelect = document.getElementById('provider');
            const provider = providerSelect ? providerSelect.value : 'gemini';
            const apiKeyGroup = document.getElementById('geminiApiKeyGroup');
            const modelSelect = document.getElementById('model');

            if (!modelSelect) return;

            modelSelect.innerHTML = '';
            const defaultModel = "<?php echo htmlspecialchars($defaultGeminiModel); ?>";

            if (provider === 'gemini') {
                if (apiKeyGroup) apiKeyGroup.style.display = 'flex';
                GEMINI_MODELS.forEach(m => {
                    const opt = document.createElement('option');
                    opt.value = m.value;
                    opt.textContent = m.label;
                    if (m.value === defaultModel || (m.selected && !defaultModel)) opt.selected = true;
                    modelSelect.appendChild(opt);
                });
            } else {
                if (apiKeyGroup) apiKeyGroup.style.display = 'none';
                OLLAMA_MODELS.forEach(m => {
                    const opt = document.createElement('option');
                    opt.value = m.value;
                    opt.textContent = m.label;
                    if (m.selected) opt.selected = true;
                    modelSelect.appendChild(opt);
                });
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            toggleProviderSettings();
            filterLanguagesByComponent();
        });

        function applyPreset(compName) {
            document.getElementById('componentName').value = compName;
            filterLanguagesByComponent();
            showToast(`Preset applied: ${compName}`);
        }

        function showToast(msg) {
            const toast = document.getElementById('toastNotification');
            toast.textContent = msg;
            toast.style.display = 'block';
            setTimeout(() => {
                toast.style.display = 'none';
            }, 2500);
        }

        function filterLanguagesByComponent() {
            const componentNameInput = document.getElementById('componentName');
            const codeSelect = document.getElementById('code');
            const componentVal = componentNameInput.value.trim().toLowerCase();
            const currentSelected = codeSelect.value;

            const isSpPageBuilder = componentVal === 'com_sppagebuilder';

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

        let scannedMissingItems = [];
        let scannedTranslatedItems = [];
        let newlyProcessedKeys = new Set();
        let activeTab = 'missing';
        let filterType = 'all';

        function setFilterType(type) {
            filterType = type;
            document.querySelectorAll('.filter-pill').forEach(pill => pill.classList.remove('active'));
            if (event && event.target) {
                event.target.classList.add('active');
            }
            renderMissingKeysTable(scannedMissingItems);
        }

        async function scanMissingKeys() {
            const code = document.getElementById('code').value.trim();
            const file = document.getElementById('file').value.trim();
            const projectPath = document.getElementById('projectPath').value.trim();
            const componentName = document.getElementById('componentName').value.trim();

            if (!code || !file || !projectPath || !componentName) {
                return;
            }

            try {
                const url = `scan-keys.php?projectPath=${encodeURIComponent(projectPath)}&componentName=${encodeURIComponent(componentName)}&code=${encodeURIComponent(code)}&file=${encodeURIComponent(file)}`;
                const res = await fetch(url);
                if (!res.ok) return;
                const data = await res.json();

                if (data.status === 'success') {
                    newlyProcessedKeys.clear();

                    document.getElementById('statsDashboard').style.display = 'grid';
                    document.getElementById('statTotalBase').textContent = data.total_base;
                    document.getElementById('statTranslated').textContent = data.existing_translated;
                    document.getElementById('statMissing').textContent = data.missing_count;
                    document.getElementById('statCached').textContent = data.cached_count;

                    document.getElementById('countTabMissing').textContent = data.missing_count;
                    document.getElementById('countTabTranslated').textContent = data.existing_translated;

                    scannedMissingItems = data.missing_items || [];
                    scannedTranslatedItems = data.translated_items || [];

                    renderMissingKeysTable(scannedMissingItems);
                    renderTranslatedKeysTable(scannedTranslatedItems);

                    document.getElementById('keysExplorer').style.display = 'block';
                }
            } catch (err) {
                console.error('Scan error:', err);
            }
        }

        function renderMissingKeysTable(items) {
            const tbody = document.getElementById('keysTableBody');
            tbody.innerHTML = '';

            const searchTerm = document.getElementById('searchKeys').value.trim().toLowerCase();
            let filtered = items.filter(item =>
                item.key.toLowerCase().includes(searchTerm) ||
                item.value.toLowerCase().includes(searchTerm)
            );

            if (filterType === 'cached') {
                filtered = filtered.filter(item => item.is_cached);
            } else if (filterType === 'llm') {
                filtered = filtered.filter(item => !item.is_cached);
            }

            filtered.forEach(item => {
                const tr = document.createElement('tr');
                tr.id = `row-missing-${item.key}`;

                const tdCheck = document.createElement('td');
                tdCheck.innerHTML = `<input type="checkbox" class="key-checkbox" value="${item.key}">`;

                const tdKey = document.createElement('td');
                tdKey.style.fontFamily = 'monospace';
                tdKey.style.fontWeight = '600';
                tdKey.innerHTML = `${item.key} <button type="button" class="btn-copy" onclick="copyToClipboard('${item.key}')" title="Copy Key">📋</button>`;

                const tdVal = document.createElement('td');
                tdVal.textContent = item.value;

                const tdCache = document.createElement('td');
                tdCache.innerHTML = item.is_cached
                    ? `<span class="badge badge-cached">Cached</span>`
                    : `<span class="badge badge-uncached">LLM</span>`;

                const tdAction = document.createElement('td');
                const singleBtn = document.createElement('button');
                singleBtn.type = 'button';
                singleBtn.id = `btn-translate-single-${item.key}`;
                singleBtn.className = 'btn btn-sm btn-primary-sm';
                singleBtn.textContent = 'Translate';
                singleBtn.onclick = () => {
                    singleBtn.disabled = true;
                    singleBtn.textContent = 'Translating...';
                    submitTranslationJob([item.key]);
                };
                tdAction.appendChild(singleBtn);

                tr.appendChild(tdCheck);
                tr.appendChild(tdKey);
                tr.appendChild(tdVal);
                tr.appendChild(tdCache);
                tr.appendChild(tdAction);

                tbody.appendChild(tr);
            });

            bindCheckboxEvents();
        }

        function renderTranslatedKeysTable(items) {
            const tbody = document.getElementById('translatedTableBody');
            tbody.innerHTML = '';

            const searchTerm = document.getElementById('searchKeys').value.trim().toLowerCase();
            const filtered = items.filter(item =>
                item.key.toLowerCase().includes(searchTerm) ||
                item.base_value.toLowerCase().includes(searchTerm) ||
                item.translated_value.toLowerCase().includes(searchTerm)
            );

            filtered.forEach(item => {
                const tr = document.createElement('tr');
                if (newlyProcessedKeys.has(item.key)) {
                    tr.className = 'row-just-translated';
                }

                const tdKey = document.createElement('td');
                tdKey.style.fontFamily = 'monospace';
                tdKey.style.fontWeight = '600';
                tdKey.innerHTML = `${item.key} <button type="button" class="btn-copy" onclick="copyToClipboard('${item.key}')" title="Copy Key">📋</button>`;

                const tdBase = document.createElement('td');
                tdBase.textContent = item.base_value;

                const tdTrans = document.createElement('td');
                tdTrans.style.color = '#15803d';
                tdTrans.style.fontWeight = '600';
                tdTrans.textContent = item.translated_value;

                const tdAction = document.createElement('td');
                const reTransBtn = document.createElement('button');
                reTransBtn.type = 'button';
                reTransBtn.className = 'btn btn-sm scan-btn';
                reTransBtn.textContent = 'Re-translate';
                reTransBtn.onclick = () => submitTranslationJob([item.key]);
                tdAction.appendChild(reTransBtn);

                tr.appendChild(tdKey);
                tr.appendChild(tdBase);
                tr.appendChild(tdTrans);
                tr.appendChild(tdAction);

                tbody.appendChild(tr);
            });
        }

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text);
            showToast(`Copied "${text}" to clipboard!`);
        }

        function updateSelectionState() {
            const checkboxes = document.querySelectorAll('.key-checkbox');
            const checked = document.querySelectorAll('.key-checkbox:checked');
            const selectAll = document.getElementById('selectAllKeys');
            const btnSelected = document.getElementById('btnTranslateSelected');
            const selectedCountSpan = document.getElementById('selectedCount');

            selectedCountSpan.textContent = checked.length;
            btnSelected.disabled = checked.length === 0;

            if (selectAll) {
                if (checkboxes.length > 0 && checked.length === checkboxes.length) {
                    selectAll.checked = true;
                    selectAll.indeterminate = false;
                } else if (checked.length > 0) {
                    selectAll.checked = false;
                    selectAll.indeterminate = true;
                } else {
                    selectAll.checked = false;
                    selectAll.indeterminate = false;
                }
            }
        }

        function bindCheckboxEvents() {
            const checkboxes = document.querySelectorAll('.key-checkbox');
            const selectAll = document.getElementById('selectAllKeys');

            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateSelectionState);
            });

            if (selectAll) {
                selectAll.onchange = () => {
                    checkboxes.forEach(cb => cb.checked = selectAll.checked);
                    updateSelectionState();
                };
            }

            updateSelectionState();
        }

        // Tab Switching
        const tabMissingBtn = document.getElementById('tabMissingBtn');
        const tabTranslatedBtn = document.getElementById('tabTranslatedBtn');
        const tabJobsBtn = document.getElementById('tabJobsBtn');
        const viewMissing = document.getElementById('viewMissing');
        const viewTranslated = document.getElementById('viewTranslated');
        const viewJobs = document.getElementById('viewJobs');

        tabMissingBtn.onclick = () => {
            activeTab = 'missing';
            tabMissingBtn.classList.add('active');
            tabTranslatedBtn.classList.remove('active');
            tabJobsBtn.classList.remove('active');
            viewMissing.style.display = 'block';
            viewTranslated.style.display = 'none';
            viewJobs.style.display = 'none';
        };

        tabTranslatedBtn.onclick = () => {
            activeTab = 'translated';
            tabTranslatedBtn.classList.add('active');
            tabMissingBtn.classList.remove('active');
            tabJobsBtn.classList.remove('active');
            viewTranslated.style.display = 'block';
            viewMissing.style.display = 'none';
            viewJobs.style.display = 'none';
        };

        tabJobsBtn.onclick = () => {
            activeTab = 'jobs';
            tabJobsBtn.classList.add('active');
            tabMissingBtn.classList.remove('active');
            tabTranslatedBtn.classList.remove('active');
            viewJobs.style.display = 'block';
            viewMissing.style.display = 'none';
            viewTranslated.style.display = 'none';
            fetchJobsList();
        };

        async function fetchJobsList() {
            try {
                const res = await fetch('job-status.php?action=list');
                const data = await res.json();
                if (data.status === 'success' && Array.isArray(data.jobs)) {
                    renderJobsTable(data.jobs);
                    document.getElementById('countTabJobs').textContent = data.jobs.length;
                }
            } catch (err) {
                console.error('Error fetching jobs:', err);
            }
        }

        function renderJobsTable(jobs) {
            const tbody = document.getElementById('jobsTableBody');
            tbody.innerHTML = '';

            if (jobs.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; color: #94a3b8; padding: 24px;">No background jobs found.</td></tr>';
                return;
            }

            jobs.forEach(job => {
                const tr = document.createElement('tr');

                // Job ID
                const tdId = document.createElement('td');
                tdId.style.fontFamily = 'monospace';
                tdId.style.fontSize = '12px';
                tdId.style.fontWeight = '600';
                tdId.textContent = job.job_id || 'N/A';

                // Status Badge
                const tdStatus = document.createElement('td');
                const badge = document.createElement('span');
                const status = job.status || 'pending';
                badge.className = `badge badge-${status}`;
                badge.textContent = status.toUpperCase();
                tdStatus.appendChild(badge);

                // Locale / Component
                const tdMeta = document.createElement('td');
                const locale = job.locale || 'N/A';
                const fileType = job.file_type || '';
                const comp = job.component_name || '';
                tdMeta.innerHTML = `<strong>${locale}</strong> <span style="color: #64748b; font-size: 12px;">(${fileType} - ${comp})</span>`;

                // Progress
                const tdProg = document.createElement('td');
                const pct = job.progress?.percentage || 0;
                const processed = job.progress?.processed || 0;
                const total = job.progress?.total || 0;
                tdProg.innerHTML = `
                    <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 2px;">
                        <span>${processed}/${total}</span>
                        <span><strong>${pct}%</strong></span>
                    </div>
                    <div class="mini-progress-bar">
                        <div class="mini-progress-fill" style="width: ${pct}%;"></div>
                    </div>
                `;

                // Created
                const tdTime = document.createElement('td');
                tdTime.style.fontSize = '12px';
                tdTime.style.color = '#64748b';
                if (job.created_at) {
                    const date = new Date(job.created_at * 1000);
                    tdTime.textContent = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                } else {
                    tdTime.textContent = 'N/A';
                }

                // Action
                const tdAction = document.createElement('td');
                tdAction.style.whiteSpace = 'nowrap';

                const btnRerun = document.createElement('button');
                btnRerun.type = 'button';
                btnRerun.className = 'btn btn-sm btn-primary-sm';
                btnRerun.style.marginRight = '6px';
                btnRerun.textContent = '🔄 Re-run';
                btnRerun.onclick = () => rerunJob(job);

                const btnView = document.createElement('button');
                btnView.type = 'button';
                btnView.className = 'btn btn-sm scan-btn';
                btnView.textContent = 'View Log';
                btnView.onclick = () => openJobModal(job);

                tdAction.appendChild(btnRerun);
                tdAction.appendChild(btnView);

                tr.appendChild(tdId);
                tr.appendChild(tdStatus);
                tr.appendChild(tdMeta);
                tr.appendChild(tdProg);
                tr.appendChild(tdTime);
                tr.appendChild(tdAction);

                tbody.appendChild(tr);
            });
        }

        function openJobModal(job) {
            const modal = document.getElementById('jobModal');
            const title = document.getElementById('modalJobTitle');
            const details = document.getElementById('modalJobDetails');
            const logsBox = document.getElementById('modalJobLogs');
            const modalRerunBtn = document.getElementById('modalRerunBtn');

            title.textContent = `Job Details: ${job.job_id}`;
            details.innerHTML = `
                <div><strong>Status:</strong> <span class="badge badge-${job.status}">${(job.status || '').toUpperCase()}</span></div>
                <div><strong>Locale:</strong> ${job.locale || 'N/A'} | <strong>Component:</strong> ${job.component_name || 'N/A'}</div>
                <div><strong>Progress:</strong> ${job.progress?.processed || 0} / ${job.progress?.total || 0} (${job.progress?.percentage || 0}%)</div>
                <div><strong>Created At:</strong> ${job.created_at ? new Date(job.created_at * 1000).toLocaleString() : 'N/A'}</div>
                ${job.error ? `<div style="color: #b91c1c; font-weight: 600; margin-top: 4px;">Error: ${job.error}</div>` : ''}
            `;

            logsBox.textContent = Array.isArray(job.logs) ? job.logs.join('\n') : 'No logs available.';
            logsBox.scrollTop = logsBox.scrollHeight;
            modalRerunBtn.onclick = () => rerunJob(job);
            modal.style.display = 'flex';
        }

        async function rerunJob(job) {
            let path = job.project_path;
            if (!path || path.includes('nonexistent')) {
                path = document.getElementById('projectPath').value.trim() || DEFAULT_PROJECT_PATH;
            }

            let comp = job.component_name;
            if (!comp || comp === 'com_test') {
                comp = document.getElementById('componentName').value.trim() || DEFAULT_COMPONENT_NAME;
            }

            showToast(`Re-running job for ${job.locale || ''}...`);

            // Populate form fields so user can inspect or adjust inputs
            if (job.locale) document.getElementById('code').value = job.locale;
            if (job.file_type) document.getElementById('file').value = job.file_type;
            document.getElementById('projectPath').value = path;
            document.getElementById('componentName').value = comp;
            if (job.provider && document.getElementById('provider')) document.getElementById('provider').value = job.provider;
            if (job.apiKey && document.getElementById('apiKey')) document.getElementById('apiKey').value = job.apiKey;
            if (job.model && document.getElementById('model')) document.getElementById('model').value = job.model;

            const code = job.locale || document.getElementById('code').value.trim();
            const file = job.file_type || document.getElementById('file').value.trim();
            const projectPath = path;
            const componentName = comp;
            const provider = job.provider || (document.getElementById('provider') ? document.getElementById('provider').value : 'gemini');
            const apiKey = job.apiKey || (document.getElementById('apiKey') ? document.getElementById('apiKey').value.trim() : '');
            const model = job.model || (document.getElementById('model') ? document.getElementById('model').value : 'gemini-flash-lite-latest');

            const formData = { code, file, projectPath, componentName, provider, apiKey, model };
            if (job.selected_keys && job.selected_keys.length > 0) {
                formData.selected_keys = job.selected_keys;
            }

            try {
                const response = await fetch('main.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData),
                });

                const result = await response.json();

                if (result.status === 'processing' && result.job_id) {
                    document.getElementById('jobModal').style.display = 'none';
                    startJobPolling(result.job_id);
                    fetchJobsList();
                    showToast(`New job launched: ${result.job_id}`);
                } else if (result.status === 'error' || result.message) {
                    showToast(`Re-run error: ${result.message}`);
                }
            } catch (err) {
                console.error('Re-run error:', err);
                showToast('Failed to re-run job.');
            }
        }

        document.getElementById('modalCloseBtn').onclick = () => {
            document.getElementById('jobModal').style.display = 'none';
        };

        document.getElementById('jobModal').onclick = (e) => {
            if (e.target.id === 'jobModal') {
                document.getElementById('jobModal').style.display = 'none';
            }
        };

        document.getElementById('btnRefreshJobs').onclick = () => {
            fetchJobsList();
        };

        const btnClearJobs = document.getElementById('btnClearJobs');
        if (btnClearJobs) {
            btnClearJobs.onclick = async () => {
                if (confirm('Are you sure you want to clear all job history?')) {
                    try {
                        const res = await fetch('job-status.php?action=clear');
                        const data = await res.json();
                        if (data.status === 'success') {
                            showToast('Job history cleared.');
                            fetchJobsList();
                        }
                    } catch (err) {
                        console.error('Clear jobs error:', err);
                        showToast('Failed to clear job history.');
                    }
                }
            };
        }


        // Periodic job list polling if Jobs tab is active
        setInterval(() => {
            if (activeTab === 'jobs') {
                fetchJobsList();
            }
        }, 3000);

        // Fetch initial jobs count on load
        fetchJobsList();

        document.getElementById('cardMissing').onclick = () => tabMissingBtn.click();
        document.getElementById('cardTranslated').onclick = () => tabTranslatedBtn.click();


        document.getElementById('searchKeys').addEventListener('input', () => {
            if (activeTab === 'missing') {
                renderMissingKeysTable(scannedMissingItems);
            } else {
                renderTranslatedKeysTable(scannedTranslatedItems);
            }
        });

        document.getElementById('scanBtn').addEventListener('click', scanMissingKeys);

        document.getElementById('btnTranslateSelected').addEventListener('click', () => {
            const checked = Array.from(document.querySelectorAll('.key-checkbox:checked')).map(cb => cb.value);
            if (checked.length > 0) {
                submitTranslationJob(checked);
            }
        });

        let pollInterval = null;

        function startJobPolling(jobId) {
            const progressBox = document.getElementById('progressBox');
            const progressBarFill = document.getElementById('progressBarFill');
            const progressMessage = document.getElementById('progressMessage');
            const progressPercent = document.getElementById('progressPercent');
            const jobLogBox = document.getElementById('jobLogBox');
            const resultDiv = document.getElementById('result');
            const liveRemainingText = document.getElementById('liveRemainingText');

            progressBox.style.display = 'block';

            if (pollInterval) clearInterval(pollInterval);

            pollInterval = setInterval(async () => {
                try {
                    const res = await fetch(`job-status.php?job_id=${encodeURIComponent(jobId)}`);
                    const data = await res.json();

                    if (data.status === 'success' && data.job) {
                        const job = data.job;
                        const pct = job.progress.percentage || 0;
                        const processed = job.progress.processed || 0;
                        const total = job.progress.total || 0;
                        const remaining = Math.max(0, total - processed);

                        progressBarFill.style.width = pct + '%';
                        progressPercent.textContent = pct + '%';
                        liveRemainingText.textContent = `${remaining} left`;

                        progressMessage.textContent = `${job.progress.message || 'Processing...'} (${remaining} strings remaining)`;

                        // Live Realtime Parse of Translated Keys from Logs (both LLM & Cached)
                        let keysChanged = false;
                        if (Array.isArray(job.logs)) {
                            job.logs.forEach(logLine => {
                                const match = logLine.match(/(?:Translated key|Cache hit for key) '([^']+)' (?:for locale '[^']+'|\([^)]+\)): '(.*?)'(?:\s*\(from cache\))?/);
                                if (match) {
                                    const key = match[1];
                                    const transVal = match[2];

                                    if (!newlyProcessedKeys.has(key)) {
                                        newlyProcessedKeys.add(key);
                                        keysChanged = true;

                                        const idx = scannedMissingItems.findIndex(item => item.key === key);
                                        if (idx !== -1) {
                                            const missingItem = scannedMissingItems.splice(idx, 1)[0];
                                            scannedTranslatedItems.unshift({
                                                key: key,
                                                base_value: missingItem.value,
                                                translated_value: transVal
                                            });
                                        }
                                    }
                                }
                            });

                            if (keysChanged) {
                                renderMissingKeysTable(scannedMissingItems);
                                renderTranslatedKeysTable(scannedTranslatedItems);

                                document.getElementById('statMissing').textContent = scannedMissingItems.length;
                                document.getElementById('countTabMissing').textContent = scannedMissingItems.length;
                                document.getElementById('statTranslated').textContent = scannedTranslatedItems.length;
                                document.getElementById('countTabTranslated').textContent = scannedTranslatedItems.length;
                            }

                            jobLogBox.textContent = job.logs.join('\n');
                            jobLogBox.scrollTop = jobLogBox.scrollHeight;
                        }

                        if (job.status === 'completed') {
                            clearInterval(pollInterval);
                            resultDiv.innerHTML = `<p class="success">✓ ${job.progress.message || 'Translation job completed successfully!'}</p>`;
                            document.getElementById('submitButton').disabled = false;
                            updateSelectionState();
                            loadingSpinner.style.display = 'none';
                            loadingText.style.display = 'none';
                            scanMissingKeys();
                        } else if (job.status === 'failed') {
                            clearInterval(pollInterval);
                            resultDiv.innerHTML = `<p class="error-message">✕ ${job.error || 'Translation job failed.'}</p>`;
                            document.getElementById('submitButton').disabled = false;
                            updateSelectionState();
                            loadingSpinner.style.display = 'none';
                            loadingText.style.display = 'none';
                        }
                    }
                } catch (err) {
                    console.error('Polling error:', err);
                }
            }, 300);
        }

        async function submitTranslationJob(selectedKeys = null) {
            document.getElementById('code-error').textContent = '';
            document.getElementById('file-error').textContent = '';
            document.getElementById('projectPath-error').textContent = '';
            document.getElementById('componentName-error').textContent = '';

            const code = document.getElementById('code').value.trim();
            const file = document.getElementById('file').value.trim();
            const projectPath = document.getElementById('projectPath').value.trim();
            const componentName = document.getElementById('componentName').value.trim();
            const provider = document.getElementById('provider') ? document.getElementById('provider').value : 'gemini';
            const apiKey = document.getElementById('apiKey') ? document.getElementById('apiKey').value.trim() : '';
            const model = document.getElementById('model') ? document.getElementById('model').value : 'gemini-flash-lite-latest';

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

            const resultDiv = document.getElementById('result');
            resultDiv.innerHTML = '';

            const submitButton = document.getElementById('submitButton');
            const btnTranslateSelected = document.getElementById('btnTranslateSelected');
            const loadingSpinner = document.getElementById('loadingSpinner');
            const loadingText = document.getElementById('loadingText');

            submitButton.disabled = true;
            if (btnTranslateSelected) btnTranslateSelected.disabled = true;
            loadingSpinner.style.display = 'block';
            loadingText.style.display = 'block';

            try {
                const formData = { code, file, projectPath, componentName, provider, apiKey, model };
                if (selectedKeys && selectedKeys.length > 0) {
                    formData.selected_keys = selectedKeys;
                }

                const response = await fetch('main.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData),
                });

                const result = await response.json();

                if (result.status === 'processing' && result.job_id) {
                    startJobPolling(result.job_id);
                } else if (result.status === 'success') {
                    resultDiv.innerHTML = `<p class="success">${result.message}</p>`;
                    submitButton.disabled = false;
                    updateSelectionState();
                    loadingSpinner.style.display = 'none';
                    loadingText.style.display = 'none';
                    scanMissingKeys();
                } else {
                    resultDiv.innerHTML = `<p class="error-message">${result.message}</p>`;
                    submitButton.disabled = false;
                    updateSelectionState();
                    loadingSpinner.style.display = 'none';
                    loadingText.style.display = 'none';
                }
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('result').innerHTML = `<p class="error-message">An unexpected error occurred.</p>`;
                submitButton.disabled = false;
                updateSelectionState();
                loadingSpinner.style.display = 'none';
                loadingText.style.display = 'none';
            }
        }

        document.getElementById('translationForm').addEventListener('submit', function (event) {
            event.preventDefault();
            submitTranslationJob(null);
        });
    </script>
</body>
</html>