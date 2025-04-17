<?php

include_once('view.inc.php');
class VIEW_MANAGEMENT_DASHBOARDS extends VIEW
{
    function view_render()
    {
        ?>

        <!--<input type="button" value="JAXON FOO TEST" onclick="jaxon_foo()" /><br />-->
        <header id="header">
            <h1>R:EEG:ISTRY | Management</h1>
            <p><A href="/?manage_users"><i class="fa fa-arrow-alt-circle-left"></i></A>&nbsp;Dashboards verwalten<br /></p>
        </header>

        <?php

        print "<br />";

        if(isset($_REQUEST['add']))
        {
            $this->view_render_create();
        }
        elseif(isset($_REQUEST['rmv']))
        {
            $this->view_render_delete();
        }
        elseif(isset($_REQUEST['dashboard_id']) && $_REQUEST['dashboard_id'] != '')
        {
            $this->view_render_dashboard($_REQUEST['dashboard_id']);
        }
        else
        {
            $this->view_render_dashboards();
        }
    }

    function view_render_dashboard($dashboard_id)
    {
        $admin = $this->db->get_column_by_column_value($this->config->user['DBTABLE_DASHBOARD_USERS'], 'admin', 'id', $_SESSION['backend_authenticated']);
        $dashboard_owner = $this->db->get_column_by_column_value($this->config->user['DBTABLE_DASHBOARDS'], 'user_id', 'id', $dashboard_id);

        if($admin == 'n' && $dashboard_owner != $_SESSION['backend_authenticated'])
        {
            print "<script>self.location.href='/?manage_dashboards';</script>";
            exit;
        }

        if(isset($_REQUEST['preaction']) && $_REQUEST['preaction'] == 'add_column' && isset($_REQUEST['column']) && isset($_REQUEST['dashboard_id']) && $_REQUEST['dashboard_id'] == $dashboard_id)
        {
            $highest_sorted_row = $this->db->get_rows_by_column_value_extended($this->config->user['DBTABLE_DASHBOARD_LAYOUT'], 'dashboard', $dashboard_id, 1, 'sort', 'DESC');
            if(count($highest_sorted_row) > 0)
            {
                $new_sort = $highest_sorted_row[0]['sort'] + 1;
            }
            else
            {
                $new_sort = 0;
            }

            $insert_array = [
                'dashboard' => $dashboard_id,
                'data' => $_REQUEST['column'],
                'sort' => $new_sort
            ];

            $this->db->insert_row_with_array($this->config->user['DBTABLE_DASHBOARD_LAYOUT'], $insert_array);
            print "<script>self.location.href='/?manage_dashboards&dashboard_id=$dashboard_id';</script>";

        }

        $dashboard_filterconfig = $this->db->get_column_by_column_value($this->config->user['DBTABLE_DASHBOARDS'], 'filterconfig', 'id', $dashboard_id);
        $dashboard_filterconfig = json_decode(base64_decode($dashboard_filterconfig), true);
        $_SESSION['dashboard']['filterconfig'] = $dashboard_filterconfig;

        if(isset($_REQUEST['column']) && isset($_REQUEST['move']))
        {
            switch($_REQUEST['move'])
            {
                case 'left':
                    $source_layout_id = $_REQUEST['column'];
                    $source_layout_position = $this->db->get_column_by_column_values($this->config->user['DBTABLE_DASHBOARD_LAYOUT'], 'sort', 'dashboard', $dashboard_id, 'id', $source_layout_id);

                    $previous_layout_row = $this->db->get_rows_by_column_value_extended($this->config->user['DBTABLE_DASHBOARD_LAYOUT'], 'dashboard', $dashboard_id, 1, 'sort', 'DESC', $this->config->user['DBTABLE_DASHBOARD_LAYOUT'] . ".sort < $source_layout_position");
                    if(count($previous_layout_row) > 0)
                    {
                        $target_layout_id = $previous_layout_row[0]['id'];
                        $target_layout_position = $previous_layout_row[0]['sort'];

                        $this->db->update_column_by_column_values($this->config->user['DBTABLE_DASHBOARD_LAYOUT'], 'sort', $target_layout_position, 'id', $source_layout_id);
                        $this->db->update_column_by_column_values($this->config->user['DBTABLE_DASHBOARD_LAYOUT'], 'sort', $source_layout_position, 'id', $target_layout_id);
                        //print "DEBUG: Moving $source_layout_id ($source_layout_position -> $target_layout_position), $target_layout_id ($target_layout_position -> $source_layout_position)<br />";
                        print "<script>self.location.href='/?manage_dashboards&dashboard_id=$dashboard_id';</script>";
                    }
                    else
                    {
                        print "DEBUG: No previous layout row found<br />";
                    }
                    break;

                case 'right':
                    $source_layout_id = $_REQUEST['column'];
                    $source_layout_position = $this->db->get_column_by_column_values($this->config->user['DBTABLE_DASHBOARD_LAYOUT'], 'sort', 'dashboard', $dashboard_id, 'id', $source_layout_id);

                    $next_layout_row = $this->db->get_rows_by_column_value_extended($this->config->user['DBTABLE_DASHBOARD_LAYOUT'], 'dashboard', $dashboard_id, 1, 'sort', 'ASC', $this->config->user['DBTABLE_DASHBOARD_LAYOUT'] . ".sort > $source_layout_position");
                    if(count($next_layout_row) > 0)
                    {
                        $target_layout_id = $next_layout_row[0]['id'];
                        $target_layout_position = $next_layout_row[0]['sort'];

                        $this->db->update_column_by_column_values($this->config->user['DBTABLE_DASHBOARD_LAYOUT'], 'sort', $target_layout_position, 'id', $source_layout_id);
                        $this->db->update_column_by_column_values($this->config->user['DBTABLE_DASHBOARD_LAYOUT'], 'sort', $source_layout_position, 'id', $target_layout_id);
                        //print "DEBUG: Moving $source_layout_id ($source_layout_position -> $target_layout_position), $target_layout_id ($target_layout_position -> $source_layout_position)<br />";
                        print "<script>self.location.href='/?manage_dashboards&dashboard_id=$dashboard_id';</script>";
                    }
                    else
                    {
                        print "DEBUG: No following layout row found<br />";
                    }
                    break;

                case 'trash':
                    $this->db->delete_row_by_id($this->config->user['DBTABLE_DASHBOARD_LAYOUT'], $_REQUEST['column']);
                    print "<script>self.location.href='/?manage_dashboards&dashboard_id=$dashboard_id';</script>";
                    break;
            }
        }

        $dashboard_name = $this->db->get_column_by_column_value($this->config->user['DBTABLE_DASHBOARDS'], 'name', 'id', $dashboard_id);
        $dashboard_colorconfig = $this->db->get_column_by_column_value($this->config->user['DBTABLE_DASHBOARDS'], 'colorconfig', 'id', $dashboard_id);
        $column_configs = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_DASHBOARD_COLUMNS'], 'visible', 'y');
        $layout_columns = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_DASHBOARD_LAYOUT'], 'dashboard', $dashboard_id, NULL, 'sort', 'ASC');

        print "<h2><i class=\"fa fa-table\"></i>&nbsp;&nbsp;$dashboard_name</h2>";
        print "<br />";
        print "<h3>Layout</h3>";
        print '
              <table class="table" style="width:100%">
                <thead>
                  <tr>
        ';

        print '<th style="width: 60px">&nbsp;</th>';

        $columns = []; // store every column configuration we get for this dashboard layout to avoid multiple lookups
        $column_count = 0;
        foreach($layout_columns as $layout_column)
        {
            $column_config = array_values(array_filter($column_configs, function($column) use ($layout_column)
            {
                return $column['name'] === $layout_column['data'];
            }));
            $columns[$column_count] = $column_config[0];
            $columns[$column_count]['layout_id'] = $layout_column['id'];

            if($columns[$column_count]['sortable'] == 'y')
            {
                print '<th>' . $columns[$column_count]['nicename'] . '<i style="margin-top:6px;color:slategrey" class="fa fa-sort fa-pull-right"></i></th>';
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

        print '<td style="color:white"><i class="fa fa-filter"></i></td>';
        foreach($columns as $column)
        {
            if($column['filterable'] == 'y' && $column['compute'] == null)
            {
                if($_SESSION['dashboard']['filterconfig'][$column['name']] != null) $filter_value = $_SESSION['dashboard']['filterconfig'][$column['name']]; else $filter_value = '';
                print '<td>
                            <input type="text" id="filter-' . $column['name'] . '" onclick="this.select();" onfocusout="JaxonInteractives.dashboard_set_filter(' . "'" . $column['name'] . "'" . ', document.getElementById(' . "'filter-" . $column['name'] . "'" . ').value);" class="filter" name="filter-' . $column['name'] . '" value="' . $filter_value . '">
                            <script>
                                input = document.getElementById("filter-' . $column['name'] . '");
                                input.addEventListener("keydown", function(event) {
                                  if (event.key === "Enter") {
                                      JaxonInteractives.dashboard_set_filterconfig(' . "'" . $column['name'] . "'" . ', document.getElementById(' . "'filter-" . $column['name'] . "'" . ').value);
                                }
                              });
                            </script>
                       </td>';
            }
            elseif($column['filterable'] == 'y' && $column['compute'] != null)
            {
                print '<td>';
                $filter_value = $_SESSION['dashboard']['filterconfig'][$column['name']] ?? null;
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

        print '<tr class="stategray">';
        print '    <td>&nbsp;</td>';

        $used_columns = [];
        foreach($columns as $column_count => $column)
        {
            print '<td class="lightup" style="text-align:center">';
            if($column_count > 0)   print '            <a style="color:black" href="/?manage_dashboards&dashboard_id=' . $dashboard_id. '&column=' . $column['layout_id'] . '&move=left"><i style="margin-top:4px;font-size:14pt" class="fa fa-caret-left fa-pull-left"></i></a>';
            print '            <a style="color:darkred" href="/?manage_dashboards&dashboard_id=' . $dashboard_id. '&column=' . $column['layout_id'] . '&move=trash"><i class="fa fa-trash"></i></a>';
            if($column_count < count($columns) - 1)   print '            <a style="color:black" href="/?manage_dashboards&dashboard_id=' . $dashboard_id. '&column=' . $column['layout_id'] . '&move=right"><i style="margin-top:4px;font-size:14pt" class="fa fa-caret-right fa-pull-right"></i></a>';
            print '</td>';
            $used_columns[$column['name']] = true;
        }

        print '</tr>';
        print '
                </tbody>
              </table>
        ';

        print '
                <div class="dataTables_pagesize" style="float:left">
                    <div style="margin-top:1px;padding-left:8px;float:left">Farbschema: </div>
                    <select onchange="JaxonInteractives.dashboard_set_colorconfig(this.value, ' . "'" . $_REQUEST['dashboard_id'] . "'" . ');"">
        ';

        if($dashboard_colorconfig == 'y') $selected = "selected=\"selected\""; else $selected = "";
        print "         <option value=\"y\" $selected>Monokai</option>";

        if($dashboard_colorconfig == 'n') $selected = "selected=\"selected\""; else $selected = "";
        print "         <option value=\"n\" $selected>Gray</option>";

        print '
                    </select>
                </div><br />&nbsp;<br />&nbsp;<br />&nbsp;<br />
        ';

        print "<h4>Verf&uuml;gbare Felder</h4>";
        print "<form action=\"/?manage_dashboards\" method=\"post\">";
        print "<input type=\"hidden\" name=\"dashboard_id\" value=\"$dashboard_id\" />";
        print "<input type=\"hidden\" name=\"preaction\" value=\"add_column\" />";
        print "<div class=\"form-container\" style=\"min-width:500px; width:500px;\">";
        print '<select class="" id="column" name="column" style="width:300px;float:left">';
        $options_arr = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_DASHBOARD_COLUMNS'], 'visible', 'y');

        foreach($options_arr as $option)
        {
            print '<option value="' . $option['name'] . '">' . $option['nicename'] . '</option>';
        }
        print "</select> <input type=\"submit\" value=\"Hinzuf&uuml;gen\" style=\"float:left\" />";
        print "<br /></div></form><br />";

    }

    function view_render_dashboards()
    {
        $admin = $this->db->get_column_by_column_value($this->config->user['DBTABLE_DASHBOARD_USERS'], 'admin', 'id', $_SESSION['backend_authenticated']);

        if(isset($_REQUEST['userid']))
        {
            $link_uid = '&userid=' . $_REQUEST['userid'];
            $user_id = $_REQUEST['userid'];
        }
        else
        {
            $link_uid = '';
            $user_id = $_SESSION['backend_authenticated'];
        }
        print '<table class="navigation green-gradient" style="width:160px;">
                <thead>
                  <tr>
                    <th class="" style="cursor:pointer" onclick="self.location.href=\'?manage_dashboards&add' . $link_uid . '\'"><i class="fa fa-plus"></i>&nbsp;Neu</th>
                  </tr>
                </thead>
               </table>
               <br />
        ';

        print '
            <div class="table-container">
              <table class="table">
                <thead>
                  <tr>
                    <th style="width:60px">&nbsp;</th>
                    <th>Bezeichnung</th>
        ';

        if($admin == 'y')   print '     <th style="width:120px">BesitzerIn</th>';

        print '
                    <th style="width:120px">Aktionen</th>
                  </tr>
                </thead>
                <tbody>
        ';

        if($admin == 'y')
        {
            $dashboards = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_DASHBOARDS']);
        }
        else
        {
            $dashboards = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_DASHBOARDS'], 'user_id', $user_id);
        }

        foreach($dashboards as $dashboard)
        {
            print '<tr class="stategray">
                    <td><i class="fa fa-table"></i></td>
                    <td>' . $dashboard['name'] . '</td>
            ';

            if($admin == 'y')
            {
                $username = $this->db->get_column_by_column_value($this->config->user['DBTABLE_DASHBOARD_USERS'], 'username', 'id', $dashboard['user_id']);
                print '     <td>' . $username . '</td>';
            }

            print '
                    <td>
                        <a href="/?manage_dashboards&dashboard_id=' . $dashboard['id'] . $link_uid . '"><i class="fa fa-edit"></i></a>
                        &nbsp;&nbsp;&nbsp;
                        <a href="/?manage_dashboards&rmv&dashboard_id=' . $dashboard['id'] . $link_uid . '"><i class="fa fa-trash"></i></a>
                    </td>
                   </tr>';
        }

        print ' </tbody>
              </table>
            </div>
        ';

    }

    function render_computed_filter_column($compute_type, $column_name, $filter_value=null)
    {
        switch($compute_type)
        {
            case 'eeg_short':
                print '<select class="filter" id="filter-' . $column_name . '" name="filter-' . $column_name . '" onchange="JaxonInteractives.dashboard_set_filterconfig(' . "'" . $column_name . "'" . ', document.getElementById(' . "'filter-" . $column_name . "'" . ').value, ' . "'" . $_REQUEST['dashboard_id'] . "'" . ');">';
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
                print '<select class="filter" id="filter-' . $column_name . '" name="filter-' . $column_name . '" onchange="JaxonInteractives.dashboard_set_filterconfig(' . "'" . $column_name . "'" . ', document.getElementById(' . "'filter-" . $column_name . "'" . ').value, ' . "'" . $_REQUEST['dashboard_id'] . "'" . ');">';
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

            case 'state':
                print '<select class="filter" id="filter-' . $column_name . '" name="filter-' . $column_name . '" onchange="JaxonInteractives.dashboard_set_filterconfig(' . "'" . $column_name . "'" . ', document.getElementById(' . "'filter-" . $column_name . "'" . ').value, ' . "'" . $_REQUEST['dashboard_id'] . "'" . ');">';
                $options_arr = ['new' => 'Neu', 'onboarding' => "Onboarding", 'active' => "Aktiv", 'suspended' => "Gesperrt", 'deactivated' => "Deaktiviert", 'refused' => "Abgelehnt"];

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

    function view_render_create()
    {
        if(isset($_REQUEST['preaction']) && $_REQUEST['preaction'] == 'add')
        {
            if(isset($_REQUEST['userid']))
            {
                $user_id = $_REQUEST['userid'];
                $userid_link = '&userid=' . $_REQUEST['userid'];
            }
            else
            {
                $user_id = $_SESSION['backend_authenticated'];
                $userid_link = '';
            }

            $insert_arr = [
                'user_id' => $user_id,
                'name' => $_REQUEST['nicename'],
            ];

            $this->db->insert_row_with_array($this->config->user['DBTABLE_DASHBOARDS'], $insert_arr);
            print '<script>self.location.href="/?manage_dashboards' . $userid_link . '";</script>';
        }


        print "<h3>Neues Dashboard hinzuf&uuml;gen</h3>";
        print "<form action=\"?manage_dashboards\" method=\"post\">";
        print "    <input type=\"hidden\" name=\"preaction\" value=\"add\">";
        print "    <input type=\"hidden\" name=\"add\" value=\"\">";
        if(isset($_REQUEST['userid']))
        {
            print " <input type=\"hidden\" name=\"userid\" value=\"" . $_REQUEST['userid'] . "\">";
        }

        print "<div class=\"form-container\" style=\"min-width:960px; width:960px;\">
                <div style=\"padding:8px;line-height:40px;\">Bezeichnung<input type=\"text\" onfocus=\"this.select()\" name=\"nicename\" id=\"nicename\" value=\"Neues Dashboard\" /></div>
                <div style=\"padding:8px;line-height:40px;\"><input type=\"submit\" value=\"Dashboard anlegen\" /></div>
               </div>
               </form>
        ";

    }

    function view_render_delete()
    {
        $admin = $this->db->get_column_by_column_value($this->config->user['DBTABLE_DASHBOARD_USERS'], 'admin', 'id', $_SESSION['backend_authenticated']);
        $dashboard_owner = $this->db->get_column_by_column_value($this->config->user['DBTABLE_DASHBOARDS'], 'user_id', 'id', $_REQUEST['dashboard_id']);

        if($admin == 'n' && $dashboard_owner != $_SESSION['backend_authenticated'])
        {
            print "<script>self.location.href='/?manage_dashboards';</script>";
            exit;
        }

        if(isset($_REQUEST['userid']))
        {
            $userid_link = '&userid=' . $_REQUEST['userid'];
        }
        else
        {
            $userid_link = '';
        }

        if(isset($_REQUEST['preaction']) && $_REQUEST['preaction'] == 'rmv')
        {
            $this->db->delete_row_by_id($this->config->user['DBTABLE_DASHBOARDS'], $_REQUEST['dashboard_id']);
            print '<script>self.location.href="/?manage_dashboards' . $userid_link . '";</script>';
        }

        $dashboard_name = $this->db->get_column_by_column_value($this->config->user['DBTABLE_DASHBOARDS'], 'name', 'id', $_REQUEST['dashboard_id']);
        print "<h3>Dashboard l&ouml;schen</h3>";
        print "<div class=\"form-container\" style=\"min-width:960px; width:960px;\">";
        print "    
                   Soll das Dashboard <b>$dashboard_name</b> tats&auml;chlich gel&ouml;scht werden?<br />
                   Alle mit dem Dashboard verkn&uuml;pften Filter und Konfigurationen gehen dadurch verloren!<br />
        ";
        print "</div><br />";

        print '<table class="navigation" style="width:400px;">
                <thead>
                  <tr>
                    <th class="" style="cursor:pointer; width:200px;" onclick="self.location.href=\'?manage_dashboards&rmv&preaction=rmv&dashboard_id=' . $_REQUEST['dashboard_id'] . $userid_link . '\'"><i class="fa fa-trash"></i>&nbsp;&nbsp;Ja, ich bin sicher!</th>
                    <th class="" style="cursor:pointer; width:200px;" onclick="self.location.href=\'?manage_dashboards' . $userid_link . '\'"><i class="fa fa-stop"></i>&nbsp;&nbsp;Abbrechen</th>
                  </tr>
                </thead>
               </table>
               <br />
        ';
    }

}