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
    * Timezone, normally unchanged
    * Valid values at http://www.php.net/manual/de/timezones.php
    */
    public $timezone = 'Europe/Berlin';

    /**
    * Optional adjustments
    */
    public $defaultHome = 'home';

    /*
    * By default the winter semester runs from October to March and the summer semester from April to October.
    * Normally no adjustments are needed; the further description only applies to the following special cases:
    * ONLY IF SEMESTERS SHOULD LIE IN OTHER MONTHS OR SEMESTERS OTHER THAN WS & SS ARE WANTED,
    * The following comment lines can be uncommented to configure which semesters lie in which months:
    *
    * In the example, since year 0 the summer semester (SS) runs from month 4 (April) to month 9 (September) and
    * the winter semester (WS) from month 10 (October) to month 3 (March), and since 2008 the first term (FT)
    * from month 1 (January) to month 6 (June) and the second term (ST) from month 7 (July) to month 12 (December).
    *
    * The example can be modified: further years can be added;
    * Semester prefixes (SS, WS, FT, ST, ...) can be changed, but they may only consist of EXACTLY 2 characters from a-z and A-Z.
    * Each year must also contain EXACTLY 12 months or 12 semester prefixes! Year 0 must be present.
    */
    //var $semestersConfig = array(
    //	0 		=> array('WS', 'WS', 'WS', 'SS', 'SS', 'SS', 'SS', 'SS', 'SS', 'WS', 'WS', 'WS'),
    //	2008 	=> array('FT', 'FT', 'FT', 'FT', 'FT', 'FT', 'ST', 'ST', 'ST', 'ST', 'ST', 'ST')
    //);
}
