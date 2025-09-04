<?php

    // Turn off all error reporting since some servers do not provide a way
    // to manipulate their php.ini and we can't handle warnings that break our
    // session setup:
    error_reporting(-1);

    include_once('views/view.inc.php');
    $view = new VIEW();

    // If we're dealing with a download request we're going to make sure this is
    // as early as possible in case we're not output buffered

    if (isset($_REQUEST['download']))
    {
        // people trying to download documents
        if (($view->view_handle_backend_login() === true) || filter_var($_SESSION['authenticated'], FILTER_VALIDATE_INT))
        {
            include_once('views/view_management_registrations.inc.php');
            $view_manage_registrations = new VIEW_MANAGEMENT_REGISTRATIONS();
            $view_manage_registrations->handle_download_request($_REQUEST['download']);
            exit();
        }
    }
    elseif (isset($_REQUEST['statistics']))
    {
        // API
        include_once('controllers/controller_api.inc.php');
        $controller_api = new CONTROLLER_API($view->object_broker);
        $controller_api->get_public_statistics();
    }

    // that's it for the download part - let's move on and
    // prepare the jaxon environment for asynchronous events
    require_once('./vendor/autoload.php');

    use Jaxon\Jaxon;
    use function Jaxon\jaxon;

    $jaxon = jaxon();

    include_once('controllers/controller_jaxon.php.inc');
    $jaxon->register(Jaxon::CALLABLE_CLASS, Interactives::class);

    if($jaxon->canProcessRequest())
    {
        $jaxon->processRequest();
    }

    ?>

    <!DOCTYPE html>
    <html lang="en">

        <head>
            <title>EEG Registrierungsportal | R-EEG-ISTRY</title>
            <meta charset="utf-8" />
            <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
            <link rel="stylesheet" href="/assets/css/main.css" />
            <link rel="stylesheet" href="/assets/css/AdminLTE.css" />
            <link rel="stylesheet" href="/assets/css/secondary.css" />
            <link rel="stylesheet" href="/assets/css/datatables.css" />
            <link rel="stylesheet" href="/plugins/dropzone/dropzone.min.css" />

            <script src="/plugins/dropzone/dropzone.min.js" defer></script>

            <script>
                window.backend_linebuffer = [];
            </script>

        </head>

        <body class="is-preload" style="min-height: 100vh; display:flex; flex-direction:column; padding-bottom:0px; padding-left:0px; padding-right:0px;">
            <div style="background-color: #151515; color:white; padding:0px; width:100%; height:46px; vertical-align:middle; text-align:right; position:absolute; top:0px;">

                <div style="float:left; font-family:Helvetica; font-size:10pt; font-weight:bold; color:white; padding:7px; padding-left:14px;"><A href="/" target="_self" style="color:white;">R:EEG:ISTRY</A></div>

                <?php
                    if(isset($_SESSION['backend_authenticated']) && $_SESSION['backend_authenticated'] != '')
                    {
                ?>

                <div style="cursor:pointer; float:right;font-size:10pt;border:1px solid #444;width:50px;height:42px;text-align:center;padding-top:5px;margin:2px" onclick="JaxonInteractives.deauthenticate();">
                    <li class="fa fa-door-open"></li>
                </div>

                <div style="cursor:pointer; float:right;font-size:10pt;border:1px solid #444;width:50px;height:42px;text-align:center;padding-top:5px;margin:2px" onclick="self.location.href='?manage_users'">
                    <li class="fa fa-users"></li>
                </div>

                <div style="cursor:pointer; float:right;font-size:10pt;border:1px solid #444;width:50px;height:42px;text-align:center;padding-top:5px;margin:2px" onclick="self.location.href='?manage_dashboards'">
                    <li class="fa fa-table"></li>
                </div>

                <div style="cursor:pointer; float:right;font-size:10pt;border:1px solid #444;width:50px;height:42px;text-align:center;padding-top:5px;margin:2px" onclick="self.location.href='?manage'">
                    <li class="fa fa-house-damage"></li>
                </div>

                <?php
                    }
                ?>

            </div>

            <div style="flex: 1; padding-left: 60px; padding-right: 60px;">

        <?php
            // there is a tenant id in our $_REQUEST superglobal. Let's check this for integrity
            if(isset($_REQUEST['tenant']) && $_REQUEST['tenant'] != '')
            {
                $clean_tenant = preg_replace("/[^a-zA-Z]/", '', $_REQUEST['tenant']);
                $tenant_info = $view->db->get_rows_by_column_value($view->config->user['DBTABLE_TENANTS'], 'referrer', $clean_tenant, 1);

                if(isset($tenant_info[0]))
                {
                    // Tenant existiert
                    $tenant_info = $tenant_info[0];
                    $_SESSION['tenant'] = $tenant_info['id'];

                    if($tenant_info['enabled'] != 'y')
                    {
                        // Tenant existiert, ist aber deaktiviert
                        print "
                            <b>Diese EEG ist leider nicht verf&uuml;gbar.</b><br />
                            Bitte &uuml;berpr&uuml;fe den Link oder kontaktiere den Betreiber der Website,<br />
                            die dich hier her gef&uuml;hrt hat.
                        ";
                    }
                }
                else
                {
                    // Tenant existiert nicht
                    print "
                        <b>Dieser Link ist leider ung&uuml;ltig.</b><br />
                        Bitte &uuml;berpr&uuml;fe den Link oder kontaktiere den Betreiber der Website,<br />
                        die dich hier her gef&uuml;hrt hat.
                    ";
                }
            }
            else
            {
                // there is no tenance information in the $_REQUEST superglobal
                // if we do not have a tenancy set yet, let's try to get it from our configuration
                if(!isset($_SESSION['tenant']))
                {
                    if (isset($view->config->user['tenant_fallback_on_empty_request']) && $view->config->user['tenant_fallback_on_empty_request'] == true)
                    {
                        if(isset($view->config->user['default_tenant_id']) && is_numeric($view->config->user['default_tenant_id']))
                        {
                            $_SESSION['tenant'] = $view->config->user['default_tenant_id'];
                        }
                    }
                }
            }

            // choose what view to display
            if(isset($_REQUEST['logout']))
            {
                // people trying to log off
                include_once('views/view_join.inc.php');
                // the user wants to log off -
                // destroy the session and redirect the user to the webroot
                session_unset();
                print '<script>self.location.href="/";</script>';
            }

            include(dirname(__FILE__) . '/configs/migrations.php');
            $db_config = $view->db->get_rows_by_column_value($view->config->user['DBTABLE_TEMPORARY'], 'feature', 'database_version');
            if(isset($db_config[0]))
            {
                $installed_database_version = $db_config[0]['value1'];
            }
            else
            {
                $installed_database_version = 0;
            }

            /** @var int $latest_database_version */
            if(count($database_migrations) > $installed_database_version)
            {
                if ($view->view_handle_backend_login() === true)
                {
                    include_once('views/view_management_updates.inc.php');
                    $view_manage_updates = new VIEW_MANAGEMENT_UPDATES();
                    $view_manage_updates->view_render_update_database();
                }
            }
            else
            {
                if (isset($_REQUEST['join']))
                {
                    // people trying to join the eeg
                    include_once('views/view_join.inc.php');
                    $view_join = new VIEW_JOIN();
                    $view_join->view_render();
                } elseif (isset($_REQUEST['fastjoin']))
                {
                    // people trying to join the eeg
                    include_once('views/view_join.inc.php');
                    $view_join = new VIEW_JOIN();
                    $view_join->view_render(true);
                } elseif (isset($_REQUEST['lookup']))
                {
                    // people trying to look up their registration data
                    include_once('views/view_lookup.inc.php');
                    $view_lookup = new VIEW_LOOKUP();
                    $view_lookup->view_render();
                } elseif (isset($_REQUEST['imprint']))
                {
                    // people trying to look up their registration data
                    include_once('views/view_imprint.inc.php');
                    $view_lookup = new VIEW_IMPRINT();
                    $view_lookup->view_render();
                } elseif (isset($_REQUEST['debug']))
                {
                    // people that are trying to debug this madness
                    if ($view->view_handle_backend_login() === true)
                    {
                        include_once('views/view_debug.inc.php');
                        $view_debug = new VIEW_DEBUG();
                        $view_debug->view_render();
                    }
                } elseif (isset($_REQUEST['manage']))
                {
                    // management
                    if ($view->view_handle_backend_login() === true)
                    {
                        include_once('views/view_management.inc.php');
                        $view_manage = new VIEW_MANAGEMENT();
                        $view_manage->view_render_registrations();
                    }
                } elseif (isset($_REQUEST['manage_select']))
                {
                    // management
                    if ($view->view_handle_backend_login() === true)
                    {
                        include_once('views/view_management.inc.php');
                        $view_manage = new VIEW_MANAGEMENT();
                        $view_manage->view_render_registrations(true);
                    }
                } elseif (isset($_REQUEST['manage_users']))
                {
                    // management
                    if ($view->view_handle_backend_login() === true)
                    {
                        include_once('views/view_management_users.inc.php');
                        $view_manage_users = new VIEW_MANAGEMENT_USERS();
                        $view_manage_users->view_render();
                    }
                } elseif (isset($_REQUEST['manage_dashboards']))
                {
                    // management
                    if ($view->view_handle_backend_login() === true)
                    {
                        include_once('views/view_management_dashboards.inc.php');
                        $view_manage_dashboards = new VIEW_MANAGEMENT_DASHBOARDS();
                        $view_manage_dashboards->view_render();
                    }
                } elseif (isset($_REQUEST['manage_registrations']))
                {
                    // management
                    if ($view->view_handle_backend_login() === true)
                    {
                        include_once('views/view_management_registrations.inc.php');
                        $view_manage_registrations = new VIEW_MANAGEMENT_REGISTRATIONS();
                        $view_manage_registrations->view_render();
                    }
                } elseif (isset($_REQUEST['upload']))
                {
                    // people trying to upload during the join procedure
                    include_once('views/view_join.inc.php');
                    $view_join = new VIEW_JOIN();
                    if (isset($_REQUEST['type'])) $upload_type = $_REQUEST['type']; else    $upload_type = 'other';
                    $view_join->handle_upload_request($upload_type);
                } elseif (isset($_REQUEST['update']))
                {
                    // management
                    if ($view->view_handle_backend_login() === true)
                    {
                        include_once('views/view_management_updates.inc.php');
                        $view_manage_updates = new VIEW_MANAGEMENT_UPDATES();
                        $view_manage_updates->view_render_update_codebase();
                    }
                } else
                {
                    // people who did not decide yet
                    include_once('views/view_default.inc.php');
                    $view_default = new VIEW_DEFAULT();
                    $view_default->view_render();
                }
            }

        ?>

            <br />

            <div id="modal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 id="modalheadline" style="color:black;">{%headline%}</h3>
                    </div>
                    <div class="modal-body" id="modalbody">
                        {%body%}
                    </div>
                    <div class="modal-footer" id="modalfooter">
                        {%footer%}
                    </div>
                </div>
            </div>

        </div>

        <?php
            if(isset($_SESSION['backend_authenticated']) && $_SESSION['backend_authenticated'] != '')
            {
                $account_details = $view->db->get_rows_by_column_value($view->config->user['DBTABLE_DASHBOARD_USERS'], 'id', $_SESSION['backend_authenticated'], 1);
                if(count($account_details) > 0)
                {
                    $account_details = $account_details[0];
                    $pi_menu = "Angemeldet als: " . $account_details['firstname'] . " " . $account_details['lastname'] . " (" . $account_details['username'] . ") &nbsp;";
                }
            }
            else
            {
                $pi_menu = "<a style=\"color:white\" href=\"/?manage\">&#960;&nbsp;</a>";
            }
        ?>

            <div style="background-color: #151515; color: white; padding: 5px; width:100%; height: 46px; vertical-align: middle; text-align: right;">
                <div style="float:left;font-family:Helvetica;font-size:10pt;color:dimgrey;padding-left:6px;"></div><div style="float:right;font-family:Helvetica;font-size:10pt;color:dimgrey;padding-right:4px;"><?php print $pi_menu; ?>&nbsp;&nbsp;</div>
                <div style="float:left;font-family:Helvetica;font-size:10pt;color:dimgrey;padding-left:6px;"></div><div style="float:left;font-family:Helvetica;font-size:10pt;color:dimgrey;padding-left:4px;"><a style="color:white" href="/?imprint">&nbsp;Impressum</a></div>
            </div>

            <!-- Scripts -->
            <script src="assets/js/main.js"></script>
        </body>

        <!-- Jaxon CSS -->
        <?php echo $jaxon->getCss(), "\n"; ?>
        <!-- Jaxon JS -->
        <?php echo $jaxon->getJs(), "\n"; ?>
        <!-- Jaxon script -->
        <?php echo $jaxon->getScript(), "\n"; ?>

    </html>