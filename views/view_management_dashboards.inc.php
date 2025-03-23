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
            <p>Dashboards verwalten<br /></p>
        </header>

        <?php

        print "<br />";

        if(isset($_REQUEST['dashboard_id']) && $_REQUEST['dashboard_id'] != '')
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

        print '<th>ID</th>';

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
                </thead>
                <tbody>
        ';

        print '<tr>';
        print '    <td>Fixiert</td>';

        $column_count = 1;
        foreach($columns as $column)
        {
            print '<td class="lightup" style="text-align:center">';
            if($column_count > 1)   print '            <a style="color:black" href="/?manage_dashboards&dashboard_id=' . $dashboard_id. '&column=' . $column['layout_id'] . '&move=left"><i style="margin-top:4px;font-size:14pt" class="fa fa-caret-left fa-pull-left"></i></a>';
            print '            <a style="color:darkred" href="/?manage_dashboards&dashboard_id=' . $dashboard_id. '&column=' . $column['layout_id'] . '&move=trash"><i class="fa fa-trash"></i></a>';
            if($column_count < count($columns))   print '            <a style="color:black" href="/?manage_dashboards&dashboard_id=' . $dashboard_id. '&column=' . $column['layout_id'] . '&move=right"><i style="margin-top:4px;font-size:14pt" class="fa fa-caret-right fa-pull-right"></i></a>';
            print '</td>';
            $column_count++;
        }

        print '</tr>';
        print '
                </tbody>
              </table>
              <br />
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
        print '
            <div class="table-container">
              <table class="table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Bezeichnung</th>
                    <th>Aktionen</th>
                  </tr>
                </thead>
                <tbody>
        ';

        $dashboards = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_DASHBOARDS'], 'user_id', $_REQUEST['userid']);
        foreach($dashboards as $dashboard)
        {
            print '<tr>
                    <td>' . $dashboard['id'] . '</td>
                    <td>' . $dashboard['name'] . '</td>
                    <td>
                        <a href="/?manage_dashboards&dashboard_id=' . $dashboard['id'] . '"><i class="fa fa-edit"></i></a>
                        &nbsp;&nbsp;&nbsp;
                        <i class="fa fa-trash"></i>
                    </td>
                   </tr>';
        }

        print ' </tbody>
              </table>
            </div>
        ';

    }

}