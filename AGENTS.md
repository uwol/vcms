# Agents.md for VCMS Mono Repo

VCMS is a free content management system for German student fraternities (Korporationen),
used as a public internet presence, as an intranet solution, or both. It covers semester
planning with a semester programme, photo galleries, event registration, a duty roster
(Chargierkalender), news posts, a member database, circular emails, a reservation system
and a download area.


## Repository Structure

- `/bin/` -- Development scripts (`format`, `lint`, `analyse`, `php-files`)
- `/custom/` -- Per-installation customization; survives engine and module updates
  - `systemconfig.php` -- The `LibConfig` class: MySQL credentials, association data, offices
    abbreviations, timezone, optional semester configuration
  - `styles/` -- Installation-specific CSS (`screen.css`), panier, Open Graph image
- `/modules/` -- Optional modules, installable and removable per installation
  - `mod_internet_*` -- Public internet modules
  - `mod_intranet_*` -- Internal intranet modules
- `/vendor/` -- Third-party libraries plus the VCMS engine
  - `/vendor/vcms/` -- The VCMS engine, the only part of `/vendor` that is VCMS' own code
    - `initialize.php` -- Bootstraps autoloaders, session, all `$lib*` globals, modules,
      timezone, current semester and the authentication context
    - `autoload.php` -- PSR-4-style autoloader for namespace `vcms\`, mapping to `lib/*.class.php`
    - `lib/` -- Engine classes (`LibDb`, `LibAuth`, `LibString`, ...) plus the subnamespaces
      `calendar`, `filesystem`, `genealogy`, `menu`, `module`, `timeline`
    - `modules/base_*` -- Base modules shipped with the engine; they cannot be uninstalled
    - `layout/` -- `header.php` and `footer.php`, wrapped around every page by `index.php`
    - `styles/` -- Engine CSS and JS, including `bootstrap-override.css`
    - `install/` -- Engine install and update scripts
  - all other directories (`bootstrap`, `jquery`, `phpmailer`, `phpass`, `pear`, `httpful`,
    `blueimp-file-upload`, `chart`, `fonts`, `hover`, `scrollreveal`) -- third-party code.
    Keep the upstream formatting, do not edit, replace whole releases instead.
- `index.php` -- Front controller for pages, selected by the request parameter `pid`
- `api.php` -- Front controller for includes, selected by the request parameter `iid`
- `installer.txt` -- Installer; renamed to `installer.php` for installation and deleted afterwards.
  It is PHP code and is covered by `bin/lint`, but not by PHPStan, which selects files by extension.
- `composer.json` -- Documents dependency versions only. `/vendor` is committed and composer is
  not executed as part of the build.


## Environment Setup

- **PHP**: 8.4+
- **MySQL**: 5+
- **ImageMagick or GDlib**: required for photo galleries
- **Tooling**: `brew install php php-cs-fixer phpstan`

There is no build step, no dependency installation and no automated test suite. Verification is
`bin/lint`, `bin/analyse` and manual testing in the browser against a local installation.


## Development Workflow

```
bin/format
bin/lint
bin/analyse
```

1. `bin/format` formats VCMS' own code according to `.php-cs-fixer.dist.php` (PSR-12).
2. `bin/lint` checks syntax (`php -l`) and code style without changing files.
3. `bin/analyse` checks semantics with PHPStan (`phpstan.dist.neon`).
4. Bump the affected versions (see *Versioning*).

`bin/php-files` prints all PHP files belonging to VCMS itself and is kept in sync with the finder
in `.php-cs-fixer.dist.php`. When adding a new top-level PHP file, add it to both.


## Check List

### Basic Quality Standards

- Comments MUST be in English. German comments are not allowed; translate the ones you touch.
- Comment style: the comment text starts with a capital letter, and `//` comments have a space
  after the marker, e.g. `// Relax sql_mode so that ...` (never `//relax sql_mode ...`). 
  The first word of `/* ... */` and `/** ... */` blocks is capitalized, too.
- Identifiers (classes, methods, functions, variables, file names) MUST be in English.
- Database tables and columns are German legacy (`base_person.gruppe`, `base_veranstaltung.titel`)
  and MUST NOT be renamed. Map them to English variable names in the PHP code.
- Page ids (`pid`) and include ids (`iid`) are part of the public URL space and MUST NOT be renamed
  either, even where they are German.
- All user-facing texts MUST be German and free of typos.
- German: `E-Mail` (not `Email`), `Adresse` (not `Addresse`).
- Intranet pages duzen (`Willst Du den Datensatz wirklich löschen?`), public internet pages
  such as registration and privacy siezen.
- Every PHP file MUST start with the GPL header block. The only exceptions are
  `custom/systemconfig.php` and `vendor/vcms/layout/header.php`.
- All text files use LF line endings, enforced by `.gitattributes`.


### Versioning

- Changing anything in `/vendor/vcms/` MUST bump `$version` in
  `/vendor/vcms/lib/LibGlobal.class.php`, e.g. `12.21` -> `12.22`. The engine version is compared
  as a float against the repository manifest by `LibRepositoryClient`.
- Changing anything in a module under `/modules/` MUST bump `version` in that module's `meta.json`,
  e.g. `3.06` -> `3.07`. Bump every module that was touched.
- Module `version` MUST be numeric, not a string, otherwise `LibModuleParser` reports an error.
- Base modules in `/vendor/vcms/modules/` have no own version; they ship with the engine.


### PHP Language

- `bin/lint` MUST pass.
- `bin/analyse` MUST pass (PHPStan level 0).
- Known PHPStan findings live in `phpstan-baseline.neon`. Do not edit that file by hand; regenerate
  it with `bin/analyse --generate-baseline=phpstan-baseline.neon`. It should shrink over time,
  not grow. Never add a new finding to the baseline to make the build pass -- fix the code.
- Code style beyond PSR-12: short array syntax, explicit visibility on properties, methods and
  constants, no unused imports, four spaces indentation.
- The engine is object-oriented, the module scripts are procedural. Engine classes declare
  `namespace vcms;` (or a subnamespace) and are named `Lib*.class.php`; the autoloader maps
  `vcms\foo\LibBar` to `lib/foo/LibBar.class.php`. A new subnamespace needs a new subdirectory.
- Module scripts use the `$lib*` globals created by `initialize.php`. Inside functions and methods,
  declare them explicitly: `global $libDb, $libGlobal, $libString;`.
- Every module script MUST start with a guard clause so that it cannot be requested directly:
  `if (!is_object($libGlobal)) { exit(); }` for public pages, and
  `if (!is_object($libGlobal) || !$libAuth->isLoggedin()) { exit(); }` for everything internal.
- `index.php` connects to the database for pages. Includes served by `api.php` MUST call
  `$libDb->connect()` themselves.
- Use `LibString`/`LibTime` helpers instead of reimplementing them: `protectXSS`, `isValidEmail`,
  `isValidURL`, `assureHttpScheme`, `randomAlphaNumericString`, `assureMysqlDate`,
  `assureMysqlDateTime`, `formatDateString`.


### Security

- Escape on output, not on input. Every value that reaches HTML MUST go through
  `$libString->protectXSS($value)`; the database stores raw text. `xmlentities` exists for XML
  output only.
- SQL MUST use prepared statements: `$libDb->prepare()` plus `bindValue()`, with
  `PDO::PARAM_INT` for numeric ids. Request data MUST NEVER be concatenated into SQL.
- Mutating actions (insert, update, delete, send) MUST use POST, not GET.
- Access control is declared in `meta.json` via `accessRestriction` and enforced by
  `LibSecurityManager::hasAccess()`. The script guard clause is defense in depth and is required
  in addition, not instead.
- Offices in an `accessRestriction` MUST exist in `LibSecurityManager::$possibleOffices`;
  otherwise the whole module is rejected on load with an error message.
- Groups in an `accessRestriction` are single characters from `base_gruppe`:
  `F` Fuchs, `B` Bursche, `P` Philister, `T` verstorbenes Mitglied, `C` Couleurdame, `G` Gattin,
  `W` Witwe, `V` verstorbene Gattin, `Y` Vereinsfreund, `X` ausgetreten.
- Passwords go through `LibAuth` (`savePassword`, `checkPassword`, `isValidPassword`), which uses
  phpass. Never hash or compare passwords manually.
- Use `random_int`, never `rand` or `mt_rand`.
- File uploads go through `LibImage::save*ByFilesArray()`. Paths derived from request data MUST be
  checked with `LibImage::checkDirectoryEscape()`.
- Sessions are configured centrally in `initialize.php` (`samesite=Strict`, `httponly`, `secure`).
  Do not set session parameters in module scripts.


### Database

- MySQL via PDO, wrapped by `LibDb`; charset utf8 throughout.
- Table name prefixes: `base_` for engine domain data, `sys_` for system tables,
  `mod_<module>_` for module-owned tables.
- Table creation belongs in the module's `install/install.php`, schema changes in
  `install/update.php`. Both start with the `if (!is_object($libGlobal)) { exit(); }` guard and
  run with `$libGlobal` and `$libDb` provided by the module manager.
- Update scripts MUST be idempotent, because they run on every module update: use
  `INSERT IGNORE`, inspect `SHOW COLUMNS` before `ALTER TABLE`, and guard helper function
  definitions with `function_exists()`.
- `date` and `datetime` columns MUST be nullable without a default. Zero dates
  (`0000-00-00`) MUST NOT be introduced; existing ones are migrated to `NULL`.
- PDO runs in silent error mode, so failures are invisible. Check statement results in install and
  update scripts and report problems via `$libGlobal->errorTexts[]`.
- Configuration values that belong to the installation rather than to a file live in
  `sys_genericstorage` and are accessed through `$libGenericStorage` (`loadValue`, `saveValue`,
  `loadArray`, `saveArrayValue`, ...). Do not add new config properties to `LibConfig` for values
  that administrators should edit in the intranet.
- Intranet actions are logged to `sys_log_intranet` with a numeric `aktion` code; the codes are
  decoded in `modules/mod_intranet_log/scripts/log.php`. A new logged action needs a new code
  there as well.
- Recurring maintenance work belongs in `LibCronjobs`, which is executed once per day from
  `index.php` via `executeDueJobs()`.


### Module System

`meta.json` is the module manifest and is parsed by `LibModuleParser`. Keys:

- `moduleName` -- required, German display name
- `version` -- numeric, required for modules in `/modules/`
- `installScript`, `updateScript`, `uninstallScript` -- paths relative to the module directory
- `pages` -- `pid`, `directory`, `file`, `title`, optional `accessRestriction`,
  optional `containerEnabled` (default `true`)
- `includes` -- `iid`, `directory`, `file`, optional `accessRestriction`; served by `api.php` and
  used for binary output, CSV, iCalendar and AJAX
- `accessRestriction` -- `{"gruppen": [...], "aemter": [...]}`; German keys, both optional
- `menuElementsInternet`, `menuElementsIntranet`, `menuElementsAdministration` -- `type` is
  `menu_entry`, `menu_entry_login`, `menu_entry_external_link` or `menu_folder`; `position` sorts
  ascending and defaults to `65535`
- `headerStrings` -- strings echoed verbatim into `<head>`

Rules:

- `pid` and `iid` MUST be globally unique across all modules. A collision silently disables the
  module that is loaded later, so check the whole repository before choosing an id.
- A menu element of type `menu_entry` or `menu_entry_login` MUST reference a `pid` defined in the
  same module, otherwise the module is rejected with an error message.
- New pages and includes MUST be registered in `meta.json`; there is no automatic scanning of the
  `scripts` directory.
- A module's own `custom/` directory is preserved across updates, everything else in the module
  directory is replaced. Installation-specific files (header images, texts) belong there.
- Deleting a module directory removes the module. The intranet module manager
  (`base_intranet_modulemanager`) installs, updates and uninstalls modules via
  `LibRepositoryClient`.

A typical module is structured as follows:

```
/modules/mod_intranet_news/
├── meta.json
├── custom/                  -- optional, preserved across updates
├── install/
│   ├── install.php          -- CREATE TABLE and default records, run once
│   └── update.php           -- idempotent schema migrations, run on every update
└── scripts/
    ├── news.php             -- page, handles its own $_POST and renders output
    ├── write.php            -- page
    └── admin/               -- optional, pages restricted to offices
```


### Frontend

- Bootstrap 3 markup (`panel`, `form-horizontal`, `col-sm-*`, `thumbnail`), jQuery 2,
  Font Awesome 4.
- Bootstrap 4/5 spacing utilities (`mb-*`, `mt-*`, `pb-*`, `pt-*`, `d-inline`) are backported in
  `vendor/vcms/styles/bootstrap-override.css` and may be used.
- Use as few own CSS rules as possible and as many Bootstrap classes as possible. Engine styles
  belong in `vendor/vcms/styles/`, installation styles in `custom/styles/screen.css`.
- Forms MUST be built with the `LibForm::print*` helpers (`printTextInput`, `printTextarea`,
  `printDateInput`, `printMembersDropDownBox`, `printSubmitButton`, ...) so that markup and
  escaping stay consistent.
- Query results rendered as HTML tables or exported as CSV go through `LibTable`.
- Errors and notifications are collected in `$libGlobal->errorTexts[]` and
  `$libGlobal->notificationTexts[]` and rendered with `$libString->getErrorBoxText()` and
  `$libString->getNotificationBoxText()` at the top of the page output.
- HTML is echoed from PHP; keep the existing one-`echo`-per-line style of the surrounding file.
- URL parameters in HTML output use `&amp;`, matching the `arg_separator.output` setting in
  `initialize.php`.
- All debug output (`var_dump`, `print_r`, `console.log`) MUST be removed before committing.
