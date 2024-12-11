<?php

use Jaxon\Response\Response;

class Interactives
{
    public function __construct()
    {

    }

    public function __destruct()
    {

    }

    public function jaxtest()
    {
        $response = new Response();
        $response->alert('TEST');
        return $response;
    }
}