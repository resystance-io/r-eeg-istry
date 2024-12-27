<?php

class VIEW_DEBUG
{
    function view_render()
    {
?>

        <!--<input type="button" value="JAXON FOO TEST" onclick="jaxon_foo()" /><br />-->
        <header id="header">
            <h1>Erneuerbare Energiegemeinschaft VIERE</h1>
            <p>Wir sind die erste solidarische Energiegemeinschaft<br />f&uuml;r Waizenkirchen und Umgebung.<br /></p>

            <p style="color:white">Debug Interface</p>
        </header>

<?php
        print "<br />&nbsp;<br />&nbsp;<br />";
        print "<h3>SESSION DUMP:</h3>";
        print "<div class=\"form-container\">";
        print "<pre>";
        print_r($_SESSION);
        print "</pre>";
        print "</div>";
    }

}
