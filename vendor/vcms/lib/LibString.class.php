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

class LibString
{
    public function xmlentities($string)
    {
        $string = (string) $string;

        return str_replace(['&', '"', "'", '<', '>'], ['&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;'], $string);
    }

    public function protectXSS($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public function randomAlphaNumericString($len, $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789')
    {
        $string = '';

        for ($i = 0; $i < $len; $i++) {
            $pos = random_int(0, strlen($chars) - 1);
            $string .= $chars[$pos];
        }

        return $string;
    }

    public function isValidEmail($email)
    {
        $email = (string) $email;

        if ($email != '') {
            if (preg_match('/^([a-zA-Z0-9\.\_\-]+)@([a-zA-Z0-9\.\-]+\.[A-Za-z][A-Za-z]+)$/', $email)) {
                return true;
            }
        } else {
            return false;
        }
    }

    /**
    * Ensures that a stored URL has an http(s) scheme. This way a database value
    * cannot become a javascript: link that escaping would not catch.
    */
    public function assureHttpScheme($url)
    {
        $url = trim((string) $url);

        if ($url == '') {
            return '';
        }

        if (substr($url, 0, 7) != 'http://' && substr($url, 0, 8) != 'https://') {
            $url = 'http://' .$url;
        }

        return $url;
    }

    public function isValidURL($string)
    {
        $string = (string) $string;

        $urlRegEx =
            "/^" .
            "http:\/\/" .           // http-protocol
            "([0-9a-zA-Z-]+\.)+" .  // hostname and subdomains
            "[a-zA-Z]{1,4}" .       // toplevel domain
            "(\/.*)*" .             // anything with a leading / as rest of path
            "$/";

        if (preg_match($urlRegEx, $string)) {
            return true;
        } else {
            return false;
        }
    }

    public function getNotificationBoxText()
    {
        global $libGlobal;

        if (isset($libGlobal->notificationTexts) && is_array($libGlobal->notificationTexts)) {
            if (count($libGlobal->notificationTexts) > 0) {
                return '<div class="alert alert-success" role="alert">'. implode('<br />', $libGlobal->notificationTexts). '</div>';
            }
        }
    }

    public function getErrorBoxText()
    {
        global $libGlobal;

        if (isset($libGlobal->errorTexts) && is_array($libGlobal->errorTexts)) {
            if (count($libGlobal->errorTexts) > 0) {
                return '<div class="alert alert-danger" role="alert">'. implode('<br />', $libGlobal->errorTexts). '</div>';
            }
        }
    }

    public function getLastInsertId($lastInsertId, $id)
    {
        if ($lastInsertId == $id) {
            return ' last-insert-id ';
        }
    }

    public function normalizeStreet($street)
    {
        $street = (string) $street;

        $street = str_replace('str.', 'str', $street);
        $street = str_replace('straße', 'str', $street);
        $street = str_replace('Straße', 'str', $street);
        $street = preg_replace('/[^a-zA-ZäöüÄÖÜß\s]/i', '', $street);
        $street = trim($street);
        return $street;
    }
}
