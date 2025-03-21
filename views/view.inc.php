<?php

class VIEW
{
    public $object_broker;
    public $config;
    public $db;
    public $session;
    public $mail;

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

        require_once('controllers/controller_db.php.inc');
        $this->db = $this->object_broker->instance['db'] = new CONTROLLER_DB($this->object_broker);

        require_once('controllers/controller_session.php.inc');
        $this->session = $this->object_broker->instance['session'] = new CONTROLLER_SESSION($this->object_broker);

        // preconfigure the mail user agent interface class
        require_once('controllers/controller_mua.inc.php');
        class_alias("codeworxtech\PHPMailerPro\PHPMailerPro", "PHPMailerPro");
        $this->mail = $this->object_broker->instance['email'] = new PHPMailerPro();

        $this->mail->SetSender($this->config->user['MAIL_FROM']);
        $this->mail->smtpHost     = $this->config->user['MAIL_MTA_ADDRESS'];
        $this->mail->smtpDebug    = $this->config->user['MAIL_DEBUGGING'];
        $this->mail->smtpPort     = $this->config->user['MAIL_MTA_PORT'];
        $this->mail->smtpUsername = $this->config->user['MAIL_MTA_USER'];
        $this->mail->smtpPassword = $this->config->user['MAIL_MTA_PASS'];
        $this->mail->smtpOptions = $this->config->user['MAIL_OPTIONS'];
    }
}

?>

