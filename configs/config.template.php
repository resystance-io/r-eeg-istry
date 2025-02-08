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
    ['banking']
];


//////* LET'S CALL THIS, THE -- AUSTRIAN -- CONFIGURATION BLOCK *//////

$preNameTitles = [
    "Dipl.-Ing.",   // Diplom-Ingenieur (equivalent to MSc)
    "Dr.",          // Doktor (PhD equivalent)
    "FH-Prof.",     // Fachhochschul-Professor
    "Ing.",         // Ingenieur
    "Mag.",         // Magister (Master’s degree)
    "MMag.",        // Double Magister (e.g., MMag. rer.soc.oec.)
    "Priv.-Doz.",   // Privatdozent (habilitation)
    "Prof.",        // Professor
    "Univ.-Prof.",  // University Professor
    "Dr. techn.",   // Doctor of Technical Sciences
    "Dr. rer. nat.",// Doctor of Natural Sciences
    "Dr. phil.",    // Doctor of Philosophy (humanities)
    "Dr. iur."      // Doctor of Law
];

$postNameDegrees = [
    "BA",       // Bachelor of Arts
    "BSc",      // Bachelor of Science
    "LL.B.",    // Bachelor of Laws
    "MA",       // Master of Arts
    "LL.M.",    // Master of Laws
    "MBA",      // Master of Business Administration
    "MSc",      // Master of Science
    "PhD",      // Doctor of Philosophy (international designation)
    "MD",       // Doctor of Medicine
    "MEd"       // Master of Education
];
// shall we print debug messages?
// (enabling this in production is a hell of a bad idea)
$debug                  =   false;
