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

            if(isset($_REQUEST['join']))
            {
                include_once('views/view_join.inc.php');
                $view_join = new VIEW_JOIN($object_broker);
                $view_join->view_render();
            }
            elseif(isset($_REQUEST['lookup']))
            {
                include_once('views/view_lookup.inc.php');
                view_render();
            }
            else
            {
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