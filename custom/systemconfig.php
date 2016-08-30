<?php
class LibConfig{
	var $mysqlServer = 'localhost';
	var $mysqlUser = 'username';
	var $mysqlPass = 'password';
	var $mysqlDb = 'datenbankname';
	var $mysqlPort = '';

	var $verbindungName = 'K.St.V. Example';
	var $verbindungDachverband = 'KV';

	var $verbindungZusatz = '';
	var $verbindungStrasse = 'Musterstr. 20';
	var $verbindungPlz = '12345';
	var $verbindungOrt = 'Musterstadt';
	var $verbindungLand = '';
	var $verbindungTelefon = '+49 251 123456789';

	var $seiteBeschreibung = 'Katholischer Studentenverein Example im Kartellverband katholischer deutscher Studentenvereine (KV) zu Muenster (Westf.)';
	var $seiteKeywords = 'Studentenverbindung, Universitaet, Verbindung, Studentenverein, Student';
	var $emailInfo = 'info@example.net';
	var $emailWebmaster = 'webmaster@example.net';

	var $chargenSenior = 'x';
	var $chargenJubelSenior = 'x';
	var $chargenConsenior = 'vx';
	var $chargenScriptor = 'xx';
	var $chargenQuaestor = 'xxx';
	var $chargenFuchsmajor = 'FM';
	var $chargenFuchsmajor2 = 'FM 2';
	var $chargenAHVSenior = 'AH-x';
	var $chargenAHVConsenior = 'AH-vx';
	var $chargenAHVKeilbeauftragter = 'K';
	var $chargenAHVScriptor = 'AH-xx';
	var $chargenAHVQuaestor = 'AH-xxx';
	var $chargenHVVorsitzender = '';
	var $chargenHVKassierer = '';
	var $chargenArchivar = '';
	var $chargenRedaktionswart = 'Red.';
	var $chargenVOP = 'VOP';
	var $chargenVVOP = 'VVOP';
	var $chargenVOPxx = 'VOPxx';
	var $chargenVOPxxx = 'VOPxxx';
	var $chargenVOPxxxx = 'VOPxxxx';

	/**
	* Zeitzone, normalerweise unverändert
	* Valide Werte unter http://www.php.net/manual/de/timezones.php
	*/
	var $timezone = 'Europe/Berlin';

	/**
	* optionale Anpassungen
	*/
	var $defaultHome = 'home';

	/*
	* Standardmäßig liegt das Wintersemester im System von Oktober bis März und das Sommersemester von April bis Oktober.
	* Normalerweise sind Anpassungen nicht nötig, sodass die weitere Beschreibung nur für folgenden Spezialfälle gilt:
	* NUR FALLS SEMESTER IN ANDEREN MONATEN LIEGEN SOLLEN ODER ANDERE SEMESTER ALS WS & SS GEWÜNSCHT SIND,
	* kann durch Entfernen der folgenden // konfiguriert werden, welche Semester in welchen Monaten liegen:
	*
	* Im Beispiel liegt seit dem Jahr 0 das Sommersemester (SS) von Monat 4 (April) bis Monat 9 (September) und
	* das Wintersemester (WS) von Monat 10 (Oktober) bis Monat 3 (März), sowie seit dem Jahr 2008 der first term (FT)
	* von Monat 1 (Januar) bis Monat 6 (Juni) und der second term (ST) von Monat 7 (Juli) bis Monat 12 (Dezember).
	*
	* Das Beispiel kann abgeändert werden: Weitere Jahre können hinzugefügt werden;
	* Semesterpräfixe (SS, WS, FT, ST, ...) können geändert werden, dürfen aber nur aus GENAU 2 Zeichen aus a-z und A-Z
	* bestehen. Jedes Jahr muss zudem GENAU 12 Monate bzw. 12 Semesterpräfixe enthalten! Das Jahr 0 muss vorhanden sein.
	*/
	//var $semestersConfig = array(
	//	0 		=> array('WS', 'WS', 'WS', 'SS', 'SS', 'SS', 'SS', 'SS', 'SS', 'WS', 'WS', 'WS'),
	//	2008 	=> array('FT', 'FT', 'FT', 'FT', 'FT', 'FT', 'ST', 'ST', 'ST', 'ST', 'ST', 'ST')
	//);
}
