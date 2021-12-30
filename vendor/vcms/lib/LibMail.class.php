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

class LibMail{
	function createPHPMailer($fromName = ''){
		global $libConfig, $libGenericStorage;

		$smtpPort = $libGenericStorage->loadValue('base_core', 'smtp_port');

		$mail = new \PHPMailer\PHPMailer\PHPMailer();
		$mail->setFrom($libConfig->emailInfo, $fromName);
		$mail->CharSet = 'UTF-8';

		$mail->isSMTP();
		$mail->SMTPAuth = true;
		$mail->Host = $libGenericStorage->loadValue('base_core', 'smtp_host');
		$mail->Port = $smtpPort;
		$mail->Username = $libGenericStorage->loadValue('base_core', 'smtp_username');
		$mail->Password = $libGenericStorage->loadValue('base_core', 'smtp_password');

		if($smtpPort == 465) {
			$mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
		} elseif($smtpPort == 587) {
			$mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
		}

		return $mail;
	}
}
