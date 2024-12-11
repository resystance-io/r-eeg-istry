<?php

    // Instantiate the object broker since it will keep
    // track of all objects later on and will be provided to every object
    // in order to allow easy interaction

    include_once('object_broker.php.inc');
    $object_broker = new OBJECT_BROKER();

    // NOW we instantiate objects that are needed in order to get things
    // going on a regular basis, like writers, databases, etc.

    include_once('controllers/controller_config.php.inc');
    $config = $object_broker->instance['config'] = new CONTROLLER_CONFIG($object_broker);

    include_once('controllers/controller_db.php.inc');

    // connect to basic database
    $db = $object_broker->instance['db'] = new CONTROLLER_DB($object_broker);

    require('./vendor/autoload.php');

    use Jaxon\Jaxon;
    use function Jaxon\jaxon;

    // 1. Define your functions or classes.
    function foo()
    {
        $response = jaxon()->newResponse();
        $text = 'bar';
        $response->assign('canvas', 'innerHTML', $text);
        return $response;
    }

    // 2. Initialize and configure the library.
    $jaxon = jaxon();

    // 3. Register your functions or classes.
    $jaxon->register(Jaxon::CALLABLE_FUNCTION, "foo");

    // 4. Process the request.
    if($jaxon->canProcessRequest())
    {
        // This function will return the response and exit.
        $jaxon->processRequest();
    }

    // 5. Insert the Jaxon codes in your HTML page.
    ?>

    <!DOCTYPE html>
    <html lang="en">

        <head>
            <title>Chores</title>
        </head>

        <body>

            <div id="canvas">&nbsp;</div>
            <input type="button" value="Foo" onclick="jaxon_foo()" /><br />

        </body>

        <!-- Jaxon CSS -->
        <?php echo $jaxon->getCss(), "\n"; ?>
        <!-- Jaxon JS -->
        <?php echo $jaxon->getJs(), "\n"; ?>
        <!-- Jaxon script -->
        <?php echo $jaxon->getScript(), "\n"; ?>

    </html>