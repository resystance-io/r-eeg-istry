<?php

class OBJECT_BROKER
{

    public $instance;           // Holds instanciated objects
    public $plugs;              // holds instantiated plugins

    public function __construct()
    {
        $this->plugs = [];
    }

}