<?php

    function view_render()
    {
?>

        <header id="header">
            <h1>Erneuerbare Energiegemeinschaft VIERE</h1>
            <p>Wir sind die erste solidarische Energiegemeinschaft<br />f&uuml;r Waizenkirchen und Umgebung.<br /></p>

            <p style="color:white">Womit k&ouml;nnen wir dir helfen?</p>
        </header>

        <div class="button_container">
            <button type="button" class="mainbtn" id="btn_enroll_new" onClick="location.href='?join'"><img src="images/noun_bio_food_energy.png" alt="Join EEG" id="join_eeg" style="height:60px"><br />Ich m&ouml;chte dieser EEG beitreten</button>
            <button type="button" class="mainbtn" id="btn_enroll_progress" onClick="location.href='?lookup'"><img src="images/noun_progress.png" alt="View Registration" id="lookup_eeg" style="height:60px"><br />Ich m&ouml;chte meine Daten abrufen</button>
        </div>
<?php
    }
