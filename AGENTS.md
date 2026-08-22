# Agents.md for VCMS Mono Repo

## Module System

- When updating modules in /modules, make sure to update the meta.json
- When updating files in /vendor, make sure to update /vendor/vcms/lib/LibGlobal.class.php

## Code Style

- PHP code follows PSR-12, enforced by PHP-CS-Fixer (`.php-cs-fixer.dist.php`)
- Run `bin/format` before committing PHP changes, and `bin/lint` to verify
- Third-party libraries in /vendor (except /vendor/vcms) are excluded from formatting
