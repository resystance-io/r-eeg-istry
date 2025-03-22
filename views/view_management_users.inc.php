<?php

include_once('view.inc.php');
class VIEW_MANAGEMENT_USERS extends VIEW
{
    function view_render()
    {
        ?>

        <!--<input type="button" value="JAXON FOO TEST" onclick="jaxon_foo()" /><br />-->
        <header id="header">
            <h1>R:EEG:ISTRY | Management</h1>
            <p>BenutzerInnen verwalten<br /></p>
        </header>

        <?php

        print "<br />&nbsp;<br />&nbsp;<br />";

        if(isset($_REQUEST['userid']) && $_REQUEST['userid'] != '')
        {
            print "USER ID: " . $_REQUEST['userid'];
        }
        else
        {
            $this->view_render_users();
        }
    }

    function view_render_user()
    {

    }

    function view_render_users()
    {
        print '
            <div class="table-container">
              <table class="table">
                <thead>
                  <tr>
                    <th>Benutzername</th>
                    <th>Vorname</th>
                    <th>Nachname</th>
                    <th>Zugeordnete EEG(s)</th>
                    <th>Aktionen</th>
                  </tr>
                </thead>
                <tbody>
        ';

        $users = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_DASHBOARD_USERS']);
        foreach($users as $user)
        {
            $user_tenants = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_DASHBOARD_USERS_X_TENANTS'], 'user_id', $user['id']);
            $tenant_scope = '';
            foreach($user_tenants as $user_tenant)
            {
                $tenant_shortname = $this->db->get_column_by_column_value($this->config->user['DBTABLE_TENANTS'], 'shortname', 'id', $user_tenant['id']);
                if($tenant_scope != '') $tenant_scope .= ', ';
                $tenant_scope .= $tenant_shortname;
            }
            if($tenant_scope == '') $tenant_scope = '-';

            print '<tr>
                    <td>' . $user['username'] . '</td>
                    <td>' . $user['firstname'] . '</td>
                    <td>' . $user['lastname'] . '</td>
                    <td>' . $tenant_scope . '</td>
                    <td>
                        <a href="/?manage_users&userid=' . $user['id'] . '"><i class="fa fa-user-edit"></i></a>
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