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
	public $primaryKey;
	
	public $dbConnection;
	
	//used by fetch function
	public $queryParams;
	
	//list of fields to search when querying during fetch if searchField is not given in fetchParams
	public $searchParams; 
	
	function __construct($name = null, $connection = null )
	{
		if(!$connection) $connection = $this->connection;
		if(!$name) $name = get_class($this);
		if(!$this->name) $this->name = $name;
		
		if(!$connection) $connection = 'default'; 
		if(!$this->connection) $this->connection = $connection;
		
		if(!$this->tableName) $this->tableName = strtolower($this->name);
		if(!$this->primaryKey) $this->primaryKey = 'id';
		
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
		return $exists ? true : false;
	}
	
	/*
	* checks the list of table fields and counter checks primary key
	*/
	protected function __getFields(){
		if( !isset($this->_fields) || !sizeof($this->_fields) )
		{
			$fields = $this->dbConnection->getFields($this->tableName);

			$this->primaryKey = $fields['primary'];
			$this->_fields = $fields['fields'];
		}

		return $this->_fields;
	}

	// THE FOLLOWING METHODS ARE SPECIALIZED QUERIES
	public function findFirst($options = array())
	{
		$options['limit'] = 1;
		$data = $this->find($options);
		if(isset($data[0]))
		{
			return $data[0];
		}
		
		return null;
	}
	
	public function findCount($options = array())
	{
		$options["fields"] = "COUNT(".$this->primaryKey.") as a";
		$count = $this->findFirst($options); 
		return $count['a'];
	}
	
	 
	public static function findQuery($dbConnection, $tableName, $options)
	{
		$where = " 1=1 ";
		if(isset($options["conditions"]))
		{
			if(is_array($options["conditions"]))
			{
				$where = array(); 
				foreach($options["conditions"] as $key => &$value)
				{
					if(is_string($value))
					{ 
						$value =   $dbConnection->quote($value)   ;
						
					}
					$where[] = $key ."=".$value;
				}
				$where = implode(" AND ", $where); 
			} else if(is_string($options["conditions"]))
			{
				$where = $options["conditions"];
			}
		} 
		
		if(isset($options["order"]))
		{
			$where .= " ORDER BY " . $options["order"];
		}
		
		if(isset($options['limit']))
		{
			if(is_array($options['limit']))
			{
				$limit = implode(",", $options["limit"]);
			}
			else 
			{
				$limit = $options["limit"];
			}
			
			$where .= " LIMIT ". $limit;
		}
		 
		
		if(isset($options["fields"]))
		{ 
			return $dbConnection->select($tableName, $where,  $options['fields']);
			
		} 
		
		return $dbConnection->select($tableName, $where, "");
	}
	
	

	function find($options = array())
	{
		if(!isset($options) && isset($this->fields)) 
		{
			$options['fields'] = $this->fields;    
		} 
		
		if(isset($options['fields']) && is_array($options['fields'])) 
		{
			$options['fields'] = implode(',', $options['fields']);
		}
		
		$data =  SELF::findQuery($this->dbConnection, $this->tableName, $options);
		
		if(isset($this->_fields))
		{ 
			if($data && sizeof($data))
			{   
				foreach($data as &$datum){ 
					foreach($datum as $column => &$value){
						if(isset($this->_fields[$column])){
							$type = $this->_fields[$column]; 
							
							switch ($type) {
								case 'INT': $value = (int) $value; break;
								case 'FLOAT': $value = (float) $value; break;
							}  
						}
					}
				}
			}
		}
		
		return $data;
	}
	
	static function updateQuery($dbConnection, $tableName, $info, $where)
	{
		$dbConnection->update($tableName, $info, $where);
		
		if($dbConnection->lastError) 
		{
			debug($dbConnection->lastError);
			return false;
		}
		
		return true;
	}
	
	function update($info, $id)
	{ 
		if(is_int($id)) $id = (int) $id; 
		$where = $this->primaryKey . " = '".  $id ."'"; 
		return self::updateQuery($this->dbConnection, $this->tableName, $info, $where);
	}
	
	
	function insert($info)
	{
		return self::insertQuery($this->dbConnection, $this->tableName, $info);
	}
	
	static function insertQuery($dbConnection, $tableName, $info)
	{
		return $dbConnection->insert($tableName, $info);
	}
	
	function get($id)
	{
		$data = $this->find(array('conditions'=>array($this->primaryKey=>$id), "limit" => 1));
		if(isset($data[0])) return $data[0];
	} 
	
	function getError()
	{
		$code = $this->getErrorCode();
		$error = $this->dbConnection->lastError;
		if($code == '23000')
			$error = str_replace('SQLSTATE[23000]: Integrity constraint violation: 1062 ', '', $error); 
		return $error;
	}
	
	function getErrorCode()
	{
		return $this->dbConnection->errorCode;
	}
	
	function delete()
	{
		\Ulap\Helpers\debug(array('tableName'=>$this->tableName, 'primaryKey'=>$this->primaryKey), 2);
		return self::deleteQuery($this->dbConnection, $this->tableName, $this->primaryKey, $this->id);
	}
	
	static function deleteQuery($dbConnection, $tableName,  $primaryKey, $id)
	{
		if($id)
		{	
			$dbConnection->delete($tableName, $primaryKey ." = '". $id ."'"); 
			
			if($dbConnection->lastError) return false;
			return true;
		}
		return false;
	}
	
	function run($sql, $bind = true, $procedure = false)
	{
		return self::runQuery($this->dbConnection, $sql, $bind, $procedure);
	} 
	
	static function runQuery($dbConnection, $sql, $bind = true, $procedure = false)
	{
		return $dbConnection->run($sql, $bind, $procedure);
	}
	
	public function insertOnUpdate($info) {
		$table = $this->tableName;
		$fields = $this->dbConnection->filter($table, $info);
		$sql = "INSERT INTO " . $table . " (" . implode($fields, ", ") . ") VALUES (:" . implode($fields, ", :") . ")";
		$bind = array();
		$u = array();
		foreach($fields as $field)
		{ 
			$bind[":$field"] = $info[$field];
			$u[] = $field.="=VALUES(".$field.")";
		}
		
		$sql.= " ON DUPLICATE KEY UPDATE ".implode(",",$u);
		
		$this->run($sql, $bind);  
		
		if($this->dbConnection->error) return false;
		return true;
	}
	
	
	
	// MORE SPECIAL QUERIES 
	
	private function __getFetchFields(){
		
		if(isset($this->queryParams['fields']))
		{
			return $this->queryParams['fields'];
		}
		
		if(isset($this->fields) && sizeof($this->fields))
		{
			return $this->fields;
		}
		
		
		return null;
	}
	
	private function __getSearchFields(){
		$searchFields = array();
		 
		 if(isset($this->queryParams['sxearchField']))
		 {
			$searchFields = array($this->queryParams['searchField']);
		 }
		 else if(sizeof($this->searchParams) > 0) 
		 {
			$searchFields = $this->searchParams;
		 }
		 else {
			 //search from list of fields that are string
			 foreach($this->_fields as $field => $type)
			 {
				if($type == 'string') $searchFields[] = $field;
			 }
		 }
		 
		 return $searchFields;
	}
	
	private function __getSearchConditions(){
		 
		 if(!isset($this->queryParams['searchPhrase'])) return null;
		 
		 if(empty($this->queryParams['searchPhrase'])) return null;
		
		 $searchFields = $this->__getSearchFields();
		 $searchPhrase = $this->queryParams['searchPhrase'];
		 
		 $conditions = array();
		 
		 foreach($searchFields as $field)
		 {
			$conditions[] = "`" .  $field . "` LIKE ". $this->dbConnection->quote( $searchPhrase.'%');
		 }
		 
		 return '(' . implode(' OR ', $conditions) .')';
	}
	
	private function __getFetchConditions(){
		
		$conditions = array();
		
		if(isset($this->queryParams['attach'])){
			
		}
		
	}
	
	/**
	 * called as api service and returns array to display in a datagrid
	 *
	 *  
	 */
	public function fetch(){
		
		//BUILD OPTIONS FOR FIND QUERY
		$options = array();
		
		$fields = $this->__getFetchFields();
		if($fields) $options['fields'] = $fields;
		
		$conditions = $this->__getFetchConditions();
		if($conditions) $options['conditions'] = $conditions;
		
		Helpers\debug($options);
		 
	}
}


// END OF FILE 

