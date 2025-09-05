<?php

include_once('view.inc.php');

class VIEW_DEFAULT extends VIEW
{
    public function view_render()
    {
        if(isset($_SESSION['tenant']) && $_SESSION['tenant'] != '')
        {
            $tenant_info = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_TENANTS'], 'id', $_SESSION['tenant'], $limit=1);
            $tenant_info = $tenant_info[0];
            print '
                <header id="header">
                    <h1>' . $tenant_info['fullname'] . '</h1>
                    <p>' . $tenant_info['slogan'] . '<br /></p>
        
                    <p style="color:white">Womit k&ouml;nnen wir dir helfen?</p>
                </header>
        
                <div class="button_container">
                    <button type="button" class="mainbtn" id="btn_enroll_new" onClick="location.href=\'?join\'"><img src="images/noun_bio_food_energy.png" alt="Join EEG" id="join_eeg" style="height:60px"><br />Ich m&ouml;chte dieser EEG beitreten</button>
                    <button type="button" class="mainbtnyellow" id="btn_enroll_progress" onClick="location.href=\'?lookup\'"><img src="images/noun_progress.png" alt="View Registration" id="lookup_eeg" style="height:60px"><br />Ich m&ouml;chte meine Daten abrufen</button>
           ';

            if(isset($_SESSION['backend_authenticated']) && $_SESSION['backend_authenticated'] != null)
            {
                print '<button type="button" class="mainbtnred" id="btn_manage" onClick="location.href=\'?manage\'"><img src="images/noun_manage.png" alt="Management" id="manage_eeg" style="height:60px"><br />Ich m&ouml;chte diese EEG verwalten</button>';
                print '<button type="button" class="mainbtnred" id="btn_manage" onClick="location.href=\'?fastjoin\'"><img src="images/noun_analogue.png" alt="Fast Join" id="manage_eeg" style="height:60px"><br />Analoge Registrierung eingeben</button>';
                print '<button type="button" class="mainbtnred" id="btn_manage" onClick="location.href=\'?update\'"><img src="images/noun_dbupdate.png" alt="Update" id="manage_eeg" style="height:60px"><br />Update</button>';
            }

            print '
                </div>
            ';
        }
        else
        {
            print '
            <header id="header">
                <h1>Willkommen im Energiegemeinschaftsportal</h1>
                <p>Zur Zeit hast du keine bestimmte Energiegemeinschaft ausgew&auml;hlt, daher ist eine Registrierung nicht m&ouml;glich. Wenn du &uuml;ber Zugangsdaten verf&uuml;gst, kannst du dennoch deine bestehenden Daten abrufen.<br /></p>
    
            </header>
    
            <div class="button_container">
                <button type="button" class="mainbtn" id="btn_enroll_progress" onClick="location.href=\'?lookup\'"><img src="images/noun_progress.png" alt="View Registration" id="lookup_eeg" style="height:60px"><br />Ich m&ouml;chte meine Daten abrufen</button>
            </div>
            ';
        }
    }

}
