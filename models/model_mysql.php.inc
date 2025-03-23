<?php


class DATABASE_MODEL
{

	// ## instance variables
	// ##
	
	private $db_host;
	private $db_name;
	private $db_user;
	private $db_pass;
	private $db_link;


	// ## import variables and connect to database when instance is created
	// ##
	
	public function __construct($db_host, $db_name, $db_user, $db_pass)
	{
		$this->db_host = $db_host;
		$this->db_name = $db_name;
		$this->db_user = $db_user;
		$this->db_pass = $db_pass;
		
		$this->establish_connection();
	}
	
	
	
	// ## force a clean close of our database connection when instance is destroyed
	// ##
	
	public function __destruct()
	{
	
		$this->close_connection();
		
	}
	
	
	// ## establish connection with built-in mysql client
 	// ##
 	
	private function establish_connection()
	{
	
                $dsn_mgmt = 'mysql:host=' . $this->db_host . ';dbname=' . $this->db_name . ';charset=utf8mb4';

                try
                {
                         $db_mgmt = new PDO($dsn_mgmt, $this->db_user, $this->db_pass);
                         $db_mgmt->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                         $db_mgmt->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                }
                catch (PDOException $e)
                {
                         echo $e->getMessage();
                         die('the dungeon collapses. you are all going to die down here.');
                }

                $this->db_link = $db_mgmt;
	
	}
	
	
	// ## close connection with built-in mysql client
	// ##
		
	private function close_connection()
	{
		return true;
	}
	
	
	
	// ## send query to database without expecting a return, so it returns true,
	// ## except for when the last_insert_id has changed, which results in returning this instead
	// ##
	
	public function execute_blind_query($query)
	{
		$last_insert_before = $this->db_link->lastInsertId();
		error_log("\n" . $query . "\n");
		$this->db_link->query($query);
		$last_insert_after = $this->db_link->lastInsertId();

		if($last_insert_before != $last_insert_after)
		{
			return $last_insert_after;
		}
		else
		{
			return true;
		}
	
	}


	// ## send query and provide the returned information as a raw object
	// ##

	public function execute_query($query)
	{

		error_log($query);
		return $this->db_link->query($query);

	}

	public function prepare($query)
	{
		return $this->db_link->prepare($query);
	}

}





?>
