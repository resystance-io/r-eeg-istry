<?php

class VIEW_LOOKUP
{

    private $config;
    private $object_broker;

    public function __construct($object_broker, $database = NULL)
    {

        $this->object_broker = $object_broker;
        $this->config = $object_broker->instance['config'];
    }

    public function view_render()
    {

        switch ($_REQUEST['lookup'])
        {
            case "profile":
                $this->view_render_lookup();
                break;

            default:
                print '
                    <header id="header">
                        <h1>Erneuerbare Energiegemeinschaft VIERE</h1>
                        <p>Beitrittsstatus- u. Datenabfrage<br /></p>
        
                        <p style="color:white">Bitte melde dich mit deinen Zugangsdaten an</p>
                    </header>
                ';
                $this->view_render_login();
                break;
        }
    }

    private function view_render_login()
    {
        print "<div class=\"form-container\">";

        print 'E-Mail Adresse:<br><input type="text" onfocus="this.select()" name="email" id="email" />';
        print '<br />';
        print 'Passwort:<br><input type="password" onfocus="this.select()" name="password" id="password" />';
        print "</div><br />";
        print '<button type="button" class="defaultbtn" id="btn_authenticate" onClick="JaxonInteractives.authenticate();">Anmelden</button>';

    }
}

?>