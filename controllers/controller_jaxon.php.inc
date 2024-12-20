<?php

use function Jaxon\jaxon;

class Interactives
{

    private $object_broker;
    private $session;
    private $config;

    public function __construct()
    {
        include_once('controllers/controller_config.php.inc');
        $this->config = new CONTROLLER_CONFIG();
    }
    public function __destruct()
    {

    }
    public function add_meter($meter_type, $prefix)
    {
        $response = jaxon()->newResponse();

        $id = $this->generate_uuid4();

        $_SESSION['meters']["$id"]['prefix'] = $prefix;
        $_SESSION['meters']["$id"]['value'] = '000000000';
        $_SESSION['meters']["$id"]['type'] = $meter_type;

        $element = '
            Z&auml;hlpunktnummer (letzte 9 Stellen) <div class="input-box">
                <span class="prefix">' . $prefix . '</span>
                <input type="text" name="' . $id . '" id="' . $id . '" value="000000000" maxlength="9" onfocus="this.select()"  onfocusout="JaxonInteractives.update_meter_value(' . "'" . $id . "'" . ', document.getElementById(' . "'" . $id . "'" . ').value);" />
                &nbsp;&nbsp;<button style="background-color:darkred"  onclick="JaxonInteractives.rmv_meter(' . "'" . $id . "'" . ', ' . "'" . $meter_type . "'" . ');"><i style="font-size:16px;color:white;" class="icon fa-trash-alt"></i></button>
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

    public function add_storage()
    {
        $response = jaxon()->newResponse();

        $id = $this->generate_uuid4();
        $_SESSION['storages']["$id"]['value'] = '0';

        $element = '
        <div id="container-' . $id . '">Speicher<br>
            <div class="input-box" style="width:254px;">
                <span class="prefix">Kapazit&auml;t:&nbsp;</span>
                <input type="text" name="' . $id . '" id="' . $id . '" value="0" maxlength="4" style="width:80px;text-align:center" onfocus="this.select()" onfocusout="JaxonInteractives.update_storage_value(' . "'" . $id . "'" . ', document.getElementById(' . "'" . $id . "'" . ').value);" />
                <span class="prefix">kWh</span>
                &nbsp;&nbsp;&nbsp;&nbsp;<button style="background-color:darkred"  onclick="JaxonInteractives.rmv_storage(' . "'" . $id . "'" . ');"><i style="font-size:16px;color:white;" class="icon fa-trash-alt"></i></button><br>
            </div>
            <br />
        </div>';

        $response->insertBefore('end_of_storages', 'div', 'container-' . $id);
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

    public function rmv_storage($id)
    {
        if(isset($_SESSION['storages']["$id"]))
        {
            unset($_SESSION['storages']["$id"]);
            $response = jaxon()->newResponse();
            $response->remove('container-' . $id);
            return $response;
        }
    }

    public function update_storage_value($id, $value)
    {
        if(isset($_SESSION['storages']["$id"]))
        {
            $_SESSION['storages']["$id"]['value'] = $value;
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

    public function step_meters()
    {
        $response = jaxon()->newResponse();
        $response->script("window.location.href='/?join=meters';");
        return $response;
    }

    public function step_banking()
    {
        $response = jaxon()->newResponse();
        $response->script("window.location.href='/?join=banking';");
        return $response;

    }

    public function copy_address($meter_key)
    {
        $response = jaxon()->newResponse();
        $response->assign('street_' . $meter_key, 'value', $_SESSION['generic_information']['street']);
        $response->assign('zip_' . $meter_key, 'value', $_SESSION['generic_information']['zip']);
        $response->assign('city_' . $meter_key, 'value', $_SESSION['generic_information']['city']);
        return $response;

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