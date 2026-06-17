VCMS Installationsanleitung
===========================

Einleitung
----------
Im Folgenden wird die Installationsprozedur für das VCMS beschrieben. Das VCMS besteht aus einer Engine und Modulen. Die Engine ist im Ordner vendor gespeichert, die Module im Ordner modules.

Die Module sind in Internet- und Intranet-Module unterteilt, und können nach Bedarf zusammengestellt werden. Falls das VCMS als reine Intranet-Lösung betrieben werden soll, sind sämtliche Internet-Module zu löschen. Dies ermöglicht den Parallelbetrieb zu einer bereits bestehenden Website.


Installationsschritte
---------------------

### Entpacken

Nach dem Download befindet sich das VCMS in einer Zipdatei, die z. B. mit Winzip entpackt werden kann.

### Systemkonfiguration

Die Datei custom/systemconfig.php ist die zentrale Konfigurationsdatei. Diese muss mit einem UTF-8-fähigen Texteditor wie z. B. Atom (https://atom.io) angepasst werden. Bei der Datenbankkonfiguration unter $mysqlServer, $mysqlUser, $mysqlPass und $mysqlDb sind die Angaben des Hosters einzutragen, die normalerweise dem Konfigurationsmenü des Hosters entnommen werden können. Es ist darauf zu achten, dass die Anführungszeichen nicht entfernt werden.

### Hochladen

Das VCMS muss per FTP im Binary-Modus (nicht ASCII-Modus) in den Hauptordner des Hostings hochgeladen werden.

### Installation der Datenbank und eines initialen Internetwartes

Die Datenbank wird mit der Datei installer.php installiert. Dazu muss die installer.txt in installer.php umbenannt werden. Anschließend ist diese im Webbrowser aufzurufen und den dortigen Angaben zu folgen. Nach der Installation der Datenbank muss in der Datenbank ein Intranetaccount für einen Internetwart generiert werden. Auch dies wird mit der installer.php durchgeführt. Abschließend ist die installer.php zu löschen.

### Test

Nun sollte man sich im Intranet mit dem generierten Internetwart anmelden können. Der Internetwart kann im Intranet die Datenbank pflegen. Wenn ein Mitglied sich für das Intranet registriert, wird eine Anfrage an die Emailadresse des Webmasters geschickt, die in der systemconfig.php unter $emailWebmaster angegeben ist.

### Design

Im Ordner custom/styles können die Farbgebung und das Panier angepasst werden. Das Bild für die Startseite findet sich unter modules/mod_internet_home/custom/header.webp und ist ebenfalls anzupassen.

### Entfernen nicht benötigter Module

Module können im Intranet mit dem Modul-Manager deinstalliert oder einfach aus dem Ordner modules gelöscht werden.

### Einpflegen bestehender Seiten

Falls bereits eine Vereinswebseite existiert, deren Inhalte übernommern werden sollen, kann das Modul mod_internet_verein als Vorlage bearbeitet werden oder ein neues Modul angelegt werden. In der Datei modules/mod_internet_verein/meta.json können Seiten registriert werden.
