<?php

use function Jaxon\jaxon;

class Interactives
{

    private $object_broker;
    private $config;
    private $db;

    public function __construct()
    {
        include_once('object_broker.php.inc');
        $this->object_broker = new OBJECT_BROKER();

        include_once('controllers/controller_config.php.inc');
        $this->config = $this->object_broker->instance['config'] = new CONTROLLER_CONFIG();

        include_once('controllers/controller_db.php.inc');
        $this->db = $this->object_broker->instance['db'] = new CONTROLLER_DB($this->object_broker);
    }

    public function __destruct()
    {

    }

    public function deauthenticate()
    {
        session_unset();

        $response = jaxon()->newResponse();
        $response->script("window.location.href='/';");
        return $response;
    }

    public function authenticate()
    {
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
        $hashed_mnemonic_stored = $this->db->get_column_by_column_value($this->config->user['DBTABLE_REGISTRATIONS'], 'mnemonic', 'email', $username);

        if($hashed_mnemonic_provided == $hashed_mnemonic_stored)
        {
            $_SESSION['authenticated'] = $username;
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

    public function add_meter($meter_type, $prefix)
    {
        $response = jaxon()->newResponse();

        $id = $this->generate_uuid4();

        $_SESSION['meters']["$id"]['prefix'] = $prefix;
        $_SESSION['meters']["$id"]['value'] = '000000000';
        $_SESSION['meters']["$id"]['type'] = $meter_type;

        $element = '
            Z&auml;hlpunktnummer (letzte 9 Stellen) <div class="input-box">
                <span class="prefix">' . $prefix . '</span>
                <input type="text" name="' . $id . '" id="' . $id . '" value="000000000" maxlength="9" onfocus="this.select()"  onfocusout="JaxonInteractives.update_meter_value(' . "'" . $id . "'" . ', document.getElementById(' . "'" . $id . "'" . ').value);" />
                &nbsp;&nbsp;<button style="background-color:darkred"  onclick="JaxonInteractives.rmv_meter(' . "'" . $id . "'" . ', ' . "'" . $meter_type . "'" . ');"><i style="font-size:16px;color:white;" class="icon fa-trash-alt"></i></button>
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
        $response = jaxon()->newResponse();

        $id = $this->generate_uuid4();
        $_SESSION['storages']["$id"]['value'] = '0';

        $element = '
        <div id="container-' . $id . '">Speicher<br>
            <div class="input-box" style="width:254px;">
                <span class="prefix">Kapazit&auml;t:&nbsp;</span>
                <input type="text" name="' . $id . '" id="' . $id . '" value="0" maxlength="4" style="width:80px;text-align:center" onfocus="this.select()" onfocusout="JaxonInteractives.update_storage_value(' . "'" . $id . "'" . ', document.getElementById(' . "'" . $id . "'" . ').value);" />
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
        if(isset($_SESSION['storages']["$id"]))
        {
            $_SESSION['storages']["$id"]['value'] = $value;
        }
    }

    public function update_meter_value($id, $value)
    {
        if(isset($_SESSION['meters']["$id"]))
        {
            $_SESSION['meters']["$id"]['value'] = $value;
        }
    }

    public function update_session_bucket($record, $value, $session_bucket)
    {
        $_SESSION["$session_bucket"]["$record"]["value"] = $value;
        $response = jaxon()->newResponse();
        return $response;
    }

    public function update_credential_cache($record, $value)
    {
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

    public function update_meter_detail($meter_key, $record, $value)
    {
        $_SESSION['meters']["$meter_key"]["$record"]['value'] = $value;
    }

    public function step_back($current_step)
    {
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
                }
            }

            $next_step = $current_step + 1;
            $response->script("window.location.href='/?join=" . $_SESSION['generic_information']['join_type'] .  "&step=" . $next_step . "';");
            return $response;
        }
    }

    public function check_step_integrity_company(&$jaxon_response_object)
    {
        $failmsg = null;
        $focustarget = null;

        $email_already_used = $this->db->get_rowcount_by_field_value_extended($this->config->user['DBTABLE_REGISTRATIONS'],'email',$_SESSION['generic_information']['email']['value']);
        if($email_already_used > 0)
        {
            $response = jaxon()->newResponse();
            $response->alert("Diese E-Mail-Adresse ist bereits einem Benutzer dieser EEG zugeordnet");
            $response->script("document.getElementById('email').focus();");
            return $response;
        }

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
        $failmsg = null;
        $focustarget = null;

        $email_already_used = $this->db->get_rowcount_by_field_value_extended($this->config->user['DBTABLE_REGISTRATIONS'],'email',$_SESSION['generic_information']['email']['value']);
        if($email_already_used > 0)
        {
            $response = jaxon()->newResponse();
            $response->alert("Diese E-Mail-Adresse ist bereits einem Benutzer dieser EEG zugeordnet");
            $response->script("document.getElementById('email').focus();");
            return $response;
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
        $failmsg = null;
        $focustarget = null;

        $email_already_used = $this->db->get_rowcount_by_field_value_extended($this->config->user['DBTABLE_REGISTRATIONS'],'email',$_SESSION['generic_information']['email']['value']);
        if($email_already_used > 0)
        {
            $response = jaxon()->newResponse();
            $response->alert("Diese E-Mail-Adresse ist bereits einem Benutzer dieser EEG zugeordnet");
            $response->script("document.getElementById('email').focus();");
            return $response;
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
        $failmsg = null;
        $focustarget = null;

        foreach($_SESSION['meters'] as $meter_key => $meter_object)
        {
            if($this->validate_record($meter_object['value'], 'meter_shortid') === false)
            {
                $failmsg = "Fehlerhaftes Format: Bitte überprüfen Sie die Zählpunktnummer";
                $focustarget = $meter_key;
            }
            else
            {
                $meter_id_count = $this->db->get_rowcount_by_field_value_extended($this->config->user['DBTABLE_METERS'],'meter_id', $meter_object['prefix'] . $meter_object['value']);
                if($meter_id_count > 0)
                {
                    $jaxon_response_object->alert("Dieser Zählpunkt ist bereits einem Mitglied dieser EEG zugeordnet");
                    $jaxon_response_object->script("document.getElementById('" . $meter_key . "').focus();");
                    return false;
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
        $failmsg = null;
        $focustarget = null;

        foreach($_SESSION['storages'] as $storage_key => $storage_object)
        {
            if($this->validate_record($storage_object['value'], 'numbers>0') === false)
            {
                $failmsg = "Bitte überprüfen Sie die angegebene Speicherkapazität";
                $focustarget = $storage_key;
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


    public function check_step_integrity_banking(&$jaxon_response_object)
    {
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

    private function validate_record($value, $integrity=null)
    {
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

                case 'meter_shortid':
                    if(isset($value))
                    {
                        if (is_numeric($value) && strlen($value) == 9 && $value != '000000000')
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
        $response = jaxon()->newResponse();

        $response->assign('street_' . $meter_key, 'value', $_SESSION['generic_information']['street']['value']);
        $_SESSION['meters']["$meter_key"]['street']['value'] = $_SESSION['generic_information']['street']['value'];

        $response->assign('number_' . $meter_key, 'value', $_SESSION['generic_information']['number']['value']);
        $_SESSION['meters']["$meter_key"]['number']['value'] = $_SESSION['generic_information']['number']['value'];

        $response->assign('zip_' . $meter_key, 'value', $_SESSION['generic_information']['zip']['value']);
        $_SESSION['meters']["$meter_key"]['zip']['value'] = $_SESSION['generic_information']['zip']['value'];

        $response->assign('city_' . $meter_key, 'value', $_SESSION['generic_information']['city']['value']);
        $_SESSION['meters']["$meter_key"]['city']['value'] = $_SESSION['generic_information']['city']['value'];

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