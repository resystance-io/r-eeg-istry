<?php

include_once('view.inc.php');
class VIEW_MANAGEMENT extends VIEW
{
    function view_render()
    {
        if(isset($_REQUEST['sortkey']))
        {
            $_SESSION['dashboard']['sortkey'] = $_REQUEST['sortkey'];
        }
        if(isset($_REQUEST['dashboard']))
        {
            $_SESSION['dashboard']['id'] = $_REQUEST['dashboard'];
        }
        ?>

        <header id="header">
            <h1>R:EEG:ISTRY | Management</h1>
            <p>Energiegemeinschaften und Anmeldungen verwalten<br /></p>
        </header>

        <?php
        print "<br />";

        $dashboards = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_DASHBOARDS'], 'user_id', '1');

        print '<table class="navigation">
                <thead>
                  <tr>';

        foreach($dashboards as $dashboard)
        {
            if(isset($_SESSION['dashboard']['id']) && $_SESSION['dashboard']['id'] == $dashboard['id']) $navclass = 'navselected'; else $navclass = '';
            print '<th class="' . $navclass . '" onclick="self.location.href=\'?manage&dashboard=' . $dashboard['id'] . '\'">' . $dashboard['name'] . '</th>';
        }

        print '   </tr>
               </thead>
             </table>
             <br />
        ';

        print '
            <div class="table-container">
              <table class="table">
                <thead>
                  <tr>
        ';

        $column_configs = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_DASHBOARD_COLUMNS'], 'visible', 'y');
        $layout_columns = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_DASHBOARD_LAYOUT'], 'dashboard', $_SESSION['dashboard']['id'], NULL, 'sort', 'ASC');
        print '<th>&nbsp;</th>';

        $columns = []; // store every column configuration we get for this dashboard layout to avoid multiple lookups
        $column_count = 0;
        foreach($layout_columns as $layout_column)
        {
            $column_config = array_values(array_filter($column_configs, function($column) use ($layout_column)
            {
                return $column['name'] === $layout_column['data'];
            }));
            $columns[$column_count] = $column_config[0];

            if($columns[$column_count]['sortable'] == 'y')
            {
                if(isset($_SESSION['dashboard']['sortkey']))
                {
                    $sortkey_arr = explode(';', $_SESSION['dashboard']['sortkey']);
                    $sortkey_field = $sortkey_arr[0];
                    $sortkey_direction = $sortkey_arr[1];
                    if($sortkey_field == $columns[$column_count]['name'])
                    {
                        // this is the sorted column
                        $fa_sort_color = 'black';
                        if($sortkey_direction == 'asc')
                        {
                            // currently we're sorting ascending
                            $fa_sort_icon = 'fa-sort-up';
                            $fa_sort_link = 'sortkey=' . $columns[$column_count]['name'] . ';desc';
                        }
                        elseif($sortkey_direction == 'desc')
                        {
                            // currently we're sorting descending
                            $fa_sort_icon = 'fa-sort-down';
                            $fa_sort_link = 'sortkey=' . $columns[$column_count]['name'] . ';asc';
                        }
                        else
                        {
                            // wtf?
                            $fa_sort_icon = 'fa-sort';
                            $fa_sort_link = 'sortkey=' . $columns[$column_count]['name'] . ';asc';
                        }
                    }
                    else
                    {
                        // this is not the sorted column
                        $fa_sort_color = 'lightblue';
                        $fa_sort_icon = 'fa-sort';
                        $fa_sort_link = 'sortkey=' . $columns[$column_count]['name'] . ';asc';
                    }
                }
                else
                {
                    $fa_sort_color = 'lightblue';
                    $fa_sort_icon = 'fa-sort';
                    $fa_sort_link = 'sortkey=' . $columns[$column_count]['name'] . ';asc';
                }

                print '<th style="cursor:pointer" onclick="self.location.href=\'?manage&' . $fa_sort_link . '\'">' . $columns[$column_count]['nicename'] . '<i style="margin-top:6px;color:' . $fa_sort_color . '" class="fa ' . $fa_sort_icon . ' fa-pull-right"></i></th>';
            }
            else
            {
                print '<th>' . $columns[$column_count]['nicename'] . '</th>';
            }

            $column_count++;
        }

        print '
                  </tr>
                  <tr>
        ';

        print '<td><i class="fa fa-search"></i></td>';
        foreach($columns as $column)
        {
            if($column['searchable'] == 'y' && $column['compute'] == null)
            {
                if($_SESSION['dashboard']['search'][$column['name']] != null) $search_value = $_SESSION['dashboard']['search'][$column['name']]; else $search_value = '';
                print '<td>
                            <input type="text" id="search-' . $column['name'] . '" onclick="this.select();" onfocusout="JaxonInteractives.dashboard_set_search(' . "'" . $column['name'] . "'" . ', document.getElementById(' . "'search-" . $column['name'] . "'" . ').value);" class="search" name="search-' . $column['name'] . '" value="' . $search_value . '">
                            <script>
                              input = document.getElementById("search-' . $column['name'] . '");
                              input.addEventListener("keydown", function(event) {
                                if (event.key === "Enter") {
                                    JaxonInteractives.dashboard_set_search(' . "'" . $column['name'] . "'" . ', document.getElementById(' . "'search-" . $column['name'] . "'" . ').value);
                                }
                              });
                            </script>
                       </td>';
            }
            elseif($column['searchable'] == 'y' && $column['compute'] != null)
            {
                print '<td>';
                if(isset($_SESSION['dashboard']['search'][$column['name']]))  $search_value = $_SESSION['dashboard']['search'][$column['name']]; else $search_value = null;
                $this->render_computed_search_column($column['compute'], $column['name'], $search_value);
                print '</td>';
            }
            else
            {
                print '<td>&nbsp;</td>';
            }
        }

        print '
                  </tr>
                </thead>
                <tbody>
        ';

        if(isset($_SESSION['dashboard']['sortkey']))
        {
            $sortkey_arr = explode(';', $_SESSION['dashboard']['sortkey']);
        }
        else
        {
            $sortkey_arr[0] = null;
            $sortkey_arr[1] = null;
        }

        $search_string = '';
        if(isset($_SESSION['dashboard']['search']))
        {
            foreach($_SESSION['dashboard']['search'] as $search_key => $search_value)
            {
                if($search_value != '')
                {
                    // only add this field if we have a proper search value
                    if ($search_string != '') $search_string .= ' AND ';
                    $search_string .= $search_key . ' LIKE "%' . $search_value . '%"';
                }
            }
        }

        $registrations = $this->db->get_rows_by_column_value_extended($this->config->user['DBTABLE_REGISTRATIONS'], 'deleted', 'n', NULL, $sortkey_arr[0], $sortkey_arr[1], $search_string);
        foreach($registrations as $registration)
        {
            print '<tr>';

            print '<td style="cursor:pointer" onclick="self.location.href=\'/?manage_registrations&registration=' . $registration['id'] . '\'"><i class="fa fa-folder-open"></td>';
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

    function render_computed_search_column($compute_type, $column_name, $search_value=null)
    {
        switch($compute_type)
        {
            case 'eeg_short':
                print '<select class="search" id="search-' . $column_name . '" name="search-' . $column_name . '" onchange="JaxonInteractives.dashboard_set_search(' . "'" . $column_name . "'" . ', document.getElementById(' . "'search-" . $column_name . "'" . ').value);">';
                $options_arr = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_TENANTS'], 'shortname', null, null, 'shortname', 'ASC');

                if($search_value == 'null') $selected = 'selected'; else $selected = '';
                print '<option ' . $selected . ' value="">&nbsp;</option>';

                foreach($options_arr as $option)
                {
                    if($search_value == $option['id'])  $selected = 'selected';     else    $selected = '';
                    print '<option ' . $selected . ' value="' . $option['id'] . '">' . $option['shortname'] . '</option>';
                }
                print "</select>";
                break;

            case 'type':
                print '<select class="search" id="search-' . $column_name . '" name="search-' . $column_name . '" onchange="JaxonInteractives.dashboard_set_search(' . "'" . $column_name . "'" . ', document.getElementById(' . "'search-" . $column_name . "'" . ').value);">';
                $options_arr = ['individual' => 'Privatperson', 'company' => 'Unternehmen', 'agriculture' => 'Landwirtschaft'];

                if($search_value == 'null') $selected = 'selected'; else $selected = '';
                print '<option ' . $selected . ' value="">&nbsp;</option>';

                foreach($options_arr as $key => $value)
                {
                    if($search_value == $key)  $selected = 'selected';     else    $selected = '';
                    print '<option ' . $selected . ' value="' . $key . '">' . $value . '</option>';
                }
                print "</select>";
                break;
        }
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
