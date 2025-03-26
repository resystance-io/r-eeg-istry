<?php

include_once('view.inc.php');
class VIEW_MANAGEMENT_REGISTRATIONS extends VIEW
{
    function view_render()
    {
        ?>

        <!--<input type="button" value="JAXON FOO TEST" onclick="jaxon_foo()" /><br />-->
        <header id="header">
            <h1>R:EEG:ISTRY | Management</h1>
            <p>Registrierung anzeigen<br /></p>
        </header>

        <?php

        print "<br />";

        if(isset($_REQUEST['registration']))
        {
            $this->view_render_registration($_REQUEST['registration']);
        }
        else
        {
            print "Ung&uuml;tige Registrierungs-ID";
        }
    }

    function view_render_registration($registration_id)
    {

        $registrations = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_REGISTRATIONS'], 'id', $_REQUEST['registration']);
        if ($registrations == NULL || count($registrations) == 0)
        {
            print '<h3>Fehler:</h3><br />Die Daten dieser Registrierung konnten nicht abgerufen werden.<br />Bitte kontaktiere den Support';
            return false;
        }
        $registration = $registrations[0];

        $tenant_info = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_TENANTS'], 'id', $registration['tenant']);
        if ($tenant_info == NULL || count($tenant_info) == 0)
        {
            print '<h3>Fehler:</h3><br />Die Eigenschaften der EEG konnten nicht abgerufen werden.<br />Bitte kontaktiere den Support';
            return false;
        }
        else
        {
            $tenant_info = $tenant_info[0];
        }

        print '<h3>Status</h3>';
        print '
              <table class="table" style="width:960px">
                <tbody>
        ';

        print "<tr><td class=\"detailheader\">Status:</td><td class=\"detailcontent\">soon</td>";
        //print "<td id=\"detail_status\" class=\"detailcontent\">" . $registration['member_id'] . "<i onclick=\"JaxonInteractives.dashboard_inline_update_init('detail_member_id', 'member_id', '" . $registration['id'] . "');\" class=\"fa fa-edit fa-pull-right\" style=\"padding-top:6px; cursor:pointer\"></i></td>";
        print "</tr>";

        print "<tr>
                <td class=\"detailheader\">EEG / Mandant:</td>
                <td class=\"detailcontent\">" . $tenant_info['fullname'] . "</td>
               </tr>
        ";

        print "<tr>
                    <td class=\"detailheader\">Anmeldung &uuml;bermittelt:</td>
                    <td class=\"detailcontent\">" . date("d.m.Y", $registration['registration_date']) . "</td>
               </tr>
        ";

        print "<tr><td class=\"detailheader\">EEG-Beitritt best&auml;tigt:</td>";
        {
            if ($registration['migration_date'] != null)
            {
                print "<td class=\"detailcontent\">" . date("d.m.Y", $registration['migration_date']) . "</td>";
            }
            else
            {
                print "<td class=\"detailcontent\">Noch ausstehend</td>";
            }
        }
        print "</tr>";


        print "<tr><td class=\"detailheader\">Beginn der Belieferung:</td>";
        {
            if ($registration['delivery_date'] != null)
            {
                print "<td class=\"detailheader\">" . date("d.m.Y", $registration['delivery_date']) . "</td>";
            }
            else
            {
                print "<td class=\"detailcontent\">Noch ausstehend</td>";
            }
        }
        print "</tr>";

        print "<tr><td class=\"detailheader\">Letzte &Auml;nderung:</td>";
        print "<td>&nbsp;</td>";
        print "</tr>";

        print '
                </tbody>
              </table>
        ';

        print '<br />&nbsp;<br />';

        print '<h3>Interne Informationen</h3>';

        print '
              <table class="table" style="width:960px">
                <tbody>
        ';

        print "<tr><td class=\"detailheader\">Umspannwerk:</td>";
        print "<td class=\"detailcontent\">&nbsp;</td>";
        print "</tr>";
        print "<tr><td class=\"detailheader\">Anmeldenummer:</td>";
        print "<td class=\"detailcontent\">" . str_pad($registration['id'], 5, '0', STR_PAD_LEFT) . "</td>";
        print "</tr>";
        print "<tr><td class=\"detailheader\">Mitgliedsnummer:</td>";
        print "<td id=\"detail_member_id\" class=\"detailcontent\">" . $registration['member_id'] . "<i onclick=\"JaxonInteractives.dashboard_inline_update_init('detail_member_id', 'member_id', '" . $registration['id'] . "');\" class=\"fa fa-edit fa-pull-right\" style=\"padding-top:6px; cursor:pointer\"></i></td>";
        print "</tr>";

        print "</tbody></table>";
        print '<br />&nbsp;<br />';


        print '<h3>Allgemeine Informationen</h3>';

        print '
              <table class="table" style="width:960px">
                <tbody>
        ';

        $identity_type_arr = ['passport' => 'Reisepass', 'idcard' => 'Personalausweis', 'driverslicense' => 'F&uuml;hrerschein', 'commerceid' => 'Firmenbuchnummer', 'associationid' => 'Vereinsregister'];
        $tax_type_arr = ['yes' => 'Ja', 'no' => 'Nein'];

        switch ($registration['type'])
        {
            case 'individual':
                print "<tr><td class=\"detailheader\">Mitgliedsform</td><td class=\"detailcontent\">Privatperson</td></tr>";
                print "<tr><td class=\"detailheader\">Titel</td><td class=\"detailcontent\">" . $registration['title'] . "</td></tr>";
                print "<tr><td class=\"detailheader\">Vorname</td><td class=\"detailcontent\">" . $registration['firstname'] . "</td></tr>";
                print "<tr><td class=\"detailheader\">Nachname</td><td class=\"detailcontent\">" . $registration['lastname'] . "</td></tr>";
                print "<tr><td class=\"detailheader\">Postnomen</td><td class=\"detailcontent\">" . $registration['postnomen'] . "</td></tr>";
                print "<tr><td class=\"detailheader\">Geburtsdatum</td><td class=\"detailcontent\">" . $registration['birthdate'] . "</td></tr>";
                print "<tr><td class=\"detailheader\">Identit&auml;tsbest&auml;tigung via</td><td class=\"detailcontent\">" . $identity_type_arr[$registration['idprovider']] . "</td></tr>";
                print "<tr><td class=\"detailheader\">Nummer des Identit&auml;tsdokumentes</td><td class=\"detailcontent\">" . $registration['idvalue'] . "</td></tr>";
                break;

            case 'agriculture':
                print "<tr><td class=\"detailheader\">Mitgliedsform</td><td class=\"detailcontent\">Landwirtschaft</td></tr>";
                print "<tr><td class=\"detailheader\">Titel</td><td class=\"detailcontent\">" . $registration['title'] . "</td></tr>";
                print "<tr><td class=\"detailheader\">Vorname</td><td class=\"detailcontent\">" . $registration['firstname'] . "</td></tr>";
                print "<tr><td class=\"detailheader\">Nachname</td><td class=\"detailcontent\">" . $registration['lastname'] . "</td></tr>";
                print "<tr><td class=\"detailheader\">Postnomen</td><td class=\"detailcontent\">" . $registration['postnomen'] . "</td></tr>";
                print "<tr><td class=\"detailheader\">Geburtsdatum</td><td class=\"detailcontent\">" . $registration['birthdate'] . "</td></tr>";
                print "<tr><td class=\"detailheader\">Identit&auml;tsbest&auml;tigung via</td><td class=\"detailcontent\">" . $identity_type_arr[$registration['idprovider']] . "</td></tr>";
                print "<tr><td class=\"detailheader\">Nummer des Identit&auml;tsdokumentes</td><td class=\"detailcontent\">" . $registration['idvalue'] . "</td></tr>";
                break;

            case 'company':
                print "<tr><td class=\"detailheader\">Mitgliedsform</td><td class=\"detailcontent\">Unternehmen</td></tr>";
                print "<tr><td class=\"detailheader\">Firmenwortlaut</td><td class=\"detailcontent\">" . $registration['company_name'] . "</td></tr>";
                print "<tr><td class=\"detailheader\">UID</td><td class=\"detailcontent\">" . $registration['uid'] . "</td></tr>";
                print "<tr><td class=\"detailheader\">Umsatzsteuerpflichtig</td><td class=\"detailcontent\">" . $tax_type_arr[$registration['salestax']] . "</td></tr>";
                print "<tr><td class=\"detailheader\">Firmenbuch oder ZVR?</td><td class=\"detailcontent\">" . $identity_type_arr[$registration['idprovider']] . "</td></tr>";
                print "<tr><td class=\"detailheader\">(FB-/ZVR) Nummer</td><td class=\"detailcontent\">" . $registration['idvalue'] . "</td></tr>";
                break;
        }

        print "<tr><td class=\"detailheader\">Stra&szlig;e</td><td class=\"detailcontent\">" . $registration['street'] . ' ' . $registration['number'] . "</td></tr>";
        print "<tr><td class=\"detailheader\">Ort</td><td class=\"detailcontent\">" . $registration['zip'] . ' ' . $registration['city'] . "</td></tr>";
        print "<tr><td class=\"detailheader\">Telefonnummer</td><td class=\"detailcontent\">" . $registration['phone'] . "</td></tr>";
        print "<tr><td class=\"detailheader\">E-Mail-Adresse</td><td class=\"detailcontent\">" . $registration['email'] . "</td></tr>";
        print "<tr><td class=\"detailheader\">Kundennummer Netzbetreiber</td><td class=\"detailcontent\">" . $registration['network_customerid'] . "</td></tr>";
        print "<tr><td class=\"detailheader\">Inventarnummer eines Z&auml;hlers</td><td class=\"detailcontent\">" . $registration['network_inventoryid'] . "</td></tr>";

        print "</tbody></table>";

        print '<br />&nbsp;<br />';
        print '<h3>Optionale Information</h3>';
        print "<table class=\"table\" style=\"width:960px\">";
        print "<tr><td class=\"detailheader\">Anzahl d. E-Autos</td><td class=\"detailcontent\">" . $registration['electric_car_count'] . "</td></tr>";
        print "<tr><td class=\"detailheader\">E-Auto Gesamt-kWh</td><td class=\"detailcontent\">" . $registration['electric_car_capacity'] . "</td></tr>";
        print "<tr><td class=\"detailheader\">E-Auto Jahreskilometer</td><td class=\"detailcontent\">" . $registration['electric_car_mileage '] . "</td></tr>";
        print "<tr><td class=\"detailheader\">Hei&szlig;wasserbereitung (Sommer)</td><td class=\"detailcontent\">" . $registration['water_heating_summer'] . "</td></tr>";
        print "</tbody></table>";

        print '<br />&nbsp;<br />';

        print '<h3>Bankverbindung</h3>';
        print "<table class=\"table\" style=\"width:960px\">";
        print "<tr><td class=\"detailheader\">Kontoinhaber*in:</td><td class=\"detailcontent\">" . $registration['banking_name'] . "</td></tr>";
        print "<tr><td class=\"detailheader\">Aktive IBAN:</td><td class=\"detailcontent\">" . $registration['banking_iban'] . "</td></tr>";
        print "<tr><td class=\"detailheader\">Einzugserm&auml;chtigung erteilt:</td><td class=\"detailcontent\">" . date("d.m.Y H:i:s", $registration['banking_consent']) . "</td></tr>";
        print "</table>";

        print '<br />&nbsp;<br />';
        print '<h3>Zustimmungen</h3>';
        print "<table class=\"table\" style=\"width:960px\">";
        print "<tr><td class=\"detailheader\">Statuten akzeptiert:</td><td class=\"detailcontent\">" . date("d.m.Y H:i:s", $registration['bylaws_consent']) . "</td></tr>";
        print "<tr><td class=\"detailheader\">Datenschutzbestimmungen akzeptiert:</td><td class=\"detailcontent\">" . date("d.m.Y H:i:s", $registration['gdpr_consent']) . "</td></tr>";
        print "<tr><td class=\"detailheader\">AGB akzeptiert:</td><td class=\"detailcontent\">" . date("d.m.Y H:i:s", $registration['tos_consent']) . "</td></tr>";
        print "<tr><td class=\"detailheader\">Netzbetreibervollmacht erteilt:</td><td class=\"detailcontent\">" . date("d.m.Y H:i:s", $registration['network_consent']) . "</td></tr>";
        print "</table>";

        print '<br />&nbsp;<br />';
        print '<h3>Registrierte Z&auml;hlpunkte</h3>';

        print '
              <table class="table" style="width:960px">
                <tbody>
        ';

        $meters = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_METERS'], 'registration_id', $registration['id']);
        foreach ($meters as $meter)
        {
            if ($meter['meter_type'] == 'consumer')
            {
                $meter_nice = "Verbrauch";
                $meter_type_shortcode = "Abn";
            } 
            else
            {
                $meter_nice = "Einspeisung";
                $meter_type_shortcode = "Erz";
            }
            
            if ($meter['meter_participation'] != null) $meter_participation = 'Faktor: ' . $meter['meter_participation'] . '%'; else $meter_participation = '';
            if ($meter['meter_power'] != null) $meter_power = ', Leistung: ' . $meter['meter_power'] . ' kWp'; else $meter_power = '';
            if ($meter['meter_feedlimit'] != null) $meter_feedlimit = ', R&uuml;ckspeiselimit: ' . $meter['meter_feedlimit'] . ' kVA'; else $meter_feedlimit = '';

            print "<tr class=\"profilemeterline\">
                    <td class=\"profilemeter\" style=\"width:100px;text-align:center;vertical-align:middle;font-size:14pt;font-weight:bold\">
                    " . $tenant_info['meter_prefix_short'] . $meter_type_shortcode . $meter['id'] . "
                    </td>
                    <td class=\"profilemeter\" style=\"text-align:left\">
                        <span class=\"metertype\">$meter_nice ($meter_participation$meter_power$meter_feedlimit)</span><br />
                        " . $meter['meter_id'] . "
                    </td>
                   </tr>";
        }

        print "</table>";

        print '<br />&nbsp;<br />';
        print '<h3>Registrierte Energiespeicher</h3>';
        print "<table>";

        $storages = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_STORAGES'], 'registration_id', $registration['id']);
        if ($storages == NULL || count($storages) == 0)
        {
            print "<tr><td class=\"detailheader\">Keine Energiespeicher registriert</td></tr>";
        }
        else
        {
            $storage_count = 0;
            foreach($storages as $storage)
            {
                $storage_count++;
                print "<tr class=\"profilemeterline\">
                        <td class=\"profileheader\" style=\"text-align:left\">Speicher #$storage_count</td>
                        <td>" . $storage['storage_capacity'] . " kWh</td>
                       </tr>";
            }
        }

        print "</table>";
    }

}