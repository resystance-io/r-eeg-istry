<?php

class VIEW_LOOKUP
{

    private $config;
    private $object_broker;
    private $db;

    public function __construct($object_broker, $database = NULL)
    {

        $this->object_broker = $object_broker;
        $this->config = $object_broker->instance['config'];
        $this->db = $object_broker->instance['db'];
    }

    public function view_render()
    {

        switch ($_REQUEST['lookup'])
        {
            case "profile":
                $this->view_render_profile();
                break;

            default:
                print '
                    <header id="header">
                        <h1>Erneuerbare Energiegemeinschaft VIERE</h1>
                        <p>Beitrittsstatus- und Datenabfrage<br /></p>
        
                        <p style="color:white">Bitte melde Dich mit Deinen Zugangsdaten an!</p>
                    </header>
                ';
                $this->view_render_login();
                break;
        }
    }

    private function view_render_login()
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
        print '<button type="button" class="defaultbtn" id="btn_authenticate" onClick="JaxonInteractives.authenticate();">Einloggen</button>';

    }

    private function view_render_profile()
    {
        if (!filter_var($_SESSION['authenticated'], FILTER_VALIDATE_EMAIL))
        {
            print "<script>window.location.href='/';</script>";
            exit;
        }

        print '
                    <header id="header">
                        <h1>Erneuerbare Energiegemeinschaft VIERE</h1>
                        <p>Beitrittsstatus- und Datenabfrage<br /></p>
        
                        <p style="color:white">' . $_SESSION['authenticated'] . ' <button type="button" class="" style="background-color:darkred;margin:9px;" id="btn_deauthenticate" onClick="JaxonInteractives.deauthenticate();">Abmelden</button></p>
                    </header>
        ';

        $registrations = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_REGISTRATIONS'], 'email', $_SESSION['authenticated']);
        if ($registrations == NULL || count($registrations) == 0)
        {
            print '<h3>Fehler:</h3><br />Die angeforderten Daten konnten nicht abgerufen werden.<br />Bitte kontaktiere den Support';
            return false;
        }
        $registration = $registrations[0];

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
        switch ($registration['type'])
        {
            case 'individual':
                print "<tr><td class=\"profileheader\">Mitgliedsform</td><td>Privatperson</td></tr>";
                print "<tr><td class=\"profileheader\">Titel</td><td>" . $registration['title'] . "</td></tr>";
                print "<tr><td class=\"profileheader\">Vorname</td><td>" . $registration['firstname'] . "</td></tr>";
                print "<tr><td class=\"profileheader\">Nachname</td><td>" . $registration['lastname'] . "</td></tr>";
                print "<tr><td class=\"profileheader\">Postnomen</td><td>" . $registration['postnomen'] . "</td></tr>";
                print "<tr><td class=\"profileheader\">Geburtsdatum</td><td>" . $registration['birthdate'] . "</td></tr>";
                break;
            case 'agriculture':
                print "<tr><td class=\"profileheader\">Mitgliedsform</td><td>Landwirtschaft</td></tr>";
                print "<tr><td class=\"profileheader\">Titel</td><td>" . $registration['title'] . "</td></tr>";
                print "<tr><td class=\"profileheader\">Vorname</td><td>" . $registration['firstname'] . "</td></tr>";
                print "<tr><td class=\"profileheader\">Nachname</td><td>" . $registration['lastname'] . "</td></tr>";
                print "<tr><td class=\"profileheader\">Postnomen</td><td>" . $registration['postnomen'] . "</td></tr>";
                print "<tr><td class=\"profileheader\">Geburtsdatum</td><td>" . $registration['birthdate'] . "</td></tr>";
                break;
            case 'company':
                print "<tr><td class=\"profileheader\">Mitgliedsform</td><td>Unternehmen</td></tr>";
                print "<tr><td class=\"profileheader\">Firmenwortlaut</td><td>" . $registration['company_name'] . "</td></tr>";
                print "<tr><td class=\"profileheader\">UID</td><td>" . $registration['uid'] . "</td></tr>";
                break;
        }

        print "<tr><td class=\"profileheader\">Stra&szlig;e</td><td>" . $registration['street'] . ' ' . $registration['number'] . "</td></tr>";
        print "<tr><td class=\"profileheader\">Ort</td><td>" . $registration['zip'] . ' ' . $registration['city'] . "</td></tr>";
        print "<tr><td class=\"profileheader\">Telefonnummer</td><td>" . $registration['phone'] . "</td></tr>";
        print "<tr><td class=\"profileheader\">E-Mail-Adresse</td><td>" . $registration['email'] . "</td></tr>";
        print "</table>";

        print '<br />&nbsp;<br />';
        print '<h3>Bankverbindung</h3>';
        print "<table>";
        print "<tr><td class=\"profileheader\">Kontoinhaber*in:</td><td>" . $registration['banking_name'] . "</td></tr>";
        print "<tr><td class=\"profileheader\">Aktive IBAN:</td><td>" . $registration['banking_iban'] . "</td></tr>";
        print "<tr><td class=\"profileheader\">Einzugserm&auml;chtigung erteilt:</td><td>" . date("d.m.Y H:i:s", $registration['banking_consent']) . "</td></tr>";
        print "</table>";

        print '<br />&nbsp;<br />';
        print '<h3>Registrierte Z&auml;hlpunkte</h3>';
        print "<table>";

        $meters = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_METERS'], 'registration_id', $registration['id']);
        foreach ($meters as $meter)
        {
            if ($meter['meter_type'] == 'consumer') $meter_nice = "Verbrauch"; else    $meter_nice = "Einspeisung";
            if ($meter['meter_power'] != null) $meter_power = '(' . $meter['meter_power'] . ' kWp)'; else    $meter_power = '';
            print "<tr class=\"profilemeterline\"><td class=\"profilemeter\"><span class=\"metertype\">$meter_nice $meter_power</span><br />" . $meter['meter_id'] . "</td></tr>";
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


    }
}

?>
