<?php

use function Jaxon\jaxon;

class Interactives
{
    public function __construct()
    {

    }

    public function __destruct()
    {

    }
    public function add_consume_meter($prefix="AT003000000000000000000003")
    {
        $response = jaxon()->newResponse();

        $name = 'consume1';
        $id = 'consume1';

        $_SESSION['consumers'][] = '000000000';

        $element = '
            &nbsp;<br />2. Z&auml;hlpunktnummer (letzte 9 Stellen) <div class="input-box">
                <span class="prefix">' . $prefix . '</span>
                <input type="text" name="' . $name . '" id="' . $id . '" value="000000000" maxlength="9" onfocus="this.select()" />
                &nbsp;&nbsp;<button style="background-color:darkred"  onclick="JaxonInteractives.rmv_additional_meter(' . "'consume1'" . ');"><i style="font-size:16px;color:white;" class="icon fa-trash-alt"></i></button>
            </div>
        ';

        $response->insertAfter('container-consume0', 'div', 'container-consume1');
        $response->assign('container-consume1', 'innerHTML', $element);
        return $response;
    }

    public function add_supply_meter($prefix="AT003000000000000000000003")
    {
        $response = jaxon()->newResponse();

        $name = 'supply1';
        $id = 'supply1';

        $_SESSION['suppliers'][] = '000000000';

        $element = '
                &nbsp;<br />2. Z&auml;hlpunktnummer (letzte 9 Stellen) <div class="input-box">
                    <span class="prefix">' . $prefix . '</span>
                    <input type="text" name="' . $name . '" id="' . $id . '" value="000000000" maxlength="9" onfocus="this.select()" />
                    &nbsp;&nbsp;<button style="background-color:darkred" onclick="JaxonInteractives.rmv_additional_meter(' . "'supply1'" . ');"><i style="font-size:16px" class="icon fa-trash-alt"></i></button>
                </div>
            ';

        $response->insertAfter('container-supply0', 'div', 'container-supply1');
        $response->assign('container-supply1', 'innerHTML', $element);
        return $response;
    }

    public function rmv_additional_meter($id)
    {
        $response = jaxon()->newResponse();
        $response->remove('container-' . $id);
        return $response;
    }
}