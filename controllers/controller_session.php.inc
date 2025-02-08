<?php

class CONTROLLER_SESSION
{

    // ## instance variables

    private $session_key;                    // session key
    private $object_broker;                 // object broker
    private $db;                            // database object
    private $config;                        // config object

    public function __construct($object_broker)
    {
        $this->object_broker = $object_broker;
        $this->db = $this->object_broker->instance['db'];
        $this->config = $this->object_broker->instance['config'];
        $this->dispatcher();
    }

    public function __destruct()
    {

    }

    // ----------------------------------------------------------------------------------
    //  DISPATCHER
    // ----------------------------------------------------------------------------------

    private function dispatcher()
    {
        // SESSION HANDLING:
        // STEP 1 - check if there is session association data in our cookie store

        // first, we start a new session:
        session_start();

        // then we get the session id for later storage and session handling:
        $this->session_key = session_id();

        // is this a new session?
        if(!isset($_SESSION['created']))
        {
            // set the start date of the session. We use this internally to indicate that the session is alive
            $_SESSION['created'] = time();

            // store additional metadata
            $_SESSION['authenticated'] = false;

            // finished flag
            $_SESSION['finished'] = false;

            // registration mnemonic
            $_SESSION['mnemonic'] = $this->generate_session_mnemonic();

            // if the user hit a deep link by accident, do a throwback to the start page
            header("Location: /");
        }
        else
        {
            if($_SESSION['finished'] === true)
            {
                // this registration was already finished.
                // delete all session data and start over

                session_unset();
                session_regenerate_id();

                // we finished this session and created a new one. Do a throwback.
                header("Location: /");
            }
        }
    }

    private function generate_session_mnemonic()
    {
        // create an mnemonic that will be used as password to authenticate users
        // that want to check their current registration details

        if (file_exists('assets/wordlists/mnemonics.txt'))
        {
            // Read the file into an array, one word per line
            $words = file('assets/wordlists/mnemonics.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            if ($words && count($words) >= 5)
            {
                // Shuffle the array and pick the first 5 words
                shuffle($words);
                $selectedWords = array_slice($words, 0, 5);

                // Join the selected words with "-"
                $mnemonic = implode('-', $selectedWords);

                // check if we used this mnemonic earlier. if yes, reshuffle and try again
                $hashed_mnemonic = hash('sha256', $mnemonic);
                while($this->object_broker->instance['db']->get_rowcount_by_field_value_extended($this->config->user['DBTABLE_REGISTRATIONS'],'mnemonic',$hashed_mnemonic) > 0)
                {
                    // Shuffle the array and pick the first 5 words
                    shuffle($words);
                    $selectedWords = array_slice($words, 0, 5);

                    // Join the selected words with "-"
                    $mnemonic = implode('-', $selectedWords);

                    // check if we used this mnemonic earlier. if yes, reshuffle and try again
                    $hashed_mnemonic = hash('sha256', $mnemonic);
                }

                return $mnemonic;

            }
            else
            {
                throw new Exception('The wordlist file does not contain enough words.');
            }
        }
        else
        {
            throw new Exception('Wordlist file not found.');
        }
    }

    // ----------------------------------------------------------------------------------
    //  PROCESSORS
    // ----------------------------------------------------------------------------------

    public function session_regenerate()
    {
        session_regenerate_id();    // Anti session fixation
    }

    public function generate_uuid4($data = null)
    {
        // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
        $data = $data ?? random_bytes(16);
        assert(strlen($data) == 16);

        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // Output the 36 character UUID.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

}

?>
