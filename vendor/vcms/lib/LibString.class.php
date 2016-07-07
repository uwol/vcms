<?php
/*
This file is part of VCMS.

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
*/

namespace vcms;

class LibString{
	/*
	* remove XML entities
	*/
	function xmlentities($string){
		return str_replace(array('&', '"', "'", '<', '>'), array('&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;'), $string);
	}

	/*
	* protect parameter from cross-site-scripting
	*/
	function protectXSS($value){
		return htmlspecialchars($value, ENT_NOQUOTES, 'UTF-8');
	}

	function randomAlphaNumericString($len, $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'){
		$string = '';

		for ($i = 0; $i < $len; $i++){
			$pos = rand(0, strlen($chars)-1);
			$string .= $chars{$pos};
		}

		return $string;
	}

	function isValidEmail($email){
		if($email != ''){
			if(preg_match('/^([a-zA-Z0-9\.\_\-]+)@([a-zA-Z0-9\.\-]+\.[A-Za-z][A-Za-z]+)$/', $email)){
				return true;
			}
		} else {
			return false;
		}
	}

	function isValidURL($string){
		$urlRegEx =
			"/^" .
			"http:\/\/" .           // http-protocol
			"([0-9a-zA-Z-]+\.)+" .  // hostname and subdomains
			"[a-zA-Z]{1,4}" .       // toplevel domain
			"(\/.*)*" .             // anything with a leading / as rest of path
			"$/";

		if(preg_match($urlRegEx, $string)){
			return true;
		} else {
			return false;
		}
	}

	/*
	* convert BBCode to HTML
	*/
	function parseBBCode($text){
		$text = preg_replace('/\[b\](.*?)\[\/b\]/', '<b>$1</b>', $text);
		$text = preg_replace('/\[i\](.*?)\[\/i\]/', '<i>$1</i>', $text);
		$text = preg_replace('#(\[url=?"?)([^\]"]*)("?\])([^\[]*)(\[/url\])#', '<a href="$2">$4</a>', $text);

		return $text;
	}

	/*
	* remove BBCode from string
	*/
	function deleteBBCode($text){
		$text = preg_replace('/\[b\](.*?)\[\/b\]/', '$1', $text);
		$text = preg_replace('/\[i\](.*?)\[\/i\]/', '$1', $text);
		$text = preg_replace('#(\[url=?"?)([^\]"]*)("?\])([^\[]*)(\[/url\])#', '$4', $text);

		return $text;
	}

	/*
	* truncate string
	*/
	function truncate($string, $start = 50, $replacement = ' ...') {
		if(strlen($string) <= $start){
			return $string;
		}

		$whitespaceposition = strpos($string, ' ', $start);

		if(is_numeric($whitespaceposition)){
			$string = substr($string, 0, $whitespaceposition);
			return substr_replace($string, $replacement, $whitespaceposition);
		} else {
			return $string;
		}
	}

	function getNotificationBoxText(){
		global $libGlobal;

		if(isset($libGlobal->notificationTexts) && is_array($libGlobal->notificationTexts)){
			if(count($libGlobal->notificationTexts) > 0){
				return '<div class="alert alert-success" role="alert">'. implode('<br />', $libGlobal->notificationTexts). '</div>';
			}
		}
	}

	function getErrorBoxText(){
		global $libGlobal;

		if(isset($libGlobal->errorTexts) && is_array($libGlobal->errorTexts)){
			if(count($libGlobal->errorTexts) > 0){
				return '<div class="alert alert-danger" role="alert">'. implode('<br />', $libGlobal->errorTexts). '</div>';
			}
		}
	}

	function getLastInsertId($lastInsertId, $id){
		if($lastInsertId == $id){
			return ' lastInsertId ';
		}
	}
}