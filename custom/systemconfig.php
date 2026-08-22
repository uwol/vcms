<?php

class LibConfig
{
    public $mysqlServer = 'localhost';
    public $mysqlUser = 'username';
    public $mysqlPass = 'password';
    public $mysqlDb = 'datenbankname';
    public $mysqlPort = '';

    public $verbindungName = 'K.St.V. Example';
    public $verbindungDachverband = 'KV';

    public $verbindungZusatz = '';
    public $verbindungStrasse = 'Musterstr. 20';
    public $verbindungPlz = '12345';
    public $verbindungOrt = 'Musterstadt';
    public $verbindungLand = '';
    public $verbindungTelefon = '+49 251 123456789';

    public $seiteBeschreibung = 'Katholischer Studentenverein Example im Kartellverband katholischer deutscher Studentenvereine (KV) zu Münster (Westf.)';
    public $seiteKeywords = 'Studentenverbindung, Universität, Verbindung, Studentenverein, Student';
    public $emailInfo = 'kontakt@example.net';
    public $emailWebmaster = 'webmaster@example.net';

    public $chargenSenior = 'x';
    public $chargenJubelSenior = 'x';
    public $chargenConsenior = 'vx';
    public $chargenScriptor = 'xx';
    public $chargenQuaestor = 'xxx';
    public $chargenFuchsmajor = 'FM';
    public $chargenFuchsmajor2 = 'FM 2';
    public $chargenAHVSenior = 'AH-x';
    public $chargenAHVConsenior = 'AH-vx';
    public $chargenAHVKeilbeauftragter = 'K';
    public $chargenAHVScriptor = 'AH-xx';
    public $chargenAHVQuaestor = 'AH-xxx';
    public $chargenHVVorsitzender = '';
    public $chargenHVKassierer = '';
    public $chargenArchivar = '';
    public $chargenRedaktionswart = 'Red.';
    public $chargenVOP = 'VOP';
    public $chargenVVOP = 'VVOP';
    public $chargenVOPxx = 'VOPxx';
    public $chargenVOPxxx = 'VOPxxx';
    public $chargenVOPxxxx = 'VOPxxxx';

    /**
    * Zeitzone, normalerweise unverändert
    * Valide Werte unter http://www.php.net/manual/de/timezones.php
    */
    public $timezone = 'Europe/Berlin';

    /**
    * optionale Anpassungen
    */
    public $defaultHome = 'home';

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
