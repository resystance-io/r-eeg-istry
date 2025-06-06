<?php

use function Jaxon\jaxon;

class Interactives
{

    private $object_broker;
    private $config;
    private $view;
    private $db;

    public function __construct()
    {
        include_once('object_broker.php.inc');
        $this->object_broker = new OBJECT_BROKER();

        include_once('controllers/controller_config.php.inc');
        $this->config = $this->object_broker->instance['config'] = new CONTROLLER_CONFIG();

        include_once('controllers/controller_db.php.inc');
        $this->db = $this->object_broker->instance['db'] = new CONTROLLER_DB($this->object_broker);

        include_once('views/view.inc.php');
        $this->view = $this->object_broker->instance['view'] = new VIEW();
    }

    public function __destruct()
    {

    }

    public function deauthenticate()
    {
        // the user wants to log off -
        // destroy the session and redirect the user to the webroot
        session_unset();

        $response = jaxon()->newResponse();
        $response->script("window.location.href='/';");
        return $response;
    }

    public function authenticate()
    {
        // the user wants to log on -
        // check if email address and mnemonic match and authenticate the session on success

        if(isset($_SESSION['auth_email']) && !filter_var($_SESSION['auth_email'], FILTER_VALIDATE_EMAIL))
        {
            $response = jaxon()->newResponse();
            $response->alert("Das Format der eMail Adresse ist nicht korrekt");
            return $response;
        }

        $username = $_SESSION['auth_email'];
        $password = $_SESSION['auth_mnemonic'];

        if(isset($_SESSION['auth_mnemonic'])) unset($_SESSION['auth_mnemonic']);

        $hashed_mnemonic_provided = hash('sha256', $password);

        $account_id = $this->db->get_column_by_column_values($this->config->user['DBTABLE_REGISTRATIONS'], 'id', 'email', $username, 'mnemonic', $hashed_mnemonic_provided);
        if($account_id)
        {
            $this->db->update_column_by_column_values($this->config->user['DBTABLE_REGISTRATIONS'], 'last_login_date', time(), 'id', $account_id);

            $_SESSION['authenticated'] = $account_id;
            $response = jaxon()->newResponse();
            $response->script("window.location.href='/?lookup=profile';");
            return $response;
        }
        else
        {
            $response = jaxon()->newResponse();
            $response->alert("Bitte überprüfe die angegebenen Anmeldedaten");
            return $response;
        }
    }

    public function backend_authenticate()
    {
        // the management user wants to log on -
        // check if username address and password match and authenticate the session on success

        $username = $_SESSION['auth_backend_username'];
        $password = $_SESSION['auth_backend_password'];
        error_log("backend_authenticate: user:$username pass:$password");

        if(isset($_SESSION['auth_backend_username'])) unset($_SESSION['auth_backend_password']);

        $hashed_mnemonic_provided = hash('sha256', $password);

        $account_details = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_DASHBOARD_USERS'], 'username', $username, 1, null, null, 'deleted', 'n');
        if(count($account_details) > 0)
        {
            $account_details = $account_details[0];

            if(substr_count($account_details['passphrase'], ':') == 1)
            {
                $stored_password_salted = explode(':', $account_details['passphrase']);
                $stored_password_hash = $stored_password_salted[0];
                $stored_password_salt = $stored_password_salted[1];
                $submitted_password_hash = hash('sha256', $password . $stored_password_salt);

                if ($stored_password_hash === $submitted_password_hash)
                {
                    $_SESSION['backend_authenticated'] = $account_details['id'];
                    session_regenerate_id(true);

                    $response = jaxon()->newResponse();
                    $response->script("window.location.href='/';");
                    return $response;
                }
                else
                {
                    $_SESSION['backend_authenticated'] = NULL;
                    unset($_SESSION['backend_authenticated']);
                    $response = jaxon()->newResponse();
                    $response->alert("Bitte überprüfe die angegebenen Anmeldedaten (WAC)");
                    return $response;
                }
            }
            else
            {
                $response = jaxon()->newResponse();
                $response->alert("Bitte überprüfe die angegebenen Anmeldedaten (AMC)");
                return $response;
            }
        }
        else
        {
            $response = jaxon()->newResponse();
            $response->alert("Bitte überprüfe die angegebenen Anmeldedaten (ANF)");
            return $response;
        }

    }

    public function add_meter($meter_type, $prefill)
    {
        // add another meter input block to the current layout
        $response = jaxon()->newResponse();

        $id = $this->generate_uuid4();

        $_SESSION['meters']["$id"]['type'] = $meter_type;
        $_SESSION['meters'][$id]['value'] = $prefill;

        $element = '
            Z&auml;hlpunktnummer <div class="input-box">
                <input type="text" name="' . $id . '" id="' . $id . '" value="' . $prefill . '" maxlength="37" onfocus="this.select()"  onfocusout="JaxonInteractives.update_meter_value(' . "'" . $id . "'" . ', document.getElementById(' . "'" . $id . "'" . ').value);" />
                <button style="background-color:darkred"  onclick="JaxonInteractives.rmv_meter(' . "'" . $id . "'" . ', ' . "'" . $meter_type . "'" . ');"><i style="font-size:16px;color:white;" class="icon fa-trash-alt"></i></button>
            </div>
            <br />
        ';

        if($meter_type == 'suppliers')
        {
            $anchor = 'end_of_suppliers';
        }
        elseif($meter_type == 'consumers')
        {
            $anchor = 'end_of_consumers';
        }

        $response->insertBefore($anchor, 'div', 'container-' . $id);
        $response->assign('container-' . $id, 'innerHTML', $element);
        return $response;
    }

    public function add_storage()
    {
        // add another storage input block to the current layout
        $response = jaxon()->newResponse();

        $id = $this->generate_uuid4();
        $_SESSION['storages']["$id"]['value'] = '0';

        $element = '
        <div id="container-' . $id . '">Speicher<br>
            <div class="input-box" style="width:254px;">
                <span class="prefix">Kapazit&auml;t:&nbsp;</span>
                <input type="text" name="' . $id . '" id="' . $id . '" value="0" maxlength="4" style="width:65px;text-align:center" onfocus="this.select()" onfocusout="JaxonInteractives.update_storage_value(' . "'" . $id . "'" . ', document.getElementById(' . "'" . $id . "'" . ').value);" />
                <span class="prefix">kWh</span>
                &nbsp;&nbsp;&nbsp;&nbsp;<button style="background-color:darkred"  onclick="JaxonInteractives.rmv_storage(' . "'" . $id . "'" . ');"><i style="font-size:16px;color:white;" class="icon fa-trash-alt"></i></button><br>
            </div>
            <br />
        </div>';

        $response->insertBefore('end_of_storages', 'div', 'container-' . $id);
        $response->assign('container-' . $id, 'innerHTML', $element);
        return $response;
    }

    public function rmv_meter($id)
    {
        // remove a meter input block from the current layout
        if(isset($_SESSION['meters']["$id"]))
        {
            if($_SESSION['meters']["$id"]['type'] == 'suppliers')
            {
                unset($_SESSION['meters']["$id"]);
                $response = jaxon()->newResponse();
                $response->remove('container-' . $id);
                return $response;
            }
            elseif($_SESSION['meters']["$id"]['type'] == 'consumers')
            {
                $consumer_count = 0;
                foreach($_SESSION['meters'] as $meter_object)
                {
                    if($meter_object['type'] == 'consumers')    $consumer_count++;
                }

                if($consumer_count == 1)
                {
                    $response = jaxon()->newResponse();
                    $response->alert("Der letzte Bezugszählpunkt kann nicht gelöscht werden.\nEs ist mindestens ein Bezugszählpunkt erforderlich.");
                    return $response;
                }
                else
                {
                    unset($_SESSION['meters']["$id"]);

                    $response = jaxon()->newResponse();
                    $response->remove('container-' . $id);
                    return $response;
                }
            }
        }
    }

    public function rmv_storage($id)
    {
        // remove a storage input block from the current layout
        if(isset($_SESSION['storages']["$id"]))
        {
            unset($_SESSION['storages']["$id"]);
            $response = jaxon()->newResponse();
            $response->remove('container-' . $id);
            return $response;
        }
    }

    public function update_storage_value($id, $value)
    {
        // write the given storage value to our session
        if(isset($_SESSION['storages']["$id"]))
        {
            $_SESSION['storages']["$id"]['value'] = $value;
        }
    }

    public function update_meter_value($id, $value)
    {
        // write the given meter value to our session
        if(isset($_SESSION['meters']["$id"]))
        {
            $_SESSION['meters']["$id"]['value'] = $value;
        }
    }

    public function update_session_bucket($record, $value, $session_bucket)
    {
        // write a given name-value tuple to our session (into the specified bucket)
        $_SESSION["$session_bucket"]["$record"]["value"] = $value;
        $response = jaxon()->newResponse();
        return $response;
    }

    public function update_credential_cache($record, $value)
    {
        // write the provided login credentials to the current session's credentials cache
        switch ($record)
        {
            case 'auth_email':
                $_SESSION['auth_email'] = $value;
                break;

            case 'auth_mnemonic':
                $_SESSION['auth_mnemonic'] = $value;
                break;
        }
    }

    public function update_backend_credential_cache($record, $value)
    {
        // write the provided login credentials to the current session's credentials cache
        switch ($record)
        {
            case 'auth_username':
                $_SESSION['auth_backend_username'] = $value;
                break;

            case 'auth_password':
                $_SESSION['auth_backend_password'] = $value;
                break;
        }
    }

    public function update_meter_detail($meter_key, $record, $value)
    {
        // write detail information about a certain meter too the current session's meter data
        $_SESSION['meters']["$meter_key"]["$record"]['value'] = $value;
    }

    public function step_back($current_step)
    {
        // navigate one step back (without checking the integrity of the data provided in the current step)
        $response = jaxon()->newResponse();
        if($current_step > 0)
        {
            $next_step = $current_step - 1;
            $response->script("window.location.href='/?join=" . $_SESSION['generic_information']['join_type'] .  "&step=" . $next_step . "';");
        }
        else
        {
            $response->script("window.location.href='/?join';");
        }
        return $response;
    }

    public function next_step($current_step)
    {
        // navigate one step forward (and check the integrity of the data provided in the current step)
        $response = jaxon()->newResponse();

        if(isset($this->config->user['JOIN_LAYOUT'][$current_step]))
        {
            foreach($this->config->user['JOIN_LAYOUT'][$current_step] as $panel)
            {
                switch($panel)
                {
                    case 'generic':
                        if($_SESSION['generic_information']['join_type'] == 'company')
                        {
                            if($this->check_step_integrity_company($response) === false)
                            {
                                return $response;
                            }
                        }
                        elseif($_SESSION['generic_information']['join_type'] == 'individual')
                        {
                            if($this->check_step_integrity_individual($response) === false)
                            {
                                return $response;
                            }
                        }
                        elseif($_SESSION['generic_information']['join_type'] == 'agriculture')
                        {
                            if($this->check_step_integrity_agriculture($response) === false)
                            {
                                return $response;
                            }
                        }
                        break;

                    case 'consumption':
                        if($this->check_step_integrity_meters($response) === false)
                        {
                            return $response;
                        }
                        break;

                    case 'supply':
                        if($this->check_step_integrity_meters($response) === false)
                        {
                            return $response;
                        }
                        break;

                    case 'storage':
                        if($this->check_step_integrity_storages($response) === false)
                        {
                            return $response;
                        }
                        break;

                    case 'banking':
                        if($this->check_step_integrity_banking($response) === false)
                        {
                            return $response;
                        }
                        break;

                    case 'meters':
                        if($this->check_step_meter_details($response) === false)
                        {
                            return $response;
                        }
                        break;

                    case 'optionals':
                        if($this->check_step_optionals($response) === false)
                        {
                            return $response;
                        }
                        break;

                    case 'approvals':
                        if($this->check_step_approval_details($response) === false)
                        {
                            return $response;
                        }
                        break;
                }
            }

            $next_step = $current_step + 1;
            $_SESSION['latestsave'] = $current_step;
            $response->script("window.location.href='/?join=" . $_SESSION['generic_information']['join_type'] .  "&step=" . $next_step . "';");
            return $response;
        }
    }

    public function check_step_integrity_company(&$jaxon_response_object)
    {
        // check the integrity of all data in the company block
        $failmsg = null;
        $focustarget = null;

        /*$email_already_used = $this->db->get_rowcount_by_field_value_extended($this->config->user['DBTABLE_REGISTRATIONS'],'email', $_SESSION['generic_information']['email']['value']);
        if($email_already_used > 0)
        {
            $failmsg = "Diese E-Mail-Adresse ist bereits einem Benutzer dieser EEG zugeordnet";
            $focustarget = 'email';
        }*/

        if($this->validate_record($_SESSION['generic_information']['company']['value'], $_SESSION['generic_information']['company']['integrity']) === false)
        {
            $failmsg = "Bitte überprüfen Sie den Firmenwortlaut";
            $focustarget = 'company';
        }

        if($this->validate_record($_SESSION['generic_information']['street']['value'], $_SESSION['generic_information']['street']['integrity']) === false)
        {
            $failmsg = "Bitte überprüfen Sie die Straße";
            $focustarget = 'street';
        }

        if($this->validate_record($_SESSION['generic_information']['zip']['value'], $_SESSION['generic_information']['zip']['integrity']) === false)
        {
            $failmsg = "Bitte überprüfen Sie die Postleitzahl";
            $focustarget = 'zip';
        }

        if($this->validate_record($_SESSION['generic_information']['city']['value'], $_SESSION['generic_information']['city']['integrity']) === false)
        {
            $failmsg = "Bitte überprüfen Sie den Ort";
            $focustarget = 'city';
        }

        if($this->validate_record($_SESSION['generic_information']['number']['value'], $_SESSION['generic_information']['number']['integrity']) === false)
        {
            $failmsg = "Bitte überprüfen Sie die Hausnummer";
            $focustarget = 'number';
        }

        if($this->validate_record($_SESSION['generic_information']['phone']['value'], $_SESSION['generic_information']['phone']['integrity']) === false)
        {
            $failmsg = "Bitte überprüfen Sie die Telefonnummer";
            $focustarget = 'phone';
        }

        if($this->validate_record($_SESSION['generic_information']['email']['value'], $_SESSION['generic_information']['email']['integrity']) === false)
        {
            $failmsg = "Bitte überprüfen Sie die eMail Adresse";
            $focustarget = 'email';
        }

        if($this->validate_record($_SESSION['generic_information']['uid']['value'], $_SESSION['generic_information']['uid']['integrity']) === false)
        {
            $failmsg = "Bitte überprüfen Sie die UID";
            $focustarget = 'uid';
        }

        if($this->validate_record($_SESSION['generic_information']['salestax']['value'], $_SESSION['generic_information']['salestax']['integrity']) === false)
        {
            $failmsg = "Bitte überprüfen Sie Ihren Umsatzsteuerstatus";
            $focustarget = 'salestax';
        }

        if($failmsg)
        {
            $jaxon_response_object->alert($failmsg);
            $jaxon_response_object->script("document.getElementById('" . $focustarget . "').focus();");
            return false;
        }

        return true;
    }

    public function check_step_integrity_individual(&$jaxon_response_object)
    {
        // check the integrity of all data in the individual block
        $failmsg = null;
        $focustarget = null;

        /*$email_already_used = $this->db->get_rowcount_by_field_value_extended($this->config->user['DBTABLE_REGISTRATIONS'],'email',$_SESSION['generic_information']['email']['value']);
        if($email_already_used > 0)
        {
            $failmsg = "Diese E-Mail-Adresse ist bereits einem Benutzer dieser EEG zugeordnet";
            $focustarget = 'email';
        }*/

        if($this->validate_record($_SESSION['generic_information']['title']['value'], $_SESSION['generic_information']['title']['integrity']) === false)
        {
            $failmsg = "Bitte überprüfen Sie Ihren Titel";
            $focustarget = 'title';
        }

        if($this->validate_record($_SESSION['generic_information']['postnomen']['value'], $_SESSION['generic_information']['postnomen']['integrity']) === false)
        {
            $failmsg = "Bitte überprüfen Sie Ihr Postnomen";
            $focustarget = 'postnomen';
        }

        if($this->validate_record($_SESSION['generic_information']['firstname']['value'], $_SESSION['generic_information']['firstname']['integrity']) === false)
        {
            $failmsg = "Bitte überprüfen Sie den Vornamen";
            $focustarget = 'firstname';
        }

        if($this->validate_record($_SESSION['generic_information']['lastname']['value'], $_SESSION['generic_information']['lastname']['integrity']) === false)
        {
            $failmsg = "Bitte überprüfen Sie den Nachnamen";
            $focustarget = 'lastname';
        }

        if($this->validate_record($_SESSION['generic_information']['street']['value'], $_SESSION['generic_information']['street']['integrity']) === false)
        {
            $failmsg = "Bitte überprüfen Sie die Straße";
            $focustarget = 'street';
        }

        if($this->validate_record($_SESSION['generic_information']['zip']['value'], $_SESSION['generic_information']['zip']['integrity']) === false)
        {
            $failmsg = "Bitte überprüfen Sie die Postleitzahl";
            $focustarget = 'zip';
        }

        if($this->validate_record($_SESSION['generic_information']['city']['value'], $_SESSION['generic_information']['city']['integrity']) === false)
        {
            $failmsg = "Bitte überprüfen Sie den Ort";
            $focustarget = 'city';
        }

        if($this->validate_record($_SESSION['generic_information']['number']['value'], $_SESSION['generic_information']['number']['integrity']) === false)
        {
            $failmsg = "Bitte überprüfen Sie die Hausnummer";
            $focustarget = 'number';
        }

        if($this->validate_record($_SESSION['generic_information']['phone']['value'], $_SESSION['generic_information']['phone']['integrity']) === false)
        {
            $failmsg = "Bitte überprüfen Sie die Telefonnummer";
            $focustarget = 'phone';
        }

        if($this->validate_record($_SESSION['generic_information']['email']['value'], $_SESSION['generic_information']['email']['integrity']) === false)
        {
            $failmsg = "Bitte überprüfen Sie die eMail Adresse";
            $focustarget = 'email';
        }

        if($this->validate_record($_SESSION['generic_information']['birthdate']['value'], $_SESSION['generic_information']['birthdate']['integrity']) === false)
        {
            $failmsg = "Bitte überprüfen Sie das Geburtsdatum";
            $focustarget = 'birthdate';
        }

        if($this->validate_record($_SESSION['generic_information']['idprovider']['value'], $_SESSION['generic_information']['idprovider']['integrity']) === false)
        {
            $failmsg = "Bitte wählen Sie ihr bevorzugtes Ausweisdokument";
            $focustarget = 'idprovider';
        }

        if($this->validate_record($_SESSION['generic_information']['idvalue']['value'], $_SESSION['generic_information']['idvalue']['integrity']) === false)
        {
            $failmsg = "Bitte geben Sie die Nummer des gewählten Ausweisdokumentes an";
            $focustarget = 'idvalue';
        }

        if($failmsg)
        {
            $jaxon_response_object->alert($failmsg);
            $jaxon_response_object->script("document.getElementById('" . $focustarget . "').focus();");
            return false;
        }

        return true;
    }


    public function check_step_integrity_agriculture(&$jaxon_response_object)
    {
        // check the integrity of all data in the agriculture block
        $failmsg = null;
        $focustarget = null;

        /*$email_already_used = $this->db->get_rowcount_by_field_value_extended($this->config->user['DBTABLE_REGISTRATIONS'],'email',$_SESSION['generic_information']['email']['value']);
        if($email_already_used > 0)
        {
            $failmsg = "Diese E-Mail-Adresse ist bereits einem Benutzer dieser EEG zugeordnet";
            $focustarget = 'email';
        }*/

        if($this->validate_record($_SESSION['generic_information']['title']['value'], $_SESSION['generic_information']['title']['integrity']) === false)
        {
            $failmsg = "Bitte überprüfen Sie Ihren Titel";
            $focustarget = 'title';
        }

        if($this->validate_record($_SESSION['generic_information']['postnomen']['value'], $_SESSION['generic_information']['postnomen']['integrity']) === false)
        {
            $failmsg = "Bitte überprüfen Sie Ihr Postnomen";
            $focustarget = 'postnomen';
        }

        if($this->validate_record($_SESSION['generic_information']['firstname']['value'], $_SESSION['generic_information']['firstname']['integrity']) === false)
        {
            $failmsg = "Bitte überprüfen Sie den Vornamen";
            $focustarget = 'firstname';
        }

        if($this->validate_record($_SESSION['generic_information']['lastname']['value'], $_SESSION['generic_information']['lastname']['integrity']) === false)
        {
            $failmsg = "Bitte überprüfen Sie den Nachnamen";
            $focustarget = 'lastname';
        }

        if($this->validate_record($_SESSION['generic_information']['street']['value'], $_SESSION['generic_information']['street']['integrity']) === false)
        {
            $failmsg = "Bitte überprüfen Sie die Straße";
            $focustarget = 'street';
        }

        if($this->validate_record($_SESSION['generic_information']['zip']['value'], $_SESSION['generic_information']['zip']['integrity']) === false)
        {
            $failmsg = "Bitte überprüfen Sie die Postleitzahl";
            $focustarget = 'zip';
        }

        if($this->validate_record($_SESSION['generic_information']['city']['value'], $_SESSION['generic_information']['city']['integrity']) === false)
        {
            $failmsg = "Bitte überprüfen Sie den Ort";
            $focustarget = 'city';
        }

        if($this->validate_record($_SESSION['generic_information']['number']['value'], $_SESSION['generic_information']['number']['integrity']) === false)
        {
            $failmsg = "Bitte überprüfen Sie die Hausnummer";
            $focustarget = 'number';
        }

        if($this->validate_record($_SESSION['generic_information']['phone']['value'], $_SESSION['generic_information']['phone']['integrity']) === false)
        {
            $failmsg = "Bitte überprüfen Sie die Telefonnummer";
            $focustarget = 'phone';
        }

        if($this->validate_record($_SESSION['generic_information']['email']['value'], $_SESSION['generic_information']['email']['integrity']) === false)
        {
            $failmsg = "Bitte überprüfen Sie die eMail Adresse";
            $focustarget = 'email';
        }

        if($this->validate_record($_SESSION['generic_information']['birthdate']['value'], $_SESSION['generic_information']['birthdate']['integrity']) === false)
        {
            $failmsg = "Bitte überprüfen Sie das Geburtsdatum";
            $focustarget = 'birthdate';
        }

        if($this->validate_record($_SESSION['generic_information']['idprovider']['value'], $_SESSION['generic_information']['idprovider']['integrity']) === false)
        {
            $failmsg = "Bitte wählen Sie ihr bevorzugtes Ausweisdokument";
            $focustarget = 'idprovider';
        }

        if($this->validate_record($_SESSION['generic_information']['idvalue']['value'], $_SESSION['generic_information']['idvalue']['integrity']) === false)
        {
            $failmsg = "Bitte geben Sie die Nummer des gewählten Ausweisdokumentes an";
            $focustarget = 'idvalue';
        }

        if($failmsg)
        {
            $jaxon_response_object->alert($failmsg);
            $jaxon_response_object->script("document.getElementById('" . $focustarget . "').focus();");
            return false;
        }

        return true;
    }

    public function check_step_integrity_meters(&$jaxon_response_object)
    {
        // check the integrity of all data in the meter block
        $failmsg = null;
        $focustarget = null;

        $meter_ids_in_session = [];
        foreach($_SESSION['meters'] as $meter_key => $meter_object)
        {
            if($this->validate_record($meter_object['value'], 'meter_longid') === false)
            {
                $failmsg = "Fehlerhaftes Format: Bitte überprüfen Sie die Zählpunktnummer";
                $focustarget = $meter_key;
            }
            else
            {
                $meter_id_count = $this->db->get_rowcount_by_field_value_extended($this->config->user['DBTABLE_METERS'],'meter_id', $meter_object['value']);
                if($meter_id_count > 0)
                {
                    $jaxon_response_object->alert("Dieser Zählpunkt ist bereits einem Mitglied dieser EEG zugeordnet");
                    $jaxon_response_object->script("document.getElementById('" . $meter_key . "').focus();");
                    return false;
                }

                if(isset($meter_ids_in_session[$meter_object['value']]))
                {
                    $jaxon_response_object->alert("Ein Zählpunkt darf nicht mehrmals in einer Registrierung verwendet werden");
                    $jaxon_response_object->script("document.getElementById('" . $meter_key . "').focus();");
                    return false;
                }
                else
                {
                    $meter_ids_in_session[$meter_object['value']] = true;
                }
            }
        }

        if($failmsg)
        {
            $jaxon_response_object->alert($failmsg);
            $jaxon_response_object->script("document.getElementById('" . $focustarget . "').focus();");
            return false;
        }

        return true;
    }

    public function check_step_meter_details(&$jaxon_response_object)
    {
        // check the integrity of all data in the meter detail block
        $failmsg = null;
        $focustarget = null;

        if(isset($_SESSION['meters']))
        {
            foreach ($_SESSION['meters'] as $meter_key => $meter_object)
            {
                if ($this->validate_record($meter_object['street']['value'], $meter_object['street']['integrity']) === false)
                {
                    $failmsg = "Bitte überprüfen Sie die angegebene Straße";
                    $focustarget = 'street_' . $meter_key;
                }

                if ($this->validate_record($meter_object['number']['value'], $meter_object['number']['integrity']) === false)
                {
                    $failmsg = "Bitte überprüfen Sie die angegebene Hausnummer";
                    $focustarget = 'number_' . $meter_key;
                }

                if ($this->validate_record($meter_object['city']['value'], $meter_object['city']['integrity']) === false)
                {
                    $failmsg = "Bitte überprüfen Sie den angegebenen Ort";
                    $focustarget = 'city_' . $meter_key;
                }

                if ($this->validate_record($meter_object['zip']['value'], $meter_object['zip']['integrity']) === false)
                {
                    $failmsg = "Bitte überprüfen Sie die angegebene Postleitzahl";
                    $focustarget = 'zip_' . $meter_key;
                }

                if ($this->validate_record($meter_object['participation']['value'], $meter_object['participation']['integrity']) === false)
                {
                    $failmsg = "Bitte überprüfen Sie den angegebenen Teilnahmefaktor";
                    $focustarget = 'participation_' . $meter_key;
                }

                if ($this->validate_record($meter_object['feedlimit']['value'], $meter_object['feedlimit']['integrity']) === false)
                {
                    $failmsg = "Bitte überprüfen Sie die maximal erlaubte Netzzugangsleistung";
                    $focustarget = 'feedlimit_' . $meter_key;
                }

                if($meter_object['type'] == 'suppliers')
                {
                    if ($this->validate_record($meter_object['power']['value'], $meter_object['power']['integrity']) === false)
                    {
                        $failmsg = "Bitte überprüfen Sie die angegebene Leistung des Einspeisezählpunktes";
                        $focustarget = 'power_' . $meter_key;
                    }
                }
            }
        }

        if($failmsg)
        {
            $jaxon_response_object->alert($failmsg);
            $jaxon_response_object->script("document.getElementById('" . $focustarget . "').focus();");
            return false;
        }

        return true;
    }

    public function check_step_integrity_storages(&$jaxon_response_object)
    {
        // check the integrity of all data in the storage block
        $failmsg = null;
        $focustarget = null;

        if(isset($_SESSION['storages']) && count($_SESSION['storages']) > 0)
        {
            foreach ($_SESSION['storages'] as $storage_key => $storage_object)
            {
                if ($this->validate_record($storage_object['value'], 'numbers>0') === false)
                {
                    $failmsg = "Bitte überprüfen Sie die angegebene Speicherkapazität";
                    $focustarget = $storage_key;
                }
            }
        }

        if($failmsg)
        {
            $jaxon_response_object->alert($failmsg);
            $jaxon_response_object->script("document.getElementById('" . $focustarget . "').focus();");
            return false;
        }

        return true;
    }

    public function check_step_optionals(&$jaxon_response_object)
    {
        // check the integrity of all data in the banking block
        $failmsg = null;
        $focustarget = null;





        if($failmsg)
        {
            $jaxon_response_object->alert($failmsg);
            $jaxon_response_object->script("document.getElementById('" . $focustarget . "').focus();");
            return false;
        }

        return true;
    }
    public function check_step_approval_details(&$jaxon_response_object)
    {
        // check the integrity of all data in the banking block
        $failmsg = null;
        $focustarget = null;

        if($this->validate_record($_SESSION['generic_information']['bylaws_consent']['value'], $_SESSION['generic_information']['bylaws_consent']['integrity']) === false)
        {
            $failmsg = "Bitte akzeptieren Sie unsere Statuten";
            $focustarget = 'bylaws_consent';
        }

        if($this->validate_record($_SESSION['generic_information']['tos_consent']['value'], $_SESSION['generic_information']['tos_consent']['integrity']) === false)
        {
            $failmsg = "Bitte akzeptieren Sie unsere AGBs";
            $focustarget = 'tos_consent';
        }

        if($this->validate_record($_SESSION['generic_information']['gdpr_consent']['value'], $_SESSION['generic_information']['gdpr_consent']['integrity']) === false)
        {
            $failmsg = "Bitte akzeptieren Sie unsere Datenschutzbestimmungen";
            $focustarget = 'gdpr_consent';
        }

        if($this->validate_record($_SESSION['generic_information']['network_consent']['value'], $_SESSION['generic_information']['network_consent']['integrity']) === false)
        {
            $failmsg = "Bitte erteilen Sie uns die Vollmacht gegenüber der Netzbetreiber";
            $focustarget = 'network_consent';
        }

        if($this->validate_record($_SESSION['generic_information']['network_customerid']['value'], $_SESSION['generic_information']['network_customerid']['integrity']) === false)
        {
            $failmsg = "Bitte geben Sie Ihre Netzbetreiber-Kundennummer bekannt";
            $focustarget = 'network_customerid';
        }

        if($this->validate_record($_SESSION['generic_information']['network_inventoryid']['value'], $_SESSION['generic_information']['network_inventoryid']['integrity']) === false)
        {
            $failmsg = "Bitte geben Sie die Inventarnummer eines beliebigen Zählers in dieser Registrierung bekannt.";
            $focustarget = 'network_inventoryid';
        }

        if($failmsg)
        {
            $jaxon_response_object->alert($failmsg);
            $jaxon_response_object->script("document.getElementById('" . $focustarget . "').focus();");
            return false;
        }

        return true;
    }

    public function check_step_integrity_banking(&$jaxon_response_object)
    {
        // check the integrity of all data in the banking block
        $failmsg = null;
        $focustarget = null;

        if($this->validate_record($_SESSION['generic_information']['banking_name']['value'], $_SESSION['generic_information']['banking_name']['integrity']) === false)
        {
            $failmsg = "Bitte überprüfen Sie den Namen d. Kontoinhabers";
            $focustarget = 'banking_name';
        }

        if($this->validate_record($_SESSION['generic_information']['banking_iban']['value'], $_SESSION['generic_information']['banking_iban']['integrity']) === false)
        {
            $failmsg = "Bitte überprüfen Sie die IBAN";
            $focustarget = 'banking_iban';
        }

        if($this->validate_record($_SESSION['generic_information']['banking_consent']['value'], $_SESSION['generic_information']['banking_consent']['integrity']) === false)
        {
            $failmsg = "Bitte bestätigen Sie die Autorisierung zum Bankeinzug";
            $focustarget = 'banking_consent';
        }

        if($failmsg)
        {
            $jaxon_response_object->alert($failmsg);
            $jaxon_response_object->script("document.getElementById('" . $focustarget . "').focus();");
            return false;
        }

        return true;
    }

    public function dashboard_set_filter($key, $value)
    {
        if($_SESSION['dashboard']['filter'][$key] == $value)
        {
            // this value is already set
            $response = jaxon()->newResponse();
            return $response;
        }

        $response = jaxon()->newResponse();
        if($value != '')
        {
            $_SESSION['dashboard']['filter'][$key] = $value;
        }
        else
        {
            unset($_SESSION['dashboard']['filter'][$key]);
        }
        $response->script("self.location.href='/?manage';");
        return $response;
    }

    public function dashboard_set_search($key, $value)
    {
        if($_SESSION['dashboard']['search'][$key] == $value)
        {
            // this value is already set
            $response = jaxon()->newResponse();
            return $response;
        }

        $response = jaxon()->newResponse();
        if($value != '' && $key != '')
        {
            unset($_SESSION['dashboard']['search']);
            $_SESSION['dashboard']['search'][$key] = $value;
        }
        else
        {
            unset($_SESSION['dashboard']['search']);
        }
        $response->script("self.location.href='/?manage';");
        return $response;
    }

    public function dashboard_set_filterconfig($key, $value, $dashboard)
    {
        if($_SESSION['dashboard']['filterconfig'][$key] == $value)
        {
            // this value is already set
            $response = jaxon()->newResponse();
            return $response;
        }

        $response = jaxon()->newResponse();
        if($value != '')
        {
            $_SESSION['dashboard']['filterconfig'][$key] = $value;
        }
        else
        {
            unset($_SESSION['dashboard']['filterconfig'][$key]);
        }

        $this->db->update_column_by_column_values($this->config->user['DBTABLE_DASHBOARDS'], 'filterconfig', base64_encode(json_encode($_SESSION['dashboard']['filterconfig'])), 'id', $dashboard);

        return $response;
    }

    public function dashboard_add_note($note, $registration_id)
    {
        $insert_arr = [
            'user_id' => $_SESSION['backend_authenticated'],
            'registration_id' => $registration_id,
            'timestamp' => time(),
            'style' => 'note',
            'content' => $note
        ];

        $this->db->insert_row_with_array($this->config->user['DBTABLE_DASHBOARD_NOTES'], $insert_arr);

        $response = jaxon()->newResponse();
        $response->script("window.location.href='/?manage_registrations&registration=" . $registration_id ."';");
        return $response;
    }

    public function dashboard_set_colorconfig($value, $dashboard)
    {
        $this->db->update_column_by_column_values($this->config->user['DBTABLE_DASHBOARDS'], 'colorconfig', $value, 'id', $dashboard);
        $response = jaxon()->newResponse();
        $response->script("window.location.href='/?manage_dashboards&dashboard_id=" . $dashboard ."';");
        return $response;
    }

    public function dashboard_select_pagination_page($page)
    {
        $_SESSION['dashboard']['page'] = $page;
        $response = jaxon()->newResponse();
        $response->script("window.location.href='/?manage';");
        return $response;
    }

    public function dashboard_select_page_size($page_size)
    {
        $_SESSION['dashboard']['page_size'] = $page_size;
        $this->db->update_column_by_column_values($this->config->user['DBTABLE_DASHBOARD_USERS'], 'result_page_size', $page_size, 'id', $_SESSION['backend_authenticated']);
        $response = jaxon()->newResponse();
        $response->script("window.location.href='/?manage';");
        return $response;
    }

    public function dashboard_recover_mnemonic($email)
    {
        $response = jaxon()->newResponse();

        if(!filter_var($email, FILTER_VALIDATE_EMAIL))
        {
            $response->alert("Bitte überprüfe das Format der E-Mail Adresse");
        }
        else
        {
            $user_status = $this->db->get_column_by_column_value($this->config->user['DBTABLE_REGISTRATIONS'], 'deleted', 'email', $email);

            // create a random string with 8 random alphanumeric characters, but try to prevent homoglyphs
            $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // Excludes homoglyphs like O/0, I/1, l/1
            $random_string = '';
            for ($i = 0; $i < 8; $i++)
            {
                $random_string .= $chars[random_int(0, strlen($chars) - 1)];
            }
            
            $insert_arr = [
                'feature' => 'recover_mnemonic',
                'value1' => $email,
                'value2' => $random_string
            ];

            $this->db->insert_row_with_array($this->config->user['DBTABLE_TEMPORARY'], $insert_arr);

            $response = jaxon()->newResponse();
            $response->script("window.location.href='/?lookup=recovernote';");
        }

        return $response;

    }

    public function dashboard_inline_update_init($div_id, $dbkey, $recordid)
    {
        $dbvalue = $this->db->get_column_by_column_value($this->config->user['DBTABLE_REGISTRATIONS'], $dbkey, 'id', $recordid);
        $input_field = "<INPUT type=\"text\" name=\"$dbkey\" id=\"$dbkey\" value=\"$dbvalue\" style=\"width:200px;float:left\" class=\"detail\"><i onclick=\"var dbkey='$dbkey'; JaxonInteractives.dashboard_inline_update_save('$div_id', '$dbkey', document.getElementById(dbkey).value, '$recordid');\" style=\"margin-top:6px; cursor:pointer\" class=\"fa fa-save fa-pull-right\"></i>";
        $response = jaxon()->newResponse();
        $response->assign($div_id, 'innerHTML', $input_field);

        return $response;
    }

    public function dashboard_inline_update_save($div_id, $dbkey, $dbvalue, $recordid)
    {
        $current_value = $this->db->get_column_by_column_value($this->config->user['DBTABLE_REGISTRATIONS'], $dbkey, 'id', $recordid);
        $this->view->record_note($recordid, 'event', 'modify', "Aktualisierung $dbkey: $current_value -> $dbvalue");
        $this->db->update_column_by_column_values($this->config->user['DBTABLE_REGISTRATIONS'], $dbkey, $dbvalue, 'id', $recordid);
        $input_field = "$dbvalue <i onclick=\"JaxonInteractives.dashboard_inline_update_init('$div_id', '$dbkey', '$recordid');\" class=\"fa fa-edit fa-pull-right\" style=\"padding-top:6px; cursor:pointer\"></i>";
        $response = jaxon()->newResponse();
        $response->assign($div_id, 'innerHTML', $input_field);

        return $response;
    }

    public function dashboard_inline_update_state_init($div_id, $recordid)
    {
        $dbvalue = $this->db->get_column_by_column_value($this->config->user['DBTABLE_REGISTRATIONS'], 'state', 'id', $recordid);
        $input_field = "<div style=\"float:left\">
                        <select name=\"state\" id=\"state\" class=\"detail\" style=\"width:280px\">";

        $available_states = $this->db->get_enum_array($this->config->user['DBTABLE_REGISTRATIONS'], 'state');
        $state_nice = ['new' => 'Neu', 'onboarding' => "Onboarding", 'active' => "Aktiv", 'suspended' => "Gesperrt", 'deactivated' => "Deaktiviert", 'refused' => "Abgelehnt"];
        foreach($available_states as $available_state)
        {
            $disabled = '';
            if($available_state == 'new' && $dbvalue != 'refused')  $disabled = 'disabled rule=1';
            if($available_state == 'onboarding' && $dbvalue != 'new')  $disabled = 'disabled rule=1';
            if($available_state != 'new' && $dbvalue == 'refused')  $disabled = 'disabled rule=2';
            if($available_state == 'refused' && $dbvalue != 'new')  $disabled = 'disabled rule=3';
            if($dbvalue == $available_state) $disabled = 'disabled rule=4';
            if($dbvalue == $available_state) $selected = 'selected="selected"'; else $selected = '';
            $input_field .= "<option value=\"" . $available_state . "\" $selected $disabled>" . $state_nice[$available_state] . "</option>";
        }

        $input_field .= "            </select>
                        </div>
                        <div style=\"float:right\">
                            <button class=\"search bg-green-gradient\" onclick=\"JaxonInteractives.dashboard_inline_update_state_save(document.getElementById('state').value, $recordid)\"><i class=\"fa fa-check\"></i></button>
                            <button class=\"search bg-red-gradient\" onclick=\"JaxonInteractives.dashboard_inline_update_state_cancel('detail_state', $recordid);\"><i class=\"fa fa-ban\"></i></button>
                        </div>";

        $response = jaxon()->newResponse();
        $response->assign($div_id, 'innerHTML', $input_field);

        return $response;
    }

    public function dashboard_inline_update_state_save($dbvalue, $recordid)
    {
        $current_value = $this->db->get_column_by_column_value($this->config->user['DBTABLE_REGISTRATIONS'], 'state', 'id', $recordid);
        $current_tenant = $this->db->get_column_by_column_value($this->config->user['DBTABLE_REGISTRATIONS'], 'tenant', 'id', $recordid);

        $response = jaxon()->newResponse();

        if($dbvalue == 'onboarding')    $this->db->update_column_by_column_values($this->config->user['DBTABLE_REGISTRATIONS'], 'migration_date', time(), 'id', $recordid);
        if($dbvalue == 'active')        $this->db->update_column_by_column_values($this->config->user['DBTABLE_REGISTRATIONS'], 'delivery_date', time(), 'id', $recordid);

        if($current_value == 'new')
        {
            if($dbvalue != 'refused' && $dbvalue != 'new' && $current_tenant != null)
            {
                // we have a noteworthy new state and a tenant was already selected,
                // therefore we're ready to assign new meter OIDs

                $associated_meters = $this->db->get_rows_by_column_value_extended($this->config->user['DBTABLE_METERS'], 'registration_id', $recordid);
                foreach($associated_meters as $meter)
                {
                    $highest_meter_row = $this->db->get_rows_by_column_value_extended($this->config->user['DBTABLE_METERS'],
                        NULL,
                        NULL,
                        1,
                        'meter_oid',
                        'DESC',
                        $this->config->user['DBTABLE_REGISTRATIONS'] . '.tenant = "' . $current_tenant . '" AND ' . $this->config->user['DBTABLE_METERS'] . '.meter_type = "' . $meter['meter_type'] . '"',
                        'INNER JOIN ' . $this->config->user['DBTABLE_REGISTRATIONS'] . ' ON (' . $this->config->user['DBTABLE_METERS'] . '.registration_id = ' . $this->config->user['DBTABLE_REGISTRATIONS'] . '.id) WHERE');
                    if(count($highest_meter_row) > 0)
                    {
                        $highest_meter_row = $highest_meter_row[0];
                        $next_meter_oid = $highest_meter_row['meter_oid'] + 1;
                        $response->alert('Meters were already present for this tenant. Increased Meter OID is ' . $next_meter_oid);
                    }
                    else
                    {
                        $next_meter_oid = 1;
                       $response->alert('This is the first meter for this tenant. New Meter OID is ' . $next_meter_oid);
                    }

                    $this->db->update_column_by_column_values($this->config->user['DBTABLE_METERS'], 'meter_oid', $next_meter_oid, 'id', $meter['id']);

                    if($meter['meter_type'] == 'supplier')  $shorttype = 'ERZ'; else $shorttype = 'ABN';
                    $tenant_meter_prefix = $this->db->get_column_by_column_value($this->config->user['DBTABLE_TENANTS'], 'meter_prefix_short', 'id', $current_tenant);
                    $this->db->update_column_by_column_values($this->config->user['DBTABLE_METERS'], 'meter_fqoid', $tenant_meter_prefix . $shorttype . $next_meter_oid, 'id', $meter['id']);

                    $tenant_network_substation_id = $this->db->get_column_by_column_value($this->config->user['DBTABLE_TENANTS'], 'network_substation_id', 'id', $current_tenant);
                    $this->db->update_column_by_column_values($this->config->user['DBTABLE_METERS'], 'meter_substation_id', $tenant_network_substation_id, 'id', $meter['id']);
                }
            }
        }

        $this->view->record_note($recordid, 'event', 'modify', "Aktualisierung Status: $current_value -> $dbvalue");
        $this->db->update_column_by_column_values($this->config->user['DBTABLE_REGISTRATIONS'], 'state', $dbvalue, 'id', $recordid);
        $response->script("window.location.href='/?manage_registrations&registration=$recordid';");
        return $response;
    }

    public function dashboard_inline_update_state_cancel($div_id, $recordid)
    {
        $dbvalue = $this->db->get_column_by_column_value($this->config->user['DBTABLE_REGISTRATIONS'], 'state', 'id', $recordid);
        $state_nice = ['new' => 'Neu', 'onboarding' => "Onboarding", 'active' => "Aktiv", 'suspended' => "Gesperrt", 'deactivated' => "Deaktiviert", 'refused' => "Abgelehnt"];
        $div_content = $state_nice[$dbvalue] . "<i onclick=\"JaxonInteractives.dashboard_inline_update_state_init('detail_state', '$recordid');\" class=\"fa fa-edit fa-pull-right\" style=\"padding-top:6px; cursor:pointer\"></i>";

        $response = jaxon()->newResponse();
        $response->assign($div_id, 'innerHTML', $div_content);
        return $response;
    }

    public function dashboard_inline_update_tenant_init($div_id, $recordid)
    {
        $dbvalue = $this->db->get_column_by_column_value($this->config->user['DBTABLE_REGISTRATIONS'], 'tenant', 'id', $recordid);
        $input_field = "<div style=\"float:left\">
                        <select name=\"tenant\" id=\"tenant\" class=\"detail\" style=\"width:280px\">";

        $tenants = $this->db->get_rows_by_column_value_extended($this->config->user['DBTABLE_TENANTS'], 'enabled', 'y');

        if($dbvalue == null)
        {
            $input_field .= "<option value=\"-\" selected=\"selected\">Bitte w&auml;hlen</option>";
        }

        foreach($tenants as $tenant)
        {
            if($dbvalue == $tenant['id']) $selected = 'selected="selected"'; else $selected = '';
            $input_field .= "<option value=\"" . $tenant['id'] . "\" $selected>" . $tenant['shortname'] . "</option>";
        }

        $input_field .= "            </select>
                        </div>
                        <div style=\"float:right\">
                            <button class=\"search bg-green-gradient\" onclick=\"JaxonInteractives.dashboard_inline_update_tenant_save(document.getElementById('tenant').value, '$recordid')\"><i class=\"fa fa-check\"></i></button>
                            <button class=\"search bg-red-gradient\" onclick=\"JaxonInteractives.dashboard_inline_update_tenant_cancel('detail_tenant', '$recordid');\"><i class=\"fa fa-ban\"></i></button>
                        </div>";

        $response = jaxon()->newResponse();
        $response->assign($div_id, 'innerHTML', $input_field);
        return $response;
    }

    public function dashboard_inline_update_tenant_save($dbvalue, $recordid, $confirm=false)
    {
        $response = jaxon()->newResponse();
        $current_value = $this->db->get_column_by_column_value($this->config->user['DBTABLE_REGISTRATIONS'], 'tenant', 'id', $recordid);
        $current_state = $this->db->get_column_by_column_value($this->config->user['DBTABLE_REGISTRATIONS'], 'state', 'id', $recordid);

        if($dbvalue == '-')
        {
            $response->script("window.location.href='/?manage_registrations&registration=$recordid';");
            return $response;
        }

        if($current_state == 'new' || $current_state == 'refused')
        {
            $current_value_nice = $this->db->get_column_by_column_value($this->config->user['DBTABLE_TENANTS'], 'shortname', 'id', $current_value);
            $new_value_nice = $this->db->get_column_by_column_value($this->config->user['DBTABLE_TENANTS'], 'shortname', 'id', $dbvalue);
            $this->view->record_note($recordid, 'event', 'modify', "Aktualisierung Tenant: $current_value_nice -> $new_value_nice");
            $this->db->update_column_by_column_values($this->config->user['DBTABLE_REGISTRATIONS'], 'tenant', $dbvalue, 'id', $recordid);
            $response->script("window.location.href='/?manage_registrations&registration=$recordid';");
        }
        else
        {
            if($current_value != $dbvalue)
            {
                if($confirm === true || ($current_value == null && $current_state != 'new' && $current_state != 'refused'))
                {
                    $current_value_nice = $this->db->get_column_by_column_value($this->config->user['DBTABLE_TENANTS'], 'shortname', 'id', $current_value);
                    $new_value_nice = $this->db->get_column_by_column_value($this->config->user['DBTABLE_TENANTS'], 'shortname', 'id', $dbvalue);
                    $this->view->record_note($recordid, 'event', 'modify', "Aktualisierung Tenant: $current_value_nice -> $new_value_nice");
                    $this->db->update_column_by_column_values($this->config->user['DBTABLE_REGISTRATIONS'], 'tenant', $dbvalue, 'id', $recordid);

                    $associated_meters = $this->db->get_rows_by_column_value_extended($this->config->user['DBTABLE_METERS'], 'registration_id', $recordid);
                    foreach($associated_meters as $meter)
                    {
                        $highest_meter_row = $this->db->get_rows_by_column_value_extended($this->config->user['DBTABLE_METERS'],
                                                                            NULL,
                                                                            NULL,
                                                                                    1,
                                                                                  'meter_oid',
                                                                             'DESC',
                                                                    $this->config->user['DBTABLE_REGISTRATIONS'] . '.tenant = "' . $dbvalue . '" AND ' . $this->config->user['DBTABLE_METERS'] . '.meter_type = "' . $meter['meter_type'] . '"',
                                                                        'INNER JOIN ' . $this->config->user['DBTABLE_REGISTRATIONS'] . ' ON (' . $this->config->user['DBTABLE_METERS'] . '.registration_id = ' . $this->config->user['DBTABLE_REGISTRATIONS'] . '.id) WHERE');
                        if(count($highest_meter_row) > 0)
                        {
                            $highest_meter_row = $highest_meter_row[0];
                            $next_meter_oid = $highest_meter_row['meter_oid'] + 1;
                            $response->alert('Meters were already present for this tenant. Increased Meter OID is ' . $next_meter_oid);
                        }
                        else
                        {
                            $next_meter_oid = 1;
                            $response->alert('This is the first meter for this tenant. Meter OID is ' . $next_meter_oid);
                        }

                        $this->db->update_column_by_column_values($this->config->user['DBTABLE_METERS'], 'meter_oid', $next_meter_oid, 'id', $meter['id']);

                        if($meter['meter_type'] == 'supplier')  $shorttype = 'ERZ'; else $shorttype = 'ABN';
                        $tenant_meter_prefix = $this->db->get_column_by_column_value($this->config->user['DBTABLE_TENANTS'], 'meter_prefix_short', 'id', $dbvalue);
                        $this->db->update_column_by_column_values($this->config->user['DBTABLE_METERS'], 'meter_fqoid', $tenant_meter_prefix . $shorttype . $next_meter_oid, 'id', $meter['id']);

                        $tenant_network_substation_id = $this->db->get_column_by_column_value($this->config->user['DBTABLE_TENANTS'], 'network_substation_id', 'id', $dbvalue);
                        $this->db->update_column_by_column_values($this->config->user['DBTABLE_METERS'], 'meter_substation_id', $tenant_network_substation_id, 'id', $meter['id']);
                    }

                    $response->script("window.location.href='/?manage_registrations&registration=$recordid';");
                }
                else
                {
                    $footer_actions = "
                        <button class=\"search bg-green-gradient\" style=\"border: white 1px solid\" onclick=\"JaxonInteractives.dashboard_inline_update_tenant_save('$dbvalue', '$recordid', true);\"><i class=\"fa fa-check-circle\"></i></button>
                        <button class=\"search bg-red-gradient\" style=\"border: white 1px solid\" onclick=\"JaxonInteractives.dashboard_inline_update_tenant_cancel('detail_tenant', '$recordid');\"><i class=\"fa fa-ban\"></i></button>
                    ";

                    $associated_meter_count = $this->db->get_rowcount_by_field_value_extended($this->config->user['DBTABLE_METERS'], 'registration_id', $recordid);

                    $response->assign('modalheadline', 'innerHTML', "&nbsp;Achtung!");
                    $response->assign('modalbody', 'innerHTML',
                        "<b style=\"color:black\">Dieses Mitglied ist bereits einer EEG zugeordnet.</b><br />" .
                        "Durch diese &Auml;nderung erhalten $associated_meter_count Z&auml;hlpunkt(e) neue IDs<br />" .
                        "und werden einem neuen Umspannwerk zugeordnet.");
                    $response->assign('modalfooter', 'innerHTML', $footer_actions);

                    $response->assign('modal', 'style.display', 'inline');
                }
            }
            else
            {
                $response->script("window.location.href='/?manage_registrations&registration=$recordid';");
            }
        }
        return $response;
    }

    public function dashboard_inline_update_tenant_cancel($div_id, $recordid)
    {
        $dbvalue = $this->db->get_column_by_column_value($this->config->user['DBTABLE_REGISTRATIONS'], 'tenant', 'id', $recordid);
        if($dbvalue == null)
        {
            $tenant_nice = 'Nicht zugewiesen';
        }
        else
        {
            $tenant_nice = $this->db->get_column_by_column_value($this->config->user['DBTABLE_TENANTS'], 'fullname', 'id', $dbvalue);
        }

        $div_content = $tenant_nice . "<i onclick=\"JaxonInteractives.dashboard_inline_update_tenant_init('detail_tenant', '$recordid');\" class=\"fa fa-edit fa-pull-right\" style=\"padding-top:6px; cursor:pointer\"></i>";

        $response = jaxon()->newResponse();
        $response->assign('modal', 'style.display', 'none');
        $response->assign($div_id, 'innerHTML', $div_content);
        return $response;
    }

    private function validate_record($value, $integrity=null)
    {
        // validate a given record based on its integrity check definition and return upon success or failure
        if(!isset($integrity) || $integrity == null)
        {
            return true;
        }
        else
        {
            switch($integrity)
            {
                case 'required':
                    if($value != '')
                    {
                        return true;
                    }
                    break;

                case 'numbers':
                    if(is_numeric($value))
                    {
                        return true;
                    }
                    break;

                case 'numbers>0':
                    if(is_numeric($value) && $value > 0)
                    {
                        return true;
                    }
                    break;
                    
                case 'decimal':
                    if(is_numeric(str_replace(',', '.', $value)))
                    {
                        return true;
                    }
                    break;

                case 'decimal>0':
                    if(is_numeric(str_replace(',', '.', $value)) && $value > 0)
                    {
                        return true;
                    }
                    break;

                case 'percent':
                    if(is_numeric($value))
                    {
                        if($value >= 0 && $value <= 100)
                        {
                            return true;
                        }
                    }
                    break;

                case 'email':
                    if(filter_var($value, FILTER_VALIDATE_EMAIL))
                    {
                        return true;
                    }
                    break;

                case 'phone':
                    if(isset($value))
                    {
                        $value = str_replace(['/', '(', ')', ' ', '-'], '', $value);
                        if (preg_match('/^\+?\d+$/', $value))
                        {
                            return true;
                        }
                    }
                    break;

                case 'booltrue':
                    if(isset($value))
                    {
                        if($value == 'true' || $value == '1' || $value == 'yes')
                        {
                            return true;
                        }
                    }
                    break;


                case 'boolean':
                    if(isset($value))
                    {
                        if($value == 'true' || $value == '1' || $value == 'yes' || $value == 'false' || $value == '0' || $value == 'no' || $value == 'y' || $value == 'n')
                        {
                            return true;
                        }
                    }
                    break;

                case 'hwinventoryid':
                    if(isset($value))
                    {
                        if (preg_match('/^[0-9]{3}([:. ])[0-9]{3}([:. ])[0-9]{3}$/', $value))
                        {
                            return true;
                        }
                        elseif($value == '' || $value == null)
                        {
                            return true;
                        }
                        else
                        {
                            // only throw an error if a provided information is obviously wrong,
                            // empty values are allowed
                            return false;
                        }
                    }
                    else
                    {
                        return true;
                    }
                    break;

                case 'iban':
                    if(isset($value))
                    {
                        $value = strtoupper(str_replace(' ', '', $value));

                        // Ensure IBAN is alphanumeric and at least 15 characters long
                        if (!ctype_alnum($value) || strlen($value) < 15)
                        {
                            return false;
                        }

                        // Rearrange IBAN for validation: move the first 4 characters to the end
                        $checkString = substr($value, 4) . substr($value, 0, 4);

                        // Convert letters to numbers (A = 10, B = 11, ..., Z = 35)
                        $numericString = '';
                        foreach (str_split($checkString) as $char)
                        {
                            $numericString .= is_numeric($char) ? $char : ord($char) - 55;
                        }

                        // Perform mod-97 operation without bcmod
                        $remainder = 0;
                        foreach (str_split($numericString, 9) as $chunk)
                        {
                            $remainder = (int)(($remainder . $chunk) % 97);
                        }

                        if ($remainder === 1)
                        {
                            return true;
                        }
                    }
                    break;

                case 'vat_basic':

                    $vatpattern = '/^(ATU\d{8}|BE0\d{9}|BG\d{9,10}|CY\d{8}L|CZ\d{8,10}|DE\d{9}|DK\d{8}|EE\d{9}|EL\d{9}|ES[A-Z0-9]\d{7}[A-Z0-9]|FI\d{8}|FR[A-Z0-9]{2}\d{9}|HR\d{11}|HU\d{8}|IE\d{7}[A-Z]{1,2}|IT\d{11}|LT\d{9,12}|LU\d{8}|LV\d{11}|MT\d{8}|NL\d{9}B\d{2}|PL\d{10}|PT\d{9}|RO\d{2,10}|SE\d{12}|SI\d{8}|SK\d{10})$/i';

                    if(isset($value) && preg_match($vatpattern, trim($value)))
                    {
                        // okay, we made it past the regex. Let's take a closer look at austria and germany and check
                        // for validity based on their modulo rules
                        $countryCode = substr(trim($value), 0, 2);

                        switch($countryCode)
                        {
                            case "AT":
                                $digits = substr(trim($value), 3); // Skip "U" for Austria
                                $weights = [1, 2, 1, 2, 1, 2, 1]; // Multiplication pattern
                                $sum = 0;

                                for ($i = 0; $i < 7; $i++) {
                                    $product = $digits[$i] * $weights[$i];
                                    $sum += ($product > 9) ? ($product - 9) : $product;
                                }

                                $checksum = (10 - ($sum % 10)) % 10;
                                if ($checksum == $digits[7]) {
                                    return true;
                                }
                                break;

                            case "DE":
                                $digits = substr(trim($value), 2);
                                $sum = 0;
                                $weight = 10; // Initial weight is 10

                                for ($i = 0; $i < 8; $i++) {
                                    $sum = ($digits[$i] + $weight) % 10;
                                    if ($sum == 0) {
                                        $sum = 10;
                                    }
                                    $sum *= 2;
                                    $sum %= 11;
                                }

                                $checksum = (11 - $sum) % 10;
                                if ($checksum == $digits[8]) {
                                    return true;
                                }
                                break;

                            default:
                                // let's return true since we don't have access to a more sophisticated check
                                // for these countries
                                return true;
                        }

                       return true; // VAT ID is valid
                    }
                    break;

                case 'vat_extended':

                    $vatpattern = '/^(ATU\d{8}|BE0\d{9}|BG\d{9,10}|CY\d{8}L|CZ\d{8,10}|DE\d{9}|DK\d{8}|EE\d{9}|EL\d{9}|ES[A-Z0-9]\d{7}[A-Z0-9]|FI\d{8}|FR[A-Z0-9]{2}\d{9}|HR\d{11}|HU\d{8}|IE\d{7}[A-Z]{1,2}|IT\d{11}|LT\d{9,12}|LU\d{8}|LV\d{11}|MT\d{8}|NL\d{9}B\d{2}|PL\d{10}|PT\d{9}|RO\d{2,10}|SE\d{12}|SI\d{8}|SK\d{10})$/i';

                    if(isset($value) && preg_match($vatpattern, trim($value)))
                    {

                        $wsdl = "https://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl";

                        $countryCode = substr(trim($value), 0, 2); // First two characters (country code)
                        $localID = substr(trim($value), 2); // Everything after the first two characters

                        try
                        {
                            $client = new SoapClient($wsdl);
                            $response = $client->checkVat([
                                'countryCode' => $countryCode,
                                'vatNumber' => $localID
                            ]);

                            if ($response->valid)
                            {
                                return true; // VAT ID is valid
                            }
                            else
                            {
                                return false; // VAT ID is not valid
                            }
                        }
                        catch (SoapFault $e)
                        {
                            // Handle error (e.g., API service down or incorrect request)
                            return false;
                        }
                    }
                    break;

                case 'meter_shortid':
                    if(isset($value))
                    {
                        if (is_numeric($value) && strlen($value) == 7 && $value != '0000000')
                        {
                            return true;
                        }
                    }
                    break;

                case 'meter_longid':
                    if(isset($value))
                    {
                        $clean_value = str_replace(' ', '', $value);
                        if (strlen($clean_value) == 33 && str_starts_with($clean_value, 'AT00') && !str_ends_with($clean_value, "0000000"))
                        {
                            return true;
                        }
                    }
                    break;

                default:
                    error_log('Unknown integrity type: ' . $integrity . ' for record ' . $value);

            }

            return false; // we did not return true earlier, so something was messed up!

        }
    }

    public function copy_address($meter_key)
    {
        // get the main address and copy it to a certain meters detail information
        $response = jaxon()->newResponse();

        if(isset($_SESSION['generic_information']['street']['value']))
        {
            $response->assign('street_' . $meter_key, 'value', $_SESSION['generic_information']['street']['value']);
            $_SESSION['meters']["$meter_key"]['street']['value'] = $_SESSION['generic_information']['street']['value'];
        }

        if(isset($_SESSION['generic_information']['number']['value']))
        {
            $response->assign('number_' . $meter_key, 'value', $_SESSION['generic_information']['number']['value']);
            $_SESSION['meters']["$meter_key"]['number']['value'] = $_SESSION['generic_information']['number']['value'];
        }

        if(isset($_SESSION['generic_information']['zip']['value']))
        {
            $response->assign('zip_' . $meter_key, 'value', $_SESSION['generic_information']['zip']['value']);
            $_SESSION['meters']["$meter_key"]['zip']['value'] = $_SESSION['generic_information']['zip']['value'];
        }

        if(isset($_SESSION['generic_information']['city']['value']))
        {
            $response->assign('city_' . $meter_key, 'value', $_SESSION['generic_information']['city']['value']);
            $_SESSION['meters']["$meter_key"]['city']['value'] = $_SESSION['generic_information']['city']['value'];
        }

        return $response;

    }

    private function generate_uuid4($data = null)
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