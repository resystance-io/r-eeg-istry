<?php


class CONTROLLER_CONFIG
{

	// ## instance variables

	public $defaults;        // Configuration items used by default
	public $user;            // Configuration items to superseed the defaults

	public function __construct()
	{
		include(dirname(__FILE__) . '/../configs/config.php');

		$this->user = get_defined_vars();
	}

	public function __destruct()
	{

	}

}


?>