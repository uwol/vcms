# Agents.md for VCMS Mono Repo

## Module System

- When updating modules in /modules, make sure to update the meta.json
- When updating files in /vendor, make sure to update /vendor/vcms/lib/LibGlobal.class.php

## Code Style

- PHP code follows PSR-12, enforced by PHP-CS-Fixer (`.php-cs-fixer.dist.php`)
- Run `bin/format` before committing PHP changes, and `bin/lint` to verify
- Third-party libraries in /vendor (except /vendor/vcms) are excluded from formatting

## Static Analysis

- Run `bin/analyse` before committing PHP changes (PHPStan, `phpstan.dist.neon`)
- It is a separate step from `bin/lint`: `bin/lint` checks syntax and style,
  `bin/analyse` checks semantics (undefined variables, unknown methods, argument counts)
- Known findings live in `phpstan-baseline.neon`. Do not edit that file by hand;
  regenerate it with `bin/analyse --generate-baseline=phpstan-baseline.neon`.
  It should shrink over time, not grow
- Never add a new finding to the baseline to make the build pass — fix the code
