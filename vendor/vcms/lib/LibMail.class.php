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
	function configurePHPMailer($mail){
		global $libConfig, $libGenericStorage;

		$mail->From = $libConfig->emailInfo;
		$mail->SMTPOptions = array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			)
		);
		$mail->CharSet = 'UTF-8';

		if($libGenericStorage->loadValue('base_core', 'smtpEnable') == 1){
			$mail->IsSMTP();
			$mail->SMTPAuth = true;
			$mail->Host = $libGenericStorage->loadValue('base_core', 'smtpHost');
			$mail->Username = $libGenericStorage->loadValue('base_core', 'smtpUsername');
			$mail->Password = $libGenericStorage->loadValue('base_core', 'smtpPassword');
		}
	}
}