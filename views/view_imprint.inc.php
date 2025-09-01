<?php

include_once('view.inc.php');

class VIEW_IMPRINT extends VIEW
{

    public $tenant_info;

    public function __construct()
    {
        parent::__construct();

        if(isset($_SESSION['tenant']) && $_SESSION['tenant'] != '')
        {
            $tenant_info = $this->object_broker->instance['db']->get_rows_by_column_value($this->config->user['DBTABLE_TENANTS'], 'id', $_SESSION['tenant'], $limit = 1);
            $this->tenant_info = $tenant_info[0];
        }
    }

    public function view_render()
    {
        print '
            <header id="header">
                <h1>' . $this->tenant_info['fullname'] . '</h1>
                <p>Impressum<br /></p>
            </header>
        ';

        $this->tenant_info['contact_imprint_name'] = str_replace("\n", "<br />", $this->tenant_info['contact_imprint_name']);
        $this->tenant_info['contact_imprint_address'] = str_replace("\n", "<br />", $this->tenant_info['contact_imprint_address']);

        print "<h2>" . $this->tenant_info['contact_imprint_name'] . "</h2><br />&nbsp;<br />";


        print "<div style=\"float:left;margin-left:100px;\">
                <h3>Adresse</h3>
                " . $this->tenant_info['contact_imprint_address'] . "
               </div>
        ";


        print "<div style=\"float:left;margin-left:150px;\">
                <h3>Firmenbuch / Vereinsregister</h3>
               " . $this->tenant_info['contact_imprint_id'] . "
               </div>
        ";


        print "<div style=\"float:left;margin-left:150px;\">
                <h3>Kontakt</h3>
                " . $this->tenant_info['contact_email'] . "
               </div>
        ";

        print "<div style=\"clear:both;\"></div>";


        print "<br />&nbsp;<br />";

        //print "<div class=\"form-container\" style=\"font-size:10pt;font-weight:normal\">";
        print "<h3>Lizenzen</h3>";
        print "<b style=\"font-size:14pt;\">Hintergrundbilder lizenziert unter der <a href=\"https://unsplash.com/license\" target=\"_blank\">Unsplash License</a>:</b><br />";
        print "<div style=\"font-size:10pt;\"><b>Benjamin Jopen</b> (benjopen), <b>Nuno Marques</b> (logvisuals), <b>Andreas Gucklhorn</b> (draufsicht), <b>David Cristian</b> (departive)<br /><b>Markus Spiske</b> (markusspiske), <b>Raphael Cruz</b> (cyborgxxl), <b>Moritz Kindler</b> (moritz_photography)</div>";
        //print "</div>";
    }
}
