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
				return '<p class="alert alert-success" role="alert">'. implode('<br />', $libGlobal->notificationTexts). '</p>';
			}
		}
	}

	function getErrorBoxText(){
		global $libGlobal;

		if(isset($libGlobal->errorTexts) && is_array($libGlobal->errorTexts)){
			if(count($libGlobal->errorTexts) > 0){
				return '<p class="alert alert-danger" role="alert">'. implode('<br />', $libGlobal->errorTexts). '</p>';
			}
		}
	}

	function printLastInsertIdClass($lastInsertId, $id){
		if($lastInsertId == $id){
			echo ' lastInsertId ';		
		}
	}

	/*
	* hyphenation
	*/
	function silbentrennung($hyph_string, $width) {
		// Trennungszeicheneichen:
		$hyph             = "\xc2\xad";  //bedingter Trennstrich = html_entity_decode('&shy;',ENT_QUOTES,'UTF-8')
		$hyph2            = "\x20\x0b";  //Zero-width-Space      = html_entity_decode('&#8203;',ENT_QUOTES,'UTF-8')
		// Zeichen, hinter denen ohne Trennstrich getrennt wird:
		$symbols          = array('-','/','"','.',',',';',':',')',']');
		$leftmin          = 2;
		$rightmin         = 2;

		if(file_exists('lib/thirdparty/hyphenation/de.php')) {
			include('lib/thirdparty/hyphenation/de.php');

			$words = explode(" ", $hyph_string);
			$result_string = '';
			$letters = 0;

			// Wort fuer Wort
			while(!is_null($word = array_shift($words))){
				$word = trim($word);
				$offset = substr_count($word, $hyph) + substr_count($word, $hyph2); // Anzahl der manuellen Trennzeichen.

				// Korrektur der Wortlaenge
				$wordlength = strlen($word) - $offset;
				$syllarray = explode($hyph, $word);

				// Wenn das Wort als Ganzes in die Zeile passt, werden manuelle Trennzeichen entfernt:
				if (($letters + $wordlength) <= $width){
					foreach ($syllarray as $syllable){
						$syllarray2 = explode($hyph2, $syllable);
						$result_string .= implode($syllarray2);
					}

					$letters += $wordlength;
				// Manuelle Trennzeichen vorhanden?
				} elseif($offset > 0) {
                    $hyphenator = '';

					// Silbe fuer Silbe anhand der manuellen Trennzeichen
					foreach ($syllarray as $syllable){
						$syllarray2 = explode($hyph2, $syllable);

						foreach ($syllarray2 as $syllable2){
							// Trennung
							if (($letters + strlen($syllable2)) >= $width){
								$result_string .= $hyphenator;
								$letters = 0;
							}

							$result_string .= $syllable2;
							$letters += strlen($syllable2);
							// Wenn eine Silbe manuell mit $hyph2 abgesetzt wurde, muss nach ihrem Anfuegen das Trennzeichen fuer eventuelle Trennungen auf $hyph2 gesetzt werden.
		                    $hyphenator = $hyph2;
						}

						// Wenn eine Silbe manuell mit $hyph abgesetzt wurde, muss nach ihrem Anfuegen das Trennzeichen fuer eventuelle Trennungen auf $hyph gesetzt werden.
	                    $hyphenator = $hyph;
					}
				}
				// TeX-Algorithmus
				else {
					$positions = "";
					$hyphenated_word = "";
					$syll_array = array();
					$word_without_hyphen = "";
					$tex_word = " " . strtolower($word) . " ";

					for($i = 0; $i < strlen($tex_word); $i++){
						$positions .= 0;
					}

					for($start = 0; $start < strlen($tex_word); $start++){
						for($length = 1; $length <= strlen($tex_word) - $start; $length++){
							$patterns_index = substr(substr($tex_word, $start), 0, $length);

							if(isset($patterns[$patterns_index])){
								$values = $patterns[$patterns_index];
								$i = $start;

								for($p = 0; $p < strlen($values); $p++){
									$value = substr($values, $p, 1);

									if($value > $positions[$i - 1]){
										$positions[$i - 1] = $value;
									}

									$i++;
								}
							}
						}
					}

					$positions = trim($positions);

					for($i = 0; $i < strlen($word); $i++){
						$word_without_hyphen = implode('',$syll_array);

						if($positions[$i] % 2 != 0 && $i != 0 && $i >= $leftmin && $i <= strlen($word) - $rightmin){
							array_push($syll_array, substr($word, strlen($word_without_hyphen), $i - strlen($word_without_hyphen)));
						}
					}

					array_push($syll_array, substr($word, strlen($word_without_hyphen), $i - strlen($word_without_hyphen)));

					// Silbe fuer Silbe
					while (!is_null($syllable = array_shift($syll_array))){
						$syllable = trim($syllable);

						if ((($letters + strlen($syllable)) == $width) && (in_array(substr($syllable, -1),$symbols))){
							$result_string .= ($syllable . $hyph2);
							$letters = 0;
						} else {
							if (($letters + strlen($syllable)) >= $width){
								$letters = 0;

								if ((implode('',$syll_array) != $word) && ($result_string == trim($result_string))){
	    							if (in_array(substr($result_string, -1),$symbols)){
										$result_string .= $hyph2;
									} else {
										$result_string .= $hyph;
									}
								}
							}

							$result_string .= $syllable;
							$letters += strlen($syllable);
						}
					}
				}

				if (strlen($word) != 0){
					$result_string .= ' ';
					$letters++;
				}
			}

			$result_string = $this->xmlentities($result_string);
		} else {
			$result_string = wordwrap($hyph_string,$width,'<br />',true);
		}

		return trim($result_string);
	}
}
?>