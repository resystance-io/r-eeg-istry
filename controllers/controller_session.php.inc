<?php

class CONTROLLER_SESSION
{

    // ## instance variables

    private $session_key;                    // session key
    private $session_secret;                // session secret
    private $parameter_spectrum_table;      // instead of a rainbow table, we use a spectrum table ;-)
    private $object_broker;                 // object broker
    private $db;                            // database object
    private $config;                        // config object

    public function __construct($object_broker)
    {
        $this->object_broker = $object_broker;
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

            // consumption meters
            $_SESSION['consumers'][0] = '000000000';

            // if the user hit a deep link by accident, do a throwback to the start page
            header("Location: /");
        }
        else
        {
            if($_SESSION['finished'] === true)
            {
                // this registration was already finished.
                // delete all session data and start over

                session_regenerate_id();

                unset($_SESSION['generic_information']);
                unset($_SESSION['meters']);
                unset($_SESSION['storages']);

                $_SESSION['created'] = time();
                $_SESSION['authenticated'] = false;

                // we finished this session and created a new one. Do a throwback.
                header("Location: /");
            }
        }
    }

    private function generate_session_mnemonic()
    {

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

    private function generate_session_canary()
    {
        if(PHP_SAPI === 'cli')
        {
            return password_hash('CLI_session_dummy_canary', PASSWORD_BCRYPT);
        }
        else
        {
            return password_hash($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'], PASSWORD_BCRYPT);
        }
    }

    private function verify_session_canary()
    {
        if(PHP_SAPI === 'cli')
        {
            return password_verify('CLI_session_dummy_canary', $_SESSION['canary']);
        }
        else
        {
            return password_verify($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'], $_SESSION['canary']);
        }
    }

    // ----------------------------------------------------------------------------------
    //  PROCESSORS
    // ----------------------------------------------------------------------------------

    public function session_regenerate()
    {
        session_regenerate_id();    // Anti session fixation
    }

	public function session_logout()
	{
		session_destroy();
	}

	public function session_get_session_var($varname)
	{
			
		if(isset($_SESSION[$varname]))
		{
			return $_SESSION[$varname];
		}
		else
		{
			return null;
		}
		
	}

    public function session_set_session_var($varname, $varvalue)
    {

        $_SESSION[$varname] = $varvalue;

    }

	public function validate_url_parameter_list($parameters, $return_true_value='active', $return_false_value=NULL)
    {
        $arr_parameters = explode('&', $parameters);
        $registered_parameter_list = NULL;

        foreach ($arr_parameters as $parameter)
        {
            if($registered_parameter_list)
            {
                $registered_parameter_list .= '&';
            }

            $arr_tuple = explode('=', $parameter);
            $param_name = $arr_tuple[0];
            $param_value = $arr_tuple[1];

            if(!isset($_REQUEST[$param_name]) || $_REQUEST[$param_name] != $param_value)
            {
                return $return_false_value;
            }
        }

        return $return_true_value;
    }

	public function register_url_parameter_list($parameters)
    {
        $arr_parameters = explode('&', $parameters);
        $registered_parameter_list = NULL;

        foreach ($arr_parameters as $parameter)
        {
            if($registered_parameter_list)
            {
                $registered_parameter_list .= '&';
            }

            $arr_tuple = explode('=', $parameter);
            $param_name = $arr_tuple[0];
            $param_value = $arr_tuple[1];

            // check if this key-value tuple should be persistent across page reloads
            if($param_name[0] == '~')
            {
                // this parameter shall not survive a page reload
                $param_name = ltrim($param_name, '~'); // remove indicator and prefix it again later
                $new_param_name = str_replace(str_split('$=_/&.'), '', hash('sha1', $param_name . $_SESSION['secret'] . microtime()));
                $new_param_value = str_replace(str_split('$=_/&.'), '', hash('sha1', $param_value . $_SESSION['secret'] . microtime()));
                $registered_parameter_list .= '~' . $new_param_name . '=' . $new_param_value;
            }
            else
            {
                // this parameter shall survive a page reload
                $new_param_name = str_replace(str_split('$=_/&.'), '', hash('sha1', $param_name . $_SESSION['secret'] . microtime()));
                $new_param_value = str_replace(str_split('$=_/&.'), '', hash('sha1', $param_value . $_SESSION['secret'] . microtime()));
                $registered_parameter_list .= '*' . $new_param_name . '=' . $new_param_value;
            }

            $_SESSION['parameter_spectrum_table'][$new_param_name][0] = $param_name;
            $_SESSION['parameter_spectrum_table'][$new_param_name][$new_param_value] = $param_value;
        }

        return $registered_parameter_list;
    }

    public function register_hidden_form_data($param_name, $param_value, $oneshot = false)
    {
        $new_param_name = str_replace(str_split('$=_/&.'), '', hash('sha1', $param_name . $_SESSION['secret'] . microtime()));
        $new_param_value = str_replace(str_split('$=_/&.'), '', hash('sha1', $param_value . $_SESSION['secret'] . microtime()));

        $_SESSION['parameter_spectrum_table'][$new_param_name][0] = $param_name;
        $_SESSION['parameter_spectrum_table'][$new_param_name][$new_param_value] = $param_value;

        if($oneshot === true)
        {
            return "<INPUT type=\"hidden\" name=\"~$new_param_name\" value=\"$new_param_value\">";
        }
        else
        {
            return "<INPUT type=\"hidden\" name=\"*$new_param_name\" value=\"$new_param_value\">";
        }
    }

    public function register_form_input_name($param_name, $inline_return = true)
    {
        $new_param_name = str_replace(str_split('$=_/&.'), '', hash('sha1', $param_name . $_SESSION['secret'] . microtime()));

        $_SESSION['parameter_spectrum_table'][$new_param_name][0] = $param_name;
        $_SESSION['parameter_spectrum_table'][$new_param_name][1] = '~';

        if($inline_return === true)
        {
            return '*' . $new_param_name;
        }
        else
        {
            return $new_param_name;
        }
    }

    public function register_form_input_value($existing_spectrum_name, $form_input_value)
    {
        $new_param_value = str_replace(str_split('$=_/&.'), '', hash('sha1', $form_input_value . $_SESSION['secret'] . microtime()));
        $_SESSION['parameter_spectrum_table'][$existing_spectrum_name][1] = '{}';
        $_SESSION['parameter_spectrum_table'][$existing_spectrum_name][$new_param_value] = $form_input_value;
        return "$new_param_value";
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

    public function validate_uuid4($data = null)
    {
        // Define the regular expression pattern for UUIDv4
        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

        // Use preg_match to check if the UUID matches the pattern
        return preg_match($pattern, $data) === 1;
    }

    public function record_history($module, $task, $instance_id = NULL, $uid = NULL)
    {
        $record['timestamp'] = time();
        $record['module'] = $module;
        $record['task'] = $task;

        if ($uid)
        {
            $record['uid'] = $uid;
        }
        else
        {
            $record['uid'] = $_SESSION['username'];
        }

        if ($instance_id)
        {
            $record['instance'] = $instance_id;
        }

        $this->db->insert_row_with_array($this->config->user['DBTABLE_HISTORY'], $record);
    }

    public function generate_password($length = 12, $add_dashes = false, $available_sets = 'luds')
    {
        $sets = array();
        if(strpos($available_sets, 'l') !== false)
            $sets[] = 'abcdefghijkmnopqrstuvwxyz';
        if(strpos($available_sets, 'u') !== false)
            $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        if(strpos($available_sets, 'd') !== false)
            $sets[] = '23456789';
        if(strpos($available_sets, 's') !== false)
            $sets[] = '+';

        $all = '';
        $password = '';
        foreach($sets as $set)
        {
            $password .= $set[array_rand(str_split($set))];
            $all .= $set;
        }

        $all = str_split($all);
        for($i = 0; $i < $length - count($sets); $i++)
            $password .= $all[array_rand($all)];

        $password = str_shuffle($password);

        if(!$add_dashes)
            return $password;

        $dash_len = floor(sqrt($length));
        $dash_str = '';
        while(strlen($password) > $dash_len)
        {
            $dash_str .= substr($password, 0, $dash_len) . '-';
            $password = substr($password, $dash_len);
        }
        $dash_str .= $password;
        return $dash_str;
    }

    public function unhash_url_parameter_list()
    {
        $spectrum_warmup = [];

        if(isset($_SESSION['username']))    $violator_uid = $_SESSION['username'];  else $violator_uid = 'unauthenticated';
        $ORIGINAL_REQUEST = $_REQUEST;

        if(!isset($_REQUEST))
        {
            return;
        }

        foreach($_REQUEST as $key => $value)
        {

            if($key[0] == '*' || $key[0] == '~')
            {

                if(preg_match('/^[0-9a-f]{40}$/i', substr($key, 1)) !== 1)
                {
                    // this is not a key we're expected to deal with. skip this one.
                    $this->db->insert_row_with_array($this->config->user['DBTABLE_SPECTRUM_VIOLATIONS'],
                        array(
                            'uid' => $violator_uid,
                            'remote_addr' => ip2long($_SERVER['REMOTE_ADDR']),
                            'skey' => base64_encode($key),
                            'svalue' => base64_encode($value),
                            'context' => base64_encode(json_encode($ORIGINAL_REQUEST)),
                            'timestamp' => time(),
                            'type' => 'format'
                        )
                    );
                    unset($_REQUEST[$key]);
                    continue;
                }

                if(isset($_SESSION['parameter_spectrum_table'][substr($key, 1)][0]))
                {
                    $unhashed_key = $_SESSION['parameter_spectrum_table'][substr($key, 1)][0];
                    error_log("spectrum: key [$key] unhashed to [$unhashed_key]");

                    // while keys are always hashed, values can be either hashed (if we already knew all possible
                    // outcomes of a selection) or cleartext (if the user can in theory enter any random value and
                    // we can not (and do not want to!) precompute this.

                    if(isset($_SESSION['parameter_spectrum_table'][substr($key, 1)][1]) && ($_SESSION['parameter_spectrum_table'][substr($key, 1)][1] == '~'))
                    {
                        // this was a cleartext value, no need to unhash anything -
                        // BUT we should handle it with care since there might be XSS or SQLi in there:

                        $filtered_value = filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                        if($value != $filtered_value)
                        {
                            $this->db->insert_row_with_array($this->config->user['DBTABLE_SPECTRUM_VIOLATIONS'],
                                array(
                                    'uid' => $violator_uid,
                                    'remote_addr' => ip2long($_SERVER['REMOTE_ADDR']),
                                    'skey' => base64_encode($key),
                                    'svalue' => base64_encode($value),
                                    'context' => base64_encode(json_encode($ORIGINAL_REQUEST)),
                                    'timestamp' => time(),
                                    'type' => 'sanitized'
                                )
                            );
                            error_log("spectrum: key [" . $unhashed_key . '_unsanitized' . "] -> successfully attached cleartext value [$value]");
                            $_REQUEST[$unhashed_key . '_unsanitized'] = $value;
                        }

                        error_log("spectrum: key [$unhashed_key] -> successfully attached cleartext value [$filtered_value]");
                        $_REQUEST[$unhashed_key] = $filtered_value;
                    }
                    else
                    {
                        // this is a hashed value, therefore we do a spectrum lookup first
                        if(isset($_SESSION['parameter_spectrum_table'][substr($key, 1)][$value]))
                        {
                            $unhashed_value = $_SESSION['parameter_spectrum_table'][substr($key, 1)][$value];
                            error_log("spectrum: key [$key] -> successfully attached hashed value [$value] ($unhashed_value) to key $unhashed_key");
                            $_REQUEST[$unhashed_key] = $unhashed_value;
                        }
                        else
                        {
                            $this->db->insert_row_with_array($this->config->user['DBTABLE_SPECTRUM_VIOLATIONS'],
                                array(
                                    'uid' => $violator_uid,
                                    'remote_addr' => ip2long($_SERVER['REMOTE_ADDR']),
                                    'skey' => base64_encode($key),
                                    'svalue' => base64_encode($value),
                                    'context' => base64_encode(json_encode($ORIGINAL_REQUEST)),
                                    'timestamp' => time(),
                                    'type' => 'lookupfail'
                                )
                            );
                        }
                    }

                    // we do not need the hashed key anymore - so let's get rid of it
                    unset($_REQUEST[$key]);

                    if($key[0] == '*')
                    {
                        // save this part of the spectrum table for warming up the new one (this enables reloading)
                        // but only for keys that are NOT prefixed with "~", which should prevent certain actions
                        // to trigger on reloads

                        $spectrum_warmup[substr($key, 1)][0] = $unhashed_key;
                        $spectrum_warmup[substr($key, 1)][$value] = $unhashed_value;
                    }
                }
            }
            else
            {
                // spectrum resolver ignores non-hashed element $key=$value<br>\n";
                // non-hashed element removed from REQUEST stack<br>\n";
                $this->db->insert_row_with_array($this->config->user['DBTABLE_SPECTRUM_VIOLATIONS'],
                    array(
                        'uid' => $violator_uid,
                        'remote_addr' => ip2long($_SERVER['REMOTE_ADDR']),
                        'skey' => base64_encode($key),
                        'svalue' => base64_encode($value),
                        'context' => base64_encode(json_encode($ORIGINAL_REQUEST)),
                        'timestamp' => time(),
                        'type' => 'nonhashed'
                    )
                );
                unset($_REQUEST[$key]);
            }
        }

        // purge spectrum table, but rescue the spectrum entries of the current request to allow 'reloading'
        $_SESSION['parameter_spectrum_table'] = $spectrum_warmup;
    }

}

?>
