<?php

    include_once('views/view.inc.php');
    $view = new VIEW();

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
            <link rel="stylesheet" href="/assets/css/secondary.css" />
            <link rel="stylesheet" href="/assets/css/datatables.css" />
        </head>

        <body class="is-preload">

        <?php
            // there is a tenant id in our $_REQUEST superglobal. Let's check this for integrity
            if(isset($_REQUEST['tenant']) && $_REQUEST['tenant'] != '')
            {
                $clean_tenant = preg_replace("/[^a-zA-Z]/", '', $_REQUEST['tenant']);
                $tenant_info = $view->db->get_rows_by_column_value($view->config->user['DBTABLE_TENANTS'], 'referrer', $clean_tenant, $limit=1);

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
            if(isset($_REQUEST['join']))
            {
                // people trying to join the eeg
                include_once('views/view_join.inc.php');
                $view_join = new VIEW_JOIN();
                $view_join->view_render();
            }
            elseif(isset($_REQUEST['lookup']))
            {
                // people trying to look up their registration data
                include_once('views/view_lookup.inc.php');
                $view_lookup = new VIEW_LOOKUP();
                $view_lookup->view_render();
            }
            elseif(isset($_REQUEST['debug']))
            {
                // people that are trying to debug this madness
                include_once('views/view_debug.inc.php');
                $view_debug = new VIEW_DEBUG();
                $view_debug->view_render();
            }
            elseif(isset($_REQUEST['manage']))
            {
                // management
                include_once('views/view_management.inc.php');
                $view_manage = new VIEW_MANAGEMENT();
                $view_manage->view_render();
            }
            elseif(isset($_REQUEST['manage_users']))
            {
                // management
                include_once('views/view_management_users.inc.php');
                $view_manage_users = new VIEW_MANAGEMENT_USERS();
                $view_manage_users->view_render();
            }
            elseif(isset($_REQUEST['manage_dashboards']))
            {
                // management
                include_once('views/view_management_dashboards.inc.php');
                $view_manage_dashboards = new VIEW_MANAGEMENT_DASHBOARDS();
                $view_manage_dashboards->view_render();
            }
            elseif(isset($_REQUEST['manage_registrations']))
            {
                // management
                include_once('views/view_management_registrations.inc.php');
                $view_manage_registrations = new VIEW_MANAGEMENT_REGISTRATIONS();
                $view_manage_registrations->view_render();
            }
            else
            {
                // people who did not decide yet
                include_once('views/view_default.inc.php');
                $view_default = new VIEW_DEFAULT();
                $view_default->view_render();
            }

        ?>


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