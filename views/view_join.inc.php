<?php

function view_render()
{

    switch($_REQUEST['join'])
    {
        case "individual":
            view_render_individual();
            break;

        case "agriculture":
            view_render_agriculture();
            break;

        case "company":
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

    view_render_part_captioned_inputfield("Firmenwortlaut", "company", "company");
    view_render_part_captioned_inputfield("UID", "uid", "uid");
    view_render_part_captioned_inputfield("Postleitzahl", "zip", "zip");
    view_render_part_captioned_inputfield("Ort", "city", "city");
    view_render_part_captioned_inputfield("Stra&szlig;e", "street", "street");
    view_render_part_captioned_inputfield("Hausnummer", "number", "number");
    view_render_part_captioned_inputfield("Telefonnummer", "phone", "phone");

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

    view_render_part_captioned_inputfield("Vorname", "firstname", "firstname");
    view_render_part_captioned_inputfield("Nachname", "lastname", "lastname");
    view_render_part_captioned_inputfield("Stra&szlig;e", "street", "street");
    view_render_part_captioned_inputfield("Hausnummer", "number", "number");
    view_render_part_captioned_inputfield("Postleitzahl", "zip", "zip");
    view_render_part_captioned_inputfield("Ort", "city", "city");
    view_render_part_captioned_inputfield("Geburtsdatum", "birthdate", "birthdate");
    view_render_part_captioned_inputfield("Telefonnummer", "phone", "phone");
    view_render_part_captioned_inputfield("E-Mail Adresse", "email", "email");

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

    view_render_part_captioned_inputfield("Vorname", "firstname", "firstname");
    view_render_part_captioned_inputfield("Nachname", "lastname", "lastname");
    view_render_part_captioned_inputfield("Stra&szlig;e", "street", "street");
    view_render_part_captioned_inputfield("Hausnummer", "number", "number");
    view_render_part_captioned_inputfield("Postleitzahl", "zip", "zip");
    view_render_part_captioned_inputfield("Ort", "city", "city");
    view_render_part_captioned_inputfield("Geburtsdatum", "birthdate", "birthdate");
    view_render_part_captioned_inputfield("Telefonnummer", "phone", "phone");
    view_render_part_captioned_inputfield("E-Mail Adresse", "email", "email");

    print "</div><br />";

    view_render_consumption_meters();

    print "<br />";

    view_render_supply_meters();
}

function view_render_consumption_meters()
{
    print "<h3>Z&auml;hlpunkte (Bezug)</h3>";
    print "<div class=\"form-container\">";

    view_render_part_captioned_prefixed_inputfield("Z&auml;hlpunktnummer (letzte 9 Stellen)", "AT003000000000000000000003", "consume0", "consume0");

    print '<br /><i style="font-size:16px" class="icon fa-plus-square"></i><span class="label" style="font-weight:normal;font-size:16px">&nbsp; Einen weiteren Z&auml;hlpunkt hinzuf&uuml;gen</span>';
    print "</div>";
}

function view_render_supply_meters()
{
    print "<h3>Z&auml;hlpunkte (Einspeisung)</h3>";
    print "<div class=\"form-container\">";

    view_render_part_captioned_prefixed_inputfield("Z&auml;hlpunktnummer (letzte 9 Stellen)", "AT003000000000000000000003", "supply0", "supply0");

    print '<br /><i style="font-size:16px" class="icon fa-plus-square"></i><span class="label" style="font-weight:normal;font-size:16px">&nbsp; Einen weiteren Z&auml;hlpunkt hinzuf&uuml;gen</span>';
    print "</div>";
}

function view_render_part_captioned_inputfield($caption, $name, $id)
{
    print "$caption<br><input type=\"text\" name=\"$name\" id=\"$id\" /><br />";
}

function view_render_part_captioned_prefixed_inputfield($caption, $prefix, $name, $id)
{
    //print "<input type=\"text\" name=\"$name\" id=\"$id\" class=\"prefix\" /><br />";
    print $caption . '<br>
        <div class="input-box">
            <span class="prefix">' . $prefix . '</span>
            <input type="text" name="' . $name . '" id="' . $id . '" value="000000000" maxlength="9" onfocus="this.select()" />
        </div>';
}

?>