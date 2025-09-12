<?php
class VIEW
{
    public $object_broker;
    public $config;
    public $db;
    public $session;
    public $mail;
    public $xlsx;
    public $fpdf;

    public function __construct()
    {
        // Instantiate the object broker since it will keep
        // track of all objects later on and will be provided to every object
        // in order to allow easy interaction

        require_once('object_broker.php.inc');
        $this->object_broker = new OBJECT_BROKER();

        // NOW we instantiate objects that are needed in order to get things
        // going on a regular basis, like writers, databases, etc.

        require_once('controllers/controller_config.php.inc');
        $this->config = $this->object_broker->instance['config'] = new CONTROLLER_CONFIG();

        if(isset($this->config->user['debug']) && $this->config->user['debug'] === true)
        {
            error_reporting(-1);
        }
        else
        {
            error_reporting(0);
        }

        require_once('controllers/controller_db.php.inc');
        $this->db = $this->object_broker->instance['db'] = new CONTROLLER_DB($this->object_broker);

        require_once('controllers/controller_session.php.inc');
        $this->session = $this->object_broker->instance['session'] = new CONTROLLER_SESSION($this->object_broker);

        // preconfigure the mail user agent interface class
        require_once('controllers/controller_mua.inc.php');
        //class_alias("codeworxtech\PHPMailerPro\PHPMailerPro", "PHPMailerPro");

        $this->mail = $this->object_broker->instance['email'] = new codeworxtech\PhpMailerPro\PHPMailerPro();
        $this->mail->SetSender($this->config->user['MAIL_FROM']);
        $this->mail->smtpHost     = $this->config->user['MAIL_MTA_ADDRESS'];
        $this->mail->smtpDebug    = $this->config->user['MAIL_DEBUGGING'];
        $this->mail->smtpPort     = $this->config->user['MAIL_MTA_PORT'];
        $this->mail->smtpUsername = $this->config->user['MAIL_MTA_USER'];
        $this->mail->smtpPassword = $this->config->user['MAIL_MTA_PASS'];
        $this->mail->smtpOptions = $this->config->user['MAIL_OPTIONS'];

        require_once('controllers/controller_xlsx.inc.php');
        $this->xlsx = new Shuchkin\SimpleXLSXGen;

        require_once('controllers/controller_fpdf.inc.php');
        $this->fpdf = new FPDF();
    }

    public function view_handle_backend_login()
    {
        if(isset($_SESSION['backend_authenticated']) && $_SESSION['backend_authenticated'] != '')
        {
            $user_deleted = $this->db->get_column_by_column_value($this->config->user['DBTABLE_DASHBOARD_USERS'], 'deleted', 'id', $_SESSION['backend_authenticated']);
            if($user_deleted === 'y')
            {
                return false;
            }
            else
            {
                return true;
            }
        }

        if(isset($_SESSION['auth_backend_username']) && isset($_SESSION['auth_backend_username']) != "")
        {
            $username_prefill = $_SESSION['auth_backend_username'];
        }
        else
        {
            $username_prefill = "";
        }

        print "<div style=\"text-align: center\">";
        print "<br />&nbsp;<br />";
        print "<h2>R:EEG:ISTRY</h2>";
        print "<hr>";
        print "<div class=\"form-control\" style=\"width: 300px; margin-left: auto; margin-right: auto\">";
        print 'Benutzername:<br><input type="text" onfocus="this.select()" name="auth_username" id="auth_username" value="' . $username_prefill . '" />';
        print '<br />';
        print 'Passwort:<br><input type="password" onfocus="this.select()" name="auth_password" id="auth_password" value="" />';
        print "</div><br />";
        print '<button type="button" class="thinbtn" id="btn_authenticate" onClick="authenticate();"><i class="fa fa-bolt"></i></button>';
        print "</div>";

        print ' 
        <script>
            input = document.getElementById("auth_password");
                input.addEventListener("keydown", function(event) {
                    if (event.key === "Enter") {
                        authenticate();
                }
            });
                
            input = document.getElementById("auth_username");
                input.addEventListener("keydown", function(event) {
                    if (event.key === "Enter") {
                        authenticate();
                }
            });
                
            async function authenticate()
            {
                await JaxonInteractives.update_backend_credential_cache(' . "'auth_username'" . ', document.getElementById(' . "'auth_username'" . ').value);
                await JaxonInteractives.update_backend_credential_cache(' . "'auth_password'" . ', document.getElementById(' . "'auth_password'" . ').value);
                JaxonInteractives.backend_authenticate();                
            }
        </script>
        ';

        return false;

    }

    public function record_note($registration_id, $style, $category, $content)
    {
        $insert_arr = [
            'registration_id' => $registration_id,
            'timestamp' => date(time()),
            'style' => $style,
            'category' => $category,
            'content' => $content
        ];
        if(isset($_SESSION['backend_authenticated']))   $insert_arr['user_id'] = $_SESSION['backend_authenticated'];

        $this->db->insert_row_with_array($this->config->user['DBTABLE_DASHBOARD_NOTES'], $insert_arr);
    }
}
