<?php

include_once('view.inc.php');
class VIEW_MANAGEMENT extends VIEW
{
    function view_render()
    {
        ?>

        <!--<input type="button" value="JAXON FOO TEST" onclick="jaxon_foo()" /><br />-->
        <header id="header">
            <h1>R:EEG:ISTRY | Management</h1>
            <p>Energiegemeinschaften und Anmeldungen verwalten<br /></p>
        </header>

        <?php
        print "<br />&nbsp;<br />&nbsp;<br />";
        print '
            <div class="table-container">
              <table class="table">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Age</th>
                    <th>Email</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>John Doe</td>
                    <td>25</td>
                    <td>john.doe@example.com</td>
                  </tr>
                  <tr>
                    <td>Jane Smith</td>
                    <td>30</td>
                    <td>jane.smith@example.com</td>
                  </tr>
                  <!-- Additional rows can go here -->
                </tbody>
              </table>
            </div>
        ';
    }

}
