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
                $this->view_render_profile();
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
        if(isset($_SESSION['auth_email']) && isset($_SESSION['auth_email']) != "")
        {
            $username_prefill = $_SESSION['auth_email'];
        }
        else
        {
            $username_prefill = "";
        }

        print "<div class=\"form-container\">";
        print 'E-Mail Adresse:<br><input type="text" onfocus="this.select()" name="auth_email" id="auth_email" value="' . $username_prefill . '" onfocusout="JaxonInteractives.update_credential_cache(' . "'auth_email'" . ', document.getElementById(' . "'auth_email'" . ').value);" />';
        print '<br />';
        print 'Passwort:<br><input type="password" onfocus="this.select()" name="auth_mnemonic" id="auth_mnemonic" value="" onfocusout="JaxonInteractives.update_credential_cache(' . "'auth_mnemonic'" . ', document.getElementById(' . "'auth_mnemonic'" . ').value);" />';
        print "</div><br />";
        print '<button type="button" class="defaultbtn" id="btn_authenticate" onClick="JaxonInteractives.authenticate();">Anmelden</button>';

    }

    private function view_render_profile()
    {
        if(!filter_var($_SESSION['authenticated'], FILTER_VALIDATE_EMAIL))
        {
            print "<script>window.location.href='/';</script>";
            return false;
        }

        print '
                    <header id="header">
                        <h1>Erneuerbare Energiegemeinschaft VIERE</h1>
                        <p>Beitrittsstatus- u. Datenabfrage<br /></p>
        
                        <p style="color:white">' . $_SESSION['authenticated'] . ' <button type="button" class="" style="background-color:darkred;margin:9px;" id="btn_deauthenticate" onClick="JaxonInteractives.deauthenticate();">Abmelden</button></p>
                    </header>
        ';
    }
}

?>