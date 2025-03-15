<?php

include_once('view_join_base.inc.php');

class VIEW_JOIN extends VIEW_JOIN_BASE
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

        print '
                    <header id="header">
                        <h1>Erneuerbare Energiegemeinschaft VIERE</h1>
                    </header>
        ';

        print "";

        if(isset($_REQUEST['join']))
        {
            if(isset($_REQUEST['step']) && $_REQUEST['step'] > 0)
            {
                // check if the requirements for the previous steps were satisfied:
                if($_REQUEST['step'] > ($_SESSION['latestsave'] + 1))
                {
                    // this step is ahead of its time. Let's go back to the next feasible one
                    $feasible_step = $_SESSION['latestsave'] + 1;
                    print '<script>window.location.href="/?join=' . $_SESSION['generic_information']['join_type'] . '&step=' . $feasible_step . '";</script>';
                    return false;
                }
            }

            switch ($_REQUEST['join'])
            {
                case "individual":
                    $_SESSION['generic_information']['join_type'] = "individual";
                    if(isset($_REQUEST['step']))
                    {
                        $this->view_render_step();
                    }
                    break;

                case "agriculture":
                    $_SESSION['generic_information']['join_type'] = "agriculture";
                    if(isset($_REQUEST['step']))
                    {
                        $this->view_render_step();
                    }
                    break;

                case "company":
                    $_SESSION['generic_information']['join_type'] = "company";
                    if(isset($_REQUEST['step']))
                    {
                        $this->view_render_step();
                    }
                    break;

                default:
                    print '
                        <header id="header">
                            <p>Sch&ouml;n dass Du dich f&uuml;r die Mitgliedschaft in unserer EEG interessierst.<br />Wir freuen uns &uuml;ber jedes neue Mitglied!<br /></p>
            
                            <p style="color:white">Bitte w&auml;hle die passende Beitrittsform:</p>
                        </header>
                    ';

                    $this->view_render_switch_dialogue();
                    break;
            }
        }
    }


    private function view_render_switch_dialogue()
    {
        print '
            <div class="button_container">
                <button type="button" class="mainbtn" style="" id="btn_enroll_company" onClick="location.href=' . "'" . "?join=company&step=0" . "'" . '"><img src="images/noun_company.png" alt="Join as Company" id="join_eeg" style="height: 60px; margin-left: 30px;"><br />Als Unternehmen beitreten</button>
                <button type="button" class="mainbtn" style="" id="btn_enroll_individual" onClick="location.href=' . "'" . "?join=individual&step=0" . "'" . '"><img src="images/noun_individual.png" alt="Join as Individual" id="lookup_eeg" style="height: 60px; margin-left: 30px;"><br />Als Privatperson beitreten</button>
                <button type="button" class="mainbtn" style="" id="btn_enroll_agriculture" onClick="location.href=' . "'" . "?join=agriculture&step=0" . "'" . '"><img src="images/noun_agriculture.png" alt="Join as Agriculture" id="lookup_eeg" style="height: 60px; margin-left: 30px;"><br />Als Landwirtschaft beitreten</button>
            </div>
        ';
    }

    private function view_render_step()
    {
        if(isset($_REQUEST['step']))
        {
            if($_REQUEST['step'] == count($this->config->user['JOIN_LAYOUT']))
            {
                $progress_bar_width = 640;
                $previous_step = 0;
            }
            else
            {
                $progress_bar_width = 520;
                $previous_step = $_REQUEST['step'] - 1;
            }

            $progress_fill_width = ceil(($progress_bar_width / count($this->config->user['JOIN_LAYOUT'])) * $_REQUEST['step']);
            $progress_percent = ceil((100 / count($this->config->user['JOIN_LAYOUT'])) * $_REQUEST['step']);

            if($progress_percent == 0)
            {
                $progress_bar_text = $progress_percent . '%';
                $progress_text = '';
            }
            else
            {
                $progress_bar_text = '';
                $progress_text = $progress_percent . '%';
            }

            if(isset($this->config->user['JOIN_LAYOUT'][$_REQUEST['step']]))
            {
                foreach ($this->config->user['JOIN_LAYOUT'][$_REQUEST['step']] as $panel)
                {
                    switch ($panel)
                    {
                        case 'generic':
                            if($_SESSION['generic_information']['join_type'] == 'company')
                            {
                                $this->view_render_company();
                            }
                            elseif($_SESSION['generic_information']['join_type'] == 'individual')
                            {
                                $this->view_render_individual();

                            }
                            elseif($_SESSION['generic_information']['join_type'] == 'agriculture')
                            {
                                $this->view_render_agriculture();
                            }
                            break;

                        case 'consumption':
                            $this->view_render_consumption_meters();
                            break;

                        case 'supply':
                            $this->view_render_supply_meters();
                            break;

                        case 'meters':
                            $this->view_render_meter_details();
                            break;

                        case 'storage':
                            $this->view_render_energy_storage();
                            break;

                        case 'banking':
                            $this->view_render_banking_details();
                            break;

                        case 'approvals':
                            $this->view_render_approvals();
                            break;
                    }
                }

                print '<br />';

                print '<div style="width:100%">';
                print '<div style="float:left;padding-right:20px;"><button type="button" class="defaultbtn" style="float:left;max-width:100px" onClick="JaxonInteractives.step_back(\'' . $_REQUEST['step'] . '\');"><i class="fa fa-arrow-left"></i></button></div>';
                print '<div style="float:left;padding-right:20px;"><button type="button" class="defaultbtn" style="float:left" onClick="JaxonInteractives.next_step(\'' . $_REQUEST['step'] . '\');">Weiter zum n&auml;chsten Schritt</button></div>';
                print '<div style="float:left;padding-top:20px;text-align:center">';
                print '    <div style="width:' . $progress_bar_width . 'px;background-color:rgba(255, 255, 255, 0.4);">';
                print '        <div style="width:' . $progress_fill_width . 'px;background-color:lightseagreen;text-align:center">' . $progress_text . '</div>' . $progress_bar_text;
                print '    </div>';
                print '</div>';
                print '<div style="clear:both"></div>';
                print '</div>';
            }
            else
            {
                if($_REQUEST['step'] == count($this->config->user['JOIN_LAYOUT']))
                {
                    if($this->view_render_finished() === true)
                    {
                        $mail_template = file_get_contents('assets/templates/mail_welcome.html');
                        $mail_template = str_replace('{%FIRSTNAME%}', $_SESSION['generic_information']['firstname']['value'], $mail_template);
                        $mail_template = str_replace('{%LASTNAME%}', $_SESSION['generic_information']['lastname']['value'], $mail_template);
                        $mail_template = str_replace('{%USERNAME%}', $_SESSION['generic_information']['email']['value'], $mail_template);
                        $mail_template = str_replace('{%PASSWORD%}', $_SESSION['mnemonic'], $mail_template);

                        $this->object_broker->instance['email']->subject = "Deine Anmeldung an der EEG " . $this->config->user['EEG_NICENAME'];
                        $this->object_broker->instance['email']->AddRecipient($_SESSION['generic_information']['email']['value']);
                        $this->object_broker->instance['email']->messageHTML = $mail_template;

                        if ($this->object_broker->instance['email']->Send('smtp'))
                        {
                            $welcome_mail_sent = true;
                        }
                        else
                        {
                            $welcome_mail_sent = false;
                        }

                        if($welcome_mail_sent === false)
                        {
                            print '
                                <script>
                                    function confirmStartover()
                                    {
                                      if (confirm("Wir konnten Deine Zugangsdaten leider nicht an deine eMail Adresse senden. Bitte stelle sicher, dass du die eingeblendeten Zugangsdaten erfolgreich notiert hast."))
                                      {
                                            window.location.href="/";
                                      }
                                    }
                                </script>
                            ';
                        }
                        else
                        {
                            print '
                                <script>
                                    function confirmStartover()
                                    {
                                        window.location.href="/";
                                    }
                                </script>
                            ';
                        }
                    }

                    print '<br />';
                    print '<div style="clear:both"></div>';
                    print '<div style="width:100%">';
                    print '    <div style="float:left;padding-right:20px;"><button type="button" class="defaultbtn" id="btn_step_startover" onClick="confirmStartover();">Zur&uuml;ck zur Hauptseite</button></div>';
                    print '    <div style="float:left;padding-top:20px;text-align:center">';
                    print '        <div style="width:' . $progress_bar_width . 'px;background-color:rgba(255, 255, 255, 0.4);">';
                    print '             <div style="width:' . $progress_fill_width . 'px;background-color:lightseagreen;text-align:center">' . $progress_text . '</div>' . $progress_bar_text;
                    print '        </div>';
                    print '    </div>';
                    print '<div style="clear:both"></div>';
                    print '</div>';
                }
            }
        }
    }
    private function view_render_company()
    {
        print '
                    <br />
                    <header id="header">
                        <h2>Als Unternehmen beitreten</h2>
                        <p>F&uuml;r die Anmeldung als Unternehmen ben&ouml;tigen wir ein paar Daten.</p>
                    </header>
        ';

        print "<h3>Allgemeine Daten</h3>";
        print "<div class=\"form-container\" style=\"min-width:960px; width:960px;\">";

        $this->view_render_part_captioned_inputfield("Firmenwortlaut", "company", "generic_information", "required", "width:440px;float:left");
        $this->view_render_part_captioned_inputfield("UID", "uid", "generic_information", null, "width:440px;float:left");
        print "<div style=\"clear:both\"></div>";
        $this->view_render_part_captioned_inputfield("Stra&szlig;e", "street", "generic_information", "required", "width:760px;float:left;");
        $this->view_render_part_captioned_inputfield("Nr.", "number", "generic_information", "required", "width:120px;float:left;");
        print "<div style=\"clear:both\"></div>";
        $this->view_render_part_captioned_inputfield("PLZ", "zip", "generic_information", "numbers", "width:120px;float:left;");
        $this->view_render_part_captioned_inputfield("Ort", "city", "generic_information", "required", "width:760px;float:left;");
        print "<div style=\"clear:both\"></div>";
        $this->view_render_part_captioned_inputfield("Telefonnummer", "phone", "generic_information", "phone", "width:440px;float:left;");
        $this->view_render_part_captioned_inputfield("E-Mail-Adresse", "email", "generic_information", "email", "width:440px;float:left;");
        print "<div style=\"clear:both\"></div>";

        print "</div>";

    }

    private function view_render_individual()
    {

        print '
                    <br />
                    <header id="header">
                        <h2>Als Privatperson beitreten</h2>
                        <p>F&uuml;r die Anmeldung als Privatperson ben&ouml;tigen wir ein paar Daten.</p>
                    </header>
        ';

        print "<h3>Allgemeine Daten</h3>";
        print "<div class=\"form-container\" style=\"min-width:960px; width:960px;\">";

        $this->view_render_part_captioned_select("Titel", "title", $this->config->user['preNameTitles'], "generic_information", null, "width:190px;float:left;");
        $this->view_render_part_captioned_inputfield("Vorname", "firstname", "generic_information", "required", "width:210px;float:left;");
        $this->view_render_part_captioned_inputfield("Nachname", "lastname", "generic_information", "required", "width:300px;float:left;");
        $this->view_render_part_captioned_select("Postnomen", "postnomen", $this->config->user['postNameTitles'], "generic_information", null, "width:160px;float:left;");
        print "<div style=\"clear:both\"></div>";
        $this->view_render_part_captioned_inputfield("Stra&szlig;e", "street", "generic_information", "required", "width:700px;float:left;");
        $this->view_render_part_captioned_inputfield("Nr.", "number", "generic_information", "required", "width:160px;float:left;");
        print "<div style=\"clear:both\"></div>";
        $this->view_render_part_captioned_inputfield("PLZ", "zip", "generic_information", "numbers", "width:200px;float:left;");
        $this->view_render_part_captioned_inputfield("Ort", "city", "generic_information", "required", "width:660px;float:left;");
        print "<div style=\"clear:both\"></div>";
        $identity_type_arr = ['passport' => 'Reisepass', 'idcard' => 'Personalausweis', 'driverslicense' => 'F&uuml;hrerschein'];
        $this->view_render_part_captioned_select("Identit&auml;tsnachweis", "idprovider", $identity_type_arr, "generic_information", "required", "width:200px;float:left;");
        $this->view_render_part_captioned_inputfield("Ausweisnummer", "idvalue", "generic_information", "required", "width:460px;float:left;");
        $this->view_render_part_captioned_inputfield("Geburtsdatum", "birthdate", "generic_information", "required", "width:200px;float:left;");
        print "<div style=\"clear:both\"></div>";
        $this->view_render_part_captioned_inputfield("Telefonnummer", "phone", "generic_information", "phone", "width:430px;float:left;");
        $this->view_render_part_captioned_inputfield("E-Mail-Adresse", "email", "generic_information", "email", "width:430px;float:left;");
        print "<div style=\"clear:both\"></div>";

        print "</div>";

    }


    private function view_render_agriculture()
    {

        print '
                    <br />
                    <header id="header">
                        <h2>Als Landwirtschaft beitreten</h2>
                        <p>F&uuml;r die Anmeldung als Landwirtschaft ben&ouml;tigen wir ein paar Informationen.</p>
                    </header>
        ';

        print "<h3>Allgemeine Daten</h3>";
        print "<div class=\"form-container\" style=\"min-width:960px; width:960px;\">";

        $this->view_render_part_captioned_select("Titel", "title", $this->config->user['preNameTitles'], "generic_information", null, "width:190px;float:left;");
        $this->view_render_part_captioned_inputfield("Vorname", "firstname", "generic_information", "required", "width:210px;float:left;");
        $this->view_render_part_captioned_inputfield("Nachname", "lastname", "generic_information", "required", "width:300px;float:left;");
        $this->view_render_part_captioned_select("Postnomen", "postnomen", $this->config->user['postNameTitles'], "generic_information", null, "width:160px;float:left;");
        print "<div style=\"clear:both\"></div>";
        $this->view_render_part_captioned_inputfield("Stra&szlig;e", "street", "generic_information", "required", "width:700px;float:left;");
        $this->view_render_part_captioned_inputfield("Nr", "number", "generic_information", "required", "width:160px;float:left;");
        print "<div style=\"clear:both\"></div>";
        $this->view_render_part_captioned_inputfield("PLZ", "zip", "generic_information", "numbers", "width:200px;float:left;");
        $this->view_render_part_captioned_inputfield("Ort", "city", "generic_information", "required", "width:660px;float:left;");
        print "<div style=\"clear:both\"></div>";
        $identity_type_arr = ['passport' => 'Reisepass', 'idcard' => 'Personalausweis', 'driverslicense' => 'F&uuml;hrerschein'];
        $this->view_render_part_captioned_select("Identit&auml;tsnachweis", "idprovider", $identity_type_arr, "generic_information", "required", "width:200px;float:left;");
        $this->view_render_part_captioned_inputfield("Ausweisnummer", "idvalue", "generic_information", "required", "width:460px;float:left;");
        $this->view_render_part_captioned_inputfield("Geburtsdatum", "birthdate", "generic_information", "required", "width:200px;float:left;");
        print "<div style=\"clear:both\"></div>";
        $this->view_render_part_captioned_inputfield("Telefonnummer", "phone", "generic_information", "phone", "width:430px;float:left;");
        $this->view_render_part_captioned_inputfield("E-Mail-Adresse", "email", "generic_information", "email", "width:430px;float:left;");
        print "<div style=\"clear:both\"></div>";

        print "</div>";

    }

    private function view_render_meter_details()
    {

        print '
                    <br />
                    <header id="header">
                        <h2>Erg&auml;nzende Angaben</h2>
                        <p>Bitte erg&auml;nze die Informationen zu den von dir angegebenen Z&auml;hlpunkten!</p>
                    </header>
        ';

        if(isset($_SESSION['meters']))
        {
            foreach ($_SESSION['meters'] as $meter_key => $meter_object)
            {

                print "<div class=\"form-container\" style=\"min-width:960px; width:960px;\">";

                print "
                    <div style='float:left;margin-right:80px;'>
                ";

                if ($meter_object['type'] == "consumers")
                {
                    print "<h3>Adresse des Bezugsz&auml;hlpunkts " . $meter_object['value'] . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</h3>";
                }
                elseif ($meter_object['type'] == "suppliers")
                {
                    print "<h3>Adresse des Einspeisez&auml;hlpunkts " . $meter_object['value'] . "</h3>";
                }

                $this->view_render_meter_detail_inputfield($meter_key, "Stra&szlig;e", 'street', 'required', "width:320px;float:left;");

                $this->view_render_meter_detail_inputfield($meter_key, "Nummer", 'number', 'required', "width:150px;float:left;");

                print "<div style=\"clear:both\"></div>";

                $this->view_render_meter_detail_inputfield($meter_key, "PLZ", 'zip', 'numbers', "width:150px;float:left;");

                $this->view_render_meter_detail_inputfield($meter_key, "Ort", 'city', 'required', "width:320px;float:left;");

                print "<div style=\"clear:both\"></div>";


                print '<br /><button type="button" class="thinbtn" id="btn_prefill_' . $meter_key . '" onClick="JaxonInteractives.copy_address(' . "'" . $meter_key . "'" . ');">Von Hauptadresse kopieren</button>';
                print "</div>";

                print "<div style='float:left;height:100%;valign:middle'>";

                $this->view_render_meter_detail_explained_inputfield($meter_key, "Teilnahmefaktor", 'participation', 'percent', null, 100, "Prozent", "Ein Z&auml;hlpunkt kann an bis zu 5 EEGs teilnehmen.<br />&nbsp;<br />Der Teilnahmefaktor legt fest, zu wieviel Prozent dieser Z&auml;hlpunkt an <b style=\"color:black\">dieser EEG</b> teilnimmt.");

                if ($meter_object['type'] == "suppliers")
                {
                    print "<br />";

                    $this->view_render_meter_detail_explained_inputfield($meter_key, "Maximale Leistung", 'power', 'decimal', null, 10, "&nbsp;&nbsp;&nbsp;&nbsp;kWp");
                }

                print "</div>";

                print "</div><br />";
            }
        }

    }

    private function view_render_banking_details()
    {
        print '
                    <br />
                    <header id="header">
                        <h2>Zahlungsinformationen</h2>
                        <p>Noch ein paar Infos zum Konto, bald ist es geschafft...</p>
                    </header>
        ';

        print "<h3>Kontoinformationen</h3>";
        print "<div class=\"form-container\" style=\"min-width:960px; width:960px;\">";

        $this->view_render_part_captioned_inputfield("Name d. Kontoinhabers", "banking_name", "generic_information", "required");
        $this->view_render_part_captioned_inputfield("IBAN", "banking_iban", "generic_information", "iban");
        $this->view_render_part_annotated_checkbox("Hiermit best&auml;tige ich die Richtigkeit der angegebenen Kontoinformationen<br />und erm&auml;chtige VIERE zum Bankeinzug im Rahmen der Leistungsabrechnung <br /> Creditor ID: AT57ZZZ00000073600.", "banking_consent", "generic_information", "booltrue");

        print "</div></div><br />";

    }

    private function view_render_approvals()
    {
        print '
                    <br />
                    <header id="header">
                        <h2>Best&auml;tigungen &amp; Freigaben</h2>
                        <p>Aus rechtlichen Gr&uuml;nden ben&ouml;tigen wir noch deine formale Zustimmung in den folgenden Bereichen:</p>
                    </header>
        ';

        print "<h3>Statuten</h3>";
        print "<div class=\"form-container\" style=\"min-width:960px; width:960px;\">";
        $this->view_render_part_annotated_checkbox(
            "Ich best&auml;tige die Kenntnisnahme der &nbsp; <a style=\"color:lightblue;text-decoration:none;\" href=\"/download/statuten.pdf\" target=\"_blank\"><span class=\"fa fa-download\"></span> Statuten der EEG VIERE</a>",
            "bylaws_consent", "generic_information", "booltrue");
        print "</div><br />";

        print "<h3>AGBs</h3>";
        print "<div class=\"form-container\" style=\"min-width:960px; width:960px;\">";
        $this->view_render_part_annotated_checkbox(
            "Ich best&auml;tige die Kenntnisnahme der  &nbsp; <a style=\"color:lightblue;text-decoration:none;\" href=\"/download/agb.pdf\" target=\"_blank\"><span class=\"fa fa-download\"></span> Allgemeinen Gesch&auml;ftsbedingungen der EEG VIERE</a>",
            "tos_consent", "generic_information", "booltrue");
        print "</div><br />";

        print "<h3>Datenschutz</h3>";
        print "<div class=\"form-container\" style=\"min-width:960px; width:960px;\">";
        $this->view_render_part_annotated_checkbox(
            "Ich best&auml;tige die Kenntnisnahme der  &nbsp; <a style=\"color:lightblue;text-decoration:none;\" href=\"/download/dsgvo.pdf\" target=\"_blank\"><span class=\"fa fa-download\"></span> Datenschutzerkl&auml;rung der EEG VIERE</a>",
            "gdpr_consent", "generic_information", "booltrue");
        print "</div><br />";

        print "<h3>Netzbetreibervollmacht</h3>";
        print "<div class=\"form-container\" style=\"min-width:960px; width:960px;\">";
        $this->view_render_part_annotated_checkbox(
            "Ich erteile der EEG VIERE f&uuml;r die Dauer der Mitgliedschaft zeitlich unbegrenzt die Vollmacht,<br />
                        in meinem Namen s&auml;mtliche Schritte und Abstimmungen mit dem zust&auml;ndigen Netzbetreiber<br />
                        (Netz O&Ouml;) durchzuf&uuml;hren, die zur vollst&auml;ndigen Aktivierung und Deaktivierung der angef&uuml;hrten<br />
                        Z&auml;hlpunkte in der EEG VIERE notwendig sind.<br />
                        &nbsp;<br />
                        Dies betrifft insbesondere auch die Registrierung und Nutzung des E-Service-Portals der Netz O&Ouml;.",
            "network_consent", "generic_information", "booltrue");

        print "</div></div><br />";

    }

    private function view_render_finished()
    {
        $_SESSION['finished'] = true;

        print "&nbsp;<br />&nbsp;<br />";
        print "<h2>Vielen Dank f&uuml;r Deine Anmeldung!</h2>";
        print "&nbsp;<br>Deine Daten werden schnellstm&ouml;glich &uuml;berpr&uuml;ft und in unser System &uuml;bernommen.<br />Sobald das Datum feststeht, ab dem Du eneuerbare Energie aus unserer Gemeinschaft beziehen wirst, kontaktieren wir Dich umgehend. <br /><br />Solltest Du Fragen haben, stehen wir Dir selbstverst&auml;ndlich gerne zur Verf&uuml;gung.<br />";

        print "<br />&nbsp;<br />&nbsp;<br />";
        print "<h3>Dein Passwort:</h3>";
        print "<div class=\"form-container\" style=\"min-width:960px; width:960px;\">";
        print "<br /><h2>" . $_SESSION['mnemonic'] . "</h2>";
        print "</div>&nbsp;<br />";
        print "Du kannst dieses Passwort nutzen, um jederzeit den Bearbeitungsfortschritt Deines Antrages einzusehen<br />und natürlich um Deine Daten zu &auml;ndern.<br />&nbsp;<br /><b>Bitte bewahre es gut auf!</b>";
        print "&nbsp;<br />";

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
                    if (isset($_SESSION['generic_information']['title']['value'])) $registration_array['title'] = $_SESSION['generic_information']['title']['value'];
                    if (isset($_SESSION['generic_information']['postnomen']['value'])) $registration_array['postnomen'] = $_SESSION['generic_information']['postnomen']['value'];
                    if (isset($_SESSION['generic_information']['firstname']['value'])) $registration_array['firstname'] = $_SESSION['generic_information']['firstname']['value'];
                    if (isset($_SESSION['generic_information']['lastname']['value'])) $registration_array['lastname'] = $_SESSION['generic_information']['lastname']['value'];
                    if (isset($_SESSION['generic_information']['birthdate']['value'])) $registration_array['birthdate'] = $_SESSION['generic_information']['birthdate']['value'];
                    if (isset($_SESSION['generic_information']['idprovider']['value'])) $registration_array['idprovider'] = $_SESSION['generic_information']['idprovider']['value'];
                    if (isset($_SESSION['generic_information']['idvalue']['value'])) $registration_array['idvalue'] = $_SESSION['generic_information']['idvalue']['value'];
                    break;

                case 'individual':
                    if (isset($_SESSION['generic_information']['title']['value'])) $registration_array['title'] = $_SESSION['generic_information']['title']['value'];
                    if (isset($_SESSION['generic_information']['postnomen']['value'])) $registration_array['postnomen'] = $_SESSION['generic_information']['postnomen']['value'];
                    if (isset($_SESSION['generic_information']['firstname']['value'])) $registration_array['firstname'] = $_SESSION['generic_information']['firstname']['value'];
                    if (isset($_SESSION['generic_information']['lastname']['value'])) $registration_array['lastname'] = $_SESSION['generic_information']['lastname']['value'];
                    if (isset($_SESSION['generic_information']['birthdate']['value'])) $registration_array['birthdate'] = $_SESSION['generic_information']['birthdate']['value'];
                    if (isset($_SESSION['generic_information']['idprovider']['value'])) $registration_array['idprovider'] = $_SESSION['generic_information']['idprovider']['value'];
                    if (isset($_SESSION['generic_information']['idvalue']['value'])) $registration_array['idvalue'] = $_SESSION['generic_information']['idvalue']['value'];
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
            if (isset($_SESSION['generic_information']['banking_iban']['value'])) $registration_array['banking_iban'] = strtoupper(str_replace(' ', '', $_SESSION['generic_information']['banking_iban']['value']));
            if (isset($_SESSION['generic_information']['banking_consent']['value']) && $_SESSION['generic_information']['banking_consent']['value'] == '1') $registration_array['banking_consent'] = time();
            if (isset($_SESSION['generic_information']['bylaws_consent']['value']) && $_SESSION['generic_information']['bylaws_consent']['value'] == '1') $registration_array['bylaws_consent'] = time();
            if (isset($_SESSION['generic_information']['tos_consent']['value']) && $_SESSION['generic_information']['tos_consent']['value'] == '1') $registration_array['tos_consent'] = time();
            if (isset($_SESSION['generic_information']['gdpr_consent']['value']) && $_SESSION['generic_information']['gdpr_consent']['value'] == '1') $registration_array['gdpr_consent'] = time();
            if (isset($_SESSION['generic_information']['network_consent']['value']) && $_SESSION['generic_information']['network_consent']['value'] == '1') $registration_array['network_consent'] = time();

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
                    $meter_array['meter_participation'] = $meter_object['participation']['value'];

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

            return true;
        }
        else
        {
            return false;
        }
    }

    private function view_render_consumption_meters()
    {
        print '
                    <br />
                    <header id="header">
                        <h2>Z&auml;hlpunkte (Bezug)</h2>
                        <p>Mit welchen Z&auml;hlpunkten m&ouml;chtest Du Energie von uns beziehen?</p>
                    </header>
        ';

        print "<div class=\"form-container\" style=\"min-width:960px; width:960px;\">";

        if(isset($_SESSION['meters']))
        {
            $consumer_count = 0;
            foreach($_SESSION['meters'] as $meter_key => $meter_object)
            {
                if($meter_object['type'] == "consumers")
                {
                    $this->view_render_prefixed_meter("Z&auml;hlpunktnummer (letzte 7 Stellen)", $this->config->user['EEG_CONSUMERS_PREFIX'], $meter_key, $meter_object['value']);
                    $consumer_count++;
                }
            }
        }

        if(!isset($_SESSION['meters']) || $consumer_count == 0)
        {
            $id = $this->generate_uuid4();
            $_SESSION['meters']["$id"]['prefix'] = $this->config->user['EEG_CONSUMERS_PREFIX'];
            $_SESSION['meters']["$id"]['value'] = '0000000';
            $_SESSION['meters']["$id"]['type'] = 'consumers';
            $this->view_render_prefixed_meter("Z&auml;hlpunktnummer (letzte 7 Stellen)", $this->config->user['EEG_CONSUMERS_PREFIX'], $id);
        }

        print "<div id='end_of_consumers'></div>";
        print '<br /><i style="font-size:16px;cursor:pointer;" class="icon fa-plus-square" onclick="JaxonInteractives.add_meter(' . "'consumers'" . ',' . "'" . $this->config->user['EEG_CONSUMERS_PREFIX'] . "'" . ');"></i><span class="label" style="font-weight:normal;font-size:16px;cursor:pointer;" onclick="JaxonInteractives.add_meter(' . "'consumers'" . ',' . "'" . $this->config->user['EEG_CONSUMERS_PREFIX'] . "'" . ');">&nbsp; Einen Bezugsz&auml;hlpunkt hinzuf&uuml;gen</span>';
        print "</div>";
    }

    private function view_render_supply_meters()
    {

        print '
                    <br />
                    <header id="header">
                        <h2>Z&auml;hlpunkte (Einspeisung)</h2>
                        <p>Erzeugst Du selbst erneuerbare Energie und m&ouml;chtest in unsere EEG einspeisen?</p>
                    </header>
        ';

        print "<div class=\"form-container\" style=\"min-width:960px; width:960px;\">";

        if(isset($_SESSION['meters']))
        {
            foreach($_SESSION['meters'] as $meter_key => $meter_object)
            {
                if($meter_object['type'] == "suppliers")
                {
                    $this->view_render_prefixed_meter("Z&auml;hlpunktnummer (letzte 7 Stellen)", $this->config->user['EEG_SUPPLIERS_PREFIX'], $meter_key, $meter_object['value']);
                }
            }

        }
        print "<div id='end_of_suppliers'></div>";
        print '<br /><i style="font-size:16px;cursor:pointer;" class="icon fa-plus-square" onclick="JaxonInteractives.add_meter(' . "'suppliers'" . ',' . "'" . $this->config->user['EEG_SUPPLIERS_PREFIX'] . "'" . ');"></i><span class="label" style="font-weight:normal;font-size:16px;cursor:pointer;" onclick="JaxonInteractives.add_meter(' . "'suppliers'" . ',' . "'" . $this->config->user['EEG_SUPPLIERS_PREFIX'] . "'" . ');">&nbsp; Einen Einspeisez&auml;hlpunkt hinzuf&uuml;gen</span>';
        print "</div>";
    }

    private function view_render_energy_storage()
    {
        print '
                    <br />
                    <header id="header">
                        <h2>Vorhandene Energiespeicher</h2>
                        <p>Diese Angabe ist freiwillig - wir erheben diese Information nur zu statistischen Zwecken</p>
                    </header>
        ';

        print "<div class=\"form-container\" style=\"min-width:960px; width:960px;\">";

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
