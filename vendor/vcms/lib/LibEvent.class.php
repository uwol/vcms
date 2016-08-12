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

class LibEvent{

	function getUrl($id){
		global $libGlobal, $libDb, $libTime;

		$stmt = $libDb->prepare('SELECT id, datum FROM base_veranstaltung WHERE id=:id');
		$stmt->bindValue(':id', $id, PDO::PARAM_INT);
		$stmt->execute();

		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		$semester = $libTime->getSemesterNameAtDate($row['datum']);
		$result = $libGlobal->getSiteUrl(). '/index.php?pid=semesterprogramm_event&amp;eventid=' .$row['id']. '&amp;semester=' .$semester;
		return $result;
	}

	function getStatusString($status){
		$result = '';

		switch($status){
			case 'o':
				$result = 'offiziell';
				break;
			case 'ho':
				$result = 'hochoffiziell';
				break;
			case '':
				$result = 'inoffiziell';
				break;
			default:
				$result = $status;
		}

		return $result;
	}

	function getTitle($id){
		global $libConfig, $libDb, $libTime;

		$stmt = $libDb->prepare('SELECT id, datum, titel FROM base_veranstaltung WHERE id=:id');
		$stmt->bindValue(':id', $id, PDO::PARAM_INT);
		$stmt->execute();

		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		$result = $libConfig->verbindungName. ' - ' .$row['titel']. ' am ' .$libTime->formatDateString($row['datum']);
		return $result;
	}

	function hasBannedTitle($id){
		global $libDb, $libGenericStorage;

		$bannedTitlesString = $libGenericStorage->loadValue('base_core', 'eventBannedTitles');
		$bannedTitles = explode(',', $bannedTitlesString);
		$bannedTitlesCleaned = array();

		foreach($bannedTitles as $bannedTitle){
			$bannedTitlesCleaned[] = strtolower(trim($bannedTitle));
		}

		$stmt = $libDb->prepare('SELECT titel FROM base_veranstaltung WHERE id=:id');
		$stmt->bindValue(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		$stmt->bindColumn('titel', $title);
		$stmt->fetch();

		$titleCleaned = strtolower(trim($title));

		$result = in_array($titleCleaned, $bannedTitlesCleaned);
		return $result;
	}

	function isFacebookEvent($row){
		global $libGenericStorage;

		$fbAppId = $libGenericStorage->loadValue('base_core', 'fbAppId');
		$fbSecretKey = $libGenericStorage->loadValue('base_core', 'fbSecretKey');

		$result = isset($row['fb_eventid']) && is_numeric($row['fb_eventid'])
			&& ini_get('allow_url_fopen') && $fbAppId != '' && $fbSecretKey != '';
		return $result;
	}

	function printFacebookShareButton($id){
		$url = $this->getUrl($id);
		$title = $this->getTitle($id);

		echo '<a href="http://www.facebook.com/share.php?u=' .urlencode($url). '&amp;t=' .urlencode($title). '" rel="nofollow">';
		echo '<i class="fa fa-facebook-official fa-lg" aria-hidden="true"></i>';
		echo '</a> ';
	}

	function printTwitterShareButton($id){
		$url = $this->getUrl($id);
		$title = $this->getTitle($id);

		echo '<a href="http://twitter.com/share?url=' .urlencode($url). '&amp;text=' .urlencode($title). '" rel="nofollow">';
		echo '<i class="fa fa-twitter-square fa-lg" aria-hidden="true"></i>';
		echo '</a> ';
	}
}