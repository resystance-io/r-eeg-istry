<?php

function view_render()
{

    switch($_REQUEST['join'])
    {
        case "individual":
            $_SESSION['generic_information']['join_type'] = "individual";
            view_render_individual();
            break;

        case "agriculture":
            $_SESSION['generic_information']['join_type'] = "agriculture";
            view_render_agriculture();
            break;

        case "company":
            $_SESSION['generic_information']['join_type'] = "company";
            view_render_company();
            break;

        default:

            print '
                <header id="header">
                    <h1>Erneuerbare Energiegemeinschaft VIERE</h1>
                    <p>Sch&ouml;n dass du dich f&uuml;r die Mitgliedschaft in unserer EEG interessierst.<br />Wir freuen uns &uuml;ber jedes neue Mitglied.<br /></p>
    
                    <p style="color:white">Bitte w&auml;hle die passende Beitrittsform:</p>
                </header>
            ';
        
            view_render_switch_dialogue();
            break;
    }
}


function view_render_switch_dialogue()
{
    print '
        <div class="button_container">
            <button type="button" class="mainbtn" style="" id="btn_enroll_company" onClick="location.href=' . "'" . "?join=company" . "'" . '"><img src="images/noun_company.png" alt="Join as Company" id="join_eeg" style="height: 60px; margin-left: 30px;"><br />Als Firma beitreten</button>
            <button type="button" class="mainbtn" style="" id="btn_enroll_individual" onClick="location.href=' . "'" . "?join=individual" . "'" . '"><img src="images/noun_individual.png" alt="Join as Individual" id="lookup_eeg" style="height: 60px; margin-left: 30px;"><br />Als Privatperson beitreten</button>
            <button type="button" class="mainbtn" style="" id="btn_enroll_agriculture" onClick="location.href=' . "'" . "?join=agriculture" . "'" . '"><img src="images/noun_agriculture.png" alt="Join as Agriculture" id="lookup_eeg" style="height: 60px; margin-left: 30px;"><br />Als Landwirtschaft beitreten</button>
        </div>
    ';
}

function view_render_company()
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

    view_render_part_captioned_inputfield("Firmenwortlaut", "company", "generic_information");
    view_render_part_captioned_inputfield("UID", "uid", "generic_information");
    view_render_part_captioned_inputfield("Postleitzahl", "zip", "generic_information");
    view_render_part_captioned_inputfield("Ort", "city", "generic_information");
    view_render_part_captioned_inputfield("Stra&szlig;e", "street", "generic_information");
    view_render_part_captioned_inputfield("Hausnummer", "number", "generic_information");
    view_render_part_captioned_inputfield("Telefonnummer", "phone", "generic_information");

    print "</div><br />";

    view_render_consumption_meters();

    print "<br />";

    view_render_supply_meters();
}

function view_render_individual()
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

    view_render_part_captioned_inputfield("Vorname", "firstname", "generic_information");
    view_render_part_captioned_inputfield("Nachname", "lastname", "generic_information");
    view_render_part_captioned_inputfield("Stra&szlig;e", "street", "generic_information");
    view_render_part_captioned_inputfield("Hausnummer", "number", "generic_information");
    view_render_part_captioned_inputfield("Postleitzahl", "zip", "generic_information");
    view_render_part_captioned_inputfield("Ort", "city", "generic_information");
    view_render_part_captioned_inputfield("Geburtsdatum", "birthdate", "generic_information");
    view_render_part_captioned_inputfield("Telefonnummer", "phone", "generic_information");
    view_render_part_captioned_inputfield("E-Mail Adresse", "email", "generic_information");

    print "</div><br />";

    view_render_consumption_meters();

    print "<br />";

    view_render_supply_meters();
}


function view_render_agriculture()
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

    view_render_part_captioned_inputfield("Vorname", "firstname", "generic_information");
    view_render_part_captioned_inputfield("Nachname", "lastname", "generic_information");
    view_render_part_captioned_inputfield("Stra&szlig;e", "street", "generic_information");
    view_render_part_captioned_inputfield("Hausnummer", "number", "generic_information");
    view_render_part_captioned_inputfield("Postleitzahl", "zip", "generic_information");
    view_render_part_captioned_inputfield("Ort", "city", "generic_information");
    view_render_part_captioned_inputfield("Geburtsdatum", "birthdate", "generic_information");
    view_render_part_captioned_inputfield("Telefonnummer", "phone", "generic_information");
    view_render_part_captioned_inputfield("E-Mail Adresse", "email", "generic_information");

    print "</div><br />";

    view_render_consumption_meters();

    print "<br />";

    view_render_supply_meters();
}

function view_render_consumption_meters()
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
                view_render_part_captioned_prefixed_inputfield("Z&auml;hlpunktnummer (letzte 9 Stellen)", "AT003000000000000000000000", $meter_key, $meter_object['value']);
                $consumer_count++;
            }
        }

        print "<div id='end_of_consumers'></div>";
    }

    if(!isset($_SESSION['meters']) || $consumer_count == 0)
    {
        $id = generate_uuid4();
        view_render_part_captioned_prefixed_inputfield("Z&auml;hlpunktnummer (letzte 9 Stellen)", "AT003000000000000000000000", $id);
    }

    print '<br /><i style="font-size:16px;cursor:pointer;" class="icon fa-plus-square" onclick="JaxonInteractives.add_meter(' . "'consumers'" . ',' . "'AT003000000000000000000000'" . ');"></i><span class="label" style="font-weight:normal;font-size:16px;cursor:pointer;" onclick="JaxonInteractives.add_meter(' . "'consumers'" . ',' . "'AT003000000000000000000000'" . ');">&nbsp; Einen Bezugsz&auml;hlpunkt hinzuf&uuml;gen</span>';
    print "</div>";
}

function view_render_supply_meters()
{
    print "<h3>Z&auml;hlpunkte (Einspeisung)</h3>";
    print "<div class=\"form-container\">";

    if(isset($_SESSION['meters']))
    {
        foreach($_SESSION['meters'] as $meter_key => $meter_object)
        {
            if($meter_object['type'] == "suppliers")
            {
                view_render_part_captioned_prefixed_inputfield("Z&auml;hlpunktnummer (letzte 9 Stellen)", "AT003000000000000000000003", $meter_key, $meter_object['value']);
            }
        }

        print "<div id='end_of_suppliers'></div>";
    }

    print '<br /><i style="font-size:16px;cursor:pointer;" class="icon fa-plus-square" onclick="JaxonInteractives.add_meter(' . "'suppliers'" . ',' . "'AT003000000000000000000003'" . ');"></i><span class="label" style="font-weight:normal;font-size:16px;cursor:pointer;" onclick="JaxonInteractives.add_meter(' . "'suppliers'" . ',' . "'AT003000000000000000000003'" . ');">&nbsp; Einen Einspeisez&auml;hlpunkt hinzuf&uuml;gen</span>';
    print "</div>";
}

function view_render_part_captioned_inputfield($caption, $id, $session_bucket = null)
{
    if($session_bucket != null)
    {
        if(isset($_SESSION["$session_bucket"]["$id"]))
        {
            $prefill = $_SESSION["$session_bucket"]["$id"];
        }
        else
        {
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

function view_render_part_captioned_prefixed_inputfield($caption, $prefix, $id, $value="000000000")
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

function generate_uuid4($data = null)
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

?>