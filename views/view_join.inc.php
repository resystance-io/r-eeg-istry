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
                <button type="button" class="mainbtn" style="" id="btn_enroll_company" onClick="location.href=' . "'" . "?join=company" . "'" . '"><img src="images/noun_company.png" alt="Join as Company" id="join_eeg" style="height: 60px; margin-left: 30px;"><br />Als Firma beitreten</button>
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
                        <h2>Als Firma beitreten</h2>
                        <p>F&uuml;r die Anmeldung als Firma ben&ouml;tigen wir ein paar Daten.</p>
                        
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
                if($meter_object['type'] == "consumers")
                {
                    print "<h4>Bezugsz&auml;hlpunkt " . $meter_object['value'] . "</h4>";
                }
                elseif ($meter_object['type'] == "suppliers")
                {
                    print "<h4>Einspeisez&auml;hlpunkt " . $meter_object['value'] . "</h4>";
                }

                print "<div class=\"form-container\">";
                print "<div style='float:left;'>";

                $prefill = (isset($_SESSION['meters']["$meter_key"]['street'])) ? $_SESSION['meters']["$meter_key"]['street'] : '';
                print ' Stra&szlig;e' . '<br><input type="text" name="id" value="' . $prefill . '" id="' . 'street' . '_' . $meter_key . '" /><br />';

                $prefill = (isset($_SESSION['meters']["$meter_key"]['city'])) ? $_SESSION['meters']["$meter_key"]['city'] : '';
                print ' Ort' . '<br><input type="text" name="id" value="' . $prefill . '"  id="' . 'city' . '_' . $meter_key  . '" /><br />';

                $prefill = (isset($_SESSION['meters']["$meter_key"]['zip'])) ? $_SESSION['meters']["$meter_key"]['zip'] : '';
                print ' PLZ' . '<br><input type="text" name="id" value="' . $prefill . '"  id="' . 'zip' . '_' . $meter_key  . '" /><br />';

                print '<button type="button" class="thinbtn" id="btn_prefill_' . $meter_key . '" onClick="JaxonInteractives.copy_address(' . "'" . $meter_key . "'" . ');">Hauptadresse &uuml;bernehmen</button>';
                print "</div>";

                print "<div style='float:left;height:100%;valign:middle'>";

                print "</div>";

                print "</div><br />";
            }
        }

    }

    private function view_render_consumption_meters()
    {
        print "<h3>Z&auml;hlpunkte (Bezug)</h3>";
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
        print "<h3>Z&auml;hlpunkte (Einspeisung)</h3>";
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
        print "<h3>Vorhandene Energiespeicher</h3>";
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

            print $caption . '<br><input type="text" name="id" id="' . $id . '" value="' . $prefill . '" onfocusout="JaxonInteractives.update_session_bucket(' . "'" . $id . "'" . ', document.getElementById(' . "'" . $id . "'" . ').value, ' . "'" .  $session_bucket . "'" . ');" />';
        }
        else
        {
            print $caption . '<br><input type="text" name="id" id="' . $id . '" />';
        }

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
        switch($_REQUEST['join'])
        {
            case 'personal':
            case 'company':
            case 'agriculture':
                print "<br />";
                print '<button type="button" class="defaultbtn" id="btn_step_meters" onClick="JaxonInteractives.step_generic_to_meters();">Weiter zum n&auml;chsten Schritt</button>';
                break;

            case 'meters':
                print "<br />";
                print '<button type="button" class="defaultbtn" id="btn_step_banking" onClick="JaxonInteractives.step_banking();">Weiter zum n&auml;chsten Schritt</button>';
                break;

            default:
                break;
        }

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