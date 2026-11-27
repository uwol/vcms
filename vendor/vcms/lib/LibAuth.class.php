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

use PDO;

class LibAuth
{
    // Absolute lifetime of a login session, regardless of activity
    public const MAX_SESSION_LIFETIME = 7 * 24 * 60 * 60;

    // Interval in seconds after which group and offices are reloaded from the database
    public const REFRESH_INTERVAL = 60;

    public $id;
    public $salutation;
    public $title;
    public $prefix;
    public $firstName;
    public $suffix;
    public $lastName;

    public $group;
    public $offices = [];
    public $possibleGroups = [];

    public $isLoggedIn = false;

    public $loginTimestamp;
    public $lastRefreshTimestamp;
    public $passwordHashFingerprint = '';

    /*
    * tries to login with email and password
    */
    public function login($email, $password)
    {
        global $libGlobal, $libDb, $libPerson, $libTime, $libSecurityManager, $libString;

        $email = trim(strtolower((string) $email));
        $password = trim((string) $password);

        //clean memory
        $this->id = '';
        $this->salutation = '';
        $this->title = '';
        $this->prefix = '';
        $this->firstName = '';
        $this->suffix = '';
        $this->lastName = '';

        $this->group = '';
        $this->offices = [];
        $this->possibleGroups = [];

        $this->isLoggedIn = false;

        $this->loginTimestamp = null;
        $this->lastRefreshTimestamp = null;
        $this->passwordHashFingerprint = '';

        $this->possibleGroups = $this->loadPossibleGroups();

        /*
        * check for problem cases
        */

        //1. no email given
        if ($email == '') {
            $libGlobal->errorTexts[] = 'Die E-Mail-Adresse fehlt.';
            return false;
        }

        //2. no password given
        if ($password == '') {
            $libGlobal->errorTexts[] = 'Das Passwort fehlt.';
            return false;
        }

        $stmt = $libDb->prepare('SELECT id, anrede, titel, praefix, vorname, suffix, gruppe, name, email, password_hash FROM base_person WHERE email=:email');
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        //3. no user with that email address given
        if (!is_array($row) || !isset($row['id']) || !is_numeric($row['id']) || !($row['id'] > 0)) {
            //error message has to be imprecise
            $libGlobal->errorTexts[] = 'E-Mail-Adresse oder Passwort falsch.';
            return false;
        }

        //4. user is in an invalid group
        if (!in_array($row['gruppe'], $this->possibleGroups)) {
            $libGlobal->errorTexts[] = 'Gruppe falsch.';
            return false;
        }

        //5. missing password hash
        if (trim((string) $row['password_hash']) == '') {
            $libGlobal->errorTexts[] = 'In der Datenbank ist kein Passwort-Hash vorhanden.';
            return false;
        }

        //6. check number of mistaken login attempts; brute force prevention
        $stmt = $libDb->prepare('SELECT COUNT(*) AS number FROM sys_log_intranet WHERE mitglied=:mitglied AND aktion=2 AND DATEDIFF(NOW(), datum) = 0');
        $stmt->bindValue(':mitglied', $row['id'], PDO::PARAM_INT);
        $stmt->execute();
        $stmt->bindColumn('number', $numberOfMistakenLoginsToday);
        $stmt->fetch();

        if ($numberOfMistakenLoginsToday > 0) {
            $stmt = $libDb->prepare('SELECT datum FROM sys_log_intranet WHERE mitglied=:mitglied AND aktion=2 AND DATEDIFF(NOW(), datum) = 0 ORDER BY datum DESC LIMIT 0,1');
            $stmt->bindValue(':mitglied', $row['id'], PDO::PARAM_INT);
            $stmt->execute();
            $stmt->bindColumn('datum', $lastMistakenLoginToday);
            $stmt->fetch();

            $nextPossibleLoginTimeStamp = strtotime((string) $lastMistakenLoginToday) + pow(2, $numberOfMistakenLoginsToday);
            $secondsToNextPossibleLogin = $nextPossibleLoginTimeStamp - time();

            if ($secondsToNextPossibleLogin > 0) {
                if ($secondsToNextPossibleLogin < 120) {
                    $libGlobal->errorTexts[] = 'Dieses Konto ist für die nächsten ' .$secondsToNextPossibleLogin. ' Sekunden gesperrt, da zu viele erfolglose Anmeldeversuche unternommen wurden.';
                } else {
                    $minutesToNextPossibleLogin = floor($secondsToNextPossibleLogin / 60);
                    $libGlobal->errorTexts[] = 'Dieses Konto ist für die nächsten ' .$minutesToNextPossibleLogin. ' Minuten gesperrt, da zu viele erfolglose Anmeldeversuche unternommen wurden.';
                }

                return false;
            }
        }

        //7. check password
        if ($this->checkPassword($password, $row['password_hash'])) {
            //a. login successful
            $this->isLoggedIn = true;
            $this->loginTimestamp = time();
            $this->lastRefreshTimestamp = time();
            $this->passwordHashFingerprint = $this->getPasswordHashFingerprint($row['password_hash']);

            $this->applyPersonRow($row);

            //b. determine functions
            $this->offices = $this->loadOffices($row['id']);

            //c. log successful login attempt
            $stmt = $libDb->prepare('INSERT INTO sys_log_intranet (mitglied, aktion, datum, punkte, ipadresse) VALUES (:mitglied, :aktion, NOW(), :punkte, :ipadresse)');
            $stmt->bindValue(':mitglied', $row['id'], PDO::PARAM_INT);
            $stmt->bindValue(':aktion', 1, PDO::PARAM_INT);
            $stmt->bindValue(':punkte', 0, PDO::PARAM_INT);
            $stmt->bindValue(':ipadresse', $_SERVER['REMOTE_ADDR']);
            $stmt->execute();

            $libPerson->setIntranetActivity($row['id'], 1, 1);

            return true;
        }

        //8. log mistaken login attempt
        $stmt = $libDb->prepare('INSERT INTO sys_log_intranet (mitglied, aktion, datum, punkte, ipadresse) VALUES (:mitglied, :aktion, NOW(), :punkte, :ipadresse)');
        $stmt->bindValue(':mitglied', $row['id'], PDO::PARAM_INT);
        $stmt->bindValue(':aktion', 2, PDO::PARAM_INT);
        $stmt->bindValue(':punkte', 0, PDO::PARAM_INT);
        $stmt->bindValue(':ipadresse', $_SERVER['REMOTE_ADDR']);
        $stmt->execute();

        //error message has to be imprecise
        $libGlobal->errorTexts[] = 'E-Mail-Adresse oder Passwort falsch.';
        return false;
    }

    /*
    * Revalidates a login session against the database. Returns false if the session
    * must be terminated, e.g. because the person was deleted, moved to an invalid
    * group, changed the password or the session exceeded its absolute lifetime.
    * Group and offices are reloaded, so revoked rights take effect.
    */
    public function refresh()
    {
        global $libDb;

        if (!$this->isLoggedin()) {
            return false;
        }

        $now = time();

        if (!is_numeric($this->loginTimestamp) || $now - $this->loginTimestamp > self::MAX_SESSION_LIFETIME) {
            return false;
        }

        if (is_numeric($this->lastRefreshTimestamp) && $now - $this->lastRefreshTimestamp < self::REFRESH_INTERVAL) {
            return true;
        }

        $stmt = $libDb->prepare('SELECT id, anrede, titel, praefix, vorname, suffix, gruppe, name, password_hash FROM base_person WHERE id=:id');
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!is_array($row)) {
            return false;
        }

        // The password was changed since login, e.g. by an administrator
        if (!hash_equals((string) $this->passwordHashFingerprint, $this->getPasswordHashFingerprint($row['password_hash']))) {
            return false;
        }

        $this->possibleGroups = $this->loadPossibleGroups();

        if (!in_array($row['gruppe'], $this->possibleGroups)) {
            return false;
        }

        $this->applyPersonRow($row);
        $this->offices = $this->loadOffices($row['id']);
        $this->lastRefreshTimestamp = $now;

        return true;
    }

    private function loadPossibleGroups()
    {
        global $libDb;

        $possibleGroups = [];

        $stmt = $libDb->prepare('SELECT bezeichnung FROM base_gruppe');
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($row['bezeichnung'] != 'T' && $row['bezeichnung'] != 'X' && $row['bezeichnung'] != 'V') {
                $possibleGroups[] = $row['bezeichnung'];
            }
        }

        return $possibleGroups;
    }

    private function applyPersonRow($row)
    {
        $this->id = $row['id'];
        $this->salutation = $row['anrede'];
        $this->title = $row['titel'];
        $this->prefix = $row['praefix'];
        $this->firstName = $row['vorname'];
        $this->suffix = $row['suffix'];
        $this->lastName = $row['name'];
        $this->group = $row['gruppe'];
    }

    private function loadOffices($personId)
    {
        global $libDb, $libTime, $libSecurityManager;

        $offices = [];

        // Offices in the current and the following semester
        $stmt = $libDb->prepare('SELECT * FROM base_semester WHERE semester=:semester OR semester=:semester_next');
        $stmt->bindValue(':semester', $libTime->getSemesterName());
        $stmt->bindValue(':semester_next', $libTime->getFollowingSemesterName());
        $stmt->execute();

        $possibleOffices = $libSecurityManager->getPossibleOffices();

        while ($semesterRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            foreach ($possibleOffices as $office) {
                if ($semesterRow[$office] == $personId) {
                    $offices[] = $office;
                }
            }
        }

        // The most recent Internetwart of the last 20 semesters keeps the office
        $semesterIterator = $libTime->getSemesterName();

        for ($i = 0; $i < 20; $i++) {
            $semesterIterator = $libTime->getPreviousSemesterNameOfSemester($semesterIterator);

            $stmt = $libDb->prepare('SELECT internetwart FROM base_semester WHERE semester=:semester');
            $stmt->bindValue(':semester', $semesterIterator);
            $stmt->execute();
            $stmt->bindColumn('internetwart', $internetwart);
            $stmt->fetch();

            if ($internetwart) {
                if ($internetwart == $personId) {
                    $offices[] = 'internetwart';
                }

                break;
            }
        }

        // Remove redundant offices from multiple semesters
        return array_values(array_unique($offices));
    }

    private function getPasswordHashFingerprint($passwordHash)
    {
        return hash('sha256', trim((string) $passwordHash));
    }

    public function encryptPassword($password)
    {
        $phpassHasher = new \phpass\PasswordHash(12, false);
        return $phpassHasher->HashPassword($password);
    }

    public function savePassword($personId, $newPassword, $quiet = false, $checkIsValidPassword = true)
    {
        global $libGlobal, $libDb;

        //1. validation of person id
        if (!is_numeric($personId)) {
            return false;
        }

        //2. validation of password
        $newPassword = trim((string) $newPassword);

        //a. empty password
        if ($newPassword == '') {
            if (!$quiet) {
                $libGlobal->errorTexts[] = 'Das neue Passwort ist leer.';
            }

            return false;
        }

        //b. invalid password
        if ($checkIsValidPassword) {
            if (!$this->isValidPassword($newPassword)) {
                if (!$quiet) {
                    $libGlobal->errorTexts[] = 'Das neue Passwort ist nicht komplex genug. '. $this->getPasswordRequirements();
                }

                return false;
            }
        }

        //3. generate hash from password
        $passwdHash = $this->encryptPassword($newPassword);

        //4. save hash
        $stmt = $libDb->prepare('UPDATE base_person SET password_hash = :password_hash WHERE id = :id');
        $stmt->bindValue(':password_hash', $passwdHash);
        $stmt->bindValue(':id', $personId, PDO::PARAM_INT);
        $stmt->execute();

        // Keep the own session valid when the logged-in person changes the own password
        if ($this->isLoggedIn && $personId == $this->id) {
            $this->passwordHashFingerprint = $this->getPasswordHashFingerprint($passwdHash);
        }

        if (!$quiet) {
            $libGlobal->notificationTexts[] = 'Das Passwort wurde gespeichert.';
        }

        return true;
    }

    public function checkPassword($password, $storedHash)
    {
        $password = trim((string) $password);
        $storedHash = trim((string) $storedHash);

        // check by BCrypt
        if ($password != '' && $storedHash != '') {
            $phpassHasher = new \phpass\PasswordHash(12, false);
            return $phpassHasher->CheckPassword($password, $storedHash);
        }

        return false;
    }

    public function checkPasswordForPerson($personId, $password)
    {
        global $libDb;

        if (!is_numeric($personId)) {
            return false;
        }

        $stmt = $libDb->prepare('SELECT password_hash FROM base_person WHERE id = :id');
        $stmt->bindValue(':id', $personId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!is_array($row)) {
            return false;
        }

        return $this->checkPassword($password, $row['password_hash']);
    }

    public function isValidPassword($password)
    {
        // Min 1 digit, min 1 lowercase letter, min 1 uppercase letter, no space, min 10 characters
        return preg_match("/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.*\s).{10,}$/", trim((string) $password));
    }

    public function getPasswordRequirements()
    {
        return 'Das Passwort muss aus mindestens 10 Zeichen bestehen, mit mindestens einer Ziffer, mindestens einem Kleinbuchstaben und mindestens einem Großbuchstaben. Leerzeichen sind nicht erlaubt.';
    }

    //-------------------------------------------------------------------------

    public function getId()
    {
        return $this->id;
    }

    public function getSalutation()
    {
        return $this->salutation;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function getSuffix()
    {
        return $this->suffix;
    }

    public function getGroup()
    {
        return $this->group;
    }

    public function getOffices()
    {
        return $this->offices;
    }

    public function isLoggedin()
    {
        if ($this->isLoggedIn && is_numeric($this->id) &&
                $this->id > 0 && $this->group != '' &&
                in_array($this->group, $this->possibleGroups)) {
            return true;
        } else {
            return false;
        }
    }
}
