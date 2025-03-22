<?php

include_once('view.inc.php');
class VIEW_MANAGEMENT extends VIEW
{
    function view_render()
    {
        ?>

        <!--<input type="button" value="JAXON FOO TEST" onclick="jaxon_foo()" /><br />-->
        <header id="header">
            <h1>R:EEG:ISTRY | Management</h1>
            <p>Energiegemeinschaften und Anmeldungen verwalten<br /></p>
        </header>

        <?php
        print "<br />&nbsp;<br />&nbsp;<br />";
        print '
            <div class="table-container">
              <table class="table">
                <thead>
                  <tr>
        ';

        $column_configs = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_DASHBOARD_COLUMNS'], 'visible', 'y');
        $layout_columns = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_DASHBOARD_LAYOUT'], 'user', '1', NULL, 'sort', 'ASC');
        print '<th>ID</th>';

        $columns = []; // store every column configuration we get for this dashboard layout to avoid multiple lookups
        $column_count = 0;
        foreach($layout_columns as $layout_column)
        {
            $column_config = array_values(array_filter($column_configs, function($column) use ($layout_column)
            {
                return $column['name'] === $layout_column['column'];
            }));
            $columns[$column_count] = $column_config[0];

            print '<th>' . $columns[$column_count]['nicename'] . '</th>';
            $column_count++;
        }

        print '
                  </tr>
                </thead>
                <tbody>
        ';

        $registrations = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_REGISTRATIONS']);
        foreach($registrations as $registration)
        {
            print '<tr>';

            print '<td>' . $registration['id'] . '</td>';
            foreach($columns as $column)
            {
                if($column['compute'] == '')
                {
                    print '<td>' . $registration[$column['name']] . '</td>';
                }
                else
                {
                    print '<td>' . $this->lookup_computed_column($column['compute'], $registration) . '</td>';
                }
            }

            print '</tr>';
        }

        print '
                </tbody>
              </table>
            </div>
        ';
    }

    function lookup_computed_column($compute_type, $registration_arr)
    {
        switch($compute_type)
        {
            case 'eeg_short':
                return $this->db->get_column_by_column_value($this->config->user['DBTABLE_TENANTS'], 'shortname', 'id', $registration_arr['tenant']);

            case 'type':
                $type_conversion = ['individual' => 'Privatperson', 'company' => 'Unternehmen', 'agriculture' => 'Landwirtschaft'];
                return $type_conversion[$registration_arr['type']];

            case 'salestax':
            case 'banking_consent':
            case 'network_consent':
            case 'bylaws_consent':
            case 'gdpr_consent':
            case 'tos_consent':
                $bool_conversion = ['y' => 'Ja', 'n' => 'Nein'];
                if($registration_arr[$compute_type])
                {
                    return $bool_conversion[$registration_arr[$compute_type]];
                }
                else
                {
                    return '-';
                }

            case 'idprovider':
                $provider_conversion = ['passport' => 'Reisepass', 'idcard' => 'Personalausweis', 'driverslicense' => 'F&uuml;hrerschein', 'commerceid' => 'Firmenbuchnummer', 'associationid' => 'Vereinsregister'];
                if($registration_arr[$compute_type])
                {
                    return $provider_conversion[$registration_arr[$compute_type]];
                }
                else
                {
                    return '-';
                }

            case 'water_heating_summer':
                $waterheating_conversion = ['boiler' => 'Heizstab/Boiler', 'heatpump' => 'W&auml;rmepumpe', 'solar' => 'Solarthermie', 'district' => 'Fernw&auml;rme', 'other' => 'Andere'];
                if($registration_arr[$compute_type])
                {
                    return $waterheating_conversion[$registration_arr[$compute_type]];
                }
                else
                {
                    return '-';
                }

            case 'bool_consuming_meters':
                $number_of_consuming_meters = $this->db->get_rowcount_by_field_value_extended($this->config->user['DBTABLE_METERS'],'registration_id',$registration_arr['id'], $this->config->user['DBTABLE_METERS'] . ".meter_type = 'consumer'");
                if($number_of_consuming_meters > 0)
                {
                    return "Ja";
                }
                else
                {
                    return "Nein";
                }

            case 'bool_supplying_meters':
                $number_of_supplying_meters = $this->db->get_rowcount_by_field_value_extended($this->config->user['DBTABLE_METERS'],'registration_id',$registration_arr['id'], $this->config->user['DBTABLE_METERS'] . ".meter_type = 'supplier'");
                if($number_of_supplying_meters > 0)
                {
                    return "Ja";
                }
                else
                {
                    return "Nein";
                }

            case 'bool_storage':
                $number_of_storages = $this->db->get_rowcount_by_field_value_extended($this->config->user['DBTABLE_STORAGES'],'registration_id',$registration_arr['id']);
                if($number_of_storages > 0)
                {
                    return "Ja";
                }
                else
                {
                    return "Nein";
                }
        }
    }

}
