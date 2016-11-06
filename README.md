VCMS
====

Das VCMS ist ein freies Content-Management-System für Korporationen, das von diversen Korporationen unterschiedlicher Dachverbände als Internet-Auftritt oder Intranet-Lösung genutzt wird. Es unterstützt die Semesterplanung mit einem Semesterprogramm, das durch Fotogalerien, ein Anmeldesystem für Veranstaltungen, einen Chargierkalender, Nachrichtenbeiträge und Facebook-Integration angereichert wird. Zur Vereins-Organisation steht eine Mitgliederdatenbank, eine Rundbrieffunktion, ein Reservierungssystem und ein Download-Bereich bereit.


Screenshots
-----------

<img src="http://uwol.github.io/img/vcms/internet.png" alt="Internet" style="width:100%"/>

<img src="http://uwol.github.io/img/vcms/intranet.png" alt="Intranet" style="width:100%"/>


Fähigkeiten
-----------

* Semesterprogramm mit Fotogalerien und Veranstaltungsanmeldungen
* Intranet-Portal mit Semester-Zeitleiste, Nachrichten, Reservierungen
* E-Mail-Rundbrief
* Datenbank
  * Mitglieder, Gruppen (Aktive, AHAH, Ehepartner etc.), Status (A/B-Phil, ex loco), Leibverhältnisse
  * Semesterprogramm, Veranstaltungen, Fotogalerien, Anmeldungen, Chargierkalender
  * Vorstände, Ämter, Rezeptionen, Promotionen, Philistrierungen, Consemester, Conchargen
  * Vereine, Mitgliedschaften
* Adress-Export für Semesteranschreiben, Semesterprogramm-Export als iCalendar
* Flexible Semesterkonfiguration: Semester, Trimester, terms, ...


Modulkatalog
------------

### Internet-Module für den öffentlichen Bereich

* Modul _Home_: Startseite mit Ankündigungen, letzten Fotos und nächsten Veranstaltungen.
* Modul _Semesterprogramm_: Kalender mit Anmeldesystem, Fotogalerien und iCalendar-Export. Mitglieder können Fotos hochladen, die durch Fotowart öffentlich oder intern freigeschaltet werden.
* Modul _Kontakt_: Impressum und Kontaktformular.
* Modul _Verein_: Beschreibung des Vereins.

### Intranet-Module für den internen Bereich

* Modul _Portal_: Zeitleiste mit Veranstaltungen, Nachrichten, Fotos und Ereignissen des Semesters.
* Modul _Person_: Mitglieder können ihre Daten selbstständig pflegen. Darstellung der Altersstruktur und des Leibverhältnis-Stammbaums.
* Modul _Neues_: Mitglieder können Nachrichten im Intranet veröffentlichen.
* Modul _Rundbrief_: E-Mail-Verteiler mit Filtermöglichkeit anhand von Gruppen (Aktive, AHAH etc.) und Regionalzirkeln.
* Modul _Chargierkalender_: Organisation der Chargierpräsenz und Teilnahmebestätigung durch Mitglieder.
* Modul _Reservierungen_: Reservierung von Kneipe, Bootshaus etc.
* Modul _Downloads_: Bereitstellung von Dateien durch Vorstand und Warte.
* Modul _Daten_: Stammdaten zu Personen, Veranstaltungen und Vereinen.
* Modul _Export_: Adress-Export für Semesteranschreiben, runde Geburtstage und Jubiläen.


Installation
------------

Die Installationsanleitung ist in der Datei INSTALL.md gespeichert. Die technischen Anforderungen sind:

* PHP ab Version 5.5
* MySQL ab Version 4.1
* ImageMagick oder GDlib für Fotogalerien


Lizenz
------

Lizenziert unter der GNU General Public License (GPL) Version 3. Details in LICENSE.

### Und zum Schluss...

Pull Requests auf GitHub und Anregungen bzw. Feedback per E-Mail sind willkommen!
