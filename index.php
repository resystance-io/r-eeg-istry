<?php

    // Instantiate the object broker since it will keep
    // track of all objects later on and will be provided to every object
    // in order to allow easy interaction

    include_once('object_broker.php.inc');
    $object_broker = new OBJECT_BROKER();

    // NOW we instantiate objects that are needed in order to get things
    // going on a regular basis, like writers, databases, etc.

    include_once('controllers/controller_config.php.inc');
    $config = $object_broker->instance['config'] = new CONTROLLER_CONFIG();

    include_once('controllers/controller_db.php.inc');
    // connect to basic database
    $db = $object_broker->instance['db'] = new CONTROLLER_DB($object_broker);

    include_once('controllers/controller_session.php.inc');
    $session = $object_broker->instance['session'] = new CONTROLLER_SESSION($object_broker);

    // preconfigure the mail user agent interface class
    include_once('controllers/controller_mua.inc.php');
    class_alias("codeworxtech\PHPMailerPro\PHPMailerPro", "PHPMailerPro");
    $mail = $object_broker->instance['email'] = new PHPMailerPro();
    $mail->SetSender($config->user['MAIL_FROM']);
    $mail->smtpHost     = $config->user['MAIL_MTA_ADDRESS'];
    $mail->smtpDebug    = $config->user['MAIL_DEBUGGING'];
    $mail->smtpPort     = $config->user['MAIL_MTA_PORT'];
    $mail->smtpUsername = $config->user['MAIL_MTA_USER'];
    $mail->smtpPassword = $config->user['MAIL_MTA_PASS'];
    $mail->smtpOptions = $config->user['MAIL_OPTIONS'];


    // prepare the jaxon environment for asynchronous events
    require('./vendor/autoload.php');

    use Jaxon\Jaxon;
    use function Jaxon\jaxon;

    $jaxon = jaxon();

    include('controllers/controller_jaxon.php.inc');
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
        </head>

        <body class="is-preload">

        <?php

            // choose what view to display

            if(isset($_REQUEST['join']))
            {
                // people trying to join the eeg
                include_once('views/view_join.inc.php');
                $view_join = new VIEW_JOIN($object_broker);
                $view_join->view_render();
            }
            elseif(isset($_REQUEST['lookup']))
            {
                // people trying to look up their registration data
                include_once('views/view_lookup.inc.php');
                $view_lookup = new VIEW_LOOKUP($object_broker);
                $view_lookup->view_render();
            }
            elseif(isset($_REQUEST['debug']))
            {
                // people that are trying to debug this madness
                include_once('views/view_debug.inc.php');
                $view_debug = new VIEW_DEBUG();
                $view_debug->view_render();
            }
            else
            {
                // people who did not decide yet
                include_once('views/view_default.inc.php');
                view_render();
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