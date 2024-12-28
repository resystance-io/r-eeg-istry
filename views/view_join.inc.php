<?php

class VIEW_JOIN
{

    private $config;
    private $object_broker;

    public function __construct($object_broker, $database=NULL)
    {

        $this->object_broker = $object_broker;
        $this->config = $object_broker->instance['config'];
    }
    public function view_render()
    {

        switch($_REQUEST['join'])
        {
            case "individual":
                $_SESSION['generic_information']['join_type'] = "individual";
                $this->view_render_individual();
                $this->view_render_navigation();
                break;

            case "agriculture":
                $_SESSION['generic_information']['join_type'] = "agriculture";
                $this->view_render_agriculture();
                $this->view_render_navigation();
                break;

            case "company":
                $_SESSION['generic_information']['join_type'] = "company";
                $this->view_render_company();
                $this->view_render_navigation();
                break;

            case "meters":
                $this->view_render_meter_details();
                $this->view_render_navigation();
                break;

            case "banking":
                $this->view_render_banking_details();
                $this->view_render_navigation();
                break;

            case "finished":
                $this->view_render_finished();
                $this->view_render_navigation();
                break;

            default:
                print '
                    <header id="header">
                        <h1>Erneuerbare Energiegemeinschaft VIERE</h1>
                        <p>Sch&ouml;n dass du dich f&uuml;r die Mitgliedschaft in unserer EEG interessierst.<br />Wir freuen uns &uuml;ber jedes neue Mitglied.<br /></p>
        
                        <p style="color:white">Bitte w&auml;hle die passende Beitrittsform:</p>
                    </header>
                ';

                $this->view_render_switch_dialogue();
                break;
        }
    }


    private function view_render_switch_dialogue()
    {
        print '
            <div class="button_container">
                <button type="button" class="mainbtn" style="" id="btn_enroll_company" onClick="location.href=' . "'" . "?join=company" . "'" . '"><img src="images/noun_company.png" alt="Join as Company" id="join_eeg" style="height: 60px; margin-left: 30px;"><br />Als Unternehmen beitreten</button>
                <button type="button" class="mainbtn" style="" id="btn_enroll_individual" onClick="location.href=' . "'" . "?join=individual" . "'" . '"><img src="images/noun_individual.png" alt="Join as Individual" id="lookup_eeg" style="height: 60px; margin-left: 30px;"><br />Als Privatperson beitreten</button>
                <button type="button" class="mainbtn" style="" id="btn_enroll_agriculture" onClick="location.href=' . "'" . "?join=agriculture" . "'" . '"><img src="images/noun_agriculture.png" alt="Join as Agriculture" id="lookup_eeg" style="height: 60px; margin-left: 30px;"><br />Als Landwirtschaft beitreten</button>
            </div>
        ';
    }

    private function view_render_company()
    {
        print '
                    <header id="header">
                        <h1>Erneuerbare Energiegemeinschaft VIERE</h1>
                        <h2>Als Unternehmen beitreten</h2>
                        <p>F&uuml;r die Anmeldung als Unternehmen ben&ouml;tigen wir ein paar Daten.</p>
                        
                    </header>
        ';

        print "<h3>Allgemeine Daten</h3>";
        print "<div class=\"form-container\">";

        $this->view_render_part_captioned_inputfield("Firmenwortlaut", "company", "generic_information", "required");
        $this->view_render_part_captioned_inputfield("UID", "uid", "generic_information");
        $this->view_render_part_captioned_inputfield("Postleitzahl", "zip", "generic_information", "numbers");
        $this->view_render_part_captioned_inputfield("Ort", "city", "generic_information", "required");
        $this->view_render_part_captioned_inputfield("Stra&szlig;e", "street", "generic_information", "required");
        $this->view_render_part_captioned_inputfield("Hausnummer", "number", "generic_information", "required");
        $this->view_render_part_captioned_inputfield("Telefonnummer", "phone", "generic_information", "phone");
        $this->view_render_part_captioned_inputfield("E-Mail Adresse", "email", "generic_information", "email");

        print "</div><br />";

        $this->view_render_consumption_meters();

        print "<br />";

        $this->view_render_supply_meters();

        print "<br />";

        $this->view_render_energy_storage();

    }

    private function view_render_individual()
    {

        print '
                    <header id="header">
                        <h1>Erneuerbare Energiegemeinschaft VIERE</h1>
                        <h2>Als Privatperson beitreten</h2>
                        <p>F&uuml;r die Anmeldung als Privatperson ben&ouml;tigen wir ein paar Daten.</p>
                        
                    </header>
        ';

        print "<h3>Allgemeine Daten</h3>";
        print "<div class=\"form-container\">";

        $this->view_render_part_captioned_inputfield("Vorname", "firstname", "generic_information", "required");
        $this->view_render_part_captioned_inputfield("Nachname", "lastname", "generic_information", "required");
        $this->view_render_part_captioned_inputfield("Stra&szlig;e", "street", "generic_information", "required");
        $this->view_render_part_captioned_inputfield("Hausnummer", "number", "generic_information", "required");
        $this->view_render_part_captioned_inputfield("Postleitzahl", "zip", "generic_information", "numbers");
        $this->view_render_part_captioned_inputfield("Ort", "city", "generic_information", "required");
        $this->view_render_part_captioned_inputfield("Geburtsdatum", "birthdate", "generic_information");
        $this->view_render_part_captioned_inputfield("Telefonnummer", "phone", "generic_information", "phone");
        $this->view_render_part_captioned_inputfield("E-Mail Adresse", "email", "generic_information", "email");

        print "</div><br />";

        $this->view_render_consumption_meters();

        print "<br />";

        $this->view_render_supply_meters();

        print "<br />";

        $this->view_render_energy_storage();

    }


    private function view_render_agriculture()
    {

        print '
                    <header id="header">
                        <h1>Erneuerbare Energiegemeinschaft VIERE</h1>
                        <h2>Als Landwirtschaft beitreten</h2>
                        <p>F&uuml;r die Anmeldung als Landwirtschaft ben&ouml;tigen wir ein paar Informationen.</p>
                    </header>
        ';

        print "<h3>Allgemeine Daten</h3>";
        print "<div class=\"form-container\">";

        $this->view_render_part_captioned_inputfield("Vorname", "firstname", "generic_information", "required");
        $this->view_render_part_captioned_inputfield("Nachname", "lastname", "generic_information", "required");
        $this->view_render_part_captioned_inputfield("Stra&szlig;e", "street", "generic_information", "required");
        $this->view_render_part_captioned_inputfield("Hausnummer", "number", "generic_information", "required");
        $this->view_render_part_captioned_inputfield("Postleitzahl", "zip", "generic_information", "numbers");
        $this->view_render_part_captioned_inputfield("Ort", "city", "generic_information", "required");
        $this->view_render_part_captioned_inputfield("Geburtsdatum", "birthdate", "generic_information");
        $this->view_render_part_captioned_inputfield("Telefonnummer", "phone", "generic_information", "phone");
        $this->view_render_part_captioned_inputfield("E-Mail Adresse", "email", "generic_information", "email");

        print "</div><br />";

        $this->view_render_consumption_meters();

        print "<br />";

        $this->view_render_supply_meters();

        print "<br />";

        $this->view_render_energy_storage();

    }

    private function view_render_meter_details()
    {

        print '
                    <header id="header">
                        <h1>Erneuerbare Energiegemeinschaft VIERE</h1>
                        <h2>Erg&auml;nzende Angaben</h2>
                        <p>Bitte erg&auml;nze die Informationen zu den von dir angegebenen Z&auml;hlpunkten</p>
                    </header>
        ';

        if(isset($_SESSION['meters']))
        {
            foreach ($_SESSION['meters'] as $meter_key => $meter_object)
            {

                print "<div class=\"form-container\">";

                print "
                    <div style='float:left;margin-right:80px;'>
                ";

                if ($meter_object['type'] == "consumers")
                {
                    print "<h3>Adresse des Bezugsz&auml;hlpunkts " . $meter_object['value'] . "</h3>";
                }
                elseif ($meter_object['type'] == "suppliers")
                {
                    print "<h3>Adresse des Einspeisez&auml;hlpunkts " . $meter_object['value'] . "</h3>";
                }

                $this->view_render_meter_detail_inputfield($meter_key, "Stra&szlig;e", 'street', 'required');

                $this->view_render_meter_detail_inputfield($meter_key, "Nummer", 'number', 'required');

                $this->view_render_meter_detail_inputfield($meter_key, "Ort", 'city', 'required');

                $this->view_render_meter_detail_inputfield($meter_key, "PLZ", 'zip', 'numbers');

                print '<button type="button" class="thinbtn" id="btn_prefill_' . $meter_key . '" onClick="JaxonInteractives.copy_address(' . "'" . $meter_key . "'" . ');">Hauptadresse &uuml;bernehmen</button>';
                print "</div>";

                print "<div style='float:left;height:100%;valign:middle'>";

                if ($meter_object['type'] == "suppliers")
                {
                    print "<h3>Einspeiseleistung</h3>";

                    $this->view_render_meter_detail_inputfield($meter_key, "Leistung (kWp)", 'power', 'decimal');
                }

                print "</div>";

                print "</div><br />";
            }
        }

    }

    private function view_render_banking_details()
    {
        print '
                    <header id="header">
                        <h1>Erneuerbare Energiegemeinschaft VIERE</h1>
                        <h2>Zahlungsinformationen</h2>
                        <p>Noch ein paar Infos zum Konto, dann ist es geschafft...</p>
                        
                    </header>
        ';

        print "<h3>Kontoinformationen</h3>";
        print "<div class=\"form-container\">";

        $this->view_render_part_captioned_inputfield("Name d. Kontoinhabers", "banking_name", "generic_information", "required");
        $this->view_render_part_captioned_inputfield("IBAN", "banking_iban", "generic_information", "iban");
        $this->view_render_part_annotated_checkbox("Hiermit best&auml;tige ich die Richtigkeit der angegebenen Kontoinformationen<br />und erm&auml;chtige VIERE zum Bankeinzug im Rahmen der Leistungsabrechnung", "banking_consent", "generic_information", "booltrue");

        print "</div><br />";

    }

    private function view_render_finished()
    {

        $_SESSION['finished'] = true;

        print '
                    <header id="header">
                        <h1>Erneuerbare Energiegemeinschaft VIERE</h1>
                    </header>
        ';

        print "&nbsp;<br />&nbsp;<br />";
        print "<h2>Vielen Dank f&uuml;r deine Anmeldung</h2>";
        print "&nbsp;<br>Deine Daten werden schnellstm&ouml;glich &uuml;berpr&uuml;ft und in unser System &uuml;bernommen.<br />Sobald das Datum feststeht ab dem du eneuerbare Energie aus unserer Gemeinschaft beziehen wirst, kontaktieren wir dich umgehend. <br /><br />Solltest du Fragen haben, stehen wir dir selbstverst&auml;ndlich gerne zur Verf&uuml;gung.<br />";

        print "<br />&nbsp;<br />&nbsp;<br />";
        print "<h3>Dein Passwort:</h3>";
        print "<div class=\"form-container\">";
        print "<h2>" . $_SESSION['mnemonic'] . "</h2>";
        print "Du kannst dieses Passwort nutzen um jederzeit den Bearbeitungsfortschritt deines Antrages einzusehen - und natürlich um deine Daten zu &auml;ndern.<br />Bitte bewahre es gut auf!";
        print "</div>";

        // check if this mnemonic was already stored
        $hashed_mnemonic = hash('sha256', $_SESSION['mnemonic']);
        $mnemonic_count = $this->object_broker->instance['db']->get_rowcount_by_field_value_extended($this->config->user['DBTABLE_REGISTRATIONS'],'mnemonic',$hashed_mnemonic);

        if($mnemonic_count == 0)
        {
            $registration_array['registration_date'] = time();
            $registration_array['structure_version'] = 1;
            $registration_array['mnemonic'] = $hashed_mnemonic;
            $registration_array['type'] = $_SESSION['generic_information']['join_type'];

            switch($_SESSION['generic_information']['join_type'])
            {
                case 'agriculture':
                    if (isset($_SESSION['generic_information']['firstname']['value'])) $registration_array['firstname'] = $_SESSION['generic_information']['firstname']['value'];
                    if (isset($_SESSION['generic_information']['lastname']['value'])) $registration_array['lastname'] = $_SESSION['generic_information']['lastname']['value'];
                    if (isset($_SESSION['generic_information']['birthdate']['value'])) $registration_array['birthdate'] = $_SESSION['generic_information']['birthdate']['value'];
                    break;

                case 'individual':
                    if (isset($_SESSION['generic_information']['firstname']['value'])) $registration_array['firstname'] = $_SESSION['generic_information']['firstname']['value'];
                    if (isset($_SESSION['generic_information']['lastname']['value'])) $registration_array['lastname'] = $_SESSION['generic_information']['lastname']['value'];
                    if (isset($_SESSION['generic_information']['birthdate']['value'])) $registration_array['birthdate'] = $_SESSION['generic_information']['birthdate']['value'];
                    break;

                case 'company':
                    if (isset($_SESSION['generic_information']['company']['value'])) $registration_array['company_name'] = $_SESSION['generic_information']['company']['value'];
                    if (isset($_SESSION['generic_information']['uid']['value'])) $registration_array['uid'] = $_SESSION['generic_information']['uid']['value'];
                    break;
            }

            if (isset($_SESSION['generic_information']['street']['value'])) $registration_array['street'] = $_SESSION['generic_information']['street']['value'];
            if (isset($_SESSION['generic_information']['number']['value'])) $registration_array['number'] = $_SESSION['generic_information']['number']['value'];
            if (isset($_SESSION['generic_information']['zip']['value'])) $registration_array['zip'] = $_SESSION['generic_information']['zip']['value'];
            if (isset($_SESSION['generic_information']['city']['value'])) $registration_array['city'] = $_SESSION['generic_information']['city']['value'];
            if (isset($_SESSION['generic_information']['phone']['value'])) $registration_array['phone'] = $_SESSION['generic_information']['phone']['value'];
            if (isset($_SESSION['generic_information']['email']['value'])) $registration_array['email'] = $_SESSION['generic_information']['email']['value'];

            if (isset($_SESSION['generic_information']['banking_name']['value'])) $registration_array['banking_name'] = $_SESSION['generic_information']['banking_name']['value'];
            if (isset($_SESSION['generic_information']['banking_iban']['value'])) $registration_array['banking_iban'] = $_SESSION['generic_information']['banking_iban']['value'];
            if (isset($_SESSION['generic_information']['banking_consent']['value']) && $_SESSION['generic_information']['banking_consent']['value'] == '1') $registration_array['banking_consent'] = time();

            if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
            {
                $registration_array['ip_address'] = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
            }
            else
            {
                $registration_array['ip_address'] = $_SERVER['REMOTE_ADDR'];
            }

            $registration_autoinc_id = $this->object_broker->instance['db']->insert_row_with_array($this->config->user['DBTABLE_REGISTRATIONS'], $registration_array);

            if(isset($_SESSION['meters']))
            {
                foreach($_SESSION['meters'] as $meter_key => $meter_object)
                {
                    if($meter_object['type'] == "suppliers")
                    {
                        $meter_type = 'supplier';
                        $meter_array['meter_power'] = $meter_object['power']['value'];
                    }
                    elseif($meter_object['type'] == "consumers")
                    {
                        $meter_type = 'consumer';
                    }

                    $meter_array['registration_id'] = $registration_autoinc_id;
                    $meter_array['meter_id'] = $meter_object['prefix'] . $meter_object['value'];
                    $meter_array['meter_uuid'] = $meter_key;
                    $meter_array['meter_type'] = $meter_type;
                    $meter_array['meter_addr_street'] = $meter_object['street']['value'];
                    $meter_array['meter_addr_number'] = $meter_object['number']['value'];
                    $meter_array['meter_addr_city'] = $meter_object['city']['value'];
                    $meter_array['meter_addr_zip'] = $meter_object['zip']['value'];

                    $meter_autoinc_id = $this->object_broker->instance['db']->insert_row_with_array($this->config->user['DBTABLE_METERS'], $meter_array);
                }
            }

            if(isset($_SESSION['storages']))
            {
                foreach($_SESSION['storages'] as $storage_key => $storage_object)
                {
                    $storage_array['registration_id'] = $registration_autoinc_id;
                    $storage_array['storage_uuid'] = $storage_key;
                    $storage_array['storage_capacity'] = $storage_object['value'];

                    $storage_autoinc_id = $this->object_broker->instance['db']->insert_row_with_array($this->config->user['DBTABLE_STORAGES'], $storage_array);
                }
            }
        }
    }

    private function view_render_consumption_meters()
    {
        print "<br /><h3 style=\"margin:0px;\">Z&auml;hlpunkte (Bezug)</h3>";
        print "<span style=\"font-size:16px;\">Mit welchen Z&auml;hlpunkten m&ouml;chtest du Energie von uns beziehen?</span>";
        print "<div class=\"form-container\">";

        if(isset($_SESSION['meters']))
        {
            $consumer_count = 0;
            foreach($_SESSION['meters'] as $meter_key => $meter_object)
            {
                if($meter_object['type'] == "consumers")
                {
                    $this->view_render_prefixed_meter("Z&auml;hlpunktnummer (letzte 9 Stellen)", $this->config->user['EEG_CONSUMERS_PREFIX'], $meter_key, $meter_object['value']);
                    $consumer_count++;
                }
            }
        }

        if(!isset($_SESSION['meters']) || $consumer_count == 0)
        {
            $id = $this->generate_uuid4();
            $_SESSION['meters']["$id"]['prefix'] = $this->config->user['EEG_CONSUMERS_PREFIX'];
            $_SESSION['meters']["$id"]['value'] = '000000000';
            $_SESSION['meters']["$id"]['type'] = 'consumers';
            $this->view_render_prefixed_meter("Z&auml;hlpunktnummer (letzte 9 Stellen)", $this->config->user['EEG_CONSUMERS_PREFIX'], $id);
        }

        print "<div id='end_of_consumers'></div>";
        print '<br /><i style="font-size:16px;cursor:pointer;" class="icon fa-plus-square" onclick="JaxonInteractives.add_meter(' . "'consumers'" . ',' . "'" . $this->config->user['EEG_CONSUMERS_PREFIX'] . "'" . ');"></i><span class="label" style="font-weight:normal;font-size:16px;cursor:pointer;" onclick="JaxonInteractives.add_meter(' . "'consumers'" . ',' . "'" . $this->config->user['EEG_CONSUMERS_PREFIX'] . "'" . ');">&nbsp; Einen Bezugsz&auml;hlpunkt hinzuf&uuml;gen</span>';
        print "</div>";
    }

    private function view_render_supply_meters()
    {
        print "<br /><h3 style=\"margin:0px;\">Z&auml;hlpunkte (Einspeisung)</h3>";
        print "<span style=\"font-size:16px;\">Erzeugst du selbst erneuerbare Energie und m&ouml;chtest in unsere EEG einspeisen?</span>";
        print "<div class=\"form-container\">";

        if(isset($_SESSION['meters']))
        {
            foreach($_SESSION['meters'] as $meter_key => $meter_object)
            {
                if($meter_object['type'] == "suppliers")
                {
                    $this->view_render_prefixed_meter("Z&auml;hlpunktnummer (letzte 9 Stellen)", $this->config->user['EEG_SUPPLIERS_PREFIX'], $meter_key, $meter_object['value']);
                }
            }

        }
        print "<div id='end_of_suppliers'></div>";
        print '<br /><i style="font-size:16px;cursor:pointer;" class="icon fa-plus-square" onclick="JaxonInteractives.add_meter(' . "'suppliers'" . ',' . "'" . $this->config->user['EEG_SUPPLIERS_PREFIX'] . "'" . ');"></i><span class="label" style="font-weight:normal;font-size:16px;cursor:pointer;" onclick="JaxonInteractives.add_meter(' . "'suppliers'" . ',' . "'" . $this->config->user['EEG_SUPPLIERS_PREFIX'] . "'" . ');">&nbsp; Einen Einspeisez&auml;hlpunkt hinzuf&uuml;gen</span>';
        print "</div>";
    }

    private function view_render_energy_storage()
    {
        print "<br /><h3 style=\"margin:0px;\">Vorhandene Energiespeicher</h3>";
        print "<span style=\"font-size:16px;\">Freiwillige Angabe / Erhebung zu statistischen Zwecken.</span>";
        print "<div class=\"form-container\">";

        if(isset($_SESSION['storages']))
        {
            foreach($_SESSION['storages'] as $storage_key => $storage_object)
            {
                $this->view_render_prefixed_storage("Energiespeicher", $storage_key, $storage_object['value']);
            }
        }

        print "<div id='end_of_storages'></div>";
        print '<br /><i style="font-size:16px;cursor:pointer;" class="icon fa-plus-square" onclick="JaxonInteractives.add_storage();"></i><span class="label" style="font-weight:normal;font-size:16px;cursor:pointer;" onclick="JaxonInteractives.add_storage();">&nbsp; Einen Energiespeicher hinzuf&uuml;gen</span>';
        print "</div>";
    }



    private function view_render_meter_detail_inputfield($meter_key, $caption, $id, $integrity)
    {
        $prefill = (isset($_SESSION['meters']["$meter_key"]["$id"]['value'])) ? $_SESSION['meters']["$meter_key"]["$id"]['value'] : '';
        $_SESSION['meters']["$meter_key"]["$id"]['integrity'] = $integrity;
        print $caption . '<br><input type="text" name="' . $id . '_' . $meter_key . '" value="' . $prefill . '" id="' . $id . '_' . $meter_key . '" onfocus="this.select()" onfocusout="JaxonInteractives.update_meter_detail(' . "'" . $meter_key . "'" . ', ' . "'" . $id . "'" . ', document.getElementById(' . "'" . $id . '_' . $meter_key . "'" . ').value);" /><br />';
    }

    private function view_render_part_captioned_inputfield($caption, $id, $session_bucket=null, $integrity=null)
    {
        if($session_bucket != null)
        {
            if(isset($_SESSION["$session_bucket"]["$id"]))
            {
                $prefill = isset($_SESSION["$session_bucket"]["$id"]["value"]) ? $_SESSION["$session_bucket"]["$id"]["value"] : '';
            }
            else
            {
                $_SESSION["$session_bucket"]["$id"]["integrity"] = $integrity;
                $prefill = '';
            }

            print $caption . '<br><input type="text" onfocus="this.select()" name="'. $id . '" id="' . $id . '" value="' . $prefill . '" onfocusout="JaxonInteractives.update_session_bucket(' . "'" . $id . "'" . ', document.getElementById(' . "'" . $id . "'" . ').value, ' . "'" .  $session_bucket . "'" . ');" />';
        }
        else
        {
            print $caption . '<br><input type="text" onfocus="this.select()" name="' . $id . '" id="' . $id . '" />';
        }

        print '<br />';
    }

    private function view_render_part_annotated_checkbox($annotation, $id, $session_bucket=null, $integrity=null)
    {
            $checked = '';
            if(isset($_SESSION["$session_bucket"]["$id"]))
            {
                if($_SESSION["$session_bucket"]["$id"]['value'] == '1')
                {
                    $checked = 'checked';
                }
            }

            $_SESSION["$session_bucket"]["$id"]['integrity'] = $integrity;
            print '<div style="display: flex; align-items: center;"><input ' . $checked . ' type="checkbox" name="' . $id . '" id="' . $id . '" onchange="JaxonInteractives.update_session_bucket(' . "'" . $id . "'" . ', document.getElementById(' . "'" . $id . "'" . ').checked, ' . "'" .  $session_bucket . "'" . ');" /><label for="' . $id . '" style="margin-top:16px;margin-left:12px;line-height: 24px;">' . $annotation . '</label>';
            
        print '<br />';
    }

    private function view_render_prefixed_meter($caption, $prefix, $id, $value="000000000")
    {

        print '
            <div id="container-' . $id . '">' . $caption . '<br>
                <div class="input-box">
                    <span class="prefix">' . $prefix . '</span>
                    <input type="text" name="' . $id . '" id="' . $id . '" value="' . $value . '" maxlength="9" onfocus="this.select()" onfocusout="JaxonInteractives.update_meter_value(' . "'" . $id . "'" . ', document.getElementById(' . "'" . $id . "'" . ').value);" />
                    &nbsp;&nbsp;<button style="background-color:darkred"  onclick="JaxonInteractives.rmv_meter(' . "'" . $id . "'" . ');"><i style="font-size:16px;color:white;" class="icon fa-trash-alt"></i></button><br>
                </div>
                <br />
            </div>';
    }


    private function view_render_prefixed_storage($caption, $id, $value=0)
    {

        print '
            <div id="container-' . $id . '">' . $caption . '<br>
                <div class="input-box" style="width:254px;">
                    <span class="prefix">Kapazit&auml;t:&nbsp;</span>
                    <input type="text" name="' . $id . '" id="' . $id . '" value="' . $value . '" maxlength="4" style="width:80px;text-align:center" onfocus="this.select()" onfocusout="JaxonInteractives.update_storage_value(' . "'" . $id . "'" . ', document.getElementById(' . "'" . $id . "'" . ').value);" />
                    <span class="prefix">kWh</span>
                    &nbsp;&nbsp;&nbsp;&nbsp;<button style="background-color:darkred"  onclick="JaxonInteractives.rmv_storage(' . "'" . $id . "'" . ');"><i style="font-size:16px;color:white;" class="icon fa-trash-alt"></i></button><br>
                </div>
                <br />
            </div>';
    }

    private function view_render_navigation()
    {
        print "<br />&nbsp;<br />";

        switch($_REQUEST['join'])
        {
            case 'individual':
            case 'company':
            case 'agriculture':
                print '<button type="button" class="defaultbtn" id="btn_step_meters" onClick="JaxonInteractives.step_generic_to_meters();">Weiter zum n&auml;chsten Schritt</button>';
                break;

            case 'meters':
                print '<button type="button" class="defaultbtn" id="btn_step_banking" onClick="JaxonInteractives.step_banking();">Weiter zum n&auml;chsten Schritt</button>';
                break;

            case 'banking':
                print '<button type="button" class="defaultbtn" id="btn_step_finish" onClick="JaxonInteractives.step_finish();">Anmeldung abschlie&szlig;en</button>';
                break;

            case 'finished':

                print '
    <script>
        function confirmStartover()
        {
          if (confirm("Ja, ich habe das Passwort notiert oder gespeichert"))
          {
                window.location.href="/";
          }
        }
    </script>
';


                print '<button type="button" class="defaultbtn" id="btn_step_startover" onClick="confirmStartover();">Zur&uuml;ck zur Hauptseite</button>';
                break;

            default:
                break;
        }

        print "<br />";

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

?>