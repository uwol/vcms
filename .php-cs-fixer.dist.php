<?php
/*
This file is part of VCMS.

VCMS is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

VCMS is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with VCMS. If not, see <http://www.gnu.org/licenses/>.
*/

/*
* Code style configuration for PHP-CS-Fixer (https://cs.symfony.com).
*
* Run `bin/format` to format the code and `bin/lint` to check it.
*
* Only VCMS' own code is covered: the third-party libraries in /vendor
* (httpful, pear, phpass, phpmailer etc.) keep their upstream formatting.
*/

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/custom',
        __DIR__ . '/modules',
        __DIR__ . '/vendor/vcms',
    ])
    ->append([
        __DIR__ . '/api.php',
        __DIR__ . '/index.php',
        __DIR__ . '/installer.txt', // PHP code, renamed to installer.php on installation
    ]);

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'visibility_required' => ['elements' => ['property', 'method', 'const']],
        'control_structure_braces' => true,
        'no_multiple_statements_per_line' => false, // keeps compact switch tables like LibMime readable
        'no_unused_imports' => true,
        'no_trailing_comma_in_singleline' => true,
    ])
    ->setIndent('    ')
    ->setLineEnding("\n")
    ->setFinder($finder);
