---
name: php-quality-gate
description: Generate and modify PHP code that passes project static analysis and coding standards. Use when creating or editing PHP files in this repository, especially under api/, and when the user asks for Symfony, PHPStan, or PHPCS compliant changes.
---

# PHP Quality Gate

## Scope

Apply this skill for any PHP file changes in this project. The API code lives in `api/` and must follow:
- PHPStan config: `api/phpstan.neon`
- PHPCS config: `api/phpcs.xml.dist`

## Required Workflow

1. Implement PHP changes.
2. Run PHPCS for changed PHP files first (faster feedback).
3. Run PHPStan analysis after PHPCS passes.
4. If a check fails, fix code and rerun until passing.
5. In the final response, report which commands were run and whether they passed.

## Commands

Run from `api/`:

```bash
composer phpcs -- src/App src/Context tests
composer phpstan -- src/App src/Context tests
```

If only specific files were changed, prefer targeted checks first:

```bash
./vendor/bin/phpcs -sp --standard=phpcs.xml.dist path/to/ChangedFile.php
./vendor/bin/phpstan analyse --configuration=phpstan.neon --memory-limit=1g path/to/ChangedFile.php
```

Then run broader checks before finishing substantial PHP work.

## Symfony + Project Conventions

- Keep strict typing and clear value objects where appropriate.
- Prefer small, focused services and explicit dependencies.
- Avoid introducing baseline ignores unless explicitly requested.
- Do not weaken PHPStan or PHPCS rules to make code pass.

## Failure Handling

- If tooling is unavailable (missing dependencies, env issues), state exactly what failed and provide the precise command for the user to run locally.
- Do not claim compliance unless the checks were actually executed successfully.
