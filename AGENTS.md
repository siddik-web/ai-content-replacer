# AGENTS.md - AI Coding Agent Guidelines

## Overview & Architecture

`ai-content-replacer` is an automated translation tool for Joomla language files powered by the Ollama API (local LLM integration). It parses Joomla `.ini` language files for site and administrator interfaces, submits translation prompts to Ollama for target languages/locales, and writes the translated output files.

### Core Structure

- `src/TranslationService.php`: Locates, parses, and manages Joomla site and administrator `.ini` language files.
- `src/OllamaApi.php`: Client wrapper for Ollama API communication, prompt formatting, and error handling.
- `src/ContentReplacer.php`: Core translation engine orchestrating content parsing, batching, LLM invocation, and output generation.
- `src/Util.php`: Helper functions for logger initialization (`Monolog`), file paths, and general utility methods.
- `src/Exceptions/`: Custom application exceptions.
- `app.php` (`TranslationApp`): Application runner handling request logic, path validation, and processing options.
- `index.php`: Web UI & AJAX API endpoint server.
- `main.php`: CLI entry point for command-line translation tasks.
- `config.php`: System constants and application defaults.

---

## Technical Stack & Environment

- **PHP**: 7.4+ / 8.x (PSR-4 autoloading with namespace `App\`)
- **Package Manager**: Composer
- **Node.js Framework**: Playwright (E2E tests)
- **Unit Testing Framework**: PHPUnit 10+ (`tests/`)
- **E2E Testing Framework**: `@playwright/test` (`tests/e2e/`)
- **Dependencies**: `ardagnsrn/ollama-php`, `monolog/monolog`, `symfony/cache`, `vlucas/phpdotenv`, `guzzlehttp/guzzle`

---

## Quick Reference Commands

### Setup & Installation
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies & Playwright browsers
npm install
npx playwright install chromium

# Copy environment configuration
cp .env.example .env
```

### Execution
```bash
# Start Web Development Server
php -S localhost:8080

# Execute CLI Tool
php main.php
```

### Testing Suite

#### PHP Unit Tests
```bash
# Run unit tests
./vendor/bin/phpunit
```

#### Playwright E2E Tests
```bash
# Run all end-to-end tests
npm test

# Run individual test suites
npm run test:form      # Form interface & validation tests
npm run test:api       # API endpoint & server tests
npm run test:workflow  # Full translation workflow tests
npm run test:utils     # UI & helper function tests

# Interactive / Debugging
npm run test:headed    # Run tests in headed browser mode
npm run test:debug     # Step-by-step debug mode
npm run test:ui        # Interactive Playwright Test UI
```

---

## Development Standards & Conventions

### PHP Guidelines
1. **Namespace**: All classes in `src/` must use `App\` namespace matching PSR-4 standards.
2. **Type Hinting**: Provide explicit parameter and return type declarations for methods.
3. **Logging**: Use Monolog instances built via `Util::createLogger()`.
4. **Exception Handling**: Throw explicit domain exceptions (`InvalidArgumentException`, custom classes in `App\Exceptions`) instead of generic exceptions.
5. **Data Preservation**: Perform safe, non-destructive file writes when reading and writing `.ini` translation files.

### Testing Guidelines
1. Place PHP unit tests in `tests/` following the `*Test.php` naming standard.
2. Maintain E2E specification files in `tests/e2e/*.spec.js`.
3. Always verify changes by running `./vendor/bin/phpunit` and `npm test` before declaring tasks complete.

---

## Agent Workflows

- Always check current code definitions and signatures using `view_file` or `grep_search` before making edits.
- Never modify `.env` sensitive credentials; ensure new environment settings are documented in `.env.example`.
- Run tests (`./vendor/bin/phpunit`, `npm test`) to confirm zero regressions after making code changes.
