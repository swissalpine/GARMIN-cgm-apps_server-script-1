<?php
    // Script zur Umformatierung der mongolab-Nighscout-Daten, damit diese von Garmin IQ weiterverwendet werden können.
    // Andreas May, Hamburg, www.laufen-mit-diabetes.de
 
	// Mongolab-URL mit der übertragenen Api ergänzen
    $_datenbank = $_GET[database];
    if($_datenbank == "") { $_datenbank = "nightscout"; }
    $_url = "https://api.mongolab.com/api/1/databases/".$_datenbank."/collections/entries?l=1&s={%27date%27:-1}&f={%27_id%27:0,%20%27sgv%27:1,%20%27direction%27:1,%27date%27:1}&apiKey=".$_GET["api"];
 
    $_lang = $_GET["sprache"];
 
       // Testfunktion
    if ($_GET["api"] == "12q34Test") {
       $_ausgabe = '{ "wert" : 149, "verzoegerung" : "------- TEST!!! --->", "pfeil" : "5" }'; 
    } else {
	// Auswertung der Abfrage, Löschen der eckigen Klammer (eine für Garmin ungültige json-Abfrage)
        $_result = implode('', file($_url));
	$_buffer = substr("$_result", 2, -2);
	$_daten = json_decode($_buffer);
 
	// Speichern der Daten in eigenen Variablen, Kürzen der Messzeit um die letzten drei Stellen (Tausendstel-Sekunden)
	$_messzeit = substr($_daten->{'date'}, 0, -3);
	$_blutzucker = $_daten->{'sgv'};
	$_richtung = $_daten->{'direction'};
 
	// Nummerierung der Pfeile, um Pfeilausgabe am Garmin vorzubereiten
	if ($_richtung == "") { $_pfeil = 0; } 
	if ($_richtung == "DoubleUp") {$_pfeil = 7; }
	if ($_richtung == "SingleUp") {$_pfeil = 6; }
	if ($_richtung == "FortyFiveUp") {$_pfeil = 5; }
	if ($_richtung == "Flat") {$_pfeil = 4; }
	if ($_richtung == "FortyFiveDown") {$_pfeil = 3; }
	if ($_richtung == "SingleDown") {$_pfeil = 2; }
	if ($_richtung == "DoubleDown") {$_pfeil = 1; }
 
	// Verzögerung berechnen und Ausgabe vorbereiten
	$_differenz = time() - $_messzeit;
	$_minuten = (int)($_differenz / 60);
	if ($_lang == 0) {
            if ($_minuten > 99) {
                $_minutenverz = $_minuten . ' min.';
            } else {
                $_minutenverz = $_minuten . ' minute' . ($_minuten != 1 ? 's' : '');
            }
        } else {
            if ($_minuten > 99) {
                $_minutenverz = $_minuten . ' Min.';
            } else {
                $_minutenverz = $_minuten . ' Minute' . ($_minuten != 1 ? 'n' : '');
            }
        }
 
	// json-Ausgabe erstellen und ausgeben
	$_ausgabe = '{ "wert" : ' . $_blutzucker . ', "verzoegerung" : "vor ' . $_minutenverz . '", "pfeil" : "' . $_pfeil . '" }'; 
    }
	echo $_ausgabe;
 
?>
