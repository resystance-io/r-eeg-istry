<?php

include_once('view.inc.php');
class VIEW_MANAGEMENT_REGISTRATIONS extends VIEW
{
    function view_render()
    {
        ?>

        <header id="header">
            <h1>R:EEG:ISTRY | Management</h1>
            <p><A href="/?manage"><i class="fa fa-arrow-alt-circle-left"></i></A>&nbsp;Registrierung anzeigen<br /></p>
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

        if ($registration['tenant'] != NULL)
        {
            $tenant_info = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_TENANTS'], 'id', $registration['tenant']);
            if ($tenant_info == NULL || count($tenant_info) == 0)
            {
                print '<h3>Fehler:</h3><br />Die Eigenschaften der EEG konnten nicht abgerufen werden.<br />Bitte kontaktiere den Support';
                return false;
            } else
            {
                $tenant_info = $tenant_info[0];
            }
        } else
        {
            // tenant not yet chosen
            $tenant_info['fullname'] = 'Nicht zugewiesen';
        }

        print '<div style="float:left; margin-right:50px">'; // MAIN CONTENT LEFT: INFORMATION

        switch ($registration['type'])
        {
            case 'individual':
                print '<h2><li class="fa fa-user"></li>&nbsp;&nbsp;' . $registration['title'] . " " . $registration['firstname'] . " " . $registration['lastname'] . " " . $registration['postnomen'] . '</h2>';
                break;

            case 'agriculture':
                print '<h2><li class="fa fa-tractor"></li>&nbsp;&nbsp;' . $registration['title'] . " " . $registration['firstname'] . " " . $registration['lastname'] . " " . $registration['postnomen'] . '</h2>';
                break;

            case 'company':
                print '<h2><li class="fa fa-building"></li>&nbsp;&nbsp;' . $registration['company_name'] . '</h2>';
                break;
        }
        print '<h3>Status</h3>';
        print '
              <table class="table" style="width:700px">
                <tbody>
        ';

        $state_nice = ['new' => 'Neu', 'onboarding' => "Onboarding", 'active' => "Aktiv", 'suspended' => "Gesperrt", 'deactivated' => "Deaktiviert", 'refused' => "Abgelehnt"];
        $debit_type = ['core' => 'SDD CORE', 'b2b' => 'SDD B2B', 'none' => 'Kein SEPA'];
        print "<tr class=\"stategray\">
                <td class=\"detailheader\">Status:</td>
                <td class=\"detailcontent\" id=\"detail_state\">
                       " . $state_nice[$registration['state']] . "<i onclick=\"JaxonInteractives.dashboard_inline_update_state_init('detail_state', '" . $registration['id'] . "');\" class=\"fa fa-edit fa-pull-right\" style=\"padding-top:6px; cursor:pointer\"></i>
                </td>
               </tr>
        ";

        print "<tr class=\"stategray\">
                <td class=\"detailheader\">EEG / Mandant:</td>
                <td class=\"detailcontent\" id=\"detail_tenant\">" . $tenant_info['fullname'] . "<i onclick=\"JaxonInteractives.dashboard_inline_update_tenant_init('detail_tenant', '" . $registration['id'] . "');\" class=\"fa fa-edit fa-pull-right\" style=\"padding-top:6px; cursor:pointer\"></i></td>
               </tr>
        ";

        print "<tr class=\"stategray\">
                    <td class=\"detailheader\">Anmeldung &uuml;bermittelt:</td>
                    <td class=\"detailcontent\">" . date("d.m.Y", $registration['registration_date']) . "</td>
               </tr>
        ";

        print "<tr class=\"stategray\"><td class=\"detailheader\">EEG-Beitritt best&auml;tigt:</td>";
        {
            if ($registration['migration_date'] != null)
            {
                print "<td class=\"detailcontent\">" . date("d.m.Y", $registration['migration_date']) . "</td>";
            } else
            {
                print "<td class=\"detailcontent\">Noch ausstehend</td>";
            }
        }
        print "</tr>";


        print "<tr class=\"stategray\"><td class=\"detailheader\">Beginn der Belieferung:</td>";
        {
            if ($registration['delivery_date'] != null)
            {
                print "<td class=\"detailheader\">" . date("d.m.Y", $registration['delivery_date']) . "</td>";
            } else
            {
                print "<td class=\"detailcontent\">Noch ausstehend</td>";
            }
        }
        print "</tr>";

        print "<tr class=\"stategray\"><td class=\"detailheader\">Letzte &Auml;nderung:</td>";
        print "<td>&nbsp;</td>";
        print "</tr>";

        print "<tr class=\"stategray\"><td class=\"detailheader\">Letztes Login:</td>";
        if ($registration['migration_date'] != null)
        {
            print "<td class=\"detailcontent\">" . date("d.m.Y", $registration['migration_date']) . "</td>";
        } else
        {
            print "<td class=\"detailcontent\">Noch nie angemeldet</td>";
        }
        print "</tr>";

        print '
                </tbody>
              </table>
        ';

        print '<br />&nbsp;<br />';

        print '<h3>Interne Informationen</h3>';

        print '
              <table class="table" style="width:700px">
                <tbody>
        ';

        print "<tr class=\"stategray\"><td class=\"detailheader\">Datensatz:</td>";
        print "<td class=\"detailcontent\">" . str_pad($registration['id'], 5, '0', STR_PAD_LEFT) . "</td>";
        print "</tr>";

        if ($registration['tenant'] != null) $network_substation = $tenant_info['network_substation_id'] . " | " . $tenant_info['network_substation_name']; else $network_substation = 'Bitte zuerst Mandant/EEG festlegen';
        print "<tr class=\"stategray\"><td class=\"detailheader\">Umspannwerk:</td>";
        print "<td id=\"detail_network_substation\" class=\"detailcontent\">" . $network_substation . "</td>";
        print "</tr>";

        print "<tr class=\"stategray\"><td class=\"detailheader\">Mitgliedsnummer:</td>";
        if($registration['tenant'] == null)
        {
            print "<td id=\"detail_member_id\" class=\"detailcontent\">Bitte zuerst EEG und Status zuweisen</td>";
        }
        elseif($registration['state'] == 'new')
        {
            print "<td id=\"detail_member_id\" class=\"detailcontent\">Bitte zuerst Status zuweisen</td>";
        }
        elseif($registration['state'] == 'refused')
        {
            print "<td id=\"detail_member_id\" class=\"detailcontent\">Abgelehnt</td>";
        }
        else
        {
            if ($registration['member_id'] != null) $member_id = $registration['member_id']; else $member_id = 'Noch nicht zugewiesen';
            print "<td id=\"detail_member_id\" class=\"detailcontent\">$member_id<i onclick=\"JaxonInteractives.dashboard_inline_update_memberid_init('detail_member_id', '" . $registration['id'] . "');\" class=\"fa fa-edit fa-pull-right\" style=\"padding-top:6px; cursor:pointer\"></i></td>";
        }
        print "</tr>";

        print "</tbody></table>";
        print '<br />&nbsp;<br />';


        print '<h3>Allgemeine Informationen</h3>';

        print '
              <table class="table" style="width:700px">
                <tbody>
        ';

        $upload_type_arr = ['invoice' => 'Rechnung', 'credit' => 'Gutschrift', 'photo' => 'Foto', 'id' => 'Ausweis', 'other' => 'Andere'];
        $identity_type_arr = ['passport' => 'Reisepass', 'idcard' => 'Personalausweis', 'driverslicense' => 'F&uuml;hrerschein', 'commerceid' => 'Firmenbuchnummer', 'associationid' => 'Vereinsregister'];
        $tax_type_arr = ['y' => 'Ja', 'n' => 'Nein'];
        $meter_status_arr = ['new' => 'Neu', 'onboarding' => 'In Bearbeitung', 'active' => 'Aktiv', 'suspended' => 'Gesperrt', 'deactivated' => 'Deaktiviert', 'refused' => 'Abgelehnt'];

        switch ($registration['type'])
        {
            case 'individual':
                print "<tr class=\"stategray\"><td class=\"detailheader\">Mitgliedsform</td><td class=\"detailcontent\">Privatperson</td></tr>";
                print "<tr class=\"stategray\"><td class=\"detailheader\">Titel</td><td class=\"detailcontent\">" . $registration['title'] . "</td></tr>";
                print "<tr class=\"stategray\"><td class=\"detailheader\">Vorname</td><td id=\"detail_firstname\" class=\"detailcontent\">" . $registration['firstname'] . "<i onclick=\"JaxonInteractives.dashboard_inline_update_init('detail_firstname', 'firstname', '" . $registration['id'] . "');\" class=\"fa fa-edit fa-pull-right\" style=\"padding-top:6px; cursor:pointer\"></i></td></tr>";
                print "<tr class=\"stategray\"><td class=\"detailheader\">Nachname</td><td id=\"detail_lastname\" class=\"detailcontent\">" . $registration['lastname'] . "<i onclick=\"JaxonInteractives.dashboard_inline_update_init('detail_lastname', 'lastname', '" . $registration['id'] . "');\" class=\"fa fa-edit fa-pull-right\" style=\"padding-top:6px; cursor:pointer\"></i></td></tr>";
                print "<tr class=\"stategray\"><td class=\"detailheader\">Postnomen</td><td class=\"detailcontent\">" . $registration['postnomen'] . "</td></tr>";
                print "<tr class=\"stategray\"><td class=\"detailheader\">Geburtsdatum</td><td id=\"detail_birthdate\" class=\"detailcontent\">" . $registration['birthdate'] . "<i onclick=\"JaxonInteractives.dashboard_inline_update_init('detail_birthdate', 'birthdate', '" . $registration['id'] . "');\" class=\"fa fa-edit fa-pull-right\" style=\"padding-top:6px; cursor:pointer\"></i></td></tr>";
                print "<tr class=\"stategray\"><td class=\"detailheader\">Identit&auml;tsbest&auml;tigung via</td><td class=\"detailcontent\">" . $identity_type_arr[$registration['idprovider']] . "</td></tr>";
                print "<tr class=\"stategray\"><td class=\"detailheader\">Nummer des Identit&auml;tsdokumentes</td><td id=\"detail_idvalue\" class=\"detailcontent\">" . $registration['idvalue'] . "<i onclick=\"JaxonInteractives.dashboard_inline_update_init('detail_idvalue', 'idvalue', '" . $registration['id'] . "');\" class=\"fa fa-edit fa-pull-right\" style=\"padding-top:6px; cursor:pointer\"></i></td></tr>";
                break;

            case 'agriculture':
                print "<tr class=\"stategray\"><td class=\"detailheader\">Mitgliedsform</td><td class=\"detailcontent\">Landwirtschaft</td></tr>";
                print "<tr class=\"stategray\"><td class=\"detailheader\">Titel</td><td class=\"detailcontent\">" . $registration['title'] . "</td></tr>";
                print "<tr class=\"stategray\"><td class=\"detailheader\">Vorname</td><td id=\"detail_firstname\" class=\"detailcontent\">" . $registration['firstname'] . "<i onclick=\"JaxonInteractives.dashboard_inline_update_init('detail_firstname', 'firstname', '" . $registration['id'] . "');\" class=\"fa fa-edit fa-pull-right\" style=\"padding-top:6px; cursor:pointer\"></i></td></tr>";
                print "<tr class=\"stategray\"><td class=\"detailheader\">Nachname</td><td id=\"detail_lastname\" class=\"detailcontent\">" . $registration['lastname'] . "<i onclick=\"JaxonInteractives.dashboard_inline_update_init('detail_lastname', 'lastname', '" . $registration['id'] . "');\" class=\"fa fa-edit fa-pull-right\" style=\"padding-top:6px; cursor:pointer\"></i></td></tr>";
                print "<tr class=\"stategray\"><td class=\"detailheader\">Postnomen</td><td class=\"detailcontent\">" . $registration['postnomen'] . "</td></tr>";
                print "<tr class=\"stategray\"><td class=\"detailheader\">Geburtsdatum</td><td id=\"detail_birthdate\" class=\"detailcontent\">" . $registration['birthdate'] . "<i onclick=\"JaxonInteractives.dashboard_inline_update_init('detail_birthdate', 'birthdate', '" . $registration['id'] . "');\" class=\"fa fa-edit fa-pull-right\" style=\"padding-top:6px; cursor:pointer\"></i></td></tr>";
                print "<tr class=\"stategray\"><td class=\"detailheader\">Identit&auml;tsbest&auml;tigung via</td><td class=\"detailcontent\">" . $identity_type_arr[$registration['idprovider']] . "</td></tr>";
                print "<tr class=\"stategray\"><td class=\"detailheader\">Nummer des Identit&auml;tsdokumentes</td><td id=\"detail_idvalue\" class=\"detailcontent\">" . $registration['idvalue'] . "<i onclick=\"JaxonInteractives.dashboard_inline_update_init('detail_idvalue', 'idvalue', '" . $registration['id'] . "');\" class=\"fa fa-edit fa-pull-right\" style=\"padding-top:6px; cursor:pointer\"></i></td></tr>";
                break;

            case 'company':
                print "<tr class=\"stategray\"><td class=\"detailheader\">Mitgliedsform</td><td class=\"detailcontent\">Unternehmen</td></tr>";
                print "<tr class=\"stategray\"><td class=\"detailheader\">Firmenwortlaut</td><td id=\"detail_company_name\" class=\"detailcontent\">" . $registration['company_name'] . "<i onclick=\"JaxonInteractives.dashboard_inline_update_init('detail_company_name', 'company_name', '" . $registration['id'] . "');\" class=\"fa fa-edit fa-pull-right\" style=\"padding-top:6px; cursor:pointer\"></i></td></tr>";
                print "<tr class=\"stategray\"><td class=\"detailheader\">UID</td><td id=\"detail_uid\" class=\"detailcontent\">" . $registration['uid'] . "<i onclick=\"JaxonInteractives.dashboard_inline_update_init('detail_uid', 'uid', '" . $registration['id'] . "');\" class=\"fa fa-edit fa-pull-right\" style=\"padding-top:6px; cursor:pointer\"></i></td></tr>";
                print "<tr class=\"stategray\"><td class=\"detailheader\">Umsatzsteuerpflichtig</td><td class=\"detailcontent\">" . $tax_type_arr[$registration['salestax']] . "</td></tr>";
                print "<tr class=\"stategray\"><td class=\"detailheader\">Firmenbuch oder ZVR?</td><td class=\"detailcontent\">" . $identity_type_arr[$registration['idprovider']] . "</td></tr>";
                print "<tr class=\"stategray\"><td class=\"detailheader\">(FB-/ZVR) Nummer</td><td id=\"detail_idvalue\" class=\"detailcontent\">" . $registration['idvalue'] . "<i onclick=\"JaxonInteractives.dashboard_inline_update_init('detail_idvalue', 'idvalue', '" . $registration['id'] . "');\" class=\"fa fa-edit fa-pull-right\" style=\"padding-top:6px; cursor:pointer\"></i></td></tr>";
                break;
        }

        print "<tr class=\"stategray\"><td class=\"detailheader\">Stra&szlig;e</td><td id=\"detail_street\" class=\"detailcontent\">" . $registration['street'] . ' ' . $registration['number'] . "<i onclick=\"JaxonInteractives.dashboard_inline_update_init('detail_street', 'street', '" . $registration['id'] . "');\" class=\"fa fa-edit fa-pull-right\" style=\"padding-top:6px; cursor:pointer\"></i></td></tr>";
        print "<tr class=\"stategray\"><td class=\"detailheader\">PLZ / Ort</td><td class=\"detailcontent\">" . $registration['zip'] . ' ' . $registration['city'] . "</td></tr>";
        print "<tr class=\"stategray\"><td class=\"detailheader\">Telefonnummer</td><td id=\"detail_phone\" class=\"detailcontent\">" . $registration['phone'] . "<i onclick=\"JaxonInteractives.dashboard_inline_update_init('detail_phone', 'phone', '" . $registration['id'] . "');\" class=\"fa fa-edit fa-pull-right\" style=\"padding-top:6px; cursor:pointer\"></i></td></tr>";
        print "<tr class=\"stategray\"><td class=\"detailheader\">E-Mail-Adresse</td><td id=\"detail_email\" class=\"detailcontent\">" . $registration['email'] . "<i onclick=\"JaxonInteractives.dashboard_inline_update_init('detail_email', 'email', '" . $registration['id'] . "');\" class=\"fa fa-edit fa-pull-right\" style=\"padding-top:6px; cursor:pointer\"></i></td></tr>";
        print "<tr class=\"stategray\"><td class=\"detailheader\">Kundennummer Netzbetreiber</td><td id=\"detail_network_customerid\" class=\"detailcontent\">" . $registration['network_customerid'] . "<i onclick=\"JaxonInteractives.dashboard_inline_update_init('detail_network_customerid', 'network_customerid', '" . $registration['id'] . "');\" class=\"fa fa-edit fa-pull-right\" style=\"padding-top:6px; cursor:pointer\"></i></td></tr>";
        print "<tr class=\"stategray\"><td class=\"detailheader\">Inventarnummer eines Z&auml;hlers</td><td id=\"detail_network_inventoryid\" class=\"detailcontent\">" . $registration['network_inventoryid'] . "<i onclick=\"JaxonInteractives.dashboard_inline_update_init('detail_network_inventoryid', 'network_inventoryid', '" . $registration['id'] . "');\" class=\"fa fa-edit fa-pull-right\" style=\"padding-top:6px; cursor:pointer\"></i></td></tr>";

        print "</tbody></table>";

        print '<br />&nbsp;<br />';
        print '<h3>Optionale Information</h3>';
        print "<table class=\"table\" style=\"width:700px\">";
        print "<tr class=\"stategray\"><td class=\"detailheader\">Anzahl d. E-Autos</td><td class=\"detailcontent\">" . $registration['electric_car_count'] . "</td></tr>";
        print "<tr class=\"stategray\"><td class=\"detailheader\">E-Auto Gesamt-kWh</td><td class=\"detailcontent\">" . $registration['electric_car_capacity'] . "</td></tr>";
        print "<tr class=\"stategray\"><td class=\"detailheader\">E-Auto Jahreskilometer</td><td class=\"detailcontent\">" . $registration['electric_car_mileage'] . "</td></tr>";

        $water_heating_arr = ['' => 'Keine Angabe', 'boiler' => 'Boiler', 'heatpump' => 'W&auml;rmepumpe', 'solar' => 'Solarthermie', 'district' => 'Fernw&auml;rme', 'other' => 'Andere'];
        print "<tr class=\"stategray\"><td class=\"detailheader\">Hei&szlig;wasserbereitung (Sommer)</td><td class=\"detailcontent\">" . $water_heating_arr[$registration['water_heating_summer']] . "</td></tr>";
        print "</tbody></table>";

        print '<br />&nbsp;<br />';

        print '<h3>Bankverbindung</h3>';
        print "<table class=\"table\" style=\"width:700px\">";
        print "<tr class=\"stategray\"><td class=\"detailheader\">Kontoinhaber*in:</td><td id=\"detail_banking_name\" class=\"detailcontent\">" . $registration['banking_name'] . "<i onclick=\"JaxonInteractives.dashboard_inline_update_init('detail_banking_name', 'banking_name', '" . $registration['id'] . "');\" class=\"fa fa-edit fa-pull-right\" style=\"padding-top:6px; cursor:pointer\"></i></td></tr>";
        print "<tr class=\"stategray\"><td class=\"detailheader\">Aktive IBAN:</td><td class=\"detailcontent\">" . $registration['banking_iban'] . "</td></tr>";
        print "<tr class=\"stategray\"><td class=\"detailheader\">Name d. Bankinstituts:</td><td class=\"detailcontent\">" . $registration['banking_institute'] . "</td></tr>";
        print "<tr class=\"stategray\"><td class=\"detailheader\">Einzugserm&auml;chtigung erteilt:</td><td class=\"detailcontent\">" . date("d.m.Y H:i:s", $registration['banking_consent']) . "</td></tr>";
        print "<tr class=\"stategray\"><td class=\"detailheader\">Mandatsreferenznummer:</td><td id=\"detail_banking_mandate_reference\" class=\"detailcontent\">" . $registration['banking_mandate_reference'] . "<i onclick=\"JaxonInteractives.dashboard_inline_update_init('detail_banking_mandate_reference', 'banking_mandate_reference', '" . $registration['id'] . "');\" class=\"fa fa-edit fa-pull-right\" style=\"padding-top:6px; cursor:pointer\"></i></td></tr>";
        if($registration['banking_debit_type'] != null)     $debit_type_nice = $debit_type[$registration['banking_debit_type']];     else    $debit_type_nice = $debit_type['none'];
        print "<tr class=\"stategray\"><td class=\"detailheader\">Einzugsart:</td><td id=\"detail_banking_debit_type\" class=\"detailcontent\">" . $debit_type_nice . "<i onclick=\"JaxonInteractives.dashboard_inline_update_debit_type_init('detail_banking_debit_type', '" . $registration['id'] . "');\" class=\"fa fa-edit fa-pull-right\" style=\"padding-top:6px; cursor:pointer\"></i></td></tr>";
        print "</table>";

        print '<br />&nbsp;<br />';
        print '<h3>Zustimmungen</h3>';
        print "<table class=\"table\" style=\"width:700px\">";

        print "<tr class=\"stategray\"><td class=\"detailheader\">Statuten akzeptiert:</td>";
        if($registration['bylaws_consent'] != null)
        {
            print "<td class=\"detailcontent\">" . date("d.m.Y H:i:s", $registration['bylaws_consent']) . "</td></tr>";
        }
        else
        {
            print "<td class=\"detailcontent bg-red-gradient-transparent\">Nein</td></tr>";
        }

        print "<tr class=\"stategray\"><td class=\"detailheader\">Datenschutzbestimmungen akzeptiert:</td>";
        if ($registration['gdpr_consent'] != null)
        {
            print "<td class=\"detailcontent\">" . date("d.m.Y H:i:s", $registration['gdpr_consent']) . "</td></tr>";
        } else
        {
            print "<td class=\"detailcontent bg-red-gradient-transparent\">Nein</td></tr>";
        }

        print "<tr class=\"stategray\"><td class=\"detailheader\">AGB akzeptiert:</td>";
        if($registration['tos_consent'] != null)
        {
            print "<td class=\"detailcontent\">" . date("d.m.Y H:i:s", $registration['tos_consent']) . "</td></tr>";
        }
        else
        {
            print "<td class=\"detailcontent bg-red-gradient-transparent\">Nein</td></tr>";
        }
        print "<tr class=\"stategray\"><td class=\"detailheader\">Netzbetreibervollmacht erteilt:</td><td class=\"detailcontent\">" . date("d.m.Y H:i:s", $registration['network_consent']) . "</td></tr>";
        print "</table>";

        $uploads = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_UPLOADS'], 'registration_id', $registration['id']);
        if(count($uploads) > 0)
        {
            print '<br />&nbsp;<br />';
            print '<h3>Bereitgestellte Dokumente / Uploads</h3>';

            print '
                  <table class="table" style="width:700px">
            ';

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

        print '<br />&nbsp;<br />';
        print '<h3>Registrierte Z&auml;hlpunkte</h3>';

        print '
              <table class="table" style="width:700px">
        ';

        $meters = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_METERS'], 'registration_id', $registration['id']);
        foreach ($meters as $meter)
        {
            if ($meter['meter_type'] == 'consumer')
            {
                $meter_nice = "Verbrauch";
                $meter_type_shortcode = "ABN";
            } 
            else
            {
                $meter_nice = "Einspeisung";
                $meter_type_shortcode = "ERZ";
            }
            
            if ($meter['meter_participation'] != null) $meter_participation = 'Faktor: ' . $meter['meter_participation'] . '%'; else $meter_participation = '';
            if ($meter['meter_power'] != null) $meter_power = ', Leistung: ' . $meter['meter_power'] . ' kWp'; else $meter_power = '';
            if ($meter['meter_feedlimit'] != null) $meter_feedlimit = ', R&uuml;ckspeiselimit: ' . $meter['meter_feedlimit'] . ' kVA'; else $meter_feedlimit = '';
            if ($meter['meter_estimated_consumption'] != null) $meter_estimated_consumption = ', Voraussichtl. Jahresverbrauch: ' . $meter['meter_estimated_consumption'] . ' kW'; else $meter_estimated_consumption = '';
            if ($meter['meter_state'] == null)  $meter['meter_state'] = 'new';

            if($registration['tenant'] == null || $registration['state'] == 'new' || $registration['state'] == 'refused')
            {
                $meter_short_id = '<i class="fa fa-question-circle"></i>';
            }
            else
            {
                $meter_short_id = $tenant_info['meter_prefix_short'] . $meter_type_shortcode . $meter['meter_oid'];
            }


            print "<tr class=\"stategray profilemeterline\">
                    <td class=\"profilemeter\" style=\"width:100px;text-align:center;vertical-align:middle;font-size:12pt;font-weight:bold\">
                        $meter_short_id
                    </td>
                    <td class=\"profilemeter\" style=\"text-align:left\">
                        <span class=\"metertype\">$meter_nice ($meter_participation$meter_power$meter_feedlimit$meter_estimated_consumption)</span><br />
                        &nbsp;<br />
                        " . $meter['meter_id'] . "<br />
                        " . $meter['meter_addr_street'] . " " . $meter['meter_addr_number'] . ", " . $meter['meter_addr_zip'] . " " . $meter['meter_addr_city'] . "<br />
                    </td>
                    <td class=\"profilemeter\" id=\"detail_meter_state_" . $meter['id'] . "\" style=\"text-align:center;align:center;vertical-align:middle;font-weight:bold;\">
                        " . $meter_status_arr[$meter['meter_state']] . "<i onclick=\"JaxonInteractives.dashboard_inline_update_meter_state_init('detail_meter_state_" . $meter['id'] . "', '" . $meter['id'] . "');\" class=\"fa fa-edit fa-pull-right\" style=\"padding-top:6px; cursor:pointer\"></i>
                    </td>
                   </tr>";
        }

        print "</table>";

        print '<br />&nbsp;<br />';
        print '<h3>Registrierte Energiespeicher</h3>';
        print '
              <table class="table" style="width:700px">
        ';

        $storages = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_STORAGES'], 'registration_id', $registration['id']);
        if ($storages == NULL || count($storages) == 0)
        {
            print "<tr class=\"stategray\"><td class=\"\">Keine Energiespeicher registriert</td></tr>";
        }
        else
        {
            $storage_count = 0;
            foreach($storages as $storage)
            {
                $storage_count++;
                print "<tr class=\"stategray profilemeterline\">
                            <td class=\"profileheader\" style=\"text-align:left\">&nbsp;<i class=\"fa fa-battery-half\"></i> &nbsp; Speicher #$storage_count</td>
                            <td>" . $storage['storage_capacity'] . " kWh</td>
                       </tr>";
            }
        }

        print "</table>";
        print '<br />&nbsp;<br />';
        print '</div>'; // END OF MAIN CONTENT: LEFT
        print '<div style="min-width:500px; float:left">';   // TIMELINE: RIGHT

        print '<h2>&nbsp;</h2>';
        print '<h3>Historie</h3>';

        $notes = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_DASHBOARD_NOTES'], 'registration_id', $registration['id'], NULL,  'timestamp', 'DESC');
        if ($notes == NULL || count($notes) == 0)
        {
            print "Keine Historie<br />";
        }
        else
        {
            print '<ul class="timeline">';

            print '
                                    <!-- timeline item -->
                                    <li>
                                      <div class="timeline-item" style="width:98%">                                                                       
                                          <input type="text" class="form-control" style="float:left; width:90%; height:36px" id="new_note_content" placeholder="Notiz eingeben...">
                                          <button style="float:right" class="bg-blue-gradient" onclick="JaxonInteractives.dashboard_add_note(document.getElementById(\'new_note_content\').value, ' . "'" .  $registration['id'] . "'" . ');"><i class="fa fa-save"></i></button>
                                      </div>
                                    </li>
            ';

            foreach($notes as $note)
            {
                $author_nicename = $this->db->get_column_by_column_value($this->config->user['DBTABLE_DASHBOARD_USERS'], 'username', 'id', $note['user_id']);
                $note_nicedate = date('d.m.Y H:i:s', $note['timestamp']);
                if($author_nicename == NULL)
                {
                    $author_nicename = 'SYSTEM';
                }

                if($note['style'] == 'event')
                {
                    print '
                                    <!-- timeline item -->
                                    <li>
                                      <i class="fa fa-edit bg-blue"></i>
                                      <div class="timeline-item" style="width:98%">
                                        <span class="time"><i class="fa fa-clock"></i> ' . $note_nicedate . ' | ' . $author_nicename . '</span>
                                        <h3 class="timeline-header" style="font-weight:normal;">' . $note['content'] . '</h3>
                                      </div>
                                    </li>
                    ';
                }
                elseif($note['style'] == 'note')
                {
                    print '
                                    <!-- timeline item -->
                                    <li>
                                      <i class="fa fa-envelope bg-blue"></i>
                                      <div class="timeline-item" style="width:98%">
                                        <span class="time"><i class="fa fa-clock-o"></i> ' . $note_nicedate . '</span>
                                        <h3 class="timeline-header">Notiz von ' . $author_nicename . '</h3>
                    ';

                    print '                    
                                        <div class="timeline-body">
                                          ' . $note['content'] . '
                                        </div>
                                        <!--<div class="timeline-footer">
                                          <a class="btn btn-danger btn-xs">Delete</a>
                                        </div>-->
                                      </div>
                                    </li>
                    ';
                }
            }

            print '</ul>';

        }
        print '<br />&nbsp;<br />';
        print '</div>'; // END OF TIMELINE: RIGHT

    }

    public function handle_download_request($download_fsid)
    {
        $file_path = "uploads/" . $download_fsid;
        $nicename = $this->db->get_column_by_column_value($this->config->user['DBTABLE_UPLOADS'], 'nicename', 'fsid', $download_fsid);

        if(filter_var($_SESSION['authenticated'], FILTER_VALIDATE_INT))
        {
            $registration_id = $this->db->get_column_by_column_value($this->config->user['DBTABLE_UPLOADS'], 'registration_id', 'fsid', $download_fsid);
            if($_SESSION['authenticated'] == $registration_id)
            {
                $download_allowed = true;
            }
            else
            {
                if($this->view_handle_backend_login() === true)
                {
                    $download_allowed = true;
                }
                else
                {
                    $download_allowed = false;
                }
            }
        }
        else
        {
            if($this->view_handle_backend_login() === true)
            {
                $download_allowed = true;
            }
            else
            {
                $download_allowed = false;
            }
        }

        if($download_allowed === true)
        {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . $nicename);
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file_path));
            ob_clean();
            flush();
            readfile($file_path);
        }
        else
        {
            die('Sorry, but you are not allowed to download this file.');
        }
    }

}