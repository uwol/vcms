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

class LibPerson
{
    public function getNameString($id, $mode)
    {
        global $libDb;

        $stmt = $libDb->prepare("SELECT anrede, titel, rang, vorname, praefix, name, suffix FROM base_person WHERE id=:id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $memberRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!is_array($memberRow)) {
            return '';
        }

        $memberString = $this->formatNameString($memberRow['anrede'], $memberRow['titel'], $memberRow['rang'], $memberRow['vorname'], $memberRow['praefix'], $memberRow['name'], $memberRow['suffix'], $mode);

        return $memberString;
    }

    public function formatNameString($salutation, $title, $rank, $firstName, $prefix, $name, $suffix, $mode = 0)
    {
        $salutation = (string) $salutation;
        $title = (string) $title;
        $rank = (string) $rank;
        $firstName = (string) $firstName;
        $prefix = (string) $prefix;
        $name = (string) $name;
        $suffix = (string) $suffix;

        $string = '';

        if ($suffix != '') {
            $suffix = ' '.$suffix;
        } else {
            $suffix = '';
        }

        if ($mode == 0) { // Full name without Mr: Dr. Heinz van Husen LLM
            $string .= $title. ' ' .$firstName. ' ' .$prefix. ' ' .$name.$suffix;
        } elseif ($mode == 1) { // Reversed: van Husen LLM, Dr. Heinz
            $string .= $prefix. ' ' .$name.$suffix. ', ' .$title. ' ' .$firstName;
        } elseif ($mode == 2) { // Full salutation: Herr Dr. Professor Heinz van Husen LLM
            $string .= $salutation. ' ' .$title. ' ' .$rank. ' ' .$firstName. ' ' .$prefix. ' ' .$name.$suffix;
        } elseif ($mode == 3) { // First name: Heinz
            $string .= $firstName;
        } elseif ($mode == 4) { // Titled name, but only with the first of the given first names
            $firstNames = explode(' ', $firstName);
            $firstFirstName = $firstNames[0];
            $string .= $title. ' ' .$firstFirstName. ' ' .$prefix. ' ' .$name.$suffix;
        } elseif ($mode == 5) { // Name without Mr and title: Heinz van Husen LLM
            $string .= $firstName. ' ' .$prefix. ' ' .$name.$suffix;
        } elseif ($mode == 6) { // Full salutation without Mr: Dr. Professor Heinz van Husen LLM
            $string .= $title. ' ' .$rank. ' ' .$firstName. ' ' .$prefix. ' ' .$name.$suffix;
        } elseif ($mode == 7) { // Reversed without title: van Husen LLM, Heinz
            $string .= $prefix. ' ' .$name.$suffix. ', ' .$firstName;
        } elseif ($mode == 8) { // Abbreviated: M. Meyer
            $string .= substr($firstName, 0, 1). '. ' .$name;
        }

        $string = str_replace('  ', ' ', str_replace('  ', ' ', trim($string)));

        return $string;
    }

    public function getIntranetActivity($id)
    {
        global $libDb;

        /*
        * define constants
        */
        $averagePeriod = 14; //days
        $fullpercentlimit = 1; //points per day, that induce 100% activity

        /*
        * sum points
        */
        $stmt = $libDb->prepare("SELECT SUM(punkte) AS summe FROM sys_log_intranet WHERE mitglied = :mitglied AND DATE_SUB(CURDATE(),INTERVAL :interval DAY) <= datum");
        $stmt->bindValue(':mitglied', $id, PDO::PARAM_INT);
        $stmt->bindValue(':interval', $averagePeriod, PDO::PARAM_INT);
        $stmt->execute();
        $stmt->bindColumn('summe', $fullPoints);
        $stmt->fetch();

        /*
        * average points per day
        */
        $avgPointsPerDay = $fullPoints / $averagePeriod;

        /*
        * calculate activity metric
        */
        $activity = min($avgPointsPerDay / $fullpercentlimit, 1);
        return $activity;
    }

    public function getIntranetActivityBox($id)
    {
        global $libDb;

        // determine group
        $stmt = $libDb->prepare("SELECT gruppe FROM base_person WHERE id=:id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $stmt->bindColumn('gruppe', $group);
        $stmt->fetch();

        $retstr = '<div class="person-activity-box">';

        if ($group != 'T' && $group != 'V') {
            $activityPercent = $this->getIntranetActivity($id) * 100;
            $barWidthActivity = ceil($activityPercent);
            $barWidthInactivity = 100 - $barWidthActivity;

            if ($barWidthActivity > 0) {
                $retstr .= '<span class="person-activity-bar person-activity-bar-active" style="width:' .$barWidthActivity. '%"></span>';
            }

            if ($barWidthInactivity > 0) {
                $retstr .= '<span class="person-activity-bar person-activity-bar-inactive" style="width:' .$barWidthInactivity. '%"></span>';
            }
        } else {
            // required for correct height of cell in bootstrap row
            $retstr .= '<span class="person-activity-bar"></span>';
        }

        $retstr .= '</div>';

        return $retstr;
    }

    public function getSignature($id)
    {
        $retstr = '<div class="person-signature-box d-block mx-auto mb-3">';

        $retstr .= '<div class="img-box">';
        $retstr .= $this->getImage($id);
        $retstr .= '</div>';

        $retstr .= $this->getIntranetActivityBox($id);
        $retstr .= '</div>';

        return $retstr;
    }

    public function getImage($id, $size = 'md')
    {
        $retstr = '<a href="index.php?pid=intranet_person&amp;id=' .$id. '" class="person-profile-link">';
        $sizeClass = '';

        switch ($size) {
            case 'xs':
                $sizeClass = 'person-img-xs';
                break;
            case 'lg':
                $sizeClass = 'person-img-lg';
                break;
            default:
                $sizeClass = 'person-img-md';
                break;
        }

        if ($this->hasImageFile($id)) {
            $retstr .= '<img src="api.php?iid=base_intranet_personenbild&amp;id=' . $id . '" class="img-fluid hvr-glow ' .$sizeClass. '" alt=""/>';
        } else {
            $retstr .= '<div class="img-fluid hvr-glow person-img-dummy ' .$sizeClass. '"></div>';
        }

        $retstr .= '</a>';

        return $retstr;
    }

    public function hasImageFile($id)
    {
        return is_numeric($id) && is_file($this->getImageFilePath($id));
    }

    public function getImageFilePath($id)
    {
        global $libFilesystem;

        return $libFilesystem->getAbsolutePath('custom/intranet/mitgliederfotos/' .$id. '.jpg');
    }

    public function setIntranetActivity($id, $points, $enablelimit)
    {
        global $libDb;

        if ($enablelimit) {
            $stmt = $libDb->prepare("SELECT COUNT(*) AS number FROM sys_log_intranet WHERE mitglied=:mitglied AND DATE_SUB(CURDATE(),INTERVAL 0 DAY) <= datum");
            $stmt->bindValue(':mitglied', $id, PDO::PARAM_INT);
            $stmt->execute();
            $stmt->bindColumn('number', $pointsAddedToday);
            $stmt->fetch();

            if ($pointsAddedToday < 2) {
                $stmt = $libDb->prepare("INSERT INTO sys_log_intranet (mitglied, datum, punkte) VALUES (:mitglied, NOW(), :punkte)");
                $stmt->bindValue(':mitglied', $id, PDO::PARAM_INT);
                $stmt->bindValue(':punkte', $points, PDO::PARAM_INT);
                $stmt->execute();
            }
        } else {
            $stmt = $libDb->prepare("INSERT INTO sys_log_intranet (mitglied, datum, punkte) VALUES (:mitglied, NOW(), :punkte)");
            $stmt->bindValue(':mitglied', $id, PDO::PARAM_INT);
            $stmt->bindValue(':punkte', $points, PDO::PARAM_INT);
            $stmt->execute();
        }
    }

    public function getChargenString($id)
    {
        global $libDb, $libTime, $libConfig;

        /*
        * current offices; is the member currently on a board?
        */
        $stmt = $libDb->prepare("
			SELECT *
			FROM base_semester
			WHERE ((base_semester.senior = :senior AND sen_dech = 0)
			OR (base_semester.jubelsenior = :jubelsenior AND jubelsen_dech = 0)
			OR (base_semester.consenior = :consenior AND con_dech = 0)
			OR (base_semester.fuchsmajor = :fuchsmajor AND fm_dech = 0)
			OR (base_semester.fuchsmajor2 = :fuchsmajor2 AND fm2_dech = 0)
			OR (base_semester.scriptor = :scriptor AND scr_dech = 0)
			OR (base_semester.quaestor = :quaestor AND quaes_dech = 0)
			OR base_semester.ahv_senior = :ahv_senior
			OR base_semester.ahv_consenior = :ahv_consenior
			OR base_semester.ahv_keilbeauftragter = :ahv_keilbeauftragter
			OR base_semester.ahv_scriptor = :ahv_scriptor
			OR base_semester.ahv_quaestor = :ahv_quaestor
			OR base_semester.hv_vorsitzender = :hv_vorsitzender
			OR base_semester.hv_kassierer = :hv_kassierer
			OR base_semester.archivar = :archivar
			OR base_semester.redaktionswart = :redaktionswart
			OR base_semester.vop = :vop
			OR base_semester.vvop = :vvop
			OR base_semester.vopxx = :vopxx
			OR base_semester.vopxxx = :vopxxx
			OR base_semester.vopxxxx = :vopxxxx)
			AND semester = :semester");
        $stmt->bindValue(':senior', $id, PDO::PARAM_INT);
        $stmt->bindValue(':jubelsenior', $id, PDO::PARAM_INT);
        $stmt->bindValue(':consenior', $id, PDO::PARAM_INT);
        $stmt->bindValue(':fuchsmajor', $id, PDO::PARAM_INT);
        $stmt->bindValue(':fuchsmajor2', $id, PDO::PARAM_INT);
        $stmt->bindValue(':scriptor', $id, PDO::PARAM_INT);
        $stmt->bindValue(':quaestor', $id, PDO::PARAM_INT);
        $stmt->bindValue(':ahv_senior', $id, PDO::PARAM_INT);
        $stmt->bindValue(':ahv_consenior', $id, PDO::PARAM_INT);
        $stmt->bindValue(':ahv_keilbeauftragter', $id, PDO::PARAM_INT);
        $stmt->bindValue(':ahv_scriptor', $id, PDO::PARAM_INT);
        $stmt->bindValue(':ahv_quaestor', $id, PDO::PARAM_INT);
        $stmt->bindValue(':hv_vorsitzender', $id, PDO::PARAM_INT);
        $stmt->bindValue(':hv_kassierer', $id, PDO::PARAM_INT);
        $stmt->bindValue(':archivar', $id, PDO::PARAM_INT);
        $stmt->bindValue(':redaktionswart', $id, PDO::PARAM_INT);
        $stmt->bindValue(':vop', $id, PDO::PARAM_INT);
        $stmt->bindValue(':vvop', $id, PDO::PARAM_INT);
        $stmt->bindValue(':vopxx', $id, PDO::PARAM_INT);
        $stmt->bindValue(':vopxxx', $id, PDO::PARAM_INT);
        $stmt->bindValue(':vopxxxx', $id, PDO::PARAM_INT);
        $stmt->bindValue(':semester', $libTime->getSemesterName());
        $stmt->execute();

        $currentChargen = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($row['senior'] == $id) {
                $currentChargen[] = $libConfig->chargenSenior;
            }
            if ($row['jubelsenior'] == $id) {
                $currentChargen[] = $libConfig->chargenJubelSenior;
            }
            if ($row['consenior'] == $id) {
                $currentChargen[] = $libConfig->chargenConsenior;
            }
            if ($row['fuchsmajor'] == $id) {
                $currentChargen[] = $libConfig->chargenFuchsmajor;
            }
            if ($row['fuchsmajor2'] == $id) {
                $currentChargen[] = $libConfig->chargenFuchsmajor2;
            }
            if ($row['scriptor'] == $id) {
                $currentChargen[] = $libConfig->chargenScriptor;
            }
            if ($row['quaestor'] == $id) {
                $currentChargen[] = $libConfig->chargenQuaestor;
            }
            if ($row['ahv_senior'] == $id) {
                $currentChargen[] = $libConfig->chargenAHVSenior;
            }
            if ($row['ahv_consenior'] == $id) {
                $currentChargen[] = $libConfig->chargenAHVConsenior;
            }
            if ($row['ahv_keilbeauftragter'] == $id) {
                $currentChargen[] = $libConfig->chargenAHVKeilbeauftragter;
            }
            if ($row['ahv_scriptor'] == $id) {
                $currentChargen[] = $libConfig->chargenAHVScriptor;
            }
            if ($row['ahv_quaestor'] == $id) {
                $currentChargen[] = $libConfig->chargenAHVQuaestor;
            }
            if ($row['hv_vorsitzender'] == $id) {
                $currentChargen[] = $libConfig->chargenHVVorsitzender;
            }
            if ($row['hv_kassierer'] == $id) {
                $currentChargen[] = $libConfig->chargenHVKassierer;
            }
            if ($row['archivar'] == $id) {
                $currentChargen[] = $libConfig->chargenArchivar;
            }
            if ($row['redaktionswart'] == $id) {
                $currentChargen[] = $libConfig->chargenRedaktionswart;
            }
            if ($row['vop'] == $id) {
                if (isset($libConfig->chargenVOP)) {
                    $currentChargen[] = $libConfig->chargenVOP;
                }
            }
            if ($row['vvop'] == $id) {
                if (isset($libConfig->chargenVVOP)) {
                    $currentChargen[] = $libConfig->chargenVVOP;
                }
            }
            if ($row['vopxx'] == $id) {
                if (isset($libConfig->chargenVOPxx)) {
                    $currentChargen[] = $libConfig->chargenVOPxx;
                }
            }
            if ($row['vopxxx'] == $id) {
                if (isset($libConfig->chargenVOPxxx)) {
                    $currentChargen[] = $libConfig->chargenVOPxxx;
                }
            }
            if ($row['vopxxxx'] == $id) {
                if (isset($libConfig->chargenVOPxxxx)) {
                    $currentChargen[] = $libConfig->chargenVOPxxxx;
                }
            }
        }

        $newCurrentChargen = [];

        foreach ($currentChargen as $value) {
            if ($value) {
                array_push($newCurrentChargen, $value);
            }
        }

        $currentChargenString = implode(', ', $newCurrentChargen);

        /*
        * offices no longer held
        */
        $stmt = $libDb->prepare("
			SELECT *
			FROM base_semester
			WHERE (base_semester.senior = :senior AND sen_dech = 1)
			OR (base_semester.jubelsenior = :jubelsenior AND jubelsen_dech = 1)
			OR (base_semester.consenior = :consenior AND con_dech = 1)
			OR (base_semester.fuchsmajor = :fuchsmajor AND fm_dech = 1)
			OR (base_semester.fuchsmajor2 = :fuchsmajor2 AND fm2_dech = 1)
			OR (base_semester.scriptor = :scriptor AND scr_dech = 1)
			OR (base_semester.quaestor = :quaestor AND quaes_dech = 1)
			OR base_semester.ahv_senior = :ahv_senior
			OR base_semester.ahv_consenior = :ahv_consenior
			OR base_semester.ahv_keilbeauftragter = :ahv_keilbeauftragter
			OR base_semester.ahv_scriptor = :ahv_scriptor
			OR base_semester.ahv_quaestor = :ahv_quaestor
			OR base_semester.hv_vorsitzender = :hv_vorsitzender
			OR base_semester.hv_kassierer = :hv_kassierer
			OR base_semester.archivar = :archivar
			OR base_semester.redaktionswart = :redaktionswart
			OR base_semester.vop = :vop
			OR base_semester.vvop = :vvop
			OR base_semester.vopxx = :vopxx
			OR base_semester.vopxxx = :vopxxx
			OR base_semester.vopxxxx = :vopxxxx
			AND semester != :semester
			ORDER BY SUBSTRING(semester,3)");
        $stmt->bindValue(':senior', $id, PDO::PARAM_INT);
        $stmt->bindValue(':jubelsenior', $id, PDO::PARAM_INT);
        $stmt->bindValue(':consenior', $id, PDO::PARAM_INT);
        $stmt->bindValue(':fuchsmajor', $id, PDO::PARAM_INT);
        $stmt->bindValue(':fuchsmajor2', $id, PDO::PARAM_INT);
        $stmt->bindValue(':scriptor', $id, PDO::PARAM_INT);
        $stmt->bindValue(':quaestor', $id, PDO::PARAM_INT);
        $stmt->bindValue(':ahv_senior', $id, PDO::PARAM_INT);
        $stmt->bindValue(':ahv_consenior', $id, PDO::PARAM_INT);
        $stmt->bindValue(':ahv_keilbeauftragter', $id, PDO::PARAM_INT);
        $stmt->bindValue(':ahv_scriptor', $id, PDO::PARAM_INT);
        $stmt->bindValue(':ahv_quaestor', $id, PDO::PARAM_INT);
        $stmt->bindValue(':hv_vorsitzender', $id, PDO::PARAM_INT);
        $stmt->bindValue(':hv_kassierer', $id, PDO::PARAM_INT);
        $stmt->bindValue(':archivar', $id, PDO::PARAM_INT);
        $stmt->bindValue(':redaktionswart', $id, PDO::PARAM_INT);
        $stmt->bindValue(':vop', $id, PDO::PARAM_INT);
        $stmt->bindValue(':vvop', $id, PDO::PARAM_INT);
        $stmt->bindValue(':vopxx', $id, PDO::PARAM_INT);
        $stmt->bindValue(':vopxxx', $id, PDO::PARAM_INT);
        $stmt->bindValue(':vopxxxx', $id, PDO::PARAM_INT);
        $stmt->bindValue(':semester', $libTime->getSemesterName());
        $stmt->execute();

        $chargen = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($row['senior'] == $id) {
                $chargen[] = $libConfig->chargenSenior;
            }
            if ($row['jubelsenior'] == $id) {
                $chargen[] = $libConfig->chargenJubelSenior;
            }
            if ($row['consenior'] == $id) {
                $chargen[] = $libConfig->chargenConsenior;
            }
            if ($row['fuchsmajor'] == $id) {
                $chargen[] = $libConfig->chargenFuchsmajor;
            }
            if ($row['fuchsmajor2'] == $id) {
                $chargen[] = $libConfig->chargenFuchsmajor2;
            }
            if ($row['scriptor'] == $id) {
                $chargen[] = $libConfig->chargenScriptor;
            }
            if ($row['quaestor'] == $id) {
                $chargen[] = $libConfig->chargenQuaestor;
            }
            if ($row['ahv_senior'] == $id) {
                if (!in_array($libConfig->chargenAHVSenior, $chargen) &&
                    !in_array($libConfig->chargenAHVSenior, $currentChargen)) {
                    $chargen[] = $libConfig->chargenAHVSenior;
                }
            }
            if ($row['ahv_consenior'] == $id) {
                if (!in_array($libConfig->chargenAHVConsenior, $chargen) &&
                    !in_array($libConfig->chargenAHVConsenior, $currentChargen)) {
                    $chargen[] = $libConfig->chargenAHVConsenior;
                }
            }
            if ($row['ahv_keilbeauftragter'] == $id) {
                if (!in_array($libConfig->chargenAHVKeilbeauftragter, $chargen) &&
                    !in_array($libConfig->chargenAHVKeilbeauftragter, $currentChargen)) {
                    $chargen[] = $libConfig->chargenAHVKeilbeauftragter;
                }
            }
            if ($row['ahv_scriptor'] == $id) {
                if (!in_array($libConfig->chargenAHVScriptor, $chargen) &&
                    !in_array($libConfig->chargenAHVScriptor, $currentChargen)) {
                    $chargen[] = $libConfig->chargenAHVScriptor;
                }
            }
            if ($row['ahv_quaestor'] == $id) {
                if (!in_array($libConfig->chargenAHVQuaestor, $chargen) &&
                    !in_array($libConfig->chargenAHVQuaestor, $currentChargen)) {
                    $chargen[] = $libConfig->chargenAHVQuaestor;
                }
            }
            if ($row['hv_vorsitzender'] == $id) {
                if (!in_array($libConfig->chargenHVVorsitzender, $chargen) &&
                    !in_array($libConfig->chargenHVVorsitzender, $currentChargen)) {
                    $chargen[] = $libConfig->chargenHVVorsitzender;
                }
            }
            if ($row['hv_kassierer'] == $id) {
                if (!in_array($libConfig->chargenHVKassierer, $chargen) &&
                    !in_array($libConfig->chargenHVKassierer, $currentChargen)) {
                    $chargen[] = $libConfig->chargenHVKassierer;
                }
            }
            if ($row['archivar'] == $id) {
                if (!in_array($libConfig->chargenArchivar, $chargen) &&
                    !in_array($libConfig->chargenArchivar, $currentChargen)) {
                    $chargen[] = $libConfig->chargenArchivar;
                }
            }
            if ($row['redaktionswart'] == $id) {
                if (!in_array($libConfig->chargenRedaktionswart, $chargen) &&
                    !in_array($libConfig->chargenRedaktionswart, $currentChargen)) {
                    $chargen[] = $libConfig->chargenRedaktionswart;
                }
            }
            if ($row['vop'] == $id) {
                if (!in_array($libConfig->chargenVOP, $chargen) &&
                    !in_array($libConfig->chargenVOP, $currentChargen)) {
                    if (isset($libConfig->chargenVOP)) {
                        $chargen[] = $libConfig->chargenVOP;
                    }
                }
            }
            if ($row['vvop'] == $id) {
                if (!in_array($libConfig->chargenVVOP, $chargen) &&
                    !in_array($libConfig->chargenVVOP, $currentChargen)) {
                    if (isset($libConfig->chargenVVOP)) {
                        $chargen[] = $libConfig->chargenVVOP;
                    }
                }
            }
            if ($row['vopxx'] == $id) {
                if (!in_array($libConfig->chargenVOPxx, $chargen) &&
                    !in_array($libConfig->chargenVOPxx, $currentChargen)) {
                    if (isset($libConfig->chargenVOPxx)) {
                        $chargen[] = $libConfig->chargenVOPxx;
                    }
                }
            }
            if ($row['vopxxx'] == $id) {
                if (!in_array($libConfig->chargenVOPxxx, $chargen) &&
                    !in_array($libConfig->chargenVOPxxx, $currentChargen)) {
                    if (isset($libConfig->chargenVOPxxx)) {
                        $chargen[] = $libConfig->chargenVOPxxx;
                    }
                }
            }
            if ($row['vopxxxx'] == $id) {
                if (!in_array($libConfig->chargenVOPxxxx, $chargen) &&
                    !in_array($libConfig->chargenVOPxxxx, $currentChargen)) {
                    if (isset($libConfig->chargenVOPxxxx)) {
                        $chargen[] = $libConfig->chargenVOPxxxx;
                    }
                }
            }
        }

        $newChargen = [];

        foreach ($chargen as $value) {
            if ($value) {
                array_push($newChargen, $value);
            }
        }

        $dechargedChargenString = implode(', ', $newChargen);

        /*
        * result string
        */
        $retstr = $currentChargenString;

        if ($dechargedChargenString != '') {
            $retstr .= ' ('.$dechargedChargenString.')';
        }

        return $retstr;
    }

    public function getAssociationsString($id)
    {
        global $libDb, $libString;

        $stmt = $libDb->prepare("SELECT base_verein.id, base_verein.kuerzel, base_verein_mitgliedschaft.ehrenmitglied
			FROM base_verein, base_verein_mitgliedschaft
			WHERE base_verein_mitgliedschaft.mitglied = :mitglied
			AND base_verein_mitgliedschaft.verein = base_verein.id");
        $stmt->bindValue(':mitglied', $id, PDO::PARAM_INT);
        $stmt->execute();

        $associationsString = '';

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($associationsString != '') {
                $associationsString .= ', ';
            }

            $honoraryString = '';

            if ($row['ehrenmitglied'] == 1) {
                $honoraryString = 'E.d. ';
            }

            $associationsString .= '<a href="index.php?pid=verein&amp;id=' .(int) $row['id']. '">' .$honoraryString.$libString->protectXSS($row['kuerzel']). '</a>';
            unset($honoraryString);
        }

        if ($associationsString != '') {
            return '('.$associationsString.')';
        } else {
            return '';
        }
    }

    public function getPersonSchema($row)
    {
        global $libTime;

        $result = [];

        $result['@context'] = 'http://schema.org';
        $result['@type'] = 'Person';
        $result['honorificPrefix'] = $row['titel'];
        $result['givenName'] = $row['vorname'];
        $result['familyName'] = $row['name'];
        $result['jobTitle'] = $row['beruf'];
        $result['email'] = $row['email'];
        $result['telephone'] = $row['mobiltelefon'];
        $result['url'] = $row['webseite'];

        if ($row['datum_geburtstag'] != '') {
            $result['birthDate'] = $libTime->formatDateString($row['datum_geburtstag']);
        }

        if ($row['tod_datum'] != '') {
            $result['deathDate'] = $libTime->formatDateString($row['tod_datum']);
        }

        $address1 = [];
        $address1['@type'] = 'PostalAddress';
        $address1['streetAddress'] = $row['strasse1'];
        $address1['addressLocality'] = $row['ort1'];
        $address1['postalCode'] = $row['plz1'];
        $address1['addressCountry'] = $row['land1'];
        $address1['telephone'] = $row['telefon1'];

        $address2 = [];
        $address2['@type'] = 'PostalAddress';
        $address2['streetAddress'] = $row['strasse2'];
        $address2['addressLocality'] = $row['ort2'];
        $address2['postalCode'] = $row['plz2'];
        $address2['addressCountry'] = $row['land2'];
        $address2['telephone'] = $row['telefon2'];

        $result['contactPoint'] = [$address1, $address2];

        return $result;
    }
}
