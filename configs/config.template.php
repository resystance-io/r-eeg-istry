<?php

/////////* DATABASE *//////////////

$DBLOGON_HOST           =   '';
$DBLOGON_USER           =   '';
$DBLOGON_PASS           =   '';
$DBLOGON_DB             =   '';

/////////* DATABASE TABLE MAPPINGS *///////////

$DBTABLE_REGISTRATIONS		= 	'registrations';
$DBTABLE_METERS             =   'meters';
$DBTABLE_STORAGES           =   'storages';

/////////* EEG SPECIFIC DATA *///////////////

$EEG_CONSUMERS_PREFIX = 'AT000000000000000000000000';
$EEG_SUPPLIERS_PREFIX = 'AT000000000000000000000000';

/////* LAYOUT SPECIFIC CONFIGURATION *///////////

$JOIN_LAYOUT = [
    ['generic'],
    ['consumption'],
    ['supply'],
    ['storage'],
    ['meters'],
    ['banking'],
    ['approvals']
];


//////* LET'S CALL THIS, THE -- AUSTRIAN -- CONFIGURATION BLOCK *//////

$preNameTitles = [
    "Dipl.-Ing." => "Dipl.-Ing.",       // Diplom-Ingenieur (equivalent to MSc)
    "Dr." => "Dr.",              // Doktor (PhD equivalent)
    "FH-Prof." => "FH-Prof.",         // Fachhochschul-Professor
    "Ing." => "Ing.",             // Ingenieur
    "Mag." => "Mag.",             // Magister (Master’s degree)
    "MMag." => "MMag.",            // Double Magister (e.g., MMag. rer.soc.oec.)
    "Priv.-Doz." => "Priv.-Doz.",       // Privatdozent (habilitation)
    "Prof." => "Prof.",            // Professor
    "Univ.-Prof." => "Univ.-Prof.",      // University Professor
    "Dr. techn." => "Dr. techn.",       // Doctor of Technical Sciences
    "Dr. rer. nat." => "Dr. rer. nat.",    // Doctor of Natural Sciences
    "Dr. phil." => "Dr. phil.",        // Doctor of Philosophy (humanities)
    "Dr. iur." => "Dr. iur."          // Doctor of Law
];

$postNameTitles = [
    "BA" => "BA",       // Bachelor of Arts
    "BSc" => "BSc",      // Bachelor of Science
    "LL.B." => "LL.B.",    // Bachelor of Laws
    "MA" => "MA",       // Master of Arts
    "LL.M." => "LL.M.",    // Master of Laws
    "MBA" => "MBA",      // Master of Business Administration
    "MSc" => "MSc",      // Master of Science
    "PhD" => "PhD",      // Doctor of Philosophy (international designation)
    "MD" => "MD",       // Doctor of Medicine
    "MEd" => "MEd"       // Master of Education
];

// shall we print debug messages?
// (enabling this in production is a hell of a bad idea)
$debug                  =   false;
