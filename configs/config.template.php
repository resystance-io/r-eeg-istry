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

// shall we print debug messages?
// (enabling this in production is a hell of a bad idea)
$debug                  =   false;
