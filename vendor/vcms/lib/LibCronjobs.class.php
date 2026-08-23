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

namespace vcms;

class LibCronjobs
{
    public $filesToDelete = ['.gitignore', 'composer.json', 'inc.php',
        'installer.php', 'installer2.php', 'installer3.php', 'installer.txt',
        'Installationsanleitung.html', 'INSTALLATIONSANLEITUNG.txt', 'INSTALL.md',
        'LICENSE', 'LICENSE.txt', 'README.md'];

    public $directoriesToDelete = ['design', 'js', 'lib', 'styles',
        'modules/base_core', 'modules/base_internet_login',
        'modules/base_internet_vereine', 'modules/base_intranet_administration_dbverwaltung',
        'modules/base_intranet_dbadmin', 'modules/base_intranet_home', 'modules/base_intranet_personen',
        'modules/base_updatemanager', 'modules/mod_intranet_administration_export'];

    public $directoriesToCreate = ['temp', 'custom/styles', 'custom/intranet',
        'custom/intranet/downloads', 'custom/intranet/mitgliederfotos',
        'custom/semestercover', 'custom/veranstaltungsfotos'];

    public $directoriesWithHtaccessFile = ['custom/intranet',
        'custom/veranstaltungsfotos', 'temp', 'vendor/httpful', 'vendor/pear',
        'vendor/phpass', 'vendor/phpmailer', 'vendor/vcms/install', 'vendor/vcms/layout',
        'vendor/vcms/lib', 'vendor/vcms/modules'];

    /*
    * Spalten, die früher beim Schreiben durch protectXSS() liefen und daher
    * HTML-encodiert in der Datenbank stehen. Siehe migrateLegacyHtmlEntities().
    *
    * Bewusst nicht enthalten:
    * - password_hash und validationkey (Geheimnisse, wurden nie encodiert)
    * - ipadresse (wurde nie encodiert)
    * - Spalten, die als Wert-Fremdschlüssel dienen (base_gruppe.bezeichnung,
    *   base_status.bezeichnung, base_person.status, semester-Spalten). Sie enthalten
    *   nur kurze, validierte Bezeichner ohne & < >, ein Dekodieren würde aber die
    *   Verknüpfung zwischen den Tabellen aufbrechen.
    */
    public $legacyEncodedColumns = [
        'base_status' => ['beschreibung'],
        'base_region' => ['bezeichnung'],
        'base_gruppe' => ['beschreibung'],
        'base_person' => ['anrede', 'titel', 'rang', 'vorname', 'praefix', 'name',
            'suffix', 'geburtsname', 'zusatz1', 'strasse1', 'ort1', 'plz1', 'land1',
            'telefon1', 'zusatz2', 'strasse2', 'ort2', 'plz2', 'land2', 'telefon2',
            'mobiltelefon', 'email', 'skype', 'webseite', 'beruf', 'tod_ort',
            'spitzname', 'vita', 'bemerkung'],
        'base_veranstaltung' => ['titel', 'spruch', 'beschreibung', 'ort', 'fb_eventid'],
        'base_verein' => ['name', 'kuerzel', 'titel', 'rang', 'dachverband', 'zusatz1',
            'strasse1', 'ort1', 'plz1', 'land1', 'telefon1', 'webseite', 'wahlspruch',
            'farbenstrophe', 'farbenstrophe_inoffiziell', 'fuchsenstrophe', 'bundeslied',
            'farbe1', 'farbe2', 'farbe3', 'farbe4', 'beschreibung'],
        'base_vip' => ['praefix', 'name', 'suffix', 'vorname', 'anrede', 'titel', 'rang',
            'zusatz1', 'strasse1', 'plz1', 'ort1', 'land1', 'telefon1', 'status',
            'grund', 'bemerkung'],
        'sys_genericstorage' => ['value'],
        'mod_internethome_nachricht' => ['text'],
        'mod_chargierkalender_veranstaltung' => ['beschreibung'],
        'mod_news_kategorie' => ['bezeichnung'],
        'mod_news_news' => ['text'],
        'mod_reservierung_reservierung' => ['beschreibung'],
    ];

    public function executeDueJobs()
    {
        global $libDb;

        $stmt = $libDb->prepare('SELECT COUNT(*) AS number FROM sys_log_intranet WHERE aktion = 10 AND DATEDIFF(NOW(), datum) < 1');
        $stmt->execute();
        $stmt->bindColumn('number', $numberOfCronJobExecutionsToday);
        $stmt->fetch();

        if ($numberOfCronJobExecutionsToday == 0) {
            $libDb->query('INSERT INTO sys_log_intranet (aktion, datum) VALUES (10, NOW())');

            $this->executeJobs();

            if (date('j') == 1) {
                $this->setGalleryPublicityLevels();
            }
        }
    }

    public function executeJobs()
    {
        global $libGenericStorage, $libDb;

        $this->deleteFiles();
        $this->deleteDirectories();
        $this->createMissingDirectories();
        $this->createHtaccessFiles();
        $this->cleanSysLogIntranet();
        $this->initConfiguration();
        $this->migrateLegacyHtmlEntities();

        if ($libGenericStorage->loadValue('base_core', 'delete_ausgetretene') == 1) {
            $this->cleanBasePerson();
        }
    }

    public function getDirectoriesToCreate()
    {
        return $this->directoriesToCreate;
    }

    public function getDirectoriesWithHtaccessFile()
    {
        return $this->directoriesWithHtaccessFile;
    }

    public function deleteFiles()
    {
        global $libFilesystem;

        foreach ($this->filesToDelete as $fileToDelete) {
            $fileAbsolutePath = $libFilesystem->getAbsolutePath($fileToDelete);

            if (is_file($fileAbsolutePath)) {
                unlink($fileAbsolutePath);
            }
        }
    }

    public function deleteDirectories()
    {
        global $libFilesystem;

        foreach ($this->directoriesToDelete as $directoryRelativePath) {
            $directoryAbsolutePath = $libFilesystem->getAbsolutePath($directoryRelativePath);

            if (is_dir($directoryAbsolutePath)) {
                $libFilesystem->deleteDirectory($directoryRelativePath);
            }
        }
    }

    public function createMissingDirectories()
    {
        global $libFilesystem;

        foreach ($this->directoriesToCreate as $relativeDirectoryToCreate) {
            $directoryAbsolutePath = $libFilesystem->getAbsolutePath($relativeDirectoryToCreate);

            if (!is_dir($directoryAbsolutePath)) {
                @mkdir($directoryAbsolutePath);
            }
        }
    }

    public function createHtaccessFiles()
    {
        global $libFilesystem;

        foreach ($this->directoriesWithHtaccessFile as $directoryRelativePath) {
            $this->createHtaccessFile($directoryRelativePath);
        }

        $files = array_diff(scandir('modules'), ['.', '..']);

        foreach ($files as $file) {
            if (is_dir('modules/' .$file)) {
                $moduleRelativePath = 'modules/' .$file;
                $moduleAbsolutePath = $libFilesystem->getAbsolutePath($moduleRelativePath);

                if (is_dir($moduleAbsolutePath. '/scripts')) {
                    if (!$this->hasHtaccessDenyFile($moduleAbsolutePath. '/scripts')) {
                        $this->generateHtaccessDenyFile($moduleAbsolutePath. '/scripts');
                    }
                }

                if (is_dir($moduleAbsolutePath. '/install')) {
                    if (!$this->hasHtaccessDenyFile($moduleAbsolutePath. '/install')) {
                        $this->generateHtaccessDenyFile($moduleAbsolutePath. '/install');
                    }
                }
            }
        }
    }

    public function createHtaccessFile($directoryRelativePath)
    {
        global $libFilesystem;

        $directoryAbsolutePath = $libFilesystem->getAbsolutePath($directoryRelativePath);

        if (!$this->hasHtaccessDenyFile($directoryAbsolutePath)) {
            $this->generateHtaccessDenyFile($directoryAbsolutePath);
        }
    }

    public function cleanSysLogIntranet()
    {
        global $libDb;

        $libDb->query('DELETE FROM sys_log_intranet WHERE datum IS NULL OR DATEDIFF(NOW(), datum) > 90');
    }

    public function cleanBasePerson()
    {
        global $libDb;

        $libDb->query("UPDATE base_person SET zusatz1=NULL, strasse1=NULL, ort1=NULL, plz1=NULL, land1=NULL, datum_adresse1_stand=NULL, zusatz2=NULL, strasse2=NULL, ort2=NULL, plz2=NULL, land2=NULL, datum_adresse2_stand=NULL, region1=NULL, region2=NULL, telefon1=NULL, telefon2=NULL, mobiltelefon=NULL, email=NULL, skype=NULL, webseite=NULL, datum_geburtstag=NULL, beruf=NULL, heirat_partner=NULL, heirat_datum=NULL, tod_datum=NULL, tod_ort=NULL, status=NULL, spitzname=NULL, vita=NULL, bemerkung=NULL, password_hash=NULL, validationkey=NULL WHERE gruppe='X' AND (datum_gruppe_stand = '0000-00-00' OR datum_gruppe_stand IS NULL OR DATEDIFF(NOW(), datum_gruppe_stand) > 30)");
    }

    /**
    * Einmalige Datenmigration: Bis zur Umstellung auf Escaping bei der Ausgabe wurden alle
    * Werte beim Schreiben durch protectXSS() geschickt und stehen daher HTML-encodiert in
    * der Datenbank. Da jetzt zusätzlich bei der Ausgabe escaped wird, würden sie doppelt
    * encodiert angezeigt ("Müller &amp;amp; Sohn"). Diese Methode macht die alte
    * Encodierung genau einmal rückgängig.
    *
    * Sie darf nicht mehrfach laufen, weil ein zweiter Durchgang einen vom Benutzer wörtlich
    * eingegebenen Text wie "&amp;amp;" zu "&amp;" verkürzen würde. Deshalb der Schalter im
    * Generic Storage.
    */
    public function migrateLegacyHtmlEntities()
    {
        global $libDb, $libGenericStorage;

        if ($libGenericStorage->attributeExists('base_core', 'legacy_entities_decoded')) {
            return;
        }

        foreach ($this->legacyEncodedColumns as $table => $columns) {
            if (!$this->tableExists($table)) {
                continue;
            }

            foreach ($columns as $column) {
                $this->decodeLegacyHtmlEntitiesInColumn($table, $column);
            }
        }

        $libGenericStorage->saveValue('base_core', 'legacy_entities_decoded', '1');
    }

    /**
    * Kehrt htmlspecialchars($value, ENT_NOQUOTES) für eine einzelne Spalte um. Die alte
    * Encodierung hat ausschließlich & < > ersetzt, daher wird auch nur das zurückgedreht
    * und nicht etwa html_entity_decode() verwendet: Entities wie &auml; hat nie das VCMS
    * erzeugt, sie sind wörtlicher Benutzertext und müssen stehen bleiben.
    *
    * Die Reihenfolge ist wichtig. &lt; und &gt; werden zuerst aufgelöst, &amp; zuletzt,
    * damit ein wörtlich eingegebenes "&lt;" (gespeichert als "&amp;lt;") wieder zu "&lt;"
    * und nicht zu "<" wird.
    */
    public function decodeLegacyHtmlEntitiesInColumn($table, $column)
    {
        global $libDb;

        $sql = 'UPDATE ' .$table. ' SET ' .$column. ' = '
            . "REPLACE(REPLACE(REPLACE(" .$column. ", '&lt;', '<'), '&gt;', '>'), '&amp;', '&') "
            . 'WHERE ' .$column. " LIKE '%&lt;%' OR " .$column. " LIKE '%&gt;%' OR " .$column. " LIKE '%&amp;%'";

        $libDb->query($sql);
    }

    public function tableExists($table)
    {
        global $libDb;

        $stmt = $libDb->prepare('SHOW TABLES LIKE :table');
        $stmt->bindValue(':table', $table);

        if ($stmt === false || !$stmt->execute()) {
            return false;
        }

        return $stmt->fetch() !== false;
    }

    public function setGalleryPublicityLevels()
    {
        global $libGenericStorage, $libGallery, $libTime;

        $eventGalleryMaxPublicSemesters = $libGenericStorage->loadValue('base_core', 'event_public_gallery_semesters');

        if ($eventGalleryMaxPublicSemesters > 0) {
            $semester = $libTime->getSemesterName();

            for ($i = 0; $i < $eventGalleryMaxPublicSemesters; $i++) {
                $semester = $libTime->getPreviousSemesterNameOfSemester($semester);
            }

            $libGallery->setPublicityLevelsUntilSemester($semester, 'I');
        }
    }

    public function initConfiguration()
    {
        global $libGenericStorage, $libConfig;

        if ($libGenericStorage->loadValue('base_core', 'site_url') == '') {
            $libGenericStorage->saveValue('base_core', 'site_url', $this->getCurrentSiteUrl());
        }

        if (!$libGenericStorage->attributeExists('base_core', 'smtp_host')) {
            $libGenericStorage->saveValue('base_core', 'smtp_host', '');
        }

        if (!$libGenericStorage->attributeExists('base_core', 'smtp_username')) {
            $libGenericStorage->saveValue('base_core', 'smtp_username', '');
        }

        if (!$libGenericStorage->attributeExists('base_core', 'smtp_password')) {
            $libGenericStorage->saveValue('base_core', 'smtp_password', '');
        }

        if (!$libGenericStorage->attributeExists('base_core', 'smtp_port')) {
            $libGenericStorage->saveValue('base_core', 'smtp_port', 587);
        }

        if (!$libGenericStorage->attributeExists('base_core', 'facebook_appid')) {
            $libGenericStorage->saveValue('base_core', 'facebook_appid', '');
        }

        if (!$libGenericStorage->attributeExists('base_core', 'facebook_secret_key')) {
            $libGenericStorage->saveValue('base_core', 'facebook_secret_key', '');
        }

        if (!$libGenericStorage->attributeExists('base_core', 'image_lib')) {
            $libGenericStorage->saveValue('base_core', 'image_lib', '1');
        }

        if (!$libGenericStorage->attributeExists('base_core', 'brand')) {
            $libGenericStorage->saveValue('base_core', 'brand', $libConfig->verbindungName);
        }

        if (!$libGenericStorage->attributeExists('base_core', 'brand_xs')) {
            $libGenericStorage->saveValue('base_core', 'brand_xs', $libConfig->verbindungName);
        }

        if (!$libGenericStorage->attributeExists('base_core', 'delete_ausgetretene')) {
            $libGenericStorage->saveValue('base_core', 'delete_ausgetretene', 0);
        }

        if (!$libGenericStorage->attributeExists('base_core', 'event_preselect_intern')) {
            $libGenericStorage->saveValue('base_core', 'event_preselect_intern', 0);
        }

        if (!$libGenericStorage->attributeExists('base_core', 'event_banned_titles')) {
            $libGenericStorage->saveValue('base_core', 'event_banned_titles', 'AH-Besuch,Vortrag,Vortragsabend');
        }

        if (!$libGenericStorage->attributeExists('base_core', 'event_public_gallery_semesters')) {
            $libGenericStorage->saveValue('base_core', 'event_public_gallery_semesters', 0);
        }

        if (!$libGenericStorage->attributeExists('base_core', 'auto_update')) {
            $libGenericStorage->saveValue('base_core', 'auto_update', '1');
        }
    }

    //------------------------------------------------------

    public function generateHtaccessAllowFile($directoryAbsolutePath)
    {
        $content = 'allow from all';
        $this->generateHtaccessFile($directoryAbsolutePath, $content);
    }

    public function generateHtaccessDenyFile($directoryAbsolutePath)
    {
        $content = 'deny from all';
        $this->generateHtaccessFile($directoryAbsolutePath, $content);
    }

    public function generateHtaccessFile($directoryAbsolutePath, $content)
    {
        global $libFilesystem;

        $fileAbsolutePath = $directoryAbsolutePath. '/.htaccess';
        $handle = @fopen($fileAbsolutePath, 'w');

        if ($handle === false) {
            return;
        }

        @fwrite($handle, $content);
        @fclose($handle);
    }

    public function hasHtaccessDenyFile($directoryAbsolutePath)
    {
        global $libFilesystem;

        $fileAbsolutePath = $directoryAbsolutePath. '/.htaccess';

        if (!is_file($fileAbsolutePath)) {
            return false;
        }

        $handle = @fopen($fileAbsolutePath, 'r');

        if ($handle === false) {
            return false;
        }

        $fileSize = @filesize($fileAbsolutePath);
        $content = $fileSize > 0 ? @fread($handle, $fileSize) : '';
        @fclose($handle);

        if ($content == 'deny from all') {
            return true;
        } else {
            return false;
        }
    }

    //------------------------------------------------------

    public function getCurrentSiteUrl()
    {
        $result = (@$_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';
        $result .= $_SERVER['SERVER_NAME'];

        if ($_SERVER['SERVER_PORT'] != '80' && $_SERVER['SERVER_PORT'] != '443') {
            $result .= ':' .$_SERVER['SERVER_PORT'];
        }

        return $result;
    }
}
