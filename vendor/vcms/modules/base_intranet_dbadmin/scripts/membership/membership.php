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

if(!is_object($libGlobal) || !$libAuth->isLoggedin())
	exit();


if($libAuth->isLoggedin()){
	$action = '';
	if(isset($_REQUEST['action'])){
		$action = $_REQUEST['action'];
	}

	$association = '';
	if(isset($_REQUEST['verein'])){
		$association = $_REQUEST['verein'];
	}

	$member = '';
	if(isset($_REQUEST['mitglied'])){
		$member = $_REQUEST['mitglied'];
	}

	$membershipRow = array();
	//Felder in der Tabelle angeben -> Metadaten
	$fields = array('mitglied', 'verein', 'ehrenmitglied', 'semester_reception', 'semester_philistrierung');

	/**
	*
	* Verschiedene Aktionen auf der Datenbank durchführen, je nach Kontext
	* der durch action definiert wird
	*
	*/

	//neues Mitglied, leerer Datensatz
	if($action == 'blank'){
		foreach($fields as $field){
			$membershipRow[$field] = '';
		}
	}
	//Daten wurden mit blank eingegeben, werden nun gespeichert
	elseif($action == 'insert'){
		if(!isset($_POST['form_complete']) || !$_POST['form_complete']){
			die('Die Eingabemaske war noch nicht komplett dargestellt. Bitte Seite neu laden.');
		}

		$error = false;

		if($_REQUEST['semester_reception'] != '' && !$libTime->isValidSemesterString($_REQUEST['semester_reception'])){
			$libGlobal->errorTexts[] = 'Das Receptionssemester ist falsch formatiert.';
			$error = true;
		}

		if($_REQUEST['semester_philistrierung'] != '' && !$libTime->isValidSemesterString($_REQUEST['semester_philistrierung'])){
			$libGlobal->errorTexts[] = 'Das Philistrierungssemester ist falsch formatiert.';
			$error = true;
		}

		if($error){
			$membershipRow = $_REQUEST;
		} else {
			$membershipRow = $libDb->insertRow($fields,$_REQUEST, 'base_verein_mitgliedschaft', array('verein' => $association, 'mitglied' => $member));
		}
	}
	//bestehende Daten werden modifiziert
	elseif($action == 'update'){
		if(!isset($_POST['form_complete']) || !$_POST['form_complete']){
			die('Die Eingabemaske war noch nicht komplett dargestellt. Bitte Seite neu laden.');
		}

		$error = false;

		if($_REQUEST['semester_reception'] != '' && !$libTime->isValidSemesterString($_REQUEST['semester_reception'])){
			$libGlobal->errorTexts[] = 'Das Receptionssemester ist falsch formatiert.';
			$error = true;
		}

		if($_REQUEST['semester_philistrierung'] != '' && !$libTime->isValidSemesterString($_REQUEST['semester_philistrierung'])){
			$libGlobal->errorTexts[] = 'Das Philistrierungssemester ist falsch formatiert.';
			$error = true;
		}

		if($error){
			$membershipRow = $_REQUEST;
		} else {
			$membershipRow = $libDb->updateRow($fields,$_REQUEST, 'base_verein_mitgliedschaft', array('verein' => $association, 'mitglied' => $member));
		}
	} else {
		$stmt = $libDb->prepare('SELECT * FROM base_verein_mitgliedschaft WHERE verein=:verein AND mitglied=:mitglied');
		$stmt->bindValue(':verein', $association, PDO::PARAM_INT);
		$stmt->bindValue(':mitglied', $member, PDO::PARAM_INT);
		$stmt->execute();
		$membershipRow = $stmt->fetch(PDO::FETCH_ASSOC);
	}

	/**
	*
	* Einleitender Text
	*
	*/

	echo '<h1>Vereinsmitgliedschaft</h1>';

	echo $libString->getErrorBoxText();
	echo $libString->getNotificationBoxText();

	/**
	*
	* Löschoption
	*
	*/
	if($membershipRow['mitglied'] != '' && $membershipRow['verein'] != ''){
		echo '<form class="mb-4" method="post" action="index.php?pid=intranet_admin_memberships" onsubmit="return confirm(\'Willst Du den Datensatz wirklich löschen?\')">';
		echo '<input type="hidden" name="action" value="delete" />';
		echo '<input type="hidden" name="mitglied" value="' .$membershipRow['mitglied']. '" />';
		echo '<input type="hidden" name="verein" value="' .$membershipRow['verein']. '" />';
		echo '<button type="submit" class="p-0 border-0 bg-transparent align-baseline text-dark cursor-pointer"><i class="fa fa-trash" aria-hidden="true"></i> Datensatz löschen</button>';
		echo '</form>';
	}

	/**
	*
	* Ausgabe des Forms starten
	*
	*/
	if($action == 'blank'){
		$extraActionParam = '&amp;action=insert';
	} else {
		$extraActionParam = '&amp;action=update';
	}

	echo '<div class="panel panel-default">';
	echo '<div class="panel-body">';
	echo '<form action="index.php?pid=intranet_admin_membership' .$extraActionParam. '" method="post" class="form-horizontal">';
	echo '<fieldset>';
	echo '<input type="hidden" name="verein" value="' .$membershipRow['verein']. '" />';
	echo '<input type="hidden" name="mitglied" value="' .$membershipRow['mitglied']. '" />';

	if($action == 'blank'){
		$libForm->printMembersDropDownBox('mitglied', 'Mitglied', $membershipRow['mitglied'], false, false);
		$libForm->printAssociationsDropDownBox('verein', 'Verein', $membershipRow['verein'], false, false);
	} else {
		$libForm->printMembersDropDownBox('mitglied', 'Mitglied', $membershipRow['mitglied'], false, true);
		$libForm->printAssociationsDropDownBox('verein', 'Verein', $membershipRow['verein'], false, true);
	}

	$libForm->printBoolSelectBox('ehrenmitglied', 'Ehrenmitglied', $membershipRow['ehrenmitglied']);

	$libForm->printTextInput('semester_reception', 'Semester Reception', $membershipRow['semester_reception']);
	$libForm->printTextInput('semester_philistrierung', 'Semester Philistrierung', $membershipRow['semester_philistrierung']);

	echo '<input type="hidden" name="form_complete" value="1" />';

	$libForm->printSubmitButton('Speichern');

	echo '</fieldset>';
	echo '</form>';
	echo '</div>';
	echo '</div>';
}
