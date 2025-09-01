<?php

include_once('view.inc.php');
class VIEW_MANAGEMENT_UPDATES extends VIEW
{
    function view_render()
    {
        ?>

        <header id="header">
            <h1>R:EEG:ISTRY | UPDATE</h1>
        </header>

        <?php

        print "<br />";

        include(dirname(__FILE__) . '/../configs/migrations.php');
        $db_config = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_TEMPORARY'], 'feature', 'database_version');
        if(isset($db_config[0]))
        {
            $installed_database_version = $db_config[0]['value1'];
        }
        else
        {
            $installed_database_version = 0;
        }
        /** @var int $latest_database_version */

        if(count($database_migrations) > $installed_database_version)
        {
            if (isset($_REQUEST['update_now']))
            {
                print "<b>Aktualisierung wird initialisiert...</b><br />&nbsp;<br /><hr>";

                print "<b>Installiertes Datenbankschema:</b><br />" . hash('crc32', count($database_migrations)) . '.' . count($database_migrations) . "<br />&nbsp;<br />";
                print "<b>Datenbankschema der Codebase:</b><br />" . hash('crc32', $installed_database_version) . ".$installed_database_version<br /><hr>";

                $migration_start = $installed_database_version + 1;
                for ($i = $migration_start; $i <= count($database_migrations); $i++)
                {
                    print "<b>Durchf&uuml;hrung von Migration " . hash('crc32', $i) . "...</b><br />";
                    print "<blockquote>";
                    foreach($database_migrations[$i] as $migration_index => $migration_step)
                    {
                        if($migration_index > 0)    print "<br />";
                        print "Adaption " . $migration_index + 1 . " von " . count($database_migrations[$i]) . "<br />\n";
                        print "&bull; " . hash('sha256', $migration_step) . "... ";
                        $this->db->execute_query($migration_step);
                        print "[confirmed]<br />&nbsp;<br />";
                    }

                    print "</blockquote>";
                }

                $db_config = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_TEMPORARY'], 'feature', 'database_version');
                if(isset($db_config[0]))
                {
                    $installed_database_version = $db_config[0]['value1'];
                }
                else
                {
                    $installed_database_version = 0;
                }

                print "<b>Installiertes Datenbankschema:</b><br />" . hash('crc32', count($database_migrations)) . '.' . count($database_migrations) . "<br />&nbsp;<br />";
                print "<b>Datenbankschema der Codebase:</b><br />" . hash('crc32', $installed_database_version) . ".$installed_database_version<br /><hr>";
                if($installed_database_version < $latest_database_version)
                {
                    print "<br /><br /><br /><center><b>Es konnte nicht alle Aktualisierungen durchgeführt werden.</b><br />Bitte konsultiere die Logs f&uuml;r weitere Informationen.&nbsp;<br />&nbsp;<br />";
                }
                else
                {
                    print "<br /><br /><br /><center>Aktualisierung erfolgreich.<br />&nbsp;<br />";
                }

                print '
                    <div class="button_container">
                        <button type="button" class="mainbtn" style="" id="btn_back" onClick="location.href=' . "'" . "/" . "'" . '"><img src="images/noun_manage.png" alt="Zur&uuml;ck zu den Einstellungen" id="back" style="height: 60px; margin-left: 30px;"><br />Zur&uuml;ck zu den Einstellungen</button>
                    </div>
                    </center>
                ';
            }
            else
            {
                print "<b>Datenbankschema der Codebase:</b><br />" . hash('crc32', count($database_migrations)) . '.' . count($database_migrations) . "<br />&nbsp;<br />";
                print "<b>Installiertes Datenbankschema:</b><br />" . hash('crc32', $installed_database_version) . ".$installed_database_version<br /><hr>";
                print "<br /><br /><br /><center>Die Datenbank muss aktualisiert werden, um die Kompatibilität zur aktuell installierten Version von R-EEG-ISTRY zu gew&auml;hrleisten.<br />&nbsp;<br />";
                print '
                    <div class="button_container">
                        <button type="button" class="mainbtn" style="" id="btn_upgrade" onClick="location.href=' . "'" . "?update_now" . "'" . '"><img src="images/noun_dbupdate.png" alt="Aktualisieren" id="upgrade" style="height: 60px; margin-left: 30px;"><br />Aktualisierung starten</button>
                    </div>
                    </center>
                ';
            }
        }
        else
        {
            print "<b>Datenbankschema der Codebase:</b><br />" . hash('crc32', count($database_migrations)) . '.' . count($database_migrations) . "<br />&nbsp;<br />";
            print "<b></b>Installiertes Datenbankschema:<br />" . hash('crc32', $installed_database_version) . ".$installed_database_version<br /><hr>";
            print "<br /><br /><br /><center>Es ist keine Aktualisierung erforderlich.<br />&nbsp;<br />";
            print '
                    <div class="button_container">
                        <button type="button" class="mainbtn" style="" id="btn_back" onClick="location.href=' . "'" . "/" . "'" . '"><img src="images/noun_manage.png" alt="Zur&uuml;ck zu den Einstellungen" id="back" style="height: 60px; margin-left: 30px;"><br />Zur&uuml;ck zu den Einstellungen</button>
                    </div>
                    </center>
                ';
        }
    }

}