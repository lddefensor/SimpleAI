<?php

namespace Ulap;
 
require_once('Helpers'.DS.'MyRuntimeHelper.php');
require_once('Helpers'.DS.'MyRuntimeException.php');
require_once('DatabaseConnection.php');  
require_once(ROOT.DS.'Config'.DS.'database.php');

use App\DBConfig as DBConfig;
use Ulap\Helpers\MyRuntimeException as MyRuntimeException;

class Model {
	
	public $connection;
	public $name;
	public $tableName;
	private $_tableName;
	
	//contains the fields and their 'type'
	public $_fields;
	
	public $dbConnection;
	public $primaryKey = 'id';
	
	function __construct($name = null, $connection = null )
	{
		if(!$connection) $connection = $this->connection;
		if(!$name) $name = get_class($this);
		if(!$this->name) $this->name = $name;
		
		if(!$connection) $connection = 'default'; 
		if(!$this->connection) $this->connection = $connection;
		
		if(!$this->tableName) $this->tableName = strtolower($this->name); 
		
		$this->_tableName = $this->tableName;
		
		$this->__initializeConnection();
		
		$exists = $this->__tableExists(); 
		if(!$exists)
		{
			throw new MyRuntimeException('Table ' . $this->tableName . ' does not exists ', 4001);
		}
		
		//initialize fields
		$this->__getFields();
	} 
	
	/**
	 * creates a DatabaseConnection Instance
	 */
	protected function __createDBConnection($config)
	{
		return new DatabaseConnection("mysql:host=".$config["host"].";port=".$config["port"].";dbname=".$config["name"], $config["user"], $config["password"]);
	}
	
	/**
	 * Initializes Database Connection
	 */
	protected function __initializeConnection(){
		$this->dbConfig = new DBConfig();
		$config = $this->connection;  
		
		if(!isset($this->dbConfig->$config))
			throw new MyRuntimeException('Missing Database Configuration '. $config .' in database.php', 4002);
		
		$dbConn = $this->__createDBConnection($this->dbConfig->$config);
		$this->dbConnection = $dbConn; 
	}
	
	protected function __tableExists() : bool{
		$query =  'SHOW TABLES LIKE \''. $this->tableName. '\';'; 
		$exists = $this->dbConnection->run($query); 
		var_dump($query);
		return $exists ? true : false;
	}
	
	protected function __getFields(){
		$fields = $this->dbConnection->run("DESCRIBE ". $this->tableName. ";");
		$_fields = array();
		
		$key = 'id';
		
		if(is_array($fields))
		{  
			array_walk($fields, function(&$field, $key) use (&$_fields, &$key){
				$type = 'string';
				$t = strtoupper($field['Type']);  
				 
				if(strstr($t, 'INT(')) $type = 'INT';
				else if($t == 'DOUBLE') $type = 'FLOAT';
				else if($t == 'TIMESTAMP' || $t == 'DATETIME' || $t == 'DATE') $type='DATETIME';	
				 
				$_fields[$field['Field']] = $type;
				 
				if($field['Key'] == 'PRI') 
				{
					$key = $field['Field'];
				}
				
			}); 
			
			$this->_fields = $_fields; 
		}
		
		$this->primaryKey = $key;
	}
}


// END OF FILE 

