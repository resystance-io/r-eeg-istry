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

/////////* EMAIL CONFIGURATION *///////////////

$MAIL_MTA_ADDRESS = '';
$MAIL_MTA_PORT = 587;
$MAIL_MTA_USER = '';
$MAIL_MTA_PASS = '';
$MAIL_FROM = ['sender@domain.tld' => 'nicename'];
$MAIL_DEBUGGING = 0;
$MAIL_OPTIONS = ['ssl'=>[ 'verify_peer'=>false, 'verify_peer_name'=>false, 'allow_self_signed'=>true ] ];

/////////* EEG SPECIFIC DATA *///////////////

$EEG_NICENAME = "FOO";
$EEG_CONSUMERS_PREFIX = 'AT 000000 00000 0000000000 000';
$EEG_SUPPLIERS_PREFIX = 'AT 000000 00000 0000000000 000';

/////* LAYOUT SPECIFIC CONFIGURATION *///////////

$JOIN_LAYOUT = [
    ['preparation'],
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
