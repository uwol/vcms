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

    /*
    * Tries to login with email and password
    */
    public function login($email, $password)
    {
        global $libGlobal, $libDb, $libPerson;

        $email = trim(strtolower((string) $email));
        $password = trim((string) $password);

        $this->reset();
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

        // 7. Check password
        if ($this->checkPassword($password, $row['password_hash'])) {
            // a. Load person, group and offices
            if (!$this->loadPerson($row['id'])) {
                $libGlobal->errorTexts[] = 'E-Mail-Adresse oder Passwort falsch.';
                return false;
            }

            // b. Log successful login attempt
            $stmt = $libDb->prepare('INSERT INTO sys_log_intranet (mitglied, aktion, datum, punkte, ipadresse) VALUES (:mitglied, :aktion, NOW(), :punkte, :ipadresse)');
            $stmt->bindValue(':mitglied', $row['id'], PDO::PARAM_INT);
            $stmt->bindValue(':aktion', 1, PDO::PARAM_INT);
            $stmt->bindValue(':punkte', 0, PDO::PARAM_INT);
            $stmt->bindValue(':ipadresse', $_SERVER['REMOTE_ADDR']);
            $stmt->execute();

            $libPerson->setIntranetActivity($row['id'], 1, 1);

            return true;
        }

        // 8. Log mistaken login attempt
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
    * Restores the authentication context of a session from the person id stored at login.
    * Group and offices are loaded from the database on every request, so that a member who
    * leaves the association or loses an office loses the corresponding access immediately.
    */
    public function restore($personId)
    {
        $this->reset();

        if (!is_numeric($personId) || $personId <= 0) {
            return false;
        }

        $this->possibleGroups = $this->loadPossibleGroups();
        return $this->loadPerson($personId);
    }

    private function reset()
    {
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
    }

    /*
    * Groups whose members may log in; deceased and former members are excluded
    */
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

    /*
    * Loads name, group and offices of a person; requires the possible groups to be loaded
    */
    private function loadPerson($personId)
    {
        global $libDb;

        $stmt = $libDb->prepare('SELECT id, anrede, titel, praefix, vorname, suffix, gruppe, name FROM base_person WHERE id=:id');
        $stmt->bindValue(':id', $personId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!is_array($row) || !in_array($row['gruppe'], $this->possibleGroups)) {
            return false;
        }

        $this->id = $row['id'];
        $this->salutation = $row['anrede'];
        $this->title = $row['titel'];
        $this->prefix = $row['praefix'];
        $this->firstName = $row['vorname'];
        $this->suffix = $row['suffix'];
        $this->lastName = $row['name'];
        $this->group = $row['gruppe'];
        $this->offices = $this->loadOffices($row['id']);

        $this->isLoggedIn = true;
        return true;
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
                if (isset($semesterRow[$office]) && $semesterRow[$office] == $personId) {
                    $offices[] = $office;
                }
            }
        }

        // The most recent internetwart of the last 20 semesters keeps the office
        $previousSemesters = [];
        $semesterIterator = $libTime->getSemesterName();

        for ($i = 0; $i < 20; $i++) {
            $semesterIterator = $libTime->getPreviousSemesterNameOfSemester($semesterIterator);
            $previousSemesters[] = $semesterIterator;
        }

        $placeholders = implode(', ', array_fill(0, count($previousSemesters), '?'));
        $stmt = $libDb->prepare('SELECT semester, internetwart FROM base_semester WHERE semester IN (' .$placeholders. ')');
        $stmt->execute($previousSemesters);

        $internetwartBySemester = [];

        while ($semesterRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $internetwartBySemester[$semesterRow['semester']] = $semesterRow['internetwart'];
        }

        foreach ($previousSemesters as $semester) {
            if (!empty($internetwartBySemester[$semester])) {
                if ($internetwartBySemester[$semester] == $personId) {
                    $offices[] = 'internetwart';
                }

                break;
            }
        }

        // Remove offices held in several semesters
        return array_values(array_unique($offices));
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
