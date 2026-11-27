---
title: VCMS von Bootstrap 5 auf Tailwind CSS 4
stage: 3-build
status: done
executor: Claude Opus (Claude Code)
date: 2026-11-27
---

# Plan: Bootstrap 5 nach Tailwind CSS 4

Vorbild: loyjoy/loyjoy, `ui/TAILWIND_MIGRATION.md` und `ui/visual-regression/MAPPING.md` aus PR #3810
(Prinzip: gleiche berechnete Werte, nicht gleiche Namen) sowie die globalen Replikatklassen `ui-*` aus
`intent/2026-10-24-shadcn-new-design` (Mapping 5).

## Ziel

1. Bootstrap (CSS und JS) ist aus VCMS entfernt; Layout, Abstände und Typografie sind Tailwind-Utilities.
2. Mehrteilige, oft wiederholte Komponenten sind `ui-*`-Klassen im Layer `components`.
3. Die Seiten sehen aus wie vorher (pixelgenauer Vergleich gegen die Baseline).
4. Installationen brauchen weiterhin keinen Build-Schritt.

## Festgelegt

1. **Build**: Tailwind-CLI (`brew install tailwindcss`, v4.3), Einstieg `vendor/vcms/styles/src/tailwind.css`,
   Ausgabe `vendor/vcms/styles/vcms.css` (minifiziert, eingecheckt). `bin/build-css` baut, `bin/lint` prüft per
   `bin/build-css --check`, dass `vcms.css` aktuell ist.
2. **Kein Preflight**: Wie in loyjoy/loyjoy ersetzt ein nachgebauter Bootstrap-Reboot (`src/base.css`) den
   Preflight, weil das Layout auf den Bootstrap-Elementvorgaben beruht (Überschriften, Absätze, Listen,
   Inline-Bilder, Tabellen).
3. **Theme**: Bootstrap-Breakpoints (`sm` 576, `md` 768, `lg` 992, `xl` 1200, `2xl` 1400), Bootstrap-Grautöne
   und -Farben, `--color-dark: #333` (bisherige VCMS-Überschreibung von `text-dark`), Radien `sm/md/lg` = .25/.375/.5rem.
4. **Layer**: `theme, base, components, utilities`. Utilities gewinnen gegen alle Komponenten, wie vorher die
   Bootstrap-Utilities mit `!important`. In `components` liegen in dieser Reihenfolge: `ui-*`, die VCMS-Elementregeln
   (ehemals `bootstrap-override.css`), `screen.css`, Navigation, Kalender, Event, Bild, Person, Timeline, danach
   `custom/styles/screen.css` (per `@import ... layer(components)` in `header.php`) und Modul-Stylesheets (umschlossen
   von `@layer components`). So entscheidet innerhalb der Komponenten wie bisher Spezifität und Reihenfolge.
5. **Font Awesome** wird per `@import ... layer(base)` geladen, sonst schlüge es als ungelayertes CSS alle VCMS-Regeln.
6. **Grid**: `row` wird `grid grid-cols-12 gap-x-6`, jede Spalte bekommt eine Spannweite. Die Inhaltsbreiten sind
   identisch zu Bootstrap (Rinne 1.5rem, keine negativen Ränder nötig).
7. **JS**: Bootstrap-Bundle entfällt. Collapse und Dropdown per `data-ui-toggle` in `screen.js`, Galerie auf
   nativem `<dialog>` mit eigenem Carousel in `gallery/modal.js`.
8. **Safelist**: `@source inline()` erzeugt die gängigen Layout-Utilities (Abstände, Display, Spalten mit allen
   Breakpoints u. a.) immer, damit Markup außerhalb des Repos (Modul-`custom/`, Fremdmodule) sie nutzen kann.

## Mapping

Skalen: Bootstrap-Abstände `1=.25, 2=.5, 3=1, 4=1.5, 5=3rem`; Tailwind `1=.25, 2=.5, 4=1, 6=1.5, 12=3rem`.

| Bootstrap | Tailwind / VCMS |
| --- | --- |
| `m*-3` / `p*-3` | `m*-4` / `p*-4` |
| `m*-4` / `p*-4` | `m*-6` / `p*-6` |
| `m*-5` / `p*-5` | `m*-12` / `p*-12` |
| `m*-0..2`, `mx-auto`, `gap-2` | unverändert |
| `d-none`, `d-block`, `d-inline`, `d-flex` | `hidden`, `block`, `inline`, `flex` |
| `d-<bp>-<v>` | `<bp>:<v>`, z. B. `d-md-none` → `md:hidden` |
| `flex-shrink-0`, `flex-grow-1` | `shrink-0`, `grow` |
| `align-items-<v>`, `align-items-md-center` | `items-<v>`, `md:items-center` |
| `float-start` | `float-left` |
| `img-fluid` | `max-w-full h-auto` |
| `visually-hidden` | `sr-only` |
| `text-body-secondary` | `text-muted` |
| `text-bg-secondary` | `bg-secondary text-white` |
| `text-dark`, `text-center`, `text-end`, `border-0`, `bg-transparent`, `align-baseline`, `cursor-pointer`, `w-auto` | unverändert |
| `row` | `grid grid-cols-12 gap-x-6` |
| `row no-gutter` (Home) | `grid grid-cols-12 -mx-3` |
| `col-N` | `col-span-N` |
| `col-<bp>-N` | `col-span-12 <bp>:col-span-N` (ohne `col-N` als Basis) |
| `offset-<bp>-N` | `<bp>:col-start-(N+1)` |
| `container`, `container-fluid` | `ui-container`, `ui-container-fluid` |
| `btn`, `btn-secondary`, `btn-outline-secondary`, `btn-sm` | `ui-btn`, `ui-btn-secondary`, `ui-btn-outline-secondary`, `ui-btn-sm` |
| `btn-toolbar` | `flex flex-wrap justify-start` |
| `btn-file` | entfällt (hatte keine Regel) |
| `card`, `card-body`, `card-header`, `card-title`, `card-img` | `ui-card`, `ui-card-body`, `ui-card-header`, `ui-card-title`, `ui-card-img` |
| `alert`, `alert-success/info/danger` | `ui-alert`, `ui-alert-success/info/danger` |
| `badge` | `ui-badge` |
| `table`, `table-sm/striped/hover/bordered` | `ui-table`, `ui-table-sm/striped/hover/bordered` |
| `form-control(-sm/-plaintext)`, `form-select`, `form-check(-input/-label)`, `col-form-label` | `ui-form-control(-sm/-plaintext)`, `ui-form-select`, `ui-form-check(-input/-label)`, `ui-col-form-label` |
| `progress`, `progress-bar` | `ui-progress`, `ui-progress-bar` |
| `navbar navbar-expand-md`, `navbar-brand/-toggler/-toggler-icon/-collapse/-nav`, `nav-link`, `nav-item` | `ui-navbar`, `ui-navbar-brand/-toggler/-toggler-icon/-collapse/-nav`, `ui-nav-link`, entfällt |
| `collapse`, `dropdown(-toggle/-menu/-item)` | `ui-collapse`, `ui-dropdown(-toggle/-menu/-item)` |
| `modal ... modal-dialog`, `fade`, `carousel ... slide` | `<dialog class="ui-modal ui-modal-lg">`, entfällt, `ui-carousel*` |
| `data-bs-toggle`, `data-bs-target` | `data-ui-toggle`, `data-ui-target` |

Konstruierte Klassennamen (`'col-sm-' .$colLabel` in `LibForm`) sind durch Lookups vollständiger Namen ersetzt.

## Umsetzung

1. Inventur: rund 100 Bootstrap-Klassen in 74 Dateien, vier JS-Komponenten (Collapse, Dropdown, Modal, Carousel).
2. Codemod (Node, Scratchpad, nicht eingecheckt) über alle `class="..."`-Attribute nach der Tabelle, danach Handarbeit
   für Klassen in PHP-Variablen, mehrzeilige Attribute, Klassen-Arrays an `LibForm` und CSS-Selektoren.
3. `bootstrap-override.css` aufgelöst (Elementregeln nach `src/elements.css`, Karten- und Formularregeln in die
   `ui-*`-Klassen); `vendor/bootstrap` gelöscht.
4. Engine 14.18 → 15.00, zwölf berührte Module +0.01.

## Prüfung

1. Lokale Installation (MySQL 9, PHP 8.5, `php -S`), Baseline von `main` und Branch parallel gegen dieselbe Datenbank.
2. Playwright-Screenshots von 49 Seiten eingeloggt und 12 öffentlich, Desktop 1280 px und Mobil 390 px, dazu
   geöffnete Navbar mobil und geöffnetes Dropdown; pixelmatch gegen die Baseline.
3. Ergebnis: 112 von 125 Screenshots pixelgleich. Rest: Zeitstempel bzw. wachsende Log-Einträge (auch zwischen zwei
   Baseline-Läufen verschieden), Subpixel-Rundung Grid gegen Flex (unter 50 px), bewusste Abweichung 1 unten.
4. Galerie-Modal (öffnen, weiterblättern, Escape) Desktop und Mobil pixelgleich, keine Konsolenfehler.
5. `bin/lint`, `bin/analyse` grün.

## Abweichungen

1. `LibForm::printFileUpload`: Der Upload-Button ragte als Zeilenkind ohne Spalte in Bootstrap 0.75rem über den
   Inhaltsbereich hinaus; jetzt `col-span-12`, bündig mit den Feldern darüber.
2. Facebook-Seiten-Plugin (Home): Breite 500 px statt bisher 476 px Inhalt (Bootstrap-Spaltenpolster entfällt).
3. Installations-CSS und Modul-CSS liegen in der Layer `components` und verlieren damit gegen Utilities; vorher
   gewannen sie gegen Utilities nur dort, wo keine Bootstrap-Utility mit `!important` gesetzt war.

## Offen

1. **Kompatibilität bestehender Installationen**: Installationsspezifisches Markup in den `custom/`-Verzeichnissen der
   Module (z. B. `mod_internet_verein/custom/*.html`, wird bei Updates nicht überschrieben), eigene Module und eigenes
   `custom/styles/screen.css` mit Bootstrap-Klassen (`.nav-link`, `row`, `col-md-6`, `card`) verlieren nach dem
   Engine-Update ihre Gestaltung. Optionen: Kompatibilitäts-Stylesheet für die häufigsten Bootstrap-Klassen, oder
   Hinweis im Release mit Mapping-Tabelle.
2. Module vor der Engine aktualisiert ergeben fehlende Klassen; es gibt keinen Abhängigkeitsmechanismus. Engine zuerst.
3. Vorbestehend, nicht Teil des Umbaus: `timeline/birthdays.php` scheitert unter striktem `sql_mode` (MySQL 8+) an
   `datum_geburtstag != ''`; Formulare ohne `id` erzeugen PHP-Warnungen „array offset on false“.
