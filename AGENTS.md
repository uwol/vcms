# VCMS Developer Notes

## Overview
VCMS is a PHP-based dual-area CMS (public internet + internal intranet) for German student corporations (Korportionen/Studentenverbindungen). There are no tests, linting, or CI. The codebase is monolithic procedural PHP (~8.0+) with module-driven page rendering. Traditional LAMP stack (Apache + PHP + MySQL). German-language codebase and UI.

## Architecture
**Engine:** `vendor/vcms/` — the core VCMS engine and its bundled modules (`base_core`, `base_intranet_dbadmin`, etc.)
** Pre-installed third-party deps from composer are in `vendor/`. **Do not edit vendor code, except for `vendor/vcms/`**

**User modules:** `modules/mod_*` — custom userland modules. Split into Internet (public) and Intranet (private). Modules register pages via `meta.json` within their directory. Each module has:
- `meta.json` — page routes, menu entries (internet/intranet), version
- `scripts/` — PHP files serving as pages/resources
- `install/` — install/update scripts run once
- `custom/`, `styles/` — static assets

Modules load based on `$menuElementsInternet` or `$menuElementsIntranet` keys in `meta.json`. Pages map to `file` entries with a `pid`/route key.

**Config:** `custom/systemconfig.php` — the only deploy-time config file (`LibConfig` class, DB credentials, timezone, semester month mapping, organization details). Uses `var` (no visibility modifiers) — legacy PHP style.

**Services are globals**: After `initialize.php` loads, all services are global variables: `$libDb`, `$libAuth`, `$libConfig`, `$libModuleHandler`, `$libGlobal`, `$libCronjobs`, etc. Use them directly in modules — no dependency injection.

**Session data:** `PhpSessionData/` directory. **Must exist and be writable.**

## Setup / deployment
1. Run `composer install` (PHP 8+, Composer 2.7+). No dev dependencies, no build/test/lint targets.
2. Edit `custom/systemconfig.php` with real DB credentials, org name, etc.
3. Rename `installer.txt` → `installer.php`, visit in browser to create DB schema + initial "Internetwart" admin account, then delete `installer.php`.
4. Ensure `PhpSessionData/` directory exists and is writable by the web server.
5. Design: edit colors/layouts in `custom/styles/*.css`, home page hero image at `modules/mod_internet_home/custom/header.webp`.

## Autoloading
Custom PSR-4-style autoloader in `vendor/vcms/autoload.php`: namespace `vcms\` maps to `vendor/vcms/lib/` with a **`.class.php`** suffix (not plain `.php`). Do not rename files when refactoring — the `.class.php` extension is mandatory.

## Initialization Flow
`index.php` → requires `custom/systemconfig.php` → requires `vendor/vcms/initialize.php` which:
1. Registers autoloaders
2. Starts PHP session (file-based, 3-day TTL)
3. Instantiates all `$lib*` globals
4. Calls `$libModuleHandler->initModules()` to scan `modules/` and `vendor/vcms/modules/`

All libraries are instantiated as global variables (`$libDb`, `$libAuth`, etc.) inside `initialize.php`. No DI container.

## Style Conventions
- Classes under `vendor/vcms/lib/` use the `Lib*` prefix (e.g., `LibDb`, `LibAuth`).
- No namespaces in older code; library classes are namespaced since migration. Mixed style exists — prefer existing convention per directory.
- Old-style PHP property declarations (`var $x`) used in config files.

## Module System
Each module directory contains a `meta.json` declaring:
- `pages` — routeable pages via `pid`, each mapping to a PHP file under `scripts/`.
- `includes` — AJAX endpoints via `iid`, served by `api.php`.
- `menuElementsInternet` / `menuElementsIntranet` — navigation entries.

Modules are named `mod_internet_*` (public) or `mod_intranet_*` (intranet). See `INSTALL.md` for removing unused modules.

## Working with modules
**Adding a new module:**
- Create `modules/mod_<area>_<name>/` directory
- Write `meta.json` with module name, version, pages (under `"pages"` and/or `"includes"`), menu entries (`"menuElementsInternet"` / `"menuElementsIntranet"`), and any install scripts.

**Disabling a module:** Delete its directory or uninstall via the Intranet Modul-Manager admin panel. Internet modules are separate — to run as intranet-only, delete all `mod_internet_*` directories.

**Page routing:** A page's URL is determined by the `pid` in `meta.json`. The file and subdirectory are resolved from the module root.

**Module dependencies:** modules can include other modules' scripts via `"includes"`. Use the admin interface (Intranet → Modul-Manager) to check installed/uninstalled state.

## Versioning
- Engine + module versions are in `manifest.json` under the engine key (`"engine": 24.01`).
- The version should consist of the year and an increment starting with `01`, e.g. `24.01` is the first version of the year 2024

## Known gotchas
- **No test suite, linter, or formatter** exists. Verification is manual via browser. Run `php -l <file>` for syntax checks.
- **hCaptcha must be configured** — cannot currently be disabled; required for registration and password reset.
- **PHP 8 compatibility** — the codebase uses no typed properties, visibility modifiers, or strict types. Avoid introducing modern PHP features in engine code (`vendor/`). Stick to existing style within `modules/`.
- `custom/systemconfig.php` instantiates `LibConfig` using bare `var` properties — do not rewrite as typed properties; the engine's global variable binding depends on it.
- Engine exposes its classes in the `\vcms\` namespace (e.g. `new \vcms\LibAuth()`).
- **Font Awesome:** currently bundled as v4 via composer; TODO list mentions migration to Bootstrap Icons (see `TODO.md`).
- **MPDF patching** is needed for font embedding — see `TODO.md` for tracking.
- **Semester mapping:** By default, WS = months 0,1,9,10,11 and SS = months 3,4,5,6,7,8 (Oct–Mar / Apr–Sep). Override via `$semestersConfig` in `systemconfig.php`.
- **Deployment**: Use Binary mode if uploading via FTP.
- **Design**: Styles are managed in `custom/styles`, and the header image is at `modules/mod_internet_home/custom/header.webp`.

## Maintenance priorities (per TODO.md)

- Upgrade packages: chartjs v2→v4, font-awesome v4→v6, scrollreveal, phpass, blueimp-file-upload
- Replace `libre-franklin.zip` with fontsource/npm package
- Remove external deps from repo (move to composer), including Libre Franklin bundle and patched phpass
- Consider Nginx support, Keycloak auth integration, 2FA/passkeys
- Fix known bugs: hCaptcha disable, responsive menu width gap (992–1199px), aria-hidden attributes

## Code Layout
| Path | Role |
|---|---|
| `vendor/vcms/` | Project framework libraries and base modules (namespace `vcms\`). **Not** third-party — it's the app engine. |
| `vendor/<rest>/` | Third-party deps managed by composer. |
| `modules/` | Feature modules, one per directory (`mod_*_name`). |
| `custom/systemconfig.php` | Only config file — defines class `LibConfig`. Must exist before the app runs. |
| `PhpSessionData/` | File-based session storage (session save path set at runtime). |

### Entry Points
- `index.php?pid=<page_id>` — main web router (public + intranet pages)
- `api.php?iid=<include_id>` — AJAX/API include endpoint

## Quick references

| Location | Purpose |
|---|---|
| `index.php` | Application entry point |
| `api.php` | API entry point |
| `vendor/vcms/autoload.php` | Class autoloading |
| `vendor/vcms/initialize.php` | Session, config, init bootstrap |
| `custom/systemconfig.php` | DB + org configuration |
| `PhpSessionData/` | PHP session files directory |
| `composer.json` / `composer.lock` | Dependencies (managed via composer-asset-plugin) |
| `manifest.json` | Module version tracking |
