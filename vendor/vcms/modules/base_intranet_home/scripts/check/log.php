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

if (!is_object($libGlobal) || !$libAuth->isLoggedin()) {
    exit();
}


$numberOfLoginErrorsThreshold = 14;
$numberOfLoginErrorDaysThreshold = 14;

if (in_array('internetwart', $libAuth->getOffices()) || in_array('datenpflegewart', $libAuth->getOffices())) {
    $stmt = $libDb->prepare('SELECT COUNT(mitglied) AS numberOfLoginErrors FROM sys_log_intranet WHERE aktion = 2 AND DATEDIFF(NOW(), datum) < :days GROUP BY mitglied HAVING numberOfLoginErrors >= :errors');
    $stmt->bindValue(':days', $numberOfLoginErrorDaysThreshold, PDO::PARAM_INT);
    $stmt->bindValue(':errors', $numberOfLoginErrorsThreshold, PDO::PARAM_INT);
    $stmt->execute();
    $stmt->bindColumn('numberOfLoginErrors', $count);
    $stmt->fetch();

    if ($count > 0) {
        $logText = 'Personen mit erfolglosen Intranet-Anmeldungen in den letzten ' .$numberOfLoginErrorDaysThreshold. ' Tagen: ';

        $stmt = $libDb->prepare('SELECT COUNT(mitglied) AS numberOfLoginErrors, mitglied FROM sys_log_intranet WHERE aktion = 2 AND DATEDIFF(NOW(), datum) < :days GROUP BY mitglied HAVING numberOfLoginErrors >= :errors ORDER BY numberOfLoginErrors DESC');
        $stmt->bindValue(':days', $numberOfLoginErrorDaysThreshold, PDO::PARAM_INT);
        $stmt->bindValue(':errors', $numberOfLoginErrorsThreshold, PDO::PARAM_INT);
        $stmt->execute();

        $affectedMembers = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $memberId = (int) $row['mitglied'];

            $affectedMembers[] =
                '<span class="badge">' .((int) $row['numberOfLoginErrors']). '</span> ' .
                '<a href="index.php?pid=intranet_person&amp;id=' .$memberId. '">' .
                $libString->protectXSS($libPerson->getNameString($memberId, 4)). '</a>';
        }

        // The member links make this message markup, so it cannot go through
        // errorTexts, which escapes its entries. Every dynamic part above is
        // escaped or cast to an integer instead.
        echo $libString->getErrorBoxHtml($libString->protectXSS($logText). implode(', ', $affectedMembers));
    }
}
