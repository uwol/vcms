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

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class LibMail{
	function createPHPMailer($fromName = ''){
		global $libConfig, $libGenericStorage;

		$smtpPort = intval($libGenericStorage->loadValue('base_core', 'smtp_port'));

		$mail = new PHPMailer(true);
		$mail->SMTPDebug = SMTP::DEBUG_OFF;
		$mail->setFrom($libConfig->emailInfo, $fromName);
		$mail->CharSet = 'UTF-8';

		$mail->isSMTP();
		$mail->SMTPAuth = true;
		$mail->Host = $libGenericStorage->loadValue('base_core', 'smtp_host');
		$mail->Port = $smtpPort;
		$mail->Username = $libGenericStorage->loadValue('base_core', 'smtp_username');
		$mail->Password = $libGenericStorage->loadValue('base_core', 'smtp_password');

		$smtp_use_auto_tls = intval($libGenericStorage->loadValue('base_core', 'smtp_use_auto_tls'));
		if($smtp_use_auto_tls == 1) {
			$mail->SMTPAutoTLS = true;
		} elseif($smtpPort == 465) {
			$mail->SMTPAutoTLS = false;
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		} elseif($smtpPort == 587) {
			$mail->SMTPAutoTLS = false;
			$smtp_use_starttls = intval($libGenericStorage->loadValue('base_core', 'smtp_use_starttls'));
			if($smtp_use_starttls == 1) {
				$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
			}
		} else {
			$mail->SMTPAutoTLS = false;
		}

		return $mail;
	}

	function createPHPRundbriefMailer($fromName = ''){
		global $libConfig, $libGenericStorage;

		$use_rundbrief_smtp = intval($libGenericStorage->loadValue('base_core', 'use_rundbrief_smtp'));
		if($use_rundbrief_smtp != 1) {
			return $this->createPHPMailer($fromName);
		}

		$smtpPort = intval($libGenericStorage->loadValue('base_core', 'rundbrief_smtp_port'));

		$mail = new PHPMailer(true);
		$mail->SMTPDebug = SMTP::DEBUG_OFF;
		$mail->setFrom($libConfig->emailInfo, $fromName);
		$mail->CharSet = 'UTF-8';

		$mail->isSMTP();
		$mail->SMTPAuth = true;
		$mail->Host = $libGenericStorage->loadValue('base_core', 'rundbrief_smtp_host');
		$mail->Port = $smtpPort;
		$mail->Username = $libGenericStorage->loadValue('base_core', 'rundbrief_smtp_username');
		$mail->Password = $libGenericStorage->loadValue('base_core', 'rundbrief_smtp_password');

		$smtp_use_auto_tls = intval($libGenericStorage->loadValue('base_core', 'rundbrief_smtp_use_auto_tls'));
		if($smtp_use_auto_tls == 1) {
			$mail->SMTPAutoTLS = true;
		} elseif($smtpPort == 465) {
			$mail->SMTPAutoTLS = false;
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		} elseif($smtpPort == 587) {
			$mail->SMTPAutoTLS = false;
			$smtp_use_starttls = intval($libGenericStorage->loadValue('base_core', 'rundbrief_smtp_use_starttls'));
			if($smtp_use_starttls == 1) {
				$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
			}
		} else {
			$mail->SMTPAutoTLS = false;
		}

		return $mail;
	}
}
