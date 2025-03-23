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
            print "DASHBOARD ID: " . $_REQUEST['dashboard_id'];
        }
        else
        {
            $this->view_render_dashboards();
        }
    }

    function view_render_user()
    {

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

        $dashboards = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_DASHBOARDS'], 'user_id', 1);
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