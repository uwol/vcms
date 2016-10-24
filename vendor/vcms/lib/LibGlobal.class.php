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

class LibGlobal{
	var $version = '7.17';

	var $semester;
	var $module;
	var $pid;
	var $page;
	var $iid;
	var $libInclude;

	var $errorTexts = array();
	var $notificationTexts = array();

	var $vcmsHostname;
	var $mkHostname;

	function __construct() {
		$this->vcmsHostname = 'ver' . 'bin' . 'dung' . 'scms' . '.' . 'de';
		$this->mkHostname = 'www' . '.' . 'mar' . 'kom' . 'ann' . 'ia' . '.' . 'org';
	}

	function getPageCanonicalUrl(){
		global $libGlobal, $libConfig, $libEvent;

		$result = '';

		if($libGlobal->pid == $libConfig->defaultHome){
			$result = $libGlobal->getSiteUrl(). '/';
		} else if($this->isEventPage()){
			$result = $libEvent->getEventUrl($_REQUEST['id']);
		} else {
			$result = $libGlobal->getSiteUrl(). '/index.php?pid=' .$libGlobal->pid;
		}

		return $result;
	}

	function getPageOgUrl(){
		global $libGlobal;

		$result = '';

		if($this->isEventPage()){
			$result = $libGlobal->getSiteUrl(). '/index.php?pid=' .$libGlobal->pid. '&amp;id=' .$_REQUEST['id'];
		} else {
			$result = $libGlobal->getSiteUrl(). '/';
		}

		return $result;
	}

	function getPageOgImageUrl(){
		return $this->getSiteUrl(). '/custom/styles/og_image.jpg';
	}

	function getPageTitle(){
		global $libGlobal, $libConfig, $libTime, $libDb;

		$result = '';

		if($libGlobal->pid == $libConfig->defaultHome){
			$result = $libConfig->verbindungName;
		} else if($this->isEventPage()){
			$stmt = $libDb->prepare("SELECT titel, datum, intern FROM base_veranstaltung WHERE id=:id");
			$stmt->bindValue(':id', $_REQUEST['id'], PDO::PARAM_INT);
			$stmt->execute();
			$event = $stmt->fetch(PDO::FETCH_ASSOC);

			if($event['titel'] != '' && $event['intern'] == 0){
				$result = $libConfig->verbindungName. ' - ' .$event['titel']. ' am ' .$libTime->formatDateString($event['datum']);
			} else {
				$result = $libConfig->verbindungName. ' - ' .$libGlobal->page->getTitle();
			}
		} else {
			$result = $libConfig->verbindungName. ' - ' .$libGlobal->page->getTitle();
		}

		return $result;
	}

	function getSiteUrl(){
		global $libGenericStorage;

		$result = $libGenericStorage->loadValue('base_core', 'siteUrl');
		return $result;
	}

	function getSiteUrlAuthority(){
		$siteUrl = $this->getSiteUrl();
		$result = preg_replace('/https?:\/\//', '', $siteUrl);
		return $result;
	}

	function getSiteUrlHost(){
		$siteUrl = $this->getSiteUrl();
		$result = parse_url($siteUrl, PHP_URL_HOST);
		return $result;
	}

	function isEventPage(){
		global $libGlobal;

		return $libGlobal->page->getPid() == 'event'
				&& isset($_REQUEST['id']) && is_numeric($_REQUEST['id']);
	}
}
