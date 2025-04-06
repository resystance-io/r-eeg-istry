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
            $filterconfig = $this->db->get_column_by_column_value($this->config->user['DBTABLE_DASHBOARDS'], 'filterconfig', 'id', $_SESSION['dashboard']['id']);
            unset($_SESSION['dashboard']['filter']);
            if($filterconfig != null)
            {
                $_SESSION['dashboard']['filter'] = json_decode(base64_decode($filterconfig), true);
            }
        }

        ?>

        <header id="header">
            <h1>R:EEG:ISTRY | Management</h1>
            <p>Energiegemeinschaften und Anmeldungen verwalten<br /></p>
        </header>

        <?php
        print "<br />";

        $dashboards = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_DASHBOARDS'], 'user_id', '1');

        print '<div class="table-container" style="height:46px;vertical-align:top">';

        print '<table class="navigation" style="float:right;width:160px;height:48px;">
                <thead>
                  <tr class="navigation">
                    <th onclick="self.location.href=\'?manage_users\'"><li class="fa fa-users"></li></th>
                    <th onClick="self.location.href=\'?manage_dashboards\'"><li class="fa fa-table"></li></th>
                    <th onClick="JaxonInteractives.deauthenticate();"><li class="fa fa-door-open"></li></th>
                  </tr>
               </thead>
             </table>
        ';

        print '<table class="navigation" style="float:left; height:48px;">
                <thead>
                  <tr class="navigation">';

        if(!isset($_SESSION['dashboard']['id']) || $_SESSION['dashboard']['id'] == '')
        {
            // if no dashboard is selected, default to the first one
            $_SESSION['dashboard']['id'] = $dashboards[0]['id'];
        }

        foreach($dashboards as $dashboard)
        {
            if(isset($_SESSION['dashboard']['id']) && $_SESSION['dashboard']['id'] == $dashboard['id']) $navclass = 'navselected'; else $navclass = '';
            print '<th style="white-space:nowrap" class="' . $navclass . '" onclick="self.location.href=\'?manage&dashboard=' . $dashboard['id'] . '\'">&nbsp;<li class="fa fa-table"></li>&nbsp;' . $dashboard['name'] . '&nbsp;</th>';
        }

        print '   </tr>
               </thead>
             </table>
        ';

        print '<div style="width:20px; height:1px; float:left">&nbsp;</div>';
        print '<div style="width:20px; height:1px; float:right">&nbsp;</div>';

        print '<table class="navigation" style="float:right; width:600px; height:46px;">
                <thead>
                  <tr>
                    <th style="width:45px;"><li class="fa fa-search"></li></th>
                    <th>
                        <select id="searchcolumn" name="searchcolumn" class="filter">
                            <option value="">Suchen nach...</option>
        ';

        $searchable_fields = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_DASHBOARD_COLUMNS'], 'searchable', 'y');
        $searchprefill = '';
        foreach($searchable_fields as $searchable_field)
        {
            if(isset($_SESSION['dashboard']['search'][$searchable_field['name']]))
            {
                $selected = 'selected="selected"';
                $searchprefill = $_SESSION['dashboard']['search'][$searchable_field['name']];
            }
            else
            {
                $selected = '';
            }

            print '<option ' . $selected . ' value="' . $searchable_field['name'] . '">' . $searchable_field['nicename'] . '</option>';
        }

        print '
                        </select>
                    </th>
                    <th>
                        <input type="text" id="searchvalue" name="searchvalue" value="' . $searchprefill . '" class="filter"/>
                        <script>
                          input = document.getElementById("searchvalue");
                          input.addEventListener("keydown", function(event) {
                            if (event.key === "Enter") {
                                JaxonInteractives.dashboard_set_search(document.getElementById(' . "'searchcolumn'" . ').value, document.getElementById(' . "'searchvalue'" . ').value);
                            }
                          });
                        </script>
                    </th>
                    <th style="width:60px">
                        <button class="search" onclick="JaxonInteractives.dashboard_set_search(document.getElementById(' . "'searchcolumn'" . ').value, document.getElementById(' . "'searchvalue'" . ').value);">Suchen</button>
                    </th>
                  </tr>
               </thead>
             </table>
        ';

        print '</div><br />';

        print '
            <div class="table-container">
              <table class="table">
                <thead>
                  <tr>
        ';

        $column_configs = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_DASHBOARD_COLUMNS'], 'visible', 'y');
        $layout_columns = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_DASHBOARD_LAYOUT'], 'dashboard', $_SESSION['dashboard']['id'], NULL, 'sort', 'ASC');
        print '<th style="width:60px">&nbsp;</th>';

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

        print '<td><i class="fa fa-filter"></i></td>';
        foreach($columns as $column)
        {
            if($column['filterable'] == 'y' && $column['compute'] == null)
            {
                if($_SESSION['dashboard']['filter'][$column['name']] != null) $filter_value = $_SESSION['dashboard']['filter'][$column['name']]; else $filter_value = '';
                print '<td>
                            <input type="text" id="filter-' . $column['name'] . '" onclick="this.select();" onfocusout="JaxonInteractives.dashboard_set_filter(' . "'" . $column['name'] . "'" . ', document.getElementById(' . "'filter-" . $column['name'] . "'" . ').value);" class="filter" name="filter-' . $column['name'] . '" value="' . $filter_value . '">
                            <script>
                              input = document.getElementById("filter-' . $column['name'] . '");
                              input.addEventListener("keydown", function(event) {
                                if (event.key === "Enter") {
                                    JaxonInteractives.dashboard_set_filter(' . "'" . $column['name'] . "'" . ', document.getElementById(' . "'filter-" . $column['name'] . "'" . ').value);
                                }
                              });
                            </script>
                       </td>';
            }
            elseif($column['filterable'] == 'y' && $column['compute'] != null)
            {
                print '<td>';
                if(isset($_SESSION['dashboard']['filter'][$column['name']]))  $filter_value = $_SESSION['dashboard']['filter'][$column['name']]; else $filter_value = null;
                $this->render_computed_filter_column($column['compute'], $column['name'], $filter_value);
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

        $filter_string = '';
        if(isset($_SESSION['dashboard']['filter']))
        {
            foreach($_SESSION['dashboard']['filter'] as $filter_key => $filter_value)
            {
                if($filter_value != '')
                {
                    // only add this field if we have a proper filter value
                    if ($filter_string != '') $filter_string .= ' AND ';
                    $filter_string .= $filter_key . ' LIKE "%' . $filter_value . '%"';
                }
            }
        }

        if(isset($_SESSION['dashboard']['search']))
        {
            foreach($_SESSION['dashboard']['search'] as $search_key => $search_value)
            {
                if($search_value != '' && $search_key != '')
                {
                    // only add this field if we have a proper search value
                    if ($filter_string != '') $filter_string .= ' AND ';
                    $filter_string .= $search_key . ' LIKE "%' . $search_value . '%"';
                }
            }
        }

        $registrations_count = $this->db->get_rowcount_by_field_value_extended($this->config->user['DBTABLE_REGISTRATIONS'], 'deleted','n', $filter_string);

        if($registrations_count > $this->config->user['page_size'])
        {
            $page_count = ceil($registrations_count / $this->config->user['page_size']);
        }
        else
        {
            $page_count = 1;
        }

        if(isset($_SESSION['dashboard']['page']) && $_SESSION['dashboard']['page'] <= $page_count)
        {
            $start_index = ($_SESSION['dashboard']['page'] - 1) * $this->config->user['page_size'];
        }
        else
        {
            $_SESSION['dashboard']['page'] = 1;
            $start_index = 0;
        }

        $registrations = $this->db->get_rows_by_column_value_extended($this->config->user['DBTABLE_REGISTRATIONS'], 'deleted', 'n', $start_index . ',' . $this->config->user['page_size'], $sortkey_arr[0], $sortkey_arr[1], $filter_string);
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
        ';

        if($registrations_count > $this->config->user['page_size'])
        {
            print '
                  <div class="dataTables_paginate">
                        <ul class="paginationlist">
            ';

            for ($page_selector = 1; $page_selector <= $page_count; $page_selector++)
            {
                if (isset($_SESSION['dashboard']['page']) && $_SESSION['dashboard']['page'] == $page_selector)
                {
                    print "
                                <li class=\"paginate_button pageactive\">
                                    $page_selector
                                </li>
                    ";
                } else
                {
                    print "
                                <li class=\"paginate_button\" onclick=\"JaxonInteractives.dashboard_select_pagination_page($page_selector)\">
                                    $page_selector
                                </li>
                    ";
                }
            }

            print '      
                        </ul>
                  </div>
            ';
        }

        print '
            </div>
            &nbsp;<br />&nbsp;<br />&nbsp;<br />
        ';
    }

    function render_computed_filter_column($compute_type, $column_name, $filter_value=null)
    {
        switch($compute_type)
        {
            case 'eeg_short':
                print '<select class="filter" id="filter-' . $column_name . '" name="filter-' . $column_name . '" onchange="JaxonInteractives.dashboard_set_filter(' . "'" . $column_name . "'" . ', document.getElementById(' . "'filter-" . $column_name . "'" . ').value);">';
                $options_arr = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_TENANTS'], 'shortname', null, null, 'shortname', 'ASC');

                if($filter_value == 'null') $selected = 'selected'; else $selected = '';
                print '<option ' . $selected . ' value="">&nbsp;</option>';

                foreach($options_arr as $option)
                {
                    if($filter_value == $option['id'])  $selected = 'selected';     else    $selected = '';
                    print '<option ' . $selected . ' value="' . $option['id'] . '">' . $option['shortname'] . '</option>';
                }
                print "</select>";
                break;

            case 'type':
                print '<select class="filter" id="filter-' . $column_name . '" name="filter-' . $column_name . '" onchange="JaxonInteractives.dashboard_set_filter(' . "'" . $column_name . "'" . ', document.getElementById(' . "'filter-" . $column_name . "'" . ').value);">';
                $options_arr = ['individual' => 'Privatperson', 'company' => 'Unternehmen', 'agriculture' => 'Landwirtschaft'];

                if($filter_value == 'null') $selected = 'selected'; else $selected = '';
                print '<option ' . $selected . ' value="">&nbsp;</option>';

                foreach($options_arr as $key => $value)
                {
                    if($filter_value == $key)  $selected = 'selected';     else    $selected = '';
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

            case 'banking_consent':
            case 'network_consent':
            case 'bylaws_consent':
            case 'gdpr_consent':
            case 'tos_consent':
                if($registration_arr[$compute_type] != NULL && is_numeric($registration_arr[$compute_type]))
                {
                    return "Ja";
                }
                else
                {
                    return "Nein";
                }

            case 'salestax':
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

            case 'sigma_supplying_meters':
                $number_of_supplying_meters = $this->db->get_rowcount_by_field_value_extended($this->config->user['DBTABLE_METERS'],'registration_id',$registration_arr['id'], $this->config->user['DBTABLE_METERS'] . ".meter_type = 'supplier'");
                return $number_of_supplying_meters;

            case 'sigma_consuming_meters':
                $number_of_consuming_meters = $this->db->get_rowcount_by_field_value_extended($this->config->user['DBTABLE_METERS'],'registration_id',$registration_arr['id'], $this->config->user['DBTABLE_METERS'] . ".meter_type = 'supplier'");
                return $number_of_consuming_meters;

            case 'sigma_supplying_kwh':
                $supplying_meters_arr = $this->db->get_columns_by_column_value($this->config->user['DBTABLE_METERS'],'meter_power', 'registration_id', $registration_arr['id'], null, null, null, 'AND ' . $this->config->user['DBTABLE_METERS'] . ".meter_type = 'supplier'");
                $total_meter_power = 0;
                foreach($supplying_meters_arr as $meter_data)
                {
                    if(is_numeric($meter_data['meter_power']))
                    {
                        $total_meter_power += $meter_data['meter_power'];
                    }
                }
                return $total_meter_power . ' kWh';


            case 'sigma_storage_kwh':
                $storages_arr = $this->db->get_columns_by_column_value($this->config->user['DBTABLE_STORAGES'],'storage_capacity', 'registration_id', $registration_arr['id']);
                $total_capacity = 0;
                foreach($storages_arr as $storage_data)
                {
                    if(is_numeric($storage_data['storage_capacity']))
                    {
                        $total_capacity += $storage_data['storage_capacity'];
                    }
                }
                return $total_capacity . ' kWh';
        }
    }

}
