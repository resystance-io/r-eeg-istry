<?php


class CONTROLLER_DB
{

	// ## instance variables

	private $db;
	private $object_broker;

	// ---------------------------------------------------------------------------------
	// ::DESCRIPT: perform proper config and connect to database on instance creation
	// ::RETURNS: -
	// ---------------------------------------------------------------------------------

	public function __construct($object_broker, $database=NULL)
	{

		$this->object_broker = $object_broker;
		$config = $object_broker->instance['config'];

		if(!file_exists(dirname(__FILE__) . '/../models/model_mysql.php.inc'))
		{
			die('DB_CONTROLLER: backend_model_not_found<br>');
		}

		require_once(dirname(__FILE__) . '/../models/model_mysql.php.inc');

		switch($database)
		{
			default:
				$this->db = new DATABASE_MODEL(
					$config->user['DBLOGON_HOST'],
					$config->user['DBLOGON_DB'],
					$config->user['DBLOGON_USER'],
					$config->user['DBLOGON_PASS']
				);
				break;
		}

	}

	



	// ---------------------------------------------------------------------------------
	// ::DESCRIPT: force a clean close of the database connection when an instance dies
	// ---------------------------------------------------------------------------------

	public function __destruct()
	{

		// destroy database model
		// caveat: we use unset, but there is a
		// bug with unset in __destruct in PHP 5.3.3,
		// which might cause unexpected behaviour!

		unset($this->db);

	}



	// ---------------------------------------------------------------------------------
	// ::DESCRIPT: send query to database model and return the raw object
	// ::RETURNS: raw retsul object
	// ---------------------------------------------------------------------------------

	public function execute_query($query)
	{

		return $this->db->execute_query($query);

	}

	public function prepare_query($query)
	{
		return $this->db->prepare($query);
	}

	public function get_column_by_column_value($table, $field, $reference_field, $reference_value)
	{

		$sql = "SELECT $table.$field FROM $table WHERE $table.$reference_field = \"$reference_value\" LIMIT 1";
		$ret = $this->db->execute_query($sql);

		return $ret->fetchColumn();

	}

	public function get_column_by_column_values($table, $field, $reference_field, $reference_value, $reference_field_2=NULL, $reference_value_2=NULL, $reference_field_3=NULL, $reference_value_3=NULL, $reference_field_4 = NULL, $reference_value_4=NULL)
	{
		$extended_where_clause = NULL;

		if($reference_field_2 && $reference_value_2)
			$extended_where_clause = "AND $table.$reference_field_2 = \"$reference_value_2\"";

		if($reference_field_3 && $reference_value_3)
			$extended_where_clause .= "AND $table.$reference_field_3 = \"$reference_value_3\"";

		if($reference_field_4 && $reference_value_4)
			$extended_where_clause .= "AND $table.$reference_field_4 = \"$reference_value_4\"";

		$sql = "SELECT $table.$field FROM $table WHERE $table.$reference_field = \"$reference_value\" $extended_where_clause LIMIT 1";
		$ret = $this->db->execute_query($sql);
		if($ret->rowCount() > 0)
		{
			return $ret->fetchColumn();
		}
		else
		{
			return NULL;
		}
	}

	public function update_column_by_column_values($table, $field, $value, $reference_field, $reference_value, $reference_field_2=NULL, $reference_value_2=NULL, $reference_field_3=NULL, $reference_value_3=NULL)
	{
		$extended_where_clause = NULL;

		if($reference_field_2 && $reference_value_2)
			$extended_where_clause = "AND $table.$reference_field_2 = \"$reference_value_2\"";

		if($reference_field_3 && $reference_value_3)
			$extended_where_clause .= "AND $table.$reference_field_3 = \"$reference_value_3\"";

		if(!$value)	$value = 'NULL';	else	$value = "\"$value\"";
		$sql = "UPDATE $table SET $table.$field = $value WHERE $table.$reference_field = \"$reference_value\" $extended_where_clause";
		$this->db->execute_blind_query($sql);
	}

	public function insert_row_with_values($table, $key, $value, $key2=NULL, $value2=NULL, $key3=NULL, $value3=NULL, $key4=NULL, $value4=NULL, $key5=NULL, $value5=NULL, $key6 = NULL, $value6 = NULL, $key7 = NULL, $value7 = NULL, $key8 = NULL, $value8 = NULL, $key9 = NULL, $value9 = NULL)
	{
		error_log("WARNING +++ insert_row_with_values() is now deprecated and should not be used. Use insert_row_with_array() instead");
		$query_val_1 = "\"$value\"";
		if($key2)	{ $query_key_2 = ',' . $key2; $query_val_2 = ',' . "\"$value2\""; } else { $query_key_2 = NULL; $query_val_2 = NULL; }
		if($key3)	{ $query_key_3 = ',' . $key3; $query_val_3 = ',' . "\"$value3\""; } else { $query_key_3 = NULL; $query_val_3 = NULL; }
		if($key4)	{ $query_key_4 = ',' . $key4; $query_val_4 = ',' . "\"$value4\""; } else { $query_key_4 = NULL; $query_val_4 = NULL; }
		if($key5)	{ $query_key_5 = ',' . $key5; $query_val_5 = ',' . "\"$value5\""; } else { $query_key_5 = NULL; $query_val_5 = NULL; }
		if($key6)	{ $query_key_6 = ',' . $key6; $query_val_6 = ',' . "\"$value6\""; } else { $query_key_6 = NULL; $query_val_6 = NULL; }
		if($key7)	{ $query_key_7 = ',' . $key7; $query_val_7 = ',' . "\"$value7\""; } else { $query_key_7 = NULL; $query_val_7 = NULL; }
		if($key8)	{ $query_key_8 = ',' . $key8; $query_val_8 = ',' . "\"$value8\""; } else { $query_key_8 = NULL; $query_val_8 = NULL; }
		if($key9)	{ $query_key_9 = ',' . $key9; $query_val_9 = ',' . "\"$value9\""; } else { $query_key_9 = NULL; $query_val_9 = NULL; }

		$last_insert_id = $this->db->execute_blind_query("INSERT INTO $table (" . $key . $query_key_2 . $query_key_3 . $query_key_4 . $query_key_5 . $query_key_6 . $query_key_7 . $query_key_8 . $query_key_9 . ") VALUES (" . $query_val_1 . $query_val_2 . $query_val_3 . $query_val_4 . $query_val_5 . $query_val_6 . $query_val_7 . $query_val_8 . $query_val_9 . ")");
		if(is_numeric($last_insert_id))
		{
			return $last_insert_id;
		}
		else
		{
			return true;
		}

	}

	public function insert_row_with_array($table, $key_value_array)
	{
		$query_keys = NULL;
		$query_values = NULL;

		foreach($key_value_array as $key => $value)
		{
			if($query_keys) $query_keys .= ',';
			if($query_values)       $query_values .= ',';

			$query_keys .= $key;
			if(strtolower($value) == 'null')
			{
				$query_values .= "NULL";
			}
			else
			{
				$query_values .= "'$value'";
			}
		}

		$last_insert_id = $this->db->execute_blind_query("INSERT INTO $table ($query_keys) VALUES ($query_values)");

		if(is_numeric($last_insert_id))
		{
			return $last_insert_id;
		}
		else
		{
			return true;
		}
	}

	public function get_columns_by_column_value($table, $field, $reference_field, $reference_value, $limit=NULL, $order=NULL, $ordermethod=NULL, $extended_where_clause=NULL)
	{
		if($limit) $limit = "LIMIT $limit";
		if(!$ordermethod) $ordermethod = 'ASC';
		if($order) $order = "ORDER BY $table.$order $ordermethod";

		$sql = "SELECT $table.$field FROM $table WHERE $table.$reference_field = \"$reference_value\" $extended_where_clause $order $limit";
		$ret = $this->db->execute_query($sql);

		return $ret->fetchAll(PDO::FETCH_ASSOC);

	}

	public function increment_column_by_value($table, $field, $increment, $reference_field, $reference_value, $limit=NULL)
	{
		if($limit) $limit = "LIMIT $limit";

		$sql = "UPDATE $table SET $field = $field + $increment WHERE $reference_field = \"$reference_value\" $limit";
		$this->db->execute_blind_query($sql);
	}

	public function get_rows($table, $inner_joins=null, $selectors=null, $limit=NULL, $order=NULL)
	{
		if(is_array($inner_joins))
		{
			$joins = '';
			foreach($inner_joins as $inner_join)
			{
				$joins .= ' INNER JOIN ' . $inner_join[0] . ' ON ' . $inner_join[1] . ' = ' . $inner_join[2];
			}
		}
		elseif($inner_joins != null)
		{	// this can not be not-an-array, since we need data for the ON-clause for this to work,
			// fail hard and early!
			throw new Exception('inner_joins must be array or null');
		}

		$selects = 'WHERE';
		if(is_array($selectors))
		{
			foreach($selectors as $selector)
			{
				if($selects != 'WHERE') $selects .= ' AND ';
				$selects .= ' ' . $selector;
			}
		}
		elseif($selectors != null)
		{
			$selects .= $selectors;
		}
		elseif($selectors != null)
		{	// this can not be not-an-array, since we need data for the ON-clause for this to work,
			// fail hard and early!
			throw new Exception('selectors must be array or null');
		}

		if($limit) $limit = "LIMIT $limit";
		if($order) $order = "ORDER BY $order";

		$sql = "SELECT * FROM $table $joins $selects $order $limit";
		$ret = $this->db->execute_query($sql);

		return $ret->fetchAll(PDO::FETCH_ASSOC);
	}

	public function get_rowcount($table, $inner_joins=null, $selectors=null, $limit=NULL, $order=NULL)
	{
		if(is_array($inner_joins))
		{
			$joins = '';
			foreach($inner_joins as $inner_join)
			{
				$joins .= ' INNER JOIN ' . $inner_join[0] . ' ON ' . $inner_join[1] . ' = ' . $inner_join[2];
			}
		}
		elseif($inner_joins != null)
		{	// this can not be not-an-array, since we need data for the ON-clause for this to work,
			// fail hard and early!
			throw new Exception('inner_joins must be array or null');
		}

		$selects = 'WHERE';
		if(is_array($selectors))
		{
			foreach($selectors as $selector)
			{
				if($selects != 'WHERE') $selects .= ' AND ';
				$selects .= ' ' . $selector;
			}
		}
		elseif($selectors != null)
		{
			$selects .= $selectors;
		}
		elseif($selectors != null)
		{	// this can not be not-an-array, since we need data for the ON-clause for this to work,
			// fail hard and early!
			throw new Exception('selectors must be array or null');
		}

		if($limit) $limit = "LIMIT $limit";
		if($order) $order = "ORDER BY $order";

		$sql = "SELECT COUNT($table.id) FROM $table $joins $selects $order $limit";
		$ret = $this->db->execute_query($sql);

		return $ret->fetchColumn();
	}

	public function get_rows_by_column_value($table, $reference_field=NULL, $reference_value=NULL, $limit=NULL, $order=NULL, $ordermethod=NULL, $reference_field_2=NULL, $reference_value_2=NULL, $reference_field_3=NULL, $reference_value_3=NULL, $reference_field_4=NULL, $reference_value_4=NULL)
	{
		if($reference_field && $reference_value)	$reference = "WHERE $reference_field = \"$reference_value\"";	else	$reference = NULL;
		if($limit) $limit = "LIMIT $limit";
		if(!$ordermethod) $ordermethod = 'ASC';
		if($order) $order = "ORDER BY $table.$order $ordermethod";

		$extended_where_clause = NULL;

		if($reference_field_2 && $reference_value_2)
			$extended_where_clause = "AND $table.$reference_field_2 = \"$reference_value_2\"";

		if($reference_field_3 && $reference_value_3)
			$extended_where_clause .= "AND $table.$reference_field_3 = \"$reference_value_3\"";

		if($reference_field_4 && $reference_value_4)
			$extended_where_clause .= "AND $table.$reference_field_4 = \"$reference_value_4\"";

		$sql = "SELECT * FROM $table $reference $extended_where_clause $order $limit";
		$ret = $this->db->execute_query($sql);

		return $ret->fetchAll(PDO::FETCH_ASSOC);

	}

	public function get_rows_by_column_value_extended($table, $reference_field=NULL, $reference_value=NULL, $limit=NULL, $order=NULL, $ordermethod=NULL, $extended_where_clause=NULL, $extended_operator='AND')
	{
		if($reference_field && $reference_value)	$reference = "WHERE $reference_field = \"$reference_value\"";	else	$reference = NULL;
		if($limit) $limit = "LIMIT $limit";
		if(!$ordermethod) $ordermethod = 'ASC';
		if($order) $order = "ORDER BY $table.$order $ordermethod";

		if($extended_where_clause)
			$extended_where_clause = "$extended_operator $extended_where_clause";

		$sql = "SELECT * FROM $table $reference $extended_where_clause $order $limit";
		$ret = $this->db->execute_query($sql);

		return $ret->fetchAll(PDO::FETCH_ASSOC);

	}

	// ---------------------------------------------------------------------------------
	// ::DESCRIPT: -
	// ::RETURNS: -
	// ---------------------------------------------------------------------------------

	public function get_rowcount_by_field_value_extended($table,$haystack,$needle,$extended_where_clause=NULL,$joinstatement=NULL)
	{

		if($extended_where_clause!=NULL) $extended_where_clause = "AND $extended_where_clause";

		$ret = $this->db->execute_query("SELECT COUNT($table.id) FROM $table $joinstatement WHERE $table.$haystack = \"$needle\" $extended_where_clause");
		return $ret->fetchColumn();
	}

	// ---------------------------------------------------------------------------------
	// ::DESCRIPT: deletes a row by given ID
	// ::RETURNS: 1 || 0
	// ---------------------------------------------------------------------------------

	public function delete_row_by_id($table, $id)
	{
		$this->db->execute_blind_query("DELETE from $table where $table.id = \"$id\" LIMIT 1");
	}

	// ---------------------------------------------------------------------------------
	// ::DESCRIPT: deletes rows by given field value
	// ::RETURNS: 1 || 0
	// ---------------------------------------------------------------------------------

	public function delete_rows_by_field_value($table,$referencefield,$referencevalue)
	{
		return $this->db->execute_blind_query("DELETE from $table where $table.$referencefield = \"$referencevalue\"");
	}
	public function delete_rows_by_field_value_extended($table,$referencefield,$referencevalue,$extended_where_clause=NULL)
	{

		if($extended_where_clause!=NULL) $extended_where_clause = "AND $extended_where_clause";
		return $this->db->execute_blind_query("DELETE FROM $table WHERE $table.$referencefield = \"$referencevalue\" $extended_where_clause ");
	}

	// ---------------------------------------------------------------------------------
	// ::DESCRIPT: returns enum content array from field
	// ::RETURNS: 1 || 0
	// ---------------------------------------------------------------------------------

	public function get_enum_array($table, $field)
	{
		$get_enum_ret = $this->db->execute_query("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = \"$table\" AND COLUMN_NAME = \"$field\"");
		$get_enum_arr = $get_enum_ret->fetch(PDO::FETCH_ASSOC);
		return explode(",", str_replace("'", "", substr($get_enum_arr['COLUMN_TYPE'], 5, (strlen($get_enum_arr['COLUMN_TYPE'])-6))));
	}

	/*
	// ---------------------------------------------------------------------------------
	// ::DESCRIPT: -
	// ::RETURNS: -
	// ---------------------------------------------------------------------------------

	public function get_ids_by_field_value($table,$haystack,$needle,$limit=NULL,$order=NULL,$ordermethod=NULL)
	{

		if($limit!=NULL) $limit = "LIMIT $limit";
		if($ordermethod==NULL) $ordermethod = 'ASC';
		if($order!=NULL) $order = "ORDER BY $table.$order $ordermethod";
		
		return $this->execute_query("SELECT $table.id FROM $table WHERE $table.$haystack = \"$needle\" $order $limit");

	}



	// ---------------------------------------------------------------------------------
	// ::DESCRIPT: -
	// ::RETURNS: -
	// ---------------------------------------------------------------------------------

	public function get_ids_by_table($table,$limit=NULL,$order=NULL,$ordermethod=NULL)
	{

		if($limit!=NULL) $limit = "LIMIT $limit";
		if($ordermethod==NULL) $ordermethod = 'ASC';
		if($order!=NULL) $order = "ORDER BY $table.$order $ordermethod";
		
		return $this->execute_query("SELECT $table.id FROM $table $order $limit");

	}



	// ---------------------------------------------------------------------------------
	// ::DESCRIPT: -
	// ::RETURNS: -
	// ---------------------------------------------------------------------------------

	public function get_ids_by_fuzzy_field_value($table,$haystack,$needle,$limit=NULL,$order=NULL,$ordermethod=NULL)
	{

		if($limit!=NULL) $limit = "LIMIT $limit";
		if($ordermethod==NULL) $ordermethod = 'ASC';
		if($order!=NULL) $order = "ORDER BY $table.$order $ordermethod";
		
		return $this->execute_query("SELECT $table.id FROM $table WHERE $table.$haystack LIKE \"%$needle%\" $order $limit");

	}




	// ---------------------------------------------------------------------------------
	// ::DESCRIPT: -
	// ::RETURNS: -
	// ---------------------------------------------------------------------------------

	public function get_ids_by_fuzzy_array($table,$barn,$needlepad,$limit=NULL,$order=NULL,$ordermethod=NULL)
	{

		if($limit!=NULL) $limit = "LIMIT $limit";
		if($ordermethod==NULL) $ordermethod = 'ASC';
		if($order!=NULL) $order = "ORDER BY $table.$order $ordermethod";
		
		$scope = NULL;
		$subscope = NULL;

		
		if(is_array($barn))
		{
			foreach($needlepad as $needle)
			{

				if($scope)	$scope.=' AND ';
				$scope.= '(';
				
				$i = NULL;
				
				foreach($barn as $haystack)
				{
					if($i)	$scope.= ' OR ';
					$scope.= "$table.$haystack LIKE \"%$needle%\"";
					
					$i = 1;
				}
				
				$scope.= ')';

			}
		}
		else
		{
			foreach($needlepad as $needle)
			{
				if($scope)	$scope.=' AND ';
				$scope.= "$table.$barn LIKE \"%$needle%\"";
			}
		}
		
		return $this->execute_query("SELECT $table.id FROM $table WHERE $scope $order $limit");

	}



	// ---------------------------------------------------------------------------------
	// ::DESCRIPT: -
	// ::RETURNS: -
	// ---------------------------------------------------------------------------------

	public function get_rowcount_by_field_value($table,$haystack,$needle)
	{
		
		//print "DBG: SELECT COUNT($table.$haystack) FROM $table WHERE $table.$haystack = \"$needle\"";
		list($rowcount) = mysql_fetch_row($this->execute_query("SELECT COUNT($table.$haystack) FROM $table WHERE $table.$haystack = \"$needle\""));
		return $rowcount;

	}




	// ---------------------------------------------------------------------------------
	// ::DESCRIPT: -
	// ::RETURNS: -
	// ---------------------------------------------------------------------------------

	public function get_rowcount_by_fuzzy_field_value($table,$haystack,$needle)
	{

		list($rowcount) = mysql_fetch_row($this->execute_query("SELECT COUNT($table.id) FROM $table WHERE $table.$haystack LIKE \"%$needle%\""));
		return $rowcount;

	}




	// ---------------------------------------------------------------------------------
	// ::DESCRIPT: -
	// ::RETURNS: -
	// ---------------------------------------------------------------------------------

	public function get_id_by_field_value_extended($table,$haystack,$needle,$extended_where_clause=NULL,$order=NULL,$ordermethod=NULL,$joinstatement=NULL)
	{

		if($extended_where_clause!=NULL) $extended_where_clause = "AND $extended_where_clause";
		if($ordermethod==NULL) $ordermethod = 'ASC';
		if($order!=NULL) $order = "ORDER BY $table.$order $ordermethod";

		//print "SELECT $table.id FROM $table WHERE $table.$haystack = \"$needle\" $extended_where_clause $order LIMIT 1";
		list($id) = mysql_fetch_row($this->execute_query("SELECT $table.id FROM $table $joinstatement WHERE $table.$haystack = \"$needle\" $extended_where_clause $order LIMIT 1"));
		return $id;

	}



	// ---------------------------------------------------------------------------------
	// ::DESCRIPT: -
	// ::RETURNS: -
	// ---------------------------------------------------------------------------------

	public function get_id_by_fuzzy_field_value_extended($table,$haystack,$needle,$extended_where_clause=NULL,$order=NULL,$ordermethod=NULL,$joinstatement=NULL)
	{

		if($extended_where_clause!=NULL) $extended_where_clause = "AND $extended_where_clause";
		if($ordermethod==NULL) $ordermethod = 'ASC';
		if($order!=NULL) $order = "ORDER BY $table.$order $ordermethod";

		list($id) = mysql_fetch_row($this->execute_query("SELECT $table.id FROM $table $joinstatement WHERE $table.$haystack LIKE \"%$needle%\" $extended_where_clause $order LIMIT 1"));
		return $id;

	}



	// ---------------------------------------------------------------------------------
	// ::DESCRIPT: -
	// ::RETURNS: -
	// ---------------------------------------------------------------------------------

	public function get_ids_by_field_value_extended($table,$haystack,$needle,$extended_where_clause=NULL,$limit=NULL,$order=NULL,$ordermethod=NULL,$joinstatement=NULL)
	{

		if($limit!=NULL) $limit = "LIMIT $limit";
		if($ordermethod==NULL) $ordermethod = 'ASC';
		if($order!=NULL) $order = "ORDER BY $table.$order $ordermethod";
		if($extended_where_clause!=NULL) $extended_where_clause = "AND $extended_where_clause";

		return $this->execute_query("SELECT $table.id FROM $table $joinstatement WHERE $table.$haystack = \"$needle\" $extended_where_clause $order $limit");

	}



	// ---------------------------------------------------------------------------------
	// ::DESCRIPT: -
	// ::RETURNS: -
	// ---------------------------------------------------------------------------------

	public function get_ids_by_fuzzy_field_value_extended($table,$haystack,$needle,$extended_where_clause=NULL,$limit=NULL,$order=NULL,$ordermethod=NULL,$joinstatement=NULL)
	{

		if($limit!=NULL) $limit = "LIMIT $limit";
		if($ordermethod==NULL) $ordermethod = 'ASC';
		if($order!=NULL) $order = "ORDER BY $table.$order $ordermethod";
		if($extended_where_clause!=NULL) $extended_where_clause = "AND $extended_where_clause";

		//echo "DEBUG: SELECT $table.id FROM $table WHERE $table.$haystack = \"$needle\" $extended_where_clause $order $limit<br>";
		return $this->execute_query("SELECT $table.id FROM $table $joinstatement WHERE $table.$haystack LIKE \"%$needle%\" $extended_where_clause $order $limit");

	}






	// ---------------------------------------------------------------------------------
	// ::DESCRIPT: -
	// ::RETURNS: -
	// ---------------------------------------------------------------------------------

	public function get_rowcount_by_fuzzy_field_value_extended($table,$haystack,$needle,$extended_where_clause=NULL,$joinstatement=NULL)
	{

		if($extended_where_clause!=NULL) $extended_where_clause = "AND $extended_where_clause";
		
		list($rowcount) = mysql_fetch_row($this->execute_query("SELECT COUNT($table.id) FROM $table $joinstatement WHERE $table.$haystack LIKE \"%$needle%\" $extended_where_clause"));
		return $rowcount;

	}



	// ---------------------------------------------------------------------------------
	// ::DESCRIPT: -
	// ::RETURNS: -
	// ---------------------------------------------------------------------------------

	public function get_field_value_by_id($table,$field,$id)
	{
		//include_once('../dev/debugger.php.inc');
		//echo "get_field_value_by_id::SELECT $table.$field from $table WHERE $table.id = \"$id\" LIMIT 1<br>";
		list($selection) = mysql_fetch_row($this->execute_query("SELECT $table.$field FROM $table WHERE $table.id = \"$id\" LIMIT 1"));
		return $selection;

	}



	// ---------------------------------------------------------------------------------
	// ::DESCRIPT: -
	// ::RETURNS: -
	// ---------------------------------------------------------------------------------

	public function get_fields_value_by_id($table,$field,$id,$limit=NULL,$order=NULL,$ordermethod=NULL)
	{

		if($limit!=NULL) $limit = "LIMIT $limit";
		if($ordermethod==NULL) $ordermethod = 'ASC';
		if($order!=NULL) $order = "ORDER BY $table.$order $ordermethod";

		return $this->execute_query("SELECT $table.$field FROM $table WHERE $table.id = \"$id\" $order $limit");

	}



	// ---------------------------------------------------------------------------------
	// ::DESCRIPT: -
	// ::RETURNS: -
	// ---------------------------------------------------------------------------------

	public function get_fields_value_by_id_extended($table,$field,$id,$limit=NULL,$extended_were_clause=NULL,$order=NULL,$ordermethod=NULL,$joinstatement=NULL)
	{

		if($limit!=NULL) $limit = "LIMIT $limit";
		if($ordermethod==NULL) $ordermethod = 'ASC';
		if($order!=NULL) $order = "ORDER BY $table.$order $ordermethod";
		if($extended_where_clause!=NULL) $extended_where_clause = "AND $extended_where_clause";

		return $this->execute_query("SELECT $table.$field FROM $table $joinstatement WHERE $table.id = \"$id\" $extended_where_clause $order $limit");

	}



	// ---------------------------------------------------------------------------------
	// ::DESCRIPT: -
	// ::RETURNS: -
	// ---------------------------------------------------------------------------------

	public function get_field_value_by_field_value($table,$field,$referencefield,$referencevalue)
	{

		list($selection) = mysql_fetch_row($this->execute_query("SELECT $table.$field FROM $table WHERE $table.$referencefield = \"$referencevalue\""));
		return $selection;

	}



	// ---------------------------------------------------------------------------------
	// ::DESCRIPT: -
	// ::RETURNS: -
	// ---------------------------------------------------------------------------------

	public function get_field_value_by_field_value_extended($table,$field,$referencefield,$referencevalue,$extended_where_clause=NULL,$joinstatement=NULL)
	{

		if($extended_where_clause!=NULL) $extended_where_clause = "AND $extended_where_clause";

		list($selection) = mysql_fetch_row($this->execute_query("SELECT $table.$field FROM $table $joinstatement WHERE $table.$referencefield = \"$referencevalue\" $extended_where_clause"));
		return $selection;

	}



	// ---------------------------------------------------------------------------------
	// ::DESCRIPT: -
	// ::RETURNS: -
	// ---------------------------------------------------------------------------------

	public function get_field_values_by_field_value_extended($table,$field,$referencefield,$referencevalue,$extended_where_clause=NULL,$limit=NULL,$order=NULL,$ordermethod=NULL,$joinstatement=NULL)
	{

		if($limit!=NULL) $limit = "LIMIT $limit";
		if($ordermethod==NULL) $ordermethod = 'ASC';
		if($order!=NULL) $order = "ORDER BY $table.$order $ordermethod";
		if($extended_where_clause!=NULL) $extended_where_clause = "AND $extended_where_clause";

		//print "SELECT $table.$field FROM $table WHERE $table.$referencefield = \"$referencevalue\" $extended_where_clause $order $limit";
		return $this->execute_query("SELECT $table.$field FROM $table $joinstatement WHERE $table.$referencefield = \"$referencevalue\" $extended_where_clause $order $limit");

	}



	// ---------------------------------------------------------------------------------
	// ::DESCRIPT: -
	// ::RETURNS: -
	// ---------------------------------------------------------------------------------

	public function get_fields_value_by_field_value($table,$field,$referencefield,$referencevalue,$limit=NULL,$order=NULL,$ordermethod=NULL)
	{

		if($limit!=NULL) $limit = "LIMIT $limit";
		if($ordermethod==NULL) $ordermethod = 'ASC';
		if($order!=NULL) $order = "ORDER BY $table.$order $ordermethod";

		return $this->execute_query("SELECT $table.$field FROM $table WHERE $table.$referencefield = \"$referencevalue\" $order $limit");

	}



	// ---------------------------------------------------------------------------------
	// ::DESCRIPT: -
	// ::RETURNS: -
	// ---------------------------------------------------------------------------------

	public function get_fields_value_by_field_value_extended($table,$field,$referencefield,$referencevalue,$extended_where_clause=NULL,$limit=NULL,$order=NULL,$ordermethod=NULL,$joinstatement)
	{

		if($limit!=NULL) $limit = "LIMIT $limit";
		if($ordermethod==NULL) $ordermethod = 'ASC';
		if($order!=NULL) $order = "ORDER BY $table.$order $ordermethod";
		if($extended_where_clause!=NULL) $extended_where_clause = "AND $extended_where_clause";

		return $this->execute_query("SELECT $table.$field FROM $table $joinstatement WHERE $table.$referencefield = \"$referencevalue\" $extended_where_clause $order $limit");
	
	}



	// ---------------------------------------------------------------------------------
	// ::DESCRIPT: Update field value selected by ID of row
	// ::RETURNS: 1 || 0
	// ---------------------------------------------------------------------------------

	public function set_field_value_by_id($table,$id,$field,$value)
	{

		return $this->execute_query("UPDATE $table SET $table.$field = \"$value\" WHERE $table.id = \"$id\"");

	}
	
	
	// ---------------------------------------------------------------------------------
	// ::DESCRIPT: Update fields and values selected by ID of row up to 5 field/value pairs
	// ::RETURNS: 1 || 0
	// ---------------------------------------------------------------------------------

	public function set_fields_values_by_id($table,$id,$field,$value,$field1=NULL,$value1=NULL,$field2=NULL,$value2=NULL,$field3=NULL,$value3=NULL,$field4=NULL,$value4=NULL,$field5=NULL,$value5=NULL,$field6=NULL,$value6=NULL)
	{
		if($field1!=NULL) $pair1=",$table.$field1 = \"$value1\""; else $pair1=NULL;
		if($field2!=NULL) $pair2=",$table.$field2 = \"$value2\""; else $pair2=NULL;
		if($field3!=NULL) $pair3=",$table.$field3 = \"$value3\""; else $pair3=NULL;
		if($field4!=NULL) $pair4=",$table.$field4 = \"$value4\""; else $pair4=NULL;
		if($field5!=NULL) $pair5=",$table.$field5 = \"$value5\""; else $pair5=NULL;
		if($field6!=NULL) $pair6=",$table.$field6 = \"$value6\""; else $pair6=NULL;
		
		//print "UPDATE $table SET $table.$field = \"$value\" $pair1 $pair2 $pair3 $pair4 $pair5 $pair6 WHERE $table.id = \"$id\"";
		
		return $this->execute_query("UPDATE $table SET $table.$field = \"$value\" $pair1 $pair2 $pair3 $pair4 $pair5 $pair6 WHERE $table.id = \"$id\"");
	}




	// ---------------------------------------------------------------------------------
	// ::DESCRIPT: Update value of field selected by value of (another) field.
	// ::RETURNS: 1 || 0
	// ---------------------------------------------------------------------------------

	public function set_field_value_by_field_value($table,$referencefield,$referencevalue,$field,$value)
	{
		return $this->execute_query("UPDATE $table SET $table.$field = \"$value\" WHERE $table.$referencefield = \"$referencevalue\"");
	}



	// ---------------------------------------------------------------------------------
	// ::DESCRIPT: Update value of field selected by value of (another) field.
	// ::RETURNS: 1 || 0
	// ---------------------------------------------------------------------------------

	public function set_field_value_by_field_value_extended($table,$referencefield,$referencevalue,$field,$value,$extended_where_clause=NULL,$joinstatement=NULL)
	{
	
		if($extended_where_clause!=NULL) $extended_where_clause = "AND $extended_where_clause";

		return $this->execute_query("UPDATE $table $joinstatement SET $table.$field = \"$value\" WHERE $table.$referencefield = \"$referencevalue\" $extended_where_clause");
	}



	// ---------------------------------------------------------------------------------
	// ::DESCRIPT: insert a new row to the table provided as argument.
	// ::RETURNS: mysql auto insert ID (if available)
	// ---------------------------------------------------------------------------------

	public function insert_new_row($table)
	{
		$this->execute_query("INSERT INTO $table (id) VALUES ('')");
		return mysql_insert_id();
	}
	
	
	// ---------------------------------------------------------------------------------
	// ::DESCRIPT: insert a new row to the table provided as argument.
	// ::RETURNS: mysql auto insert ID (if available)
	// ---------------------------------------------------------------------------------

	public function insert_new_row_with_key($table,$key,$value)
	{
		$this->execute_query("INSERT INTO $table ($key) VALUES ($value)");
		return mysql_insert_id();
	}



	// ---------------------------------------------------------------------------------
	// ::DESCRIPT: deletes a row by given values(s)
	// ::RETURNS: 1 || 0
	// ---------------------------------------------------------------------------------

	public function delete_row_by_value_extended($table,$reference_field,$reference_value,$extended_where_clause=NULL,$joinstatement=NULL)
	{
		if($extended_where_clause!=NULL) $extended_where_clause = "AND $extended_where_clause";

		return $this->execute_query("DELETE from $table $joinstatement where $table.$reference_field = \"$reference_value\" $extended_where_clause LIMIT 1");
	}



	// ---------------------------------------------------------------------------------
	// ::DESCRIPT: deletes rows by given field value
	// ::RETURNS: 1 || 0
	// ---------------------------------------------------------------------------------

	public function delete_rows_by_field_value($table,$referencefield,$referencevalue)
	{
		return $this->execute_query("DELETE from $table where $table.$referencefield = \"$referencevalue\"");
	}

	*/
}



?>
