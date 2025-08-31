<?php

include_once('view.inc.php');

class VIEW_LOOKUP extends VIEW
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

    public function view_render()
    {

        switch ($_REQUEST['lookup'])
        {
            case "profile":
                $this->view_render_profile();
                break;

            case "reset":
                $this->view_render_reset();
                break;

            case "recovernote":
                $this->view_render_recovernote();
                break;

            case "recovernotefail":
                $this->view_render_recovernotefail();
                break;

            case "recover":
                $this->view_render_recover();
                break;

            default:
                print '
                    <header id="header">
                        <h1>' . $this->tenant_info['fullname'] . '</h1>
                        <p>Beitrittsstatus- und Datenabfrage<br /></p>
        
                        <p style="color:white">Bitte melde Dich mit Deinen Zugangsdaten an!</p>
                    </header>
                ';
                $this->view_render_login();
                break;
        }
    }

    private function view_render_login(): void
    {
        if(isset($_SESSION['auth_email']) && isset($_SESSION['auth_email']) != "")
        {
            $username_prefill = $_SESSION['auth_email'];
        }
        else
        {
            $username_prefill = "";
        }

        print "<div class=\"form-container\">";
        print 'E-Mail-Adresse:<br><input type="text" onfocus="this.select()" name="auth_email" id="auth_email" value="' . $username_prefill . '" onfocusout="JaxonInteractives.update_credential_cache(' . "'auth_email'" . ', document.getElementById(' . "'auth_email'" . ').value);" />';
        print '<br />';
        print 'Passwort:<br><input type="password" onfocus="this.select()" name="auth_mnemonic" id="auth_mnemonic" value="" onfocusout="JaxonInteractives.update_credential_cache(' . "'auth_mnemonic'" . ', document.getElementById(' . "'auth_mnemonic'" . ').value);" />';
        print "</div><br />";
        print '<button type="button" class="defaultbtn" style="float:left" id="btn_authenticate" onClick="JaxonInteractives.authenticate();">Einloggen</button>';
        print '<div style="float:right"><a href="/?lookup=reset">Passwort vergessen?</a></div>';
    }

    private function view_render_recover(): void
    {
        if(isset($_GET['email']) && isset($_GET['code']))
        {
            $recovery_email = $_GET['email'];

            if (!filter_var($recovery_email, FILTER_VALIDATE_EMAIL))
            {
                print "Dieser Link ist leider ung&uuml;ltig<br />";
            }
            else
            {
                $recovery_requests = $this->db->get_rows_by_column_value_extended($this->config->user['DBTABLE_TEMPORARY'], 'value1', $recovery_email,null, null, null, $this->config->user['DBTABLE_TEMPORARY'] . ".feature = \"recover_mnemonic\"");
                if(count($recovery_requests) == 0)
                {
                    print "Dieser Link ist leider nicht (mehr) g&uuml;ltig<br />";
                }
                else
                {
                    if($recovery_requests[0]['value2'] != $_GET['code'])
                    {
                        print "Dieser Link ist leider nicht (mehr) g&uuml;ltig<br />";
                    }
                    else
                    {
                        $new_mnemonic = $this->object_broker->instance['session']->generate_session_mnemonic();
                        $account_id = $this->db->get_column_by_column_values($this->config->user['DBTABLE_REGISTRATIONS'], 'id', 'email', $recovery_email);
                        if ($account_id)
                        {
                            $this->db->update_column_by_column_values($this->config->user['DBTABLE_REGISTRATIONS'], 'mnemonic', hash('sha256', $new_mnemonic), 'id', $account_id);
                            $this->db->delete_rows_by_field_value_extended($this->config->user['DBTABLE_TEMPORARY'], 'value1', $recovery_email, $this->config->user['DBTABLE_TEMPORARY'] . ".feature = \"recover_mnemonic\"");

                            print '
                                    <header id="header">
                                        <h1>Passwort vergessen</h1>
                                        <p>Dein neues Passwort wurde erstellt<br /></p>
                                    </header>
                            ';

                            print "Wir haben soeben das Passwort Deines Benutzerkontos aktualisiert.<br />";
                            print "Bitte bewahre dieses an einem sicheren Ort auf.<br />&nbsp;<br />";
                            print "<b>Dein neues Passwort lautet:</b><br />&nbsp;<br />";
                            print "<span style=\"font-size:28pt;font-weight:bold;\">$new_mnemonic</span><br />";
                            print "<br />&nbsp;<br />";
                            print "<b>Alles bereit?</b><br />Dann kannst Du dich ab sofort mit deinem neuen Passwort anmelden:<br />&nbsp;<br>";
                            print "<a href=\"?lookup\" style=\"font-weight:bold;\">Weiter zum Login</a>";
                        }
                        else
                        {
                            print "Wir konnten Dein Benutzerkonto nicht (mehr) finden.<br />Bitte kontaktiere unser Team.";
                        }
                    }
                }
            }
        }
        else
        {
            print "Dieser Link ist leider unvollst&auml;ndig<br />";
        }
    }
    private function view_render_recovernote(): void
    {

        print '
                    <header id="header">
                        <h1>Passwort vergessen</h1>
                        <p>Bitte &uuml;berpr&uuml;fe deinen Posteingang<br /></p>
                    </header>
        ';

        print "Falls du mit dieser E-Mail Adresse ein Benutzerkonto bei uns registriert hast, solltest du<br />";
        print "in K&uuml;rze eine E-Mail mit einem Link zum Zur&uuml;cksetzen deines Passwortes erhalten.<br />&nbsp;<br />";
        print "Falls du keine E-Mail erhalten hast, &uuml;berpr&uuml;fe bitte deinen Spam-Ordner.<br />";
    }


    private function view_render_recovernotefail(): void
    {

        print '
                    <header id="header">
                        <h1>Passwort vergessen</h1>
                        <p>Fehler beim Versand der Nachricht<br /></p>
                    </header>
        ';

        print "Beim Versand der Nachricht mit den Wiederherstellungsinformationen ist ein Fehler aufgetreten.<br />";
        print "Bitte kontaktiere unser Team, wir werden versuchen das Problem zu l&ouml;sen.<br />";
    }


    private function view_render_reset(): void
    {

        print '
                    <header id="header">
                        <h1>Passwort vergessen</h1>
                        <p>Hier kannst Du dein Passwort zur&uuml;cksetzen lassen<br /></p>
                    </header>
        ';

        print "<table>";
        print "<tr><td class=\"profileheader\" style=\"width:400px;\">E-Mail Adresse</td><td><input type=\"text\" style=\"width:400px;\" id=\"forgot_email\"></td></tr>";

        print "<tr><td>&nbsp;</td>";
            print "<td><button style=\"margin-top:12px;\" onclick=\"JaxonInteractives.dashboard_recover_mnemonic(document.getElementById('forgot_email').value);\">Neues Passwort anfordern</button></td>";
        print "</tr>";
        print "</table>";
    }

    private function view_render_profile()
    {
        if (!filter_var($_SESSION['authenticated'], FILTER_VALIDATE_INT))
        {
            print "<script>window.location.href='/';</script>";
            exit;
        }

        $registrations = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_REGISTRATIONS'], 'id', $_SESSION['authenticated']);
        if ($registrations == NULL || count($registrations) == 0)
        {
            print '<h3>Fehler:</h3><br />Die Daten dieser Registrierung konnten nicht abgerufen werden.<br />Bitte kontaktiere den Support';
            return;
        }
        $registration = $registrations[0];

        $tenant_info = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_TENANTS'], 'id', $registration['tenant']);
        if ($tenant_info == NULL || count($tenant_info) == 0)
        {
            print '<h3>Fehler:</h3><br />Die Eigenschaften der EEG konnten nicht abgerufen werden.<br />Bitte kontaktiere den Support';
            return;
        }
        $tenant_info = $tenant_info[0];


        print '
                    <header id="header">
                        <h1>' . $tenant_info['fullname'] . '</h1>
                        <p>Beitrittsstatus- und Datenabfrage<br /></p>
        
                        <p style="color:white">' . $_SESSION['auth_email'] . ' <button type="button" class="" style="background-color:darkred;margin:9px;" id="btn_deauthenticate" onClick="JaxonInteractives.deauthenticate();">Abmelden</button></p>
                    </header>
        ';

        print '<h3>Status</h3>';
        print "<table>";
        print "<tr><td class=\"profileheader\">Anmeldung &uuml;bermittelt:</td><td>" . date("d.m.Y", $registration['registration_date']) . "</td></tr>";

        print "<tr><td class=\"profileheader\">EEG-Beitritt best&auml;tigt:</td>";
        if ($registration['migration_date'] != null)
        {
            print "<td>" . date("d.m.Y", $registration['migration_date']) . "</td>";
        } else
        {
            print "<td>Noch ausstehend</td>";
        }
        print "</tr>";

        print "<tr><td class=\"profileheader\">Beginn der Belieferung:</td>";
        if ($registration['delivery_date'] != null)
        {
            print "<td>" . date("d.m.Y", $registration['delivery_date']) . "</td>";
        } else
        {
            print "<td>Noch ausstehend</td>";
        }
        print "</tr>";

        print "</table>";

        print '<br />&nbsp;<br />';
        print '<h3>Allgemeine Informationen</h3>';
        print "<table>";

        $identity_type_arr = ['passport' => 'Reisepass', 'idcard' => 'Personalausweis', 'driverslicense' => 'F&uuml;hrerschein', 'commerceid' => 'Firmenbuchnummer', 'associationid' => 'Vereinsregister'];
        $tax_type_arr = ['yes' => 'Ja', 'no' => 'Nein'];

        switch ($registration['type'])
        {
            case 'individual':
                print "<tr><td class=\"profileheader\">Mitgliedsform</td><td>Privatperson</td></tr>";
                print "<tr><td class=\"profileheader\">Titel</td><td>" . $registration['title'] . "</td></tr>";
                print "<tr><td class=\"profileheader\">Vorname</td><td>" . $registration['firstname'] . "</td></tr>";
                print "<tr><td class=\"profileheader\">Nachname</td><td>" . $registration['lastname'] . "</td></tr>";
                print "<tr><td class=\"profileheader\">Postnomen</td><td>" . $registration['postnomen'] . "</td></tr>";
                print "<tr><td class=\"profileheader\">Geburtsdatum</td><td>" . $registration['birthdate'] . "</td></tr>";
                print "<tr><td class=\"profileheader\">Identit&auml;tsbest&auml;tigung via</td><td>" . $identity_type_arr[$registration['idprovider']] . "</td></tr>";
                print "<tr><td class=\"profileheader\">Nummer des Identit&auml;tsdokumentes</td><td>" . $registration['idvalue'] . "</td></tr>";
                break;

            case 'agriculture':
                print "<tr><td class=\"profileheader\">Mitgliedsform</td><td>Landwirtschaft</td></tr>";
                print "<tr><td class=\"profileheader\">Titel</td><td>" . $registration['title'] . "</td></tr>";
                print "<tr><td class=\"profileheader\">Vorname</td><td>" . $registration['firstname'] . "</td></tr>";
                print "<tr><td class=\"profileheader\">Nachname</td><td>" . $registration['lastname'] . "</td></tr>";
                print "<tr><td class=\"profileheader\">Postnomen</td><td>" . $registration['postnomen'] . "</td></tr>";
                print "<tr><td class=\"profileheader\">Geburtsdatum</td><td>" . $registration['birthdate'] . "</td></tr>";
                print "<tr><td class=\"profileheader\">Identit&auml;tsbest&auml;tigung via</td><td>" . $identity_type_arr[$registration['idprovider']] . "</td></tr>";
                print "<tr><td class=\"profileheader\">Nummer des Identit&auml;tsdokumentes</td><td>" . $registration['idvalue'] . "</td></tr>";
                break;

            case 'company':
                print "<tr><td class=\"profileheader\">Mitgliedsform</td><td>Unternehmen</td></tr>";
                print "<tr><td class=\"profileheader\">Firmenwortlaut</td><td>" . $registration['company_name'] . "</td></tr>";
                print "<tr><td class=\"profileheader\">UID</td><td>" . $registration['uid'] . "</td></tr>";
                print "<tr><td class=\"profileheader\">Umsatzsteuerpflichtig</td><td>" . $tax_type_arr[$registration['salestax']] . "</td></tr>";
                print "<tr><td class=\"profileheader\">Firmenbuch oder ZVR?</td><td>" . $identity_type_arr[$registration['idprovider']] . "</td></tr>";
                print "<tr><td class=\"profileheader\">(FB-/ZVR) Nummer</td><td>" . $registration['idvalue'] . "</td></tr>";
                break;
        }

        print "<tr><td class=\"profileheader\">Stra&szlig;e</td><td>" . $registration['street'] . ' ' . $registration['number'] . "</td></tr>";
        print "<tr><td class=\"profileheader\">Ort</td><td>" . $registration['zip'] . ' ' . $registration['city'] . "</td></tr>";
        print "<tr><td class=\"profileheader\">Telefonnummer</td><td>" . $registration['phone'] . "</td></tr>";
        print "<tr><td class=\"profileheader\">E-Mail-Adresse</td><td>" . $registration['email'] . "</td></tr>";
        print "<tr><td class=\"profileheader\">Kundennummer Netzbetreiber</td><td>" . $registration['network_customerid'] . "</td></tr>";
        print "<tr><td class=\"profileheader\">Inventarnummer eines Z&auml;hlers</td><td>" . $registration['network_inventoryid'] . "</td></tr>";
        print "<tr><td>&nbsp;</td></tr>";
        print "<tr><td class=\"profileheader\">Anzahl d. E-Autos<br />(Optional)</td><td>" . $registration['electric_car_count'] . "</td></tr>";
        print "<tr><td>&nbsp;</td></tr>";
        print "<tr><td class=\"profileheader\">E-Auto Gesamt-kWh<br />(Optional)</td><td>" . $registration['electric_car_capacity'] . "</td></tr>";
        print "<tr><td>&nbsp;</td></tr>";
        print "<tr><td class=\"profileheader\">E-Auto Jahreskilometer<br />(Optional)</td><td>" . $registration['electric_car_mileage'] . "</td></tr>";
        print "<tr><td>&nbsp;</td></tr>";
        print "<tr><td class=\"profileheader\">Hei&szlig;wasserbereitung Sommer<br />(Optional)</td><td>" . $registration['water_heating_summer'] . "</td></tr>";

        print "</table>";

        print '<br />&nbsp;<br />';
        print '<h3>Bankverbindung</h3>';
        print "<table>";
        print "<tr><td class=\"profileheader\">Kontoinhaber*in:</td><td>" . $registration['banking_name'] . "</td></tr>";
        print "<tr><td class=\"profileheader\">Aktive IBAN:</td><td>" . $registration['banking_iban'] . "</td></tr>";
        print "<tr><td class=\"profileheader\">Name der Bank:</td><td>" . $registration['banking_institute'] . "</td></tr>";
        print "<tr><td class=\"profileheader\">Einzugserm&auml;chtigung erteilt:</td><td>" . date("d.m.Y H:i:s", $registration['banking_consent']) . "</td></tr>";
        print "</table>";

        print '<br />&nbsp;<br />';
        print '<h3>Zustimmungen</h3>';
        print "<table>";
        print "<tr><td class=\"profileheader\">Statuten akzeptiert:</td><td>" . date("d.m.Y H:i:s", $registration['bylaws_consent']) . "</td></tr>";
        print "<tr><td class=\"profileheader\">Datenschutzbestimmungen akzeptiert:</td><td>" . date("d.m.Y H:i:s", $registration['gdpr_consent']) . "</td></tr>";
        print "<tr><td class=\"profileheader\">AGB akzeptiert:</td><td>" . date("d.m.Y H:i:s", $registration['tos_consent']) . "</td></tr>";
        print "<tr><td class=\"profileheader\">Netzbetreibervollmacht erteilt:</td><td>" . date("d.m.Y H:i:s", $registration['network_consent']) . "</td></tr>";
        print "</table>";

        print '<br />&nbsp;<br />';
        print '<h3>Registrierte Z&auml;hlpunkte</h3>';
        print "<table>";

        $meters = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_METERS'], 'registration_id', $registration['id']);
        foreach ($meters as $meter)
        {
            if ($meter['meter_type'] == 'consumer') $meter_nice = "Verbrauch"; else    $meter_nice = "Einspeisung";
            if ($meter['meter_participation'] != null) $meter_participation = 'Faktor: ' . $meter['meter_participation'] . '%'; else $meter_participation = '';
            if ($meter['meter_estimated_consumption'] != null) $meter_estimated_consumption = ', voraussichtl. Jahresstromverbrauch: ' . $meter['meter_estimated_consumption'] . ' kW'; else $meter_estimated_consumption = '';
            if ($meter['meter_power'] != null) $meter_power = ', Leistung: ' . $meter['meter_power'] . ' kWp'; else $meter_power = '';
            if ($meter['meter_feedlimit'] != null) $meter_feedlimit = ', R&uuml;ckspeiselimit: ' . $meter['meter_feedlimit'] . ' kVA'; else $meter_feedlimit = '';

            print "<tr class=\"profilemeterline\"><td class=\"profilemeter\"><span class=\"metertype\">$meter_nice ($meter_participation$meter_power$meter_feedlimit$meter_estimated_consumption)</span><br />" . $meter['meter_id'] . "</td></tr>";
        }

        print "</table>";

        print '<br />&nbsp;<br />';
        print '<h3>Registrierte Energiespeicher</h3>';
        print "<table>";

        $storages = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_STORAGES'], 'registration_id', $registration['id']);
        if ($storages == NULL || count($storages) == 0)
        {
            print "<tr><td class=\"profileheader\">Keine Energiespeicher registriert</td></tr>";
        }
        else
        {
            $storage_count = 0;
            foreach($storages as $storage)
            {
                $storage_count++;
                print "<tr class=\"profilemeterline\"><td class=\"profileheader\">Speicher #$storage_count</td><td>" . $storage['storage_capacity'] . " kWh</td></tr>";
            }
        }

        print "</table>";

        $uploads = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_UPLOADS'], 'registration_id', $registration['id']);
        if(count($uploads) > 0)
        {
            print '<br />&nbsp;<br />';
            print '<h3>Bereitgestellte Dokumente / Uploads</h3>';

            print '
                  <table class="table" style="width:700px">
            ';

            $upload_type_arr = ['invoice' => 'Rechnung', 'credit' => 'Gutschrift', 'id' => 'Ausweis', 'photo' => 'Foto', 'other' => 'Andere'];
            foreach ($uploads as $upload)
            {
                $download_icon = '<i class="fa fa-file-download"></i>';

                print "<tr class=\"stategray profilemeterline\">
                        <td class=\"profilemeter\" style=\"width:100px;text-align:center;vertical-align:middle;font-size:12pt;font-weight:bold\" onclick=\"window.open('/?download=" . $upload['fsid'] . "', '_blank');\">
                            <a href=\"/?download=" . $upload['fsid'] . "\" target=\"_blank\">$download_icon</a>
                        </td>
                        <td class=\"profilemeter\" style=\"text-align:left\" onclick=\"window.open('/?download=" . $upload['fsid'] . "', '_blank');\">
                            <span class=\"metertype\">" . $upload['nicename'] . "</span><br />
                            " . $upload_type_arr[$upload['type']] . "
                        </td>
                    </tr>";
            }

            print "</table>";
        }


    }
}
