<?php
// 
// JSON2SQL PHP Class Library
// 
// This class library is used to convert JSON data to SQL OR SQL data to JSON
// 
// 

class JSON2SQL{

	private $debugMode;		// Debug mode switch
	private $SQLiteFile;	// SQLite file
	private $tableName;		// Table name
	private $dbHandler;		// Database handler
	private $result;		// Select result

	// 
	// @construct 
	// @param $database
	public function __construct($sqlitefile, $tableName){

		$this->debug = false;

		// save table name 
		$this->tableName = $tableName;

		// save database file
		$this->SQLiteFile = $sqlitefile;
		$this->dbHandler = new SQLite3($this->SQLiteFile);

		// Clear result
		$this->result = array();

	}

	// 
	// @function open or close debug mode
	// 
	public function debugMode($mode)
	{
		$this->debug = $mode;
	}

	// 
	// @function output debug infomation
	// 
	private function _D($msg)
	{
		if($this->debug)
		{
			echo "DEBUG >>> ";
			echo $msg . "\n";
		}
	}

	// 
	// @function output as JSON
	// 
	public function toJSON()
	{
		return json_encode($this->result);
	}

	// 
	// @function output as JSON array
	// 
	public function toJSONArray()
	{

	}

	// 
	// @function output as JSON Object
	// 
	public function toJSONObject()
	{

	}

	// 
	// @function Select table by manual
	// @param $tableName(String) The new tabel name
	// 
	public function selectTable($tableName)
	{
		$this->tableName = $tableName;
	}

	// 
	// @function Drop table
	// @param $tableName(String) Drop table name
	// 
	public function dropTable($tableName = null)
	{
		$_tableName = empty($tableName) ? $this->tableName : $tableName;
		$sql = "DROP TABLE IF EXISTS $_tableName";

		$this->dbHandler->exec($sql);

		$this->_D($sql);

		return $this;
	}

	//
	// @function Create new table
	// @param $tableName(String) Table Name
	// @param #schema(JSON) Table schema
	// 		  JSON Struct
	// 			   {"Column Name":"Column Type"}
	// 
	public function createTable($schema,$tableName=null)
	{
		$_tableName = empty($tableName) ? $this->tableName : $tableName;
		$sql = "CREATE TABLE IF NOT EXISTS %s(ID INTEGER PRIMARY KEY AUTOINCREMENT,%s);";

		$columns = json_decode($schema);	// convert JSON to PHP Array or PHP Object

		// for Object
		if(is_object($columns))
		{	

			$key = key((array)$columns);
			$val = current((array)$columns);
			$cmd = sprintf($sql, $_tableName,  $key . " " . $val);
		}
		// for Array
		else if(is_array($columns))
		{
			$tableColumns = array();
			foreach ($columns as $col) {
				$key = key((array)$col);
				$val = current((array)$col);
				array_push($tableColumns, $key . " " . $val);
			}

			$cmd = sprintf($sql, $_tableName, implode(",", $tableColumns));
		}

		// Execute sql command
		$this->dbHandler->exec($cmd);

		// Debug
		$this->_D($cmd);

		// return self
		return $this;
	}

	// 
	// @function select data from database
	// @param $where
	// 
	public function find($where)
	{	
		$sql = "SELECT * FROM %s WHERE %s";

		$cmd = sprintf($sql, $this->tableName, $where);

		// query
		$result = $this->dbHandler->query($cmd);

		// debug
		$this->_D($cmd);

		// Store result
		while($res = $result->fetchArray(SQLITE3_ASSOC))
		{
			array_push($this->result, $res);
		}

		return $this;
	}

	//
	// @function Clear result set
	// 
	public function clearResult()
	{
		$this->result = null;
		$this->result = array();
		return $this;
	}

	// 
	// @function add new data
	// 
	public function add($JSONData)
	{
		$sql = "INSERT INTO %s(%s) VALUES(%s)";

		$data = json_decode($JSONData);

		// for object
		if(is_object($data))
		{
			$items = (array)$data;

			$keys = array();
			$values = array();
			foreach ($items as $key => $value) {
				array_push($keys, "`".$key."`");
				array_push($values, "'".$value."'");
			}

			// format command
			$cmd = sprintf($sql, $this->tableName, implode(',', $keys), implode(',', $values));

			// execute
			$this->dbHandler->exec($cmd);

			// debug
			$this->_D($cmd);

		}
		// for array
		else if(is_array($data))
		{
			$items = (array)$data;

			foreach ($items as $key => $value) {

				// execute self add function
				$this->add(json_encode($value));
			}
		}

		return $this;
	}

	// 
	// @function update data
	// 
	public function update($data)
	{
		$sql = "UPDATE %s SET %s WHERE ID = %s";
		
		// fetch update data ID from self result set
		foreach ($this->result as $item) {
			$ID = $item['ID'];
				
			// translate JSON data to PHP array
			$_data = (array)json_decode($data);

			$changes = array();

			foreach ($_data as $key => $value) {
				array_push($changes, $key."='".$value."'");
			}

			// format query
			$cmd = sprintf($sql, $this->tableName, implode(",", $changes) ,$ID);

			// debug
			$this->_D($cmd);

			// execute
			$this->dbHandler->exec($cmd);
		}

		return $this;
	}

	//
	// @function delete data
	// 
	public function delete()
	{
		$sql = "DELETE FROM %s WHERE ID = %s";

		// fetch delete data ID from self result set
		foreach ($this->result as $item) {
			$ID = $item['ID'];

			// format query
			$cmd = sprintf($sql, $this->tableName, $ID);

			// debug
			$this->_D($cmd);

			// execute
			$this->dbHandler->exec($cmd);
		}

		return $this;
	}


	// 
	// @function open SQLite database file
	// 
	private function openDatabase()
	{
		$this->dbHandler = new SQLite3($this->SQLiteFile);
	}

	// 
	// @function close Database
	// 
	private function closeDatabase()
	{
		$this->dbHandler->close();
	}

	public function __destruct()
	{
		$this->closeDatabase();
	}

}

?>