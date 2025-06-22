<?php

class VIEW_DEBUG
{
    function view_render()
    {
?>

        <header id="header">
            <h1>DEBUG VIEW</h1>
            <p>NOT SUITABLE FOR PRODUCTION ENVIRONMENTS<br /></p>

            <p style="color:white">Debug Interface</p>
        </header>

<?php
        print "<br />&nbsp;<br />&nbsp;<br />";
        print "<h3>SESSION DUMP:</h3>";
        print "<div class=\"form-container\">";
        print '
            <script>
              Dropzone.options.myDropzone = {
                maxFilesize: "10M",
                maxFiles: 1
              };
            </script>
            <form action="?upload" class="dropzone" id="my-dropzone">
                <div class="dz-message" data-dz-message><span style="color:dimgrey">Datei zur &Uuml;bermittlung hier ablegen oder klicken um eine Datei auszuw&auml;hlen</span></div>
            </form>
        ';
        print "<pre>";
        print_r($_SESSION);
        print "</pre>";
        print "</div>";
    }

}
