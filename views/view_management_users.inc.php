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

        print "<br />";

        if(isset($_REQUEST['add']))
        {
            $this->view_render_create();
        }
        elseif(isset($_REQUEST['rmv']))
        {
            $this->view_render_delete();
        }
        elseif(isset($_REQUEST['userid']) && $_REQUEST['userid'] != '')
        {
            $this->view_render_user($_REQUEST['userid']);
        }
        else
        {
            $this->view_render_users();
        }
    }

    function view_render_user($userid)
    {
        $firstname = $this->db->get_column_by_column_value($this->config->user['DBTABLE_DASHBOARD_USERS'], 'firstname', 'id', $userid);
        $lastname = $this->db->get_column_by_column_value($this->config->user['DBTABLE_DASHBOARD_USERS'], 'lastname', 'id', $userid);
        $username = $this->db->get_column_by_column_value($this->config->user['DBTABLE_DASHBOARD_USERS'], 'username', 'id', $userid);

        print "<h2>$firstname $lastname [$username]</h2>";
        print "<br />";
        print "<h3>Passwort &auml;ndern</h3>";
        print "<div class=\"form-container\" style=\"min-width:960px; width:960px;\">";
        print "    
                       
        ";

        print "</div><br />";
    }

    function view_render_delete()
    {
        if(isset($_REQUEST['preaction']) && $_REQUEST['preaction'] == 'rmv')
        {
            $this->db->update_column_by_column_values($this->config->user['DBTABLE_DASHBOARD_USERS'], 'deleted', 'y', 'id', $_REQUEST['userid']);
            print '<script>self.location.href="/?manage_users";</script>';
        }

        print "<h3>Benutzeraccount l&ouml;schen</h3>";
        print "<div class=\"form-container\" style=\"min-width:960px; width:960px;\">";
        print "    
                   Soll der Benutzeraccount tats&auml;chlich gel&ouml;scht werden?<br />
                   Dieser Benutzername kann nicht erneut registriert werden(!)<br />
        ";
        print "</div><br />";

        print '<table class="navigation" style="width:400px;">
                <thead>
                  <tr>
                    <th class="" style="cursor:pointer; width:200px;" onclick="self.location.href=\'?manage_users&rmv&preaction=rmv&userid=' . $_REQUEST['userid'] . '\'"><i class="fa fa-trash"></i>&nbsp;&nbsp;Ja, ich bin sicher!</th>
                    <th class="" style="cursor:pointer; width:200px;" onclick="self.location.href=\'?manage_users\'"><i class="fa fa-stop"></i>&nbsp;&nbsp;Abbrechen</th>
                  </tr>
                </thead>
               </table>
               <br />
        ';
    }

    function view_render_create()
    {
        if(isset($_REQUEST['preaction']) && $_REQUEST['preaction'] == 'add')
        {
            $existing_user_count = $this->db->get_rowcount_by_field_value_extended($this->config->user['DBTABLE_DASHBOARD_USERS'], 'username', $_REQUEST['username']);
            if($existing_user_count == 0)
            {
                if($_REQUEST['password'] == $_REQUEST['password_repeat'])
                {
                    $salt = rand(1111111111111111, 9999999999999999);
                    $insert_arr = [
                        'firstname' => $_REQUEST['firstname'],
                        'lastname' => $_REQUEST['lastname'],
                        'username' => $_REQUEST['username'],
                        'passphrase' => hash('sha256', $_REQUEST['password'] . $salt) . ':' . $salt
                    ];

                    $this->db->insert_row_with_array($this->config->user['DBTABLE_DASHBOARD_USERS'], $insert_arr);
                    print '<script>self.location.href="/?manage_users";</script>';
                }
                else
                {
                    print '<script>alert("Account konnte nicht angelegt werden:\nPasswörter sind nicht identisch!");</script>';
                }
            }
            else
            {
                print '<script>alert("Account konnte nicht angelegt werden:\nBenutzername existiert bereits!");</script>';
            }
        }

        if(isset($_REQUEST['username']))    $prefill_username = $_REQUEST['username']; else $prefill_username = '';
        if(isset($_REQUEST['firstname']))    $prefill_firstname = $_REQUEST['firstname']; else $prefill_firstname = '';
        if(isset($_REQUEST['lastname']))    $prefill_lastname = $_REQUEST['lastname']; else $prefill_lastname = '';

        print "<h3>Neuen Verwaltungsaccount hinzuf&uuml;gen</h3>";
        print "<form action=\"?manage_users\" method=\"post\">";
        print "<input type=\"hidden\" name=\"preaction\" value=\"add\">";
        print "<input type=\"hidden\" name=\"add\" value=\"\">";
        print "<div class=\"form-container\" style=\"min-width:960px; width:960px;\">
                <div style=\"padding:8px;line-height:40px;\">Benutzername<input type=\"text\" onfocus=\"this.select()\" name=\"username\" id=\"username\" value=\"$prefill_username\" /></div>
                <div style=\"padding:8px;line-height:40px;\">Vorname<input type=\"text\" onfocus=\"this.select()\" name=\"firstname\" id=\"firstname\" value=\"$prefill_firstname\"/></div>
                <div style=\"padding:8px;line-height:40px;\">Nachname<input type=\"text\" onfocus=\"this.select()\" name=\"lastname\" id=\"lastname\" value=\"$prefill_lastname\"/></div>
                <div style=\"padding:8px;line-height:40px;\">Passwort<input type=\"password\" onfocus=\"this.select()\" name=\"password\" id=\"password\" /></div>
                <div style=\"padding:8px;line-height:40px;\">Passwort (Wiederholung)<input type=\"password\" onfocus=\"this.select()\" name=\"password_repeat\" id=\"password_repeat\" /></div>
                <div style=\"padding:8px;line-height:40px;\"><input type=\"submit\" value=\"Account anlegen\" /></div>
               </div>
               </form>
        ";

    }

    function view_render_users()
    {
        print '<table class="navigation green-gradient" style="width:160px;">
                <thead>
                  <tr>
                    <th class="" style="cursor:pointer" onclick="self.location.href=\'?manage_users&add\'"><i class="fa fa-plus"></i>&nbsp;Neu</th>
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
                    <th>Benutzername</th>
                    <th>Vorname</th>
                    <th>Nachname</th>
                    <th>Zugeordnete EEG(s)</th>
                    <th>Aktionen</th>
                  </tr>
                </thead>
                <tbody>
        ';

        $users = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_DASHBOARD_USERS'], 'deleted', 'n');
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
                        <a href="/?manage_dashboards&userid=' . $user['id'] . '"><i class="fa fa-table"></i></a>
                        &nbsp;&nbsp;&nbsp;
                        <a href="/?manage_users&rmv&userid=' . $user['id'] . '"><i class="fa fa-trash"></i>
                        
                    </td>
                   </tr>';
        }

        print ' </tbody>
              </table>
            </div>
        ';

    }

}