<?php

function view_render()
{
    print "JOIN";

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
            view_render_switch_dialogue();
            break;
    }
}


function view_render_switch_dialogue()
{
    print '
        <div class="canvas_center">
            <button type="button" class="mainbtn" style="" id="btn_enroll_company" onClick="location.href=' . "'" . "?join=company" . "'" . '"><img src="img/noun_company.png" alt="Join as Company" id="join_eeg" style="width: 70px; margin-left: 30px;">Als Firma<br />beitreten</button>&nbsp;&nbsp;
            <button type="button" class="mainbtn" style="" id="btn_enroll_individual" onClick="location.href=' . "'" . "?join=individual" . "'" . '"><img src="img/noun_individual.png" alt="Join as Individual" id="lookup_eeg" style="width: 20px; margin-left: 30px;">Als Privatperson<br />beitreten</button>&nbsp;&nbsp;
            <button type="button" class="mainbtn" style="" id="btn_enroll_agriculture" onClick="location.href=' . "'" . "?join=agriculture" . "'" . '"><img src="img/noun_agriculture.png" alt="Join as Agriculture" id="lookup_eeg" style="width: 70px; margin-left: 30px;">Als<br />Landwirtschaft<br />beitreten</button>
        </div>
    ';
}

function view_render_company()
{
    view_render_part_inputfield("Firmenwortlaut", "company", "company");
    view_render_part_inputfield("UID", "uid", "uid");
    view_render_part_inputfield("Postleitzahl", "zip", "zip");
    view_render_part_inputfield("Ort", "city", "city");
    view_render_part_inputfield("Stra&szlig;e", "street", "street");
    view_render_part_inputfield("Hausnummer", "number", "number");
    view_render_part_inputfield("Telefonnummer", "phone", "phone");
}

function view_render_individual()
{

}

function view_render_agriculture()
{

}


function view_render_part_inputfield($caption, $name, $id)
{
    print "$caption<br><input type=\"text\" name=\"$name\" id=\"$id\" /><br />&nbsp;<br />";
}

?>