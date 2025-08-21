<?php

include_once('view_join_base.inc.php');

class VIEW_JOIN extends VIEW_JOIN_BASE
{
    private $tenant_info;

    public function __construct()
    {
        parent::__construct();

        if(isset($_SESSION['tenant']) && $_SESSION['tenant'] != '')
        {
            $tenant_info = $this->object_broker->instance['db']->get_rows_by_column_value($this->config->user['DBTABLE_TENANTS'], 'id', $_SESSION['tenant'], $limit = 1);
            $this->tenant_info = $tenant_info[0];
        }
    }

    public function view_render($fastjoin=false)
    {
        print '
<script>
    function format_meter_id($id)
    {
        const positions = [2, 8, 13, 23];
        meter_id = document.getElementById($id).value.replace(/\s+/g,\'\');
        document.getElementById(\'counter_\' + $id).textContent=33 - meter_id.length;
        if(meter_id.length === 33)
        {
            document.getElementById(\'cta-\' + $id).style.display = \'none\';
            document.getElementById(\'poa-\' + $id).style.display = \'block\';
        }
        else
        {
            document.getElementById(\'cta-\' + $id).style.display = \'block\';
            document.getElementById(\'poa-\' + $id).style.display = \'none\';
        }
        positions.forEach((pos, i) => {
            if (meter_id.length > pos + i) {
                meter_id =  meter_id.slice(0, pos + i) + \' \' + meter_id.slice(pos + i);
            }
        });
        document.getElementById($id).value = meter_id;
    }
</script>';
        print '
                    <header id="header">
                        <h1>' . $this->tenant_info['fullname'] . '</h1>
                    </header>
        ';

        print "";

        if(isset($_REQUEST['join']) || isset($_REQUEST['fastjoin']))
        {
            if(isset($_REQUEST['step']) && $_REQUEST['step'] > 0)
            {
                // check if the requirements for the previous steps were satisfied:
                if($_REQUEST['step'] > ($_SESSION['latestsave'] + 1))
                {
                    // this step is ahead of its time. Let's go back to the next feasible one
                    $feasible_step = $_SESSION['latestsave'] + 1;
                    if(isset($_REQUEST['join']))
                    {
                        print '<script>window.location.href="/?join=' . $_SESSION['generic_information']['join_type'] . '&step=' . $feasible_step . '";</script>';
                    }
                    elseif(isset($_REQUEST['fastjoin']))
                    {
                        print '<script>window.location.href="/?fastjoin=' . $_SESSION['generic_information']['join_type'] . '&step=' . $feasible_step . '";</script>';
                    }
                    return false;
                }
            }

            if($fastjoin === true)    $selection = $_REQUEST['fastjoin']; else $selection = $_REQUEST['join'];
            switch ($selection)
            {
                case "individual":
                    $_SESSION['generic_information']['join_type'] = "individual";
                    if(isset($_REQUEST['step']))
                    {
                        $this->view_render_step($fastjoin);
                    }
                    break;

                case "agriculture":
                    $_SESSION['generic_information']['join_type'] = "agriculture";
                    if(isset($_REQUEST['step']))
                    {
                        $this->view_render_step($fastjoin);
                    }
                    break;

                case "company":
                    $_SESSION['generic_information']['join_type'] = "company";
                    if(isset($_REQUEST['step']))
                    {
                        $this->view_render_step($fastjoin);
                    }
                    break;

                default:
                    print '
                        <header id="header">
                            <p>Sch&ouml;n, dass Du Dich f&uuml;r die Mitgliedschaft in unserer EEG interessierst.<br />Wir freuen uns &uuml;ber jedes neue Mitglied!<br /></p>
            
                            <p style="color:white">Bitte w&auml;hle die passende Beitrittsform:</p>
                        </header>
                    ';

                    $this->view_render_switch_dialogue($fastjoin);
                    break;
            }
        }
    }


    private function view_render_switch_dialogue($fastjoin = false)
    {
        if($fastjoin === true)
        {
            print '
                <div class="button_container">
                    <button type="button" class="mainbtn" style="" id="btn_enroll_company" onClick="location.href=' . "'?fastjoin=company&step=0'" . '"><img src="images/noun_company.png" alt="Join as Company" id="join_eeg" style="height: 60px; margin-left: 30px;"><br />Ein Unternehmen eintragen</button>
                    <button type="button" class="mainbtn" style="" id="btn_enroll_individual" onClick="location.href=' . "'?fastjoin=individual&step=0'" . '"><img src="images/noun_individual.png" alt="Join as Individual" id="lookup_eeg" style="height: 60px; margin-left: 30px;"><br />Eine Privatperson eintragen</button>
                    <button type="button" class="mainbtn" style="" id="btn_enroll_agriculture" onClick="location.href=' . "'?fastjoin=agriculture&step=0'" . '"><img src="images/noun_agriculture.png" alt="Join as Agriculture" id="lookup_eeg" style="height: 60px; margin-left: 30px;"><br />Eine Landwirtschaft eintragen</button>
                </div>
            ';
        }
        else
        {
            print '
                <div class="button_container">
                    <button type="button" class="mainbtn" style="" id="btn_enroll_company" onClick="location.href=' . "'?join=company&step=0'" . '"><img src="images/noun_company.png" alt="Join as Company" id="join_eeg" style="height: 60px; margin-left: 30px;"><br />Als Unternehmen beitreten</button>
                    <button type="button" class="mainbtn" style="" id="btn_enroll_individual" onClick="location.href=' . "'?join=individual&step=0'" . '"><img src="images/noun_individual.png" alt="Join as Individual" id="lookup_eeg" style="height: 60px; margin-left: 30px;"><br />Als Privatperson beitreten</button>
                    <button type="button" class="mainbtn" style="" id="btn_enroll_agriculture" onClick="location.href=' . "'?join=agriculture&step=0'" . '"><img src="images/noun_agriculture.png" alt="Join as Agriculture" id="lookup_eeg" style="height: 60px; margin-left: 30px;"><br />Als Landwirtschaft beitreten</button>
                </div>
            ';
        }
    }

    private function view_render_step($fastjoin = false)
    {
        if($fastjoin === true)
        {
            $join_layout = $this->config->user['FAST_JOIN_LAYOUT'];
        }
        else
        {
            $join_layout = $this->config->user['JOIN_LAYOUT'];
        }

        if(isset($_REQUEST['step']))
        {
            if($_REQUEST['step'] == count($join_layout))
            {
                $progress_bar_width = 640;
                $previous_step = 0;
            }
            else
            {
                $progress_bar_width = 520;
                $previous_step = $_REQUEST['step'] - 1;
            }

            $progress_fill_width = ceil(($progress_bar_width / count($join_layout)) * $_REQUEST['step']);
            $progress_percent = ceil((100 / count($join_layout)) * $_REQUEST['step']);

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

            if(isset($join_layout[$_REQUEST['step']]))
            {
                foreach ($join_layout[$_REQUEST['step']] as $panel)
                {
                    switch ($panel)
                    {
                        case 'preparation':
                            if($_SESSION['generic_information']['join_type'] == 'company')
                            {
                                $this->view_render_company_preparation();
                            }
                            elseif($_SESSION['generic_information']['join_type'] == 'individual')
                            {
                                $this->view_render_individual_preparation();

                            }
                            elseif($_SESSION['generic_information']['join_type'] == 'agriculture')
                            {
                                $this->view_render_agriculture_preparation();
                            }
                            break;

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

                        case 'uploads':
                            $this->view_render_uploads();
                            break;

                        case 'storage':
                            $this->view_render_energy_storage();
                            break;

                        case 'banking':
                            $this->view_render_banking_details();
                            break;

                        case 'optionals':
                            $this->view_render_optionals();
                            break;

                        case 'approvals':
                            $this->view_render_approvals();
                            break;
                    }
                }

                print '<br />';

                if(($_REQUEST['step'] == 0) && ($panel == 'preparation'))
                {
                    $step_caption = 'Los gehts!';
                }
                else
                {
                    $step_caption = 'Weiter zum n&auml;chsten Schritt';
                }
                print '<div style="width:100%">';

                if($fastjoin === true)
                {
                    print '<div style="float:left;padding-right:20px;"><button type="button" class="defaultbtn" style="float:left;max-width:100px" onClick="JaxonInteractives.step_back(\'' . $_REQUEST['step'] . '\', true);"><i class="fa fa-arrow-left"></i></button></div>';
                    print '<div style="float:left;padding-right:20px;"><button type="button" class="defaultbtn" style="float:left" onClick="JaxonInteractives.next_step(\'' . $_REQUEST['step'] . '\', true);">' . $step_caption . '</button></div>';
                }
                else
                {
                    print '<div style="float:left;padding-right:20px;"><button type="button" class="defaultbtn" style="float:left;max-width:100px" onClick="JaxonInteractives.step_back(\'' . $_REQUEST['step'] . '\', false);"><i class="fa fa-arrow-left"></i></button></div>';
                    print '<div style="float:left;padding-right:20px;"><button type="button" class="defaultbtn" style="float:left" onClick="JaxonInteractives.next_step(\'' . $_REQUEST['step'] . '\', false);">' . $step_caption . '</button></div>';
                }
                print '<div style="float:left;padding-top:20px;text-align:center">';
                print '    <div style="width:' . $progress_bar_width . 'px;background-color:rgba(255, 255, 255, 0.4);">';
                print '        <div style="width:' . $progress_fill_width . 'px;background-color:lightseagreen;text-align:center">' . $progress_text . '</div>' . $progress_bar_text;
                print '    </div>';
                print '</div>';
                print '<div style="clear:both"></div>';
                print '</div>';
                print '<br />&nbsp;<br />';
            }
            else
            {
                if($_REQUEST['step'] == count($join_layout))
                {
                    if($this->view_render_finished() === true)
                    {
                        $proxyUrl = $this->config->user['telemetry_endpoint'];
                        if($proxyUrl)
                        {
                            $tenant = $this->tenant_info['shortname'];
                            $website = $this->tenant_info['reegistry_website'];
                            if(isset($_SESSION['generic_information']['id']))
                            {
                                $caption = "JOIN request received. $website/?manage_registrations&registration=" . $_SESSION['generic_information']['id'];
                            }
                            else
                            {
                                $caption = "JOIN request received. Database issue - please verify";
                            }
                            $sessionFilePath = tempnam(sys_get_temp_dir(), 'tg_');
                            file_put_contents($sessionFilePath, json_encode($_SESSION, JSON_PRETTY_PRINT));

                            $postFields = [
                                'tenant' => $tenant,
                                'caption' => $caption,
                                'session_data' => new CURLFile($sessionFilePath, 'application/json', 'session_data.json')
                            ];

                            $ch = curl_init();
                            curl_setopt_array($ch, [
                                CURLOPT_URL => $proxyUrl,
                                CURLOPT_POST => true,
                                CURLOPT_RETURNTRANSFER => true,
                                CURLOPT_POSTFIELDS => $postFields,
                                CURLOPT_CONNECTTIMEOUT => 5,
                                CURLOPT_TIMEOUT => 10
                            ]);

                            curl_exec($ch);
                            curl_close($ch);
                            unlink($sessionFilePath);
                        }

                        $mail_template = file_get_contents('assets/templates/mail_welcome.html');
                        $mail_template = str_replace('{%FIRSTNAME%}', $_SESSION['generic_information']['firstname']['value'], $mail_template);
                        $mail_template = str_replace('{%LASTNAME%}', $_SESSION['generic_information']['lastname']['value'], $mail_template);
                        $mail_template = str_replace('{%USEREMAIL%}', $_SESSION['generic_information']['email']['value'], $mail_template);
                        $mail_template = str_replace('{%MNEMONIC%}', $_SESSION['mnemonic'], $mail_template);
                        $mail_template = str_replace('{%REFERRER%}', $this->tenant_info['referrer'], $mail_template);
                        $mail_template = str_replace('{%FULLNAME%}', $this->tenant_info['fullname'], $mail_template);
                        $mail_template = str_replace('{%SHORTNAME%}', $this->tenant_info['shortname'], $mail_template);
                        $mail_template = str_replace('{%SLOGAN%}', $this->tenant_info['slogan'], $mail_template);
                        $mail_template = str_replace('{%DOWNLOAD_TOS%}', $this->tenant_info['reegistry_website'] . '/' . $this->tenant_info['download_tos'], $mail_template);
                        $mail_template = str_replace('{%DOWNLOAD_BYLAWS%}', $this->tenant_info['reegistry_website'] . '/' . $this->tenant_info['download_bylaws'], $mail_template);
                        $mail_template = str_replace('{%DOWNLOAD_GDPR%}', $this->tenant_info['reegistry_website'] . '/' . $this->tenant_info['download_gdpr'], $mail_template);
                        $mail_template = str_replace('{%REEGISTRY_WEBSITE%}', $this->tenant_info['reegistry_website'], $mail_template);
                        $mail_template = str_replace('{%CONTACT_WEBSITE%}', $this->tenant_info['contact_website'], $mail_template);
                        $mail_template = str_replace('{%CONTACT_EMAIL%}', $this->tenant_info['contact_email'], $mail_template);
                        $mail_template = str_replace('{%CREDITOR_ID%}', $this->tenant_info['creditor_id'], $mail_template);

                        $this->object_broker->instance['email']->subject = "Deine Anmeldung an der " . $this->tenant_info['shortname'];
                        $this->object_broker->instance['email']->AddRecipient($_SESSION['generic_information']['email']['value']);
                        if($this->tenant_info['carbon_copy_email'])
                        {
                            $this->object_broker->instance['email']->AddBCC($this->tenant_info['carbon_copy_email']);
                        }
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
                                      if (confirm("Wir konnten Deine Zugangsdaten leider nicht an Deine eMail Adresse senden. Bitte stelle sicher, dass Du die eingeblendeten Zugangsdaten erfolgreich notiert hast."))
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

    private function view_render_company_preparation()
    {
        print '
                    <br />
                    <header id="header">
                        <h2>Als Unternehmen beitreten</h2>
                        <p>Bevor Du loslegst, bereite bitte folgende Unterlagen und Informationen vor, da Du diese im Verlauf der Registrierung ben&ouml;tigen wirst:</p>
                    </header>
        ';

        print "<h3>Allgemein</h3>";
        print "<div class=\"form-container\" style=\"min-width:960px; width:960px;\">";
        print '<i class="fa fa-keyboard"></i>&nbsp;&nbsp;&nbsp;&nbsp;Umsatzsteuer ID (falls vorhanden)<br />';
        print '<i class="fa fa-keyboard"></i>&nbsp;&nbsp;&nbsp;&nbsp;Firmenbuchnummer oder ZVR Zahl (falls vorhanden)<br />';
        print '<i class="fa fa-envelope"></i>&nbsp;&nbsp;&nbsp;&nbsp;Aktive E-Mail-Adresse f&uuml;r die Registrierung (und zum Erhalt der Rechnungen)<br />';
        print '<i class="fa fa-piggy-bank"></i>&nbsp;&nbsp;&nbsp;Bankinformationen (IBAN und Name des Kontoinhabers)<br />';
        print '<i class="fa fa-clipboard-check"></i>&nbsp;&nbsp;&nbsp;&nbsp;Befugnis, eine SEPA-Lastschrift zu akzeptieren<br />';
        print "</div>";

        print "<br />"; "<br />";

        print "<h3>Z&auml;hlpunkte</h3>";
        print "<div class=\"form-container\" style=\"min-width:960px; width:960px;\">";
        print '<i class="fa fa-list-ol"></i>&nbsp;&nbsp;&nbsp;&nbsp;Z&auml;hlpunktnummern f&uuml;r Bezug und ggf. Einspeisung (siehe letzte Energieabrechnung)<br />';
        print '<i class="fa fa-circle"></i>&nbsp;&nbsp;&nbsp;&nbsp;R&uuml;ckspeiselimitierung (falls vom Netzbetreiber vorgeschrieben)<br />';
        print '<i class="fa fa-chart-line"></i>&nbsp;&nbsp;&nbsp;&nbsp;Photovoltaikleistung (oder anderweitige Generatorleistung) f&uuml;r Einspeisez&auml;hlpunkte<br />';
        print '<i class="fa fa-list-ol"></i>&nbsp;&nbsp;&nbsp;&nbsp;Inventarnummer der betroffenen Z&auml;hler<br />';
        print "</div>";

        print "<br />"; "<br />";
        
        print "<h3>Netzbetreiber</h3>";
        print "<div class=\"form-container\" style=\"min-width:960px; width:960px;\">";
        print '<i class="fa fa-keyboard"></i>&nbsp;&nbsp;&nbsp;&nbsp;Zugangsdaten f&uuml;r das Online-Portal des Netzbetreibers (z. B. Netz O&Ouml;, Linz Netz)<br />';
        print '<i class="fa fa-keyboard"></i>&nbsp;&nbsp;&nbsp;&nbsp;Falls vorhanden: Kundennummer beim Netzbetreiber (Achtung: nicht vom Energieversorger!)<br />';
        print "</div>";

        print "<br />"; "<br />";
        
        print "<h3>Energieversorger</h3>";
        print "<div class=\"form-container\" style=\"min-width:960px; width:960px;\">";
        print '<i class="fa fa-receipt"></i>&nbsp;&nbsp;&nbsp;&nbsp;Rechnung eines Verbraucherz&auml;hlpunktes (Monats- oder Jahresabrechnung m&ouml;glich)<br />';
        print '<i class="fa fa-file-invoice-dollar"></i>&nbsp;&nbsp;&nbsp;&nbsp;Gutschrift eines Lieferz&auml;hlpunktes (Monats- oder Jahresabrechnung m&ouml;glich)<br />';
        print "</div>";


        print "<br />";
        print "<br />";

        print "<h3>Alles bereit? Dann geht's auch schon los...</h3>";
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

        $this->view_render_part_captioned_inputfield("Firmenwortlaut", "company", "generic_information", "required", "width:400px;float:left");
        $this->view_render_part_captioned_inputfield("UID", "uid", "generic_information", null, "width:280px;float:left");
        $tax_type_arr = ['y' => 'Ja', 'n' => 'Nein'];
        $this->view_render_part_captioned_select("Umsatzsteuerpflichtig", "salestax", $tax_type_arr, "generic_information", "boolean", "width:200px;float:left;");
        print "<div style=\"clear:both\"></div>";
        $this->view_render_part_captioned_inputfield("Stra&szlig;e", "street", "generic_information", "required", "width:760px;float:left;");
        $this->view_render_part_captioned_inputfield("Nr.", "number", "generic_information", "required", "width:120px;float:left;");
        print "<div style=\"clear:both\"></div>";
        $this->view_render_part_captioned_inputfield("PLZ", "zip", "generic_information", "numbers", "width:120px;float:left;");
        $this->view_render_part_captioned_inputfield("Ort", "city", "generic_information", "required", "width:760px;float:left;");
        print "<div style=\"clear:both\"></div>";
        $registration_type_arr = ['commerceid' => 'Firmenbuchnummer', 'associationid' => 'Vereinsnummer'];
        $this->view_render_part_captioned_select("FBN oder ZVR (falls vorhanden)", "idprovider", $registration_type_arr, "generic_information", "required", "width:300px;float:left;");
        $this->view_render_part_captioned_inputfield("Registrierungsnummer (falls vorhanden)", "idvalue", "generic_information", null, "width:580px;float:left;");
        print "<div style=\"clear:both\"></div>";
        $this->view_render_part_captioned_inputfield("Telefonnummer", "phone", "generic_information", "phone", "width:440px;float:left;");
        $this->view_render_part_captioned_inputfield("E-Mail-Adresse", "email", "generic_information", "email", "width:440px;float:left;");
        print "<div style=\"clear:both\"></div>";

        print "</div>";

    }

    private function view_render_individual_preparation()
    {
        print '
                    <br />
                    <header id="header">
                        <h2>Als Privatperson beitreten</h2>
                        <p>Bevor Du loslegst, bereite bitte folgende Unterlagen und Informationen vor, da Du diese im Verlauf der Registrierung ben&ouml;tigen wirst:</p>
                    </header>
        ';

        print "<h3>Allgemein</h3>";
        print "<div class=\"form-container\" style=\"min-width:960px; width:960px;\">";
        print '<i class="fa fa-passport"></i>&nbsp;&nbsp;&nbsp;&nbsp;Ein g&uuml;ltiges Ausweisdokument (Reisepass, Personalausweis, F&uuml;hrerschein)<br />';
        print '<i class="fa fa-envelope"></i>&nbsp;&nbsp;&nbsp;Aktive E-Mail-Adresse f&uuml;r die Registrierung (und zum Erhalt der Rechnungen)<br />';
        print '<i class="fa fa-piggy-bank"></i>&nbsp;&nbsp;&nbsp;Bankinformationen (IBAN und Name des Kontoinhabers)<br />';
        print '<i class="fa fa-clipboard-check"></i>&nbsp;&nbsp;&nbsp;&nbsp;Befugnis, eine SEPA-Lastschrift zu akzeptieren<br />';
        print "</div>";

        print "<br />"; "<br />";

        print "<h3>Z&auml;hlpunkte</h3>";
        print "<div class=\"form-container\" style=\"min-width:960px; width:960px;\">";
        print '<i class="fa fa-list-ol"></i>&nbsp;&nbsp;&nbsp;&nbsp;Z&auml;hlpunktnummern f&uuml;r Bezug und ggf. Einspeisung (siehe letzte Energieabrechnung)<br />';
        print '<i class="fa fa-circle"></i>&nbsp;&nbsp;&nbsp;&nbsp;R&uuml;ckspeiselimitierung (falls vom Netzbetreiber vorgeschrieben)<br />';
        print '<i class="fa fa-chart-line"></i>&nbsp;&nbsp;&nbsp;&nbsp;Photovoltaikleistung (oder anderweitige Generatorleistung) f&uuml;r Einspeisez&auml;hlpunkte<br />';
        print '<i class="fa fa-list-ol"></i>&nbsp;&nbsp;&nbsp;&nbsp;Inventarnummer der betroffenen Z&auml;hler<br />';
        print "</div>";

        print "<br />"; "<br />";
        
        print "<h3>Netzbetreiber</h3>";
        print "<div class=\"form-container\" style=\"min-width:960px; width:960px;\">";
        print '<i class="fa fa-keyboard"></i>&nbsp;&nbsp;&nbsp;&nbsp;Zugangsdaten f&uuml;r das Online-Portal des Netzbetreibers (z. B. Netz O&Ouml;, Linz Netz)<br />';
        print '<i class="fa fa-keyboard"></i>&nbsp;&nbsp;&nbsp;&nbsp;Falls vorhanden: Kundennummer beim Netzbetreiber (Achtung: nicht vom Energiever-
sorger!)<br />';
        print "</div>";

        print "<br />"; "<br />";
        
        print "<h3>Energieversorger</h3>";
        print "<div class=\"form-container\" style=\"min-width:960px; width:960px;\">";
        print '<i class="fa fa-receipt"></i>&nbsp;&nbsp;&nbsp;&nbsp;Rechnung eines Verbraucherz&auml;hlpunktes (Monats- oder Jahresabrechnung m&ouml;glich)<br />';
        print '<i class="fa fa-file-invoice-dollar"></i>&nbsp;&nbsp;&nbsp;&nbsp;Gutschrift eines Lieferz&auml;hlpunktes (Monats- oder Jahresabrechnung m&ouml;glich)<br />';
        print "</div>";


        print "<br />";
        print "<br />";

        print "<h3>Alles bereit? Dann geht's auch schon los...</h3>";
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

    private function view_render_agriculture_preparation()
    {
        print '
                    <br />
                    <header id="header">
                        <h2>Als Landwirtschaft beitreten</h2>
                        <p>Bevor Du loslegst, bereite bitte folgende Unterlagen und Informationen vor, da Du diese im Verlauf der Registrierung ben&ouml;tigen wirst:</p>
                    </header>
        ';

        print "<h3>Allgemein</h3>";
        print "<div class=\"form-container\" style=\"min-width:960px; width:960px;\">";
        print '<i class="fa fa-passport"></i>&nbsp;&nbsp;&nbsp;&nbsp;Ein g&uuml;ltiges Ausweisdokument (Reisepass, Personalausweis, F&uuml;hrerschein)<br />';
        print '<i class="fa fa-envelope"></i>&nbsp;&nbsp;&nbsp;&nbsp;Aktive E-Mail-Adresse f&uuml;r die Registrierung (und zum Erhalt der Rechnungen)<br />';
        print '<i class="fa fa-piggy-bank"></i>&nbsp;&nbsp;&nbsp;Bankinformationen (IBAN und Name des Kontoinhabers)<br />';
        print '<i class="fa fa-clipboard-check"></i>&nbsp;&nbsp;&nbsp;&nbsp;Befugnis, eine SEPA-Lastschrift zu akzeptieren<br />';
        print "</div>";

        print "<br />"; "<br />";

        print "<h3>Z&auml;hlpunkte</h3>";
        print "<div class=\"form-container\" style=\"min-width:960px; width:960px;\">";
        print '<i class="fa fa-list-ol"></i>&nbsp;&nbsp;&nbsp;&nbsp;Z&auml;hlpunktnummern f&uuml;r Bezug und ggf. Einspeisung (siehe letzte Energieabrechnung)<br />';
        print '<i class="fa fa-circle"></i>&nbsp;&nbsp;&nbsp;&nbsp;R&uuml;ckspeiselimitierung (falls vom Netzbetreiber vorgeschrieben)<br />';
        print '<i class="fa fa-chart-line"></i>&nbsp;&nbsp;&nbsp;&nbsp;Photovoltaikleistung (oder anderweitige Generatorleistung) f&uuml;r Einspeisez&auml;hlpunkte<br />';
        print '<i class="fa fa-list-ol"></i>&nbsp;&nbsp;&nbsp;&nbsp;Inventarnummer der betroffenen Z&auml;hler<br />';
        print "</div>";

        print "<br />"; "<br />";

        print "<h3>Netzbetreiber</h3>";
        print "<div class=\"form-container\" style=\"min-width:960px; width:960px;\">";
        print '<i class="fa fa-keyboard"></i>&nbsp;&nbsp;&nbsp;&nbsp;Zugangsdaten f&uuml;r das Online-Portal des Netzbetreibers (z. B. Netz O&Ouml;, Linz Netz)<br />';
        print '<i class="fa fa-keyboard"></i>&nbsp;&nbsp;&nbsp;&nbsp;Falls vorhanden: Kundennummer beim Netzbetreiber (Achtung: nicht vom Energiever-
sorger!)<br />';
        print "</div>";

        print "<br />"; "<br />";

        print "<h3>Energieversorger</h3>";
        print "<div class=\"form-container\" style=\"min-width:960px; width:960px;\">";
        print '<i class="fa fa-receipt"></i>&nbsp;&nbsp;&nbsp;&nbsp;Rechnung eines Verbraucherz&auml;hlpunktes (Monats- oder Jahresabrechnung m&ouml;glich)<br />';
        print '<i class="fa fa-file-invoice-dollar"></i>&nbsp;&nbsp;&nbsp;&nbsp;Gutschrift eines Lieferz&auml;hlpunktes (Monats- oder Jahresabrechnung m&ouml;glich)<br />';
        print "</div>";


        print "<br />";
        print "<br />";

        print "<h3>Alles bereit? Dann geht's auch schon los...</h3>";
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
                        <p>Bitte erg&auml;nze die Informationen zu den von Dir angegebenen Z&auml;hlpunkten!</p>
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
                    print "<h3>Adresse des Bezugsz&auml;hlpunkts " . substr($meter_object['value'], -8) . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</h3>";
                }
                elseif ($meter_object['type'] == "suppliers")
                {
                    print "<h3>Adresse des Einspeisez&auml;hlpunkts " . substr($meter_object['value'], -8) . "</h3>";
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
                    $this->view_render_meter_detail_explained_inputfield($meter_key, "Maximale Leistung", 'power', 'decimal', null, 0, "&nbsp;&nbsp;&nbsp;&nbsp;kWp");

                    $this->view_render_meter_detail_explained_inputfield($meter_key, "Erlaubte Netzzugangsleistung", 'feedlimit', 'decimal', null, 0, "&nbsp;&nbsp;&nbsp;&nbsp;kVA", null, true);
                }
                else
                {
                    $this->view_render_meter_detail_explained_inputfield($meter_key, "Jahresstromverbrauch", 'estimated_consumption', 'decimal', null, 0, "&nbsp;&nbsp;&nbsp;&nbsp;kWh");
                }

                print "</div>";

                print "<div style=\"clear:both\"></div>";

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
                        <p>Noch ein paar Infos zum Konto, bald ist es geschafft ...</p>
                    </header>
        ';

        $shortname = $this->tenant_info['shortname'];
        $cid = $this->tenant_info['creditor_id'];

        print "<h3>Kontoinformationen</h3>";
        print "<div class=\"form-container\" style=\"min-width:960px; width:960px;\">";

        $this->view_render_part_captioned_inputfield("Name KontoinhaberIn", "banking_name", "generic_information", "required");
        $this->view_render_part_captioned_inputfield("IBAN", "banking_iban", "generic_information", "iban");
        $this->view_render_part_captioned_inputfield("Name der Bank", "banking_institute", "generic_information", "required");
        $this->view_render_part_annotated_checkbox("Hiermit best&auml;tige ich die Richtigkeit der angegebenen Kontoinformationen<br />und erm&auml;chtige " . $shortname . " zum Bankeinzug im Rahmen der Leistungsabrechnung. <br /> Creditor ID: " . $cid . ".", "banking_consent", "generic_information", "required");

        print "</div><br />";

    }

    private function view_render_approvals()
    {
        print '
                    <br />
                    <header id="header">
                        <h2>Best&auml;tigungen &amp; Freigaben</h2>
                        <p>Aus rechtlichen Gr&uuml;nden ben&ouml;tigen wir noch Deine formale Zustimmung in den folgenden Bereichen:</p>
                    </header>
        ';

        $bylaws_asset = $this->tenant_info['download_bylaws'];
        if($bylaws_asset != '')
        {
            print "<h3>Statuten</h3>";
            print "<div class=\"form-container\" style=\"min-width:960px; width:960px;\">";
            $this->view_render_part_annotated_checkbox(
                "Ich best&auml;tige die Kenntnisnahme der &nbsp; <a style=\"color:lightblue;text-decoration:none;\" href=\"/download/$bylaws_asset\" target=\"_blank\"><span class=\"fa fa-download\"></span> Statuten</a> der EEG " . $this->tenant_info['shortname'],
                "bylaws_consent", "generic_information", "required");
            print "</div><br />";
        }

        $tos_asset = $this->tenant_info['download_tos'];
        if($tos_asset != '')
        {
            print "<h3>AGBs</h3>";
            print "<div class=\"form-container\" style=\"min-width:960px; width:960px;\">";
            $this->view_render_part_annotated_checkbox(
                "Ich best&auml;tige die Kenntnisnahme der  &nbsp; <a style=\"color:lightblue;text-decoration:none;\" href=\"/download/$tos_asset\" target=\"_blank\"><span class=\"fa fa-download\"></span> Allgemeinen Gesch&auml;ftsbedingungen</a> der EEG " . $this->tenant_info['shortname'],
                "tos_consent", "generic_information", "required");
            print "</div><br />";
        }

        $gdpr_asset = $this->tenant_info['download_gdpr'];
        if($gdpr_asset != '')
        {
            print "<h3>Datenschutz</h3>";
            print "<div class=\"form-container\" style=\"min-width:960px; width:960px;\">";
            $this->view_render_part_annotated_checkbox(
                "Ich best&auml;tige die Kenntnisnahme der  &nbsp; <a style=\"color:lightblue;text-decoration:none;\" href=\"/download/$gdpr_asset\" target=\"_blank\"><span class=\"fa fa-download\"></span> Datenschutzerkl&auml;rung</a> der EEG " . $this->tenant_info['shortname'],
                "gdpr_consent", "generic_information", "required");
            print "</div><br />";
        }

        print "<h3>Netzbetreibervollmacht</h3>";
        print "<div class=\"form-container\" style=\"min-width:960px; width:960px;\">";
        $this->view_render_part_annotated_checkbox(
            "Ich erteile der EEG " . $this->tenant_info['shortname'] . " f&uuml;r die Dauer der Mitgliedschaft zeitlich unbegrenzt die Vollmacht,<br />
                        in meinem Namen s&auml;mtliche Schritte und Abstimmungen mit dem zust&auml;ndigen Netzbetreiber<br />
                        (Netz O&Ouml;) durchzuf&uuml;hren, die zur vollst&auml;ndigen Aktivierung und Deaktivierung der angef&uuml;hrten<br />
                        Z&auml;hlpunkte in der EEG " . $this->tenant_info['shortname'] . " notwendig sind.<br />
                        Dies betrifft insbesondere auch die Registrierung und Nutzung des E-Service-Portals der Netz O&Ouml;.",
            "network_consent", "generic_information", "required");

        $this->view_render_part_captioned_inputfield("Kundennummer beim zust&auml;ndigen Netzbetreiber", "network_customerid", "generic_information", null, "max-width:500px;padding-left:60px;");
        print "<br />";
        $this->view_render_part_captioned_inputfield("Inventarnummer eines beliebigen Z&auml;hlers", "network_inventoryid", "generic_information", "hwinventoryid", "width:500px;padding-left:10px;", "xxx.xxx.xxx");


        print "</div><br />";

    }

    private function view_render_uploads()
    {
        print '
                    <br />
                    <header id="header">
                        <h2>Bereitzustellende Unterlagen</h2>
                        <p>Bitte stelle uns folgende Unterlage(n) bereit damit wir Deine Teilnahme an unserer EEG besser kalkulieren k&ouml;nnen:</p>
                    </header>
        ';

        print "<h3>Rechnung eines Verbraucherz&auml;hlpunktes (Monats- oder Jahresabrechnung)</h3>";
        print "<div class=\"form-container\" style=\"min-width:960px; width:960px;\">";
        print '
            <script>
              Dropzone.options.DropzoneInvoice = {
                maxFilesize: "20M",
                maxFiles: 1,
                addRemoveLinks: true
              };
            </script>
            <form action="?upload&type=invoice" class="dropzone" id="DropzoneInvoice">
                <div class="dz-message" data-dz-message><span style="color:dimgrey;font-weight:normal;">Datei zur &Uuml;bermittlung hier ablegen oder klicken um eine Datei auszuw&auml;hlen</span></div>
            </form>
        ';
        print "</div><br />";

        print "<h3>Gutschrift eines Lieferz&auml;hlpunktes (falls vorhanden. Monats- oder Jahresabrechnung)</h3>";
        print "<div class=\"form-container\" style=\"min-width:960px; width:960px;\">";
        print '
            <script>
              Dropzone.options.DropzoneCredit = {
                maxFilesize: "20M",
                maxFiles: 1,
                addRemoveLinks: true
              };
            </script>
            <form action="?upload&type=credit" class="dropzone" id="DropzoneCredit">
                <div class="dz-message" data-dz-message><span style="color:dimgrey;font-weight:normal;">Datei zur &Uuml;bermittlung hier ablegen oder klicken um eine Datei auszuw&auml;hlen</span></div>
            </form>
        ';
        print "</div><br />";

        if($_SESSION['generic_information']['join_type'] != 'company')
        {
            // ID Document Upload does only apply to individual or aggriculture memberships
            print "<h3>Scan eines g&uuml;ltigen Lichtbildausweises (Reisepass, Personalausweis, F&uuml;hrerschein)</h3>";
            print "<div class=\"form-container\" style=\"min-width:960px; width:960px;\">";
            print '
                <script>
                  Dropzone.options.DropzoneID = {
                    maxFilesize: "20M",
                    maxFiles: 1,
                    addRemoveLinks: true
                  };
                </script>
                <form action="?upload&type=id" class="dropzone" id="DropzoneID">
                    <div class="dz-message" data-dz-message><span style="color:dimgrey;font-weight:normal;">Datei zur &Uuml;bermittlung hier ablegen oder klicken um eine Datei auszuw&auml;hlen</span></div>
                </form>
            ';
            print "</div><br />";
        }

        print "<h3>Foto eines (oder mehrerer) Z&auml;hler (bitte beachte, dass die INV.Nr. gut sichbar sind)</h3>";
        print "<div class=\"form-container\" style=\"min-width:960px; width:960px;\">";
        print '
            <script>
              Dropzone.options.DropzonePhoto = {
                maxFilesize: "20M",
                maxFiles: 1,
                addRemoveLinks: true
              };
            </script>
            <form action="?upload&type=photo" class="dropzone" id="DropzonePhoto">
                <div class="dz-message" data-dz-message><span style="color:dimgrey;font-weight:normal;">Datei zur &Uuml;bermittlung hier ablegen oder klicken um eine Datei auszuw&auml;hlen</span></div>
            </form>
        ';
        print "</div><br />";

    }

    private function view_render_optionals()
    {
        print '
                    <br />
                    <header id="header">
                        <h2>Freiwillige Angaben</h2>
                        <p>Diese Daten sind wichtig, damit wir die Energiefl&uuml;sse in unserer EEG langfristig optimieren k&ouml;nnen.</p>
                    </header>
        ';

        print "<h3>E-Auto(s)</h3>";
        print "<div class=\"form-container\" style=\"min-width:960px; width:960px;\">";
        $car_count_arr = ['0' => 'Keine', '1' => '1', '2' => '2', '3' => '3', '4' => '4', 'F' => '5 oder mehr (Fuhrpark)'];
        $this->view_render_part_captioned_select("Anzahl der E-Autos im Haushalt", "electric_car_count", $car_count_arr, "generic_information", null, "width:600px;float:left;");
        $this->view_render_part_captioned_inputfield("Batteriekapazit&aumlt der Kfz in kWh", "electric_car_capacity", "generic_information", null, "width:600px", "(Bitte Gesamtsumme aller Kfz angeben)");
        $this->view_render_part_captioned_inputfield("Jahreskilometer der Kfz", "electric_car_mileage", "generic_information", null, "width:600px", "(Bitte Gesamtsumme aller Kfz angeben)");
        print "</div><br />";

        print "<h3>Warmwasser</h3>";
        print "<div class=\"form-container\" style=\"min-width:960px; width:960px;\">";
        $water_heating_arr = ['boiler' => 'Heizstab/Boiler', 'heatpump' => 'W&auml;rmepumpe', 'solar' => 'Solarthermie', 'district' => 'Fernw&auml;rme', 'other' => 'Sonstige'];
        $this->view_render_part_captioned_select("Wie bereitest Du Warmwasser im Sommer?", "water_heating_summer", $water_heating_arr, "generic_information", null, "width:600px;float:left;");
        print "<div style=\"clear:both\"></div>";
        print "</div><br />";

    }

    private function view_render_finished()
    {
        $_SESSION['finished'] = true;

        // check if this mnemonic was already stored
        $hashed_mnemonic = hash('sha256', $_SESSION['mnemonic']);
        $mnemonic_count = $this->object_broker->instance['db']->get_rowcount_by_field_value_extended($this->config->user['DBTABLE_REGISTRATIONS'],'mnemonic',$hashed_mnemonic);

        if($mnemonic_count == 0)
        {
            if($this->config->user['defer_tenant_assignment'] === true)
            {
                // assign the tenant based on either the ID chosen by the visitor
                // or the default tenant configured as fallback ID
                $registration_array['tenant'] = $_SESSION['tenant'];
            }
            $registration_array['registration_date'] = time();
            $registration_array['mnemonic'] = $hashed_mnemonic;
            $registration_array['type'] = $_SESSION['generic_information']['join_type'];
            $registration_array['state'] = 'new';

            $tenant_link = $this->db->get_column_by_column_value($this->config->user['DBTABLE_TENANTS'], 'contact_website', 'id', $_SESSION['tenant']);

            print "&nbsp;<br />&nbsp;<br />";
            print "<h2>Vielen Dank f&uuml;r Deine Anmeldung!</h2>";
            print "&nbsp;<br>Deine Daten werden schnellstm&ouml;glich &uuml;berpr&uuml;ft und in unser System &uuml;bernommen.<br />Sobald das Datum feststeht, ab dem Du eneuerbare Energie aus unserer Gemeinschaft beziehen wirst, kontaktieren wir Dich umgehend. <br /><br />Solltest Du Fragen haben, stehen wir Dir selbstverst&auml;ndlich gerne zur Verf&uuml;gung.<br />";
            print "In der Zwischenzeit, besuche gerne <a href=\"$tenant_link\">unsere Website</A><br />";
            print "<br />&nbsp;<br />&nbsp;<br />";
            print "<h3>Dein Passwort:</h3>";
            print "<div class=\"form-container\" style=\"min-width:960px; width:960px;\">";
            print "<br /><h2>" . $_SESSION['mnemonic'] . "</h2>";
            print "</div>&nbsp;<br />";
            print "Du kannst dieses Passwort nutzen, um jederzeit den Bearbeitungsfortschritt Deines Antrages einzusehen<br />und nat&uuml;rlich um Deine Daten zu &auml;ndern.<br />&nbsp;<br /><b>Bitte bewahre es gut auf!</b>";
            print "&nbsp;<br />";
            print "&nbsp;<br />";
            print "&nbsp;<br />";

            switch($_SESSION['generic_information']['join_type'])
            {
                case 'agriculture':
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
                    if (isset($_SESSION['generic_information']['idprovider']['value'])) $registration_array['idprovider'] = $_SESSION['generic_information']['idprovider']['value'];
                    if (isset($_SESSION['generic_information']['idvalue']['value'])) $registration_array['idvalue'] = $_SESSION['generic_information']['idvalue']['value'];
                    if (isset($_SESSION['generic_information']['salestax']['value'])) $registration_array['salestax'] = $_SESSION['generic_information']['salestax']['value'];
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
            if (isset($_SESSION['generic_information']['banking_institute']['value'])) $registration_array['banking_institute'] = $_SESSION['generic_information']['banking_institute']['value'];
            if (isset($_SESSION['generic_information']['banking_consent']['value']) && $_SESSION['generic_information']['banking_consent']['value'] != '') $registration_array['banking_consent'] = $_SESSION['generic_information']['banking_consent']['value'];
            if (isset($_SESSION['generic_information']['bylaws_consent']['value']) && $_SESSION['generic_information']['bylaws_consent']['value'] != '') $registration_array['bylaws_consent'] = $_SESSION['generic_information']['bylaws_consent']['value'];
            if (isset($_SESSION['generic_information']['tos_consent']['value']) && $_SESSION['generic_information']['tos_consent']['value'] != '') $registration_array['tos_consent'] = $_SESSION['generic_information']['tos_consent']['value'];
            if (isset($_SESSION['generic_information']['gdpr_consent']['value']) && $_SESSION['generic_information']['gdpr_consent']['value'] != '') $registration_array['gdpr_consent'] = $_SESSION['generic_information']['gdpr_consent']['value'];
            if (isset($_SESSION['generic_information']['network_consent']['value']) && $_SESSION['generic_information']['network_consent']['value'] != '') $registration_array['network_consent'] = $_SESSION['generic_information']['network_consent']['value'];
            if (isset($_SESSION['generic_information']['network_customerid']['value'])) $registration_array['network_customerid'] = $_SESSION['generic_information']['network_customerid']['value'];
            if (isset($_SESSION['generic_information']['network_inventoryid']['value'])) $registration_array['network_inventoryid'] = $_SESSION['generic_information']['network_inventoryid']['value'];

            if (isset($_SESSION['generic_information']['electric_car_count']['value'])) $registration_array['electric_car_count'] = $_SESSION['generic_information']['electric_car_count']['value'];
            if (isset($_SESSION['generic_information']['electric_car_capacity']['value'])) $registration_array['electric_car_capacity'] = $_SESSION['generic_information']['electric_car_capacity']['value'];
            if (isset($_SESSION['generic_information']['electric_car_mileage']['value'])) $registration_array['electric_car_mileage'] = $_SESSION['generic_information']['electric_car_mileage']['value'];
            if (isset($_SESSION['generic_information']['water_heating_summer']['value'])) $registration_array['water_heating_summer'] = $_SESSION['generic_information']['water_heating_summer']['value'];


            if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
            {
                $registration_array['ip_address'] = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
            }
            else
            {
                $registration_array['ip_address'] = $_SERVER['REMOTE_ADDR'];
            }

            $registration_autoinc_id = $this->object_broker->instance['db']->insert_row_with_array($this->config->user['DBTABLE_REGISTRATIONS'], $registration_array);
            $_SESSION['generic_information']['id'] = $registration_autoinc_id;

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
                    $meter_array['meter_id'] = $meter_object['value'];
                    $meter_array['meter_uuid'] = $meter_key;
                    $meter_array['meter_type'] = $meter_type;
                    $meter_array['meter_addr_street'] = $meter_object['street']['value'];
                    $meter_array['meter_addr_number'] = $meter_object['number']['value'];
                    $meter_array['meter_addr_city'] = $meter_object['city']['value'];
                    $meter_array['meter_addr_zip'] = $meter_object['zip']['value'];
                    $meter_array['meter_participation'] = $meter_object['participation']['value'];
                    $meter_array['meter_feedlimit'] = $meter_object['feedlimit']['value'];
                    $meter_array['meter_estimated_consumption'] = $meter_object['estimated_consumption']['value'];

                    $this->object_broker->instance['db']->insert_row_with_array($this->config->user['DBTABLE_METERS'], $meter_array);
                }
            }

            if(isset($_SESSION['storages']))
            {
                foreach($_SESSION['storages'] as $storage_key => $storage_object)
                {
                    $storage_array['registration_id'] = $registration_autoinc_id;
                    $storage_array['storage_uuid'] = $storage_key;
                    $storage_array['storage_capacity'] = $storage_object['value'];

                    $this->object_broker->instance['db']->insert_row_with_array($this->config->user['DBTABLE_STORAGES'], $storage_array);
                }
            }

            if(isset($_SESSION['generic_information']['uploads']))
            {
                foreach($_SESSION['generic_information']['uploads'] as $upload_object)
                {
                    $upload_array['registration_id'] = $registration_autoinc_id;
                    $upload_array['fsid'] = $upload_object['fsid'];
                    $upload_array['type'] = $upload_object['type'];
                    $upload_array['nicename'] = $upload_object['nicename'];

                    $this->object_broker->instance['db']->insert_row_with_array($this->config->user['DBTABLE_UPLOADS'], $upload_array);
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
                    $this->view_render_prefilled_meter("Z&auml;hlpunktnummer", $meter_object['value'], $meter_key);
                    $consumer_count++;
                }
            }
        }

        if(!isset($_SESSION['meters']) || $consumer_count == 0)
        {
            $id = $this->generate_uuid4();
            $_SESSION['meters']["$id"]['value'] = $this->config->user['EEG_CONSUMERS_PREFILL'];
            $_SESSION['meters']["$id"]['type'] = 'consumers';
            $this->view_render_prefilled_meter("Z&auml;hlpunktnummer", $this->config->user['EEG_CONSUMERS_PREFILL'], $id);
        }

        print "<div id='end_of_consumers'></div>";
        print '<br /><i style="font-size:16px;cursor:pointer;" class="icon fa-plus-square" onclick="JaxonInteractives.add_meter(' . "'consumers'" . ',' . "'" . $this->config->user['EEG_CONSUMERS_PREFILL'] . "'" . ');"></i><span class="label" style="font-weight:normal;font-size:16px;cursor:pointer;" onclick="JaxonInteractives.add_meter(' . "'consumers'" . ',' . "'" . $this->config->user['EEG_CONSUMERS_PREFILL'] . "'" . ');">&nbsp; Einen Bezugsz&auml;hlpunkt hinzuf&uuml;gen</span>';
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
                    $this->view_render_prefilled_meter("Z&auml;hlpunktnummer", $meter_object['value'], $meter_key);
                }
            }

        }
        print "<div id='end_of_suppliers'></div>";
        print '<br /><i style="font-size:16px;cursor:pointer;" class="icon fa-plus-square" onclick="JaxonInteractives.add_meter(' . "'suppliers'" . ',' . "'" . $this->config->user['EEG_SUPPLIERS_PREFILL'] . "'" . ');"></i><span class="label" style="font-weight:normal;font-size:16px;cursor:pointer;" onclick="JaxonInteractives.add_meter(' . "'suppliers'" . ',' . "'" . $this->config->user['EEG_SUPPLIERS_PREFILL'] . "'" . ');">&nbsp; Einen Einspeisez&auml;hlpunkt hinzuf&uuml;gen</span>';
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

    public function handle_upload_request($upload_type=null)
    {
        error_log("join: upload request received");
        $basedir = 'uploads/';
        if(!is_dir($basedir))
        {
            mkdir($basedir);
        }

        if (!empty($_FILES))
        {
            $target_file_name = $this->generate_uuid4();
            $original_file_name = $_FILES['file']['name'];
            $source_fs_location = $_FILES['file']['tmp_name'];
            $target_fs_location = $basedir . '/' . $target_file_name;
            if (is_file($target_fs_location))
            {
                error_log('File successfully uploaded: ' . $target_fs_location);
            }
            else
            {
                error_log('File uploaded but missing on landing zone: ' . $target_fs_location);
            }

            if(move_uploaded_file($source_fs_location, $target_fs_location))
            {
                $_SESSION['generic_information']['uploads'][] = array(
                    'fsid' => $target_file_name,
                    'type' => $upload_type,
                    'nicename' => $original_file_name
                );
            }
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
