<?php


class CONTROLLER_CONFIG
{

	// ## instance variables

	public $defaults;        // Configuration items used by default
	public $user;            // Configuration items to superseed the defaults

	public function __construct()
	{
		// load our configuration file and provide all variables in there in a uniform array
		include(dirname(__FILE__) . '/../configs/config.php');
		$this->user = get_defined_vars();
		$this->load_defaults();
	}

	public function __destruct()
	{

	}

	private function load_defaults()
	{
		if(!isset($this->user['tenant_fallback_on_empty_request']))	$this->user['tenant_fallback_on_empty_request'] = true;
		if(!isset($this->user['cross_tenant_member_ids']))	$this->user['cross_tenant_member_ids'] = false;
		if(!isset($this->user['defer_tenant_assignment']))	$this->user['defer_tenant_assignment'] = false;
		if(!isset($this->user['debug']))	$this->user['debug'] = false;

	}
}


?>
