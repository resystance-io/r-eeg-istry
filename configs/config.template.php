<?php

/////////* DATABASE *//////////////

$DBLOGON_HOST           =   '';
$DBLOGON_USER           =   '';
$DBLOGON_PASS           =   '';
$DBLOGON_DB             =   '';

/////////* DATABASE TABLE MAPPINGS *///////////

$DBTABLE_REGISTRATIONS		       = 'registrations';
$DBTABLE_METERS                    = 'meters';
$DBTABLE_STORAGES                  = 'storages';
$DBTABLE_TENANTS                   = 'tenants';
$DBTABLE_DASHBOARDS                = 'dashboards';
$DBTABLE_DASHBOARD_NOTES           = 'dashboard_notes';
$DBTABLE_DASHBOARD_COLUMNS         = 'dashboard_columns';
$DBTABLE_DASHBOARD_LAYOUT          = 'dashboard_layout';
$DBTABLE_DASHBOARD_USERS           = 'dashboard_users';
$DBTABLE_DASHBOARD_USERS_X_TENANTS = 'dashboard_users_x_tenants';
$DBTABLE_UPLOADS                   = 'uploads';
$DBTABLE_TEMPORARY                 = 'temporary';

/////////* EMAIL CONFIGURATION *///////////////

$MAIL_MTA_ADDRESS = '';
$MAIL_MTA_PORT = 587;
$MAIL_MTA_USER = '';
$MAIL_MTA_PASS = '';
$MAIL_FROM = ['email_address@domain.com'=>'nicename'];
$MAIL_DEBUGGING = 0;
$MAIL_OPTIONS = ['ssl'=>[ 'verify_peer'=>false, 'verify_peer_name'=>false, 'allow_self_signed'=>true ] ];

/////////* BEHAVIOR *///////////////

// CATCH-ALL:
// if a visitor does not visit with a shortcode,
// shall we fall back to a default tenant that is going to act as a catch-all?
$tenant_fallback_on_empty_request = true;

// DEFAULT TENANT:
// if catch-all is enabled, which tenant is designated as default tenant?
$default_tenant_id = '1';

// DEFER TENANT ASSIGNMENT:
// while being used for presenting information about a certain tenant during
// the onboarding dialogues, the tenant will NOT be assigned to the finished
// registration. The tenant will be manually assigned later.
$defer_tenant_assignment = true;

// DASHBOARD PAGE SIZE
// how many entries shall be displayed on a single backend result page by
// default (this can be overridden by a user)
$default_page_size = 5;

$EEG_CONSUMERS_PREFILL = 'AT 003000 00000 0000000000 000';
$EEG_SUPPLIERS_PREFILL = 'AT 003000 00000 0000000000 003';

$mandatory_file_uploads = ['invoice', 'credit'];

/////* LAYOUT SPECIFIC CONFIGURATION *///////////

// Each array index creates an additional step/pane.
// You can put multiple panes into a single step,

// CAVEAT !!! "meters" MUST be after individual meters (consumption, supply),
// so it can't be BEFORE individuals and must NOT be in the same step either.
// The "preparation" step is not mandatory and can be omitted, but if enabled
// it MUST be the first step as it would not make any sense later.

$JOIN_LAYOUT = [
    ['preparation'],
    ['generic'],
    ['consumption','supply','storage'],
    ['meters'],
    ['uploads'],
    ['banking'],
    ['approvals'],
    ['optionals']
];

$FAST_JOIN_LAYOUT = [
    ['generic','consumption','supply','storage','banking','approvals','uploads','optionals'],
    ['meters']
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


// Alternative Layout: All meters on one page:

/*$JOIN_LAYOUT = [
    ['generic'],
    ['consumption','supply','storage'],
    ['meters'],
    ['uploads'],
    ['banking'],
    ['approvals'],
    ['optionals']
];*/


// shall we print debug messages?
// (enabling this in production is a hell of a bad idea)
$debug                  =   false;
