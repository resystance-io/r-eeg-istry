<?php

use function Jaxon\jaxon;

class Interactives
{

    private $object_broker;
    private $session;

    public function __construct()
    {

    }
    public function __destruct()
    {

    }
    public function add_meter($meter_type, $prefix)
    {
        $response = jaxon()->newResponse();

        $id = $this->generate_uuid4();

        $_SESSION['meters']["$id"]['value'] = '000000000';
        $_SESSION['meters']["$id"]['type'] = $meter_type;

        $element = '
            Z&auml;hlpunktnummer (letzte 9 Stellen) <div class="input-box">
                <span class="prefix">' . $prefix . '</span>
                <input type="text" name="' . $id . '" id="' . $id . '" value="000000000" maxlength="9" onfocus="this.select()"  onfocusout="JaxonInteractives.update_meter_value(' . "'" . $id . "'" . ', document.getElementById(' . "'" . $id . "'" . ').value);" />
                &nbsp;&nbsp;<button style="background-color:darkred"  onclick="JaxonInteractives.rmv_additional_meter(' . "'" . $id . "'" . ', ' . "'" . $meter_type . "'" . ');"><i style="font-size:16px;color:white;" class="icon fa-trash-alt"></i></button>
            </div>
            <br />
        ';

        if($meter_type == 'suppliers')
        {
            $anchor = 'end_of_suppliers';
        }
        elseif($meter_type == 'consumers')
        {
            $anchor = 'end_of_consumers';
        }

        $response->insertBefore($anchor, 'div', 'container-' . $id);
        $response->assign('container-' . $id, 'innerHTML', $element);
        return $response;
    }

    public function rmv_meter($id)
    {
        if(isset($_SESSION['meters']["$id"]))
        {
            if($_SESSION['meters']["$id"]['type'] == 'suppliers')
            {
                unset($_SESSION['meters']["$id"]);
                $response = jaxon()->newResponse();
                $response->remove('container-' . $id);
                return $response;
            }
            elseif($_SESSION['meters']["$id"]['type'] == 'consumers')
            {
                $consumer_count = 0;
                foreach($_SESSION['meters'] as $meter_object)
                {
                    if($meter_object['type'] == 'consumers')    $consumer_count++;
                }

                if($consumer_count == 1)
                {
                    $response = jaxon()->newResponse();
                    $response->alert("Der letzte Bezugszählpunkt kann nicht gelöscht werden.\nEs ist mindestens ein Bezugszählpunkt erforderlich.");
                    return $response;
                }
                else
                {
                    unset($_SESSION['meters']["$id"]);

                    $response = jaxon()->newResponse();
                    $response->remove('container-' . $id);
                    return $response;
                }
            }
        }
    }

    public function update_meter_value($id, $value)
    {
        if(isset($_SESSION['meters']["$id"]))
        {
            $_SESSION['meters']["$id"]['value'] = $value;
        }
    }

    public function update_session_bucket($field, $value, $session_bucket)
    {
        $_SESSION["$session_bucket"]["$field"] = $value;
    }

    private function generate_uuid4($data = null)
    {
        // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
        $data = $data ?? random_bytes(16);
        assert(strlen($data) == 16);

        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // Output the 36 character UUID.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}