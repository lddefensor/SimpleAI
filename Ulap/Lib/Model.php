<?php

namespace Ulap;
 
require_once('Helpers'.DS.'MyRuntimeHelper.php');
require_once('Helpers'.DS.'MyRuntimeException.php');
require_once('Helpers'.DS.'QueryHelper.php');
require_once('DatabaseConnection.php');  
require_once(ROOT.DS.'Config'.DS.'database.php');

use App\DBConfig as DBConfig;
use Ulap\Helpers\MyRuntimeException as MyRuntimeException;
use Ulap\Helpers\QueryHelper as QueryHelper;

class Model {
	
	public $connection;
	public $name;
	public $tableName;
	private $_tableName;
	
	//contains the fields and their 'type'
	public $_fields;
	public $primaryKey;
	
	public $dbConnection;
	public $queryBuilder;
	
	//used by fetch function
	public $queryParams;
	
	//list of fields to search when querying during fetch if searchField is not given in fetchParams
	public $searchParams;  

	public $lastError;
	public $lastErrorCode;


	// not required fields
	// $uniqueFields
	// $uniqueMessage
	
	function __construct($name = null, $connection = null )
	{ 
		if(!$connection) $connection = $this->connection;
		if(!$name) $name = get_class($this);
		if(!$this->name) $this->name = $name;
		
		if(!$connection) $connection = 'default'; 
		if(!$this->connection) $this->connection = $connection;
		
		if(!$this->tableName) $this->tableName = strtolower($this->name);
 
		if(!$this->primaryKey) 
		{
			$this->primaryKey = 'id';
		}  
		
		$this->_tableName = $this->tableName;
		
		$this->__initializeConnection();
		
		$exists = $this->__tableExists(); 

		if(!$exists)
		{
			throw new MyRuntimeException('Table ' . $this->tableName . ' does not exists ', 4001);
		} 
		//initialize fields
		$this->__getFields();
 
		$this->queryBuilder = new QueryHelper($this);
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
 
 			if(!$this->primaryKey)
				$this->primaryKey = $fields['primary'];
			$this->_fields = $fields['fields'];
		}

		return $this->_fields;
	} 

	public function run($query, $args = '')
	{
		$this->lastError = null;
		$this->lastErrorCode = null;
		return $this->dbConnection->run($query, $args);
	} 

	/*
	* called every find
	* can be overriden 
	* parse all returns appropriate to type of field
	*/
	public function afterFind(&$results)
	{ 
		
		if(!$results || !sizeof($results)) return array();

		foreach($results as &$result)
		{
			foreach($result as $field => $value)
			{
				if(isset($this->_fields[$field]))
				{
					$type = $this->_fields[$field]; 

					if(strtolower($type) == 'int') $value = (int) $value;  
					if(strtolower($type) == 'float') $value = (float) $value;
					if(strtolower($type) == 'year') $value = (int) $value;
 
 					$result[$field] = $value;
				}
			}
		}

		return $results;
	}

	/*
	* calls a select query 
	*/
	public function find(array $options = array()){

		$query = $this->queryBuilder->buildSelectQuery($options);  
		
		$results =  $this->run($query);

		if(!isset($this->_findCount))
			$this->afterFind($results); 
 
		return $results;
	}

	/**
	* calls a find but with limit 1
	*/
	public function findFirst(array $options = array())
	{
		$options['limit'] = 1;
		$result = $this->find($options);  
 
		if(isset($result[0])) return $result[0];

		return null;
	}

	/**
	* find first by primaryKey
	*/
	public function get($id)
	{
		$options = array('conditions'=>array());
		$options['conditions'][$this->primaryKey] = $id;

		return $this->findFirst($options);
	}

	/*
	* calls a find but field count
	*/
	public function findCount(array $options = array()) : int{
		$options['fields'] = 'COUNT('.$this->primaryKey . ') as count_id';

		$this->_findCount = true;

		$result = $this->findFirst($options); 

		unset($this->_findCount);

		if(isset($result['count_id'])) return $result['count_id'];

		return 0;
	}

	/*
	* runs an insert query (single valued data only)
	*/
	public function insert(array $data)  
	{

		$build = $this->queryBuilder->buildInsertQuery($data); 
		extract($build); 

		$this->run($query, $args); 

		if(!$this->dbConnection->lastError) return $this->dbConnection->lastInsertId();

		return false;
	}

	/*
	* runs an update query
	*/
	public function update(array $data, $id)
	{
		$build = $this->queryBuilder->buildUpdateByIdQuery($data, $id);

		extract($build);  

		$this->run($query, $args, false);

		return $this->dbConnection->lastError ? false : true;
  
	}

	/*
	* runs a delete query
	* id of model should be set first
	*/
	public function delete()
	{
		if(!isset($this->id) || !$this->id) return false;

		$query = $this->queryBuilder->buildDeleteByIdQuery($this->id);

		$this->run($query);

		return $this->dbConnection->lastError ? false : true;
	}

	/*
	* runs an insert on update query
	*/
	public function insertOnUpdate(array $data)
	{ 
		$build = $this->queryBuilder->buildInsertOnUpdateQuery($data);
		extract($build); 

		$this->run($query, $args, false);

		return $this->dbConnection->lastError ? false : true;
	}


	/*
	* special function called by grid
	*/
	public function fetch()
	{   
		// BUILD OPTIONS FOR FETCHING
		$options = array(); 
		$options['conditions'] = array(); 

		//allow direct send of conditions
		if(isset($this->queryParams['conditions']))
		{ 
			$options['conditions'] = (array) $this->queryParams['conditions'];
		} 


		//allow search
		if(isset($this->queryParams['searchPhrase']))
		{
			$subQuery =  'LIKE %' . $this->queryParams['searchPhrase'] . '%';
			if(isset($this->queryParams['searchField']))
			{
				$options['conditions'][$this->queryParams['searchField']] = $subQuery;

			}
			else if(isset($this->searchParam))
			{
				$options['conditions'][$this->searchParam] = $subQuery;
			}
			else if(isset($this->searchParams) && sizeof($this->searchParams))
			{
				$conditions = array();
				foreach($this->searchParams as $param)
				{
					$conditions[$param] = $subQuery;
				}
				$options['conditions']['OR'] = $conditions;
			} 

		}

		if(isset($this->queryParams['attach']))
		{ 
			$attach = (array) $this->queryParams['attach'];

			$additionalConditions = array();

			foreach($attach as $key=>$value)
			{
				$additionalConditions[$key] = $value;
			}

			if(isset($options['conditions']))
				$options['conditions'] =   array($options['conditions'], $additionalConditions);  
			else $options['conditions'] = $additionalConditions;
 
		}

		if(isset($this->queryParams['rowCount']) && $this->queryParams['rowCount'])
			$options['limit'] = $this->queryParams['rowCount'];

		if(isset($this->queryParams['current']))
			$options['page'] = $this->queryParams['current'];

		if(isset($this->queryParams['sort']))
		{
			$sort = (array) $this->queryParams['sort'];
			// name - value pair
			$sortField = array_keys($sort) [0];

			$options['order'] = $sortField . ' ' . $sort[$sortField];
 		} 
 		else if(isset($this->_fields['order_field']))
 		{ 
 			$options['order'] = 'order_field';
 		}
 		else if(isset($this->order))
 		{
 			$options['order'] = $this->order;
 		}
 		
		$rows = $this->findFetch($options);

		unset($options['limit']);
		unset($options['page']);
		$totalCount = $this->findCount($options); 

		$current = isset($this->queryParams['current']) ? $this->queryParams['current'] : 1;

		return array('rows' => $rows , 'total' => $totalCount, 'current' => $current);

	}

	public function findFetch(array $options = []) {
		return $this->find($options);
	}

	/**
	* saves the data (decides if it's an insert or an update)
	* should use property unique field of model
	* returns integer (id) or boolean false
	*/
	public function saveUnique(array $data, string $name = ''){

		$name = empty($name) ? $this->name : $name;

		if(!isset($this->uniqueFields)) throw new MyRuntimeException('No unique fields, unable to peform save unique');

		$uniqueFields = $this->uniqueFields; 
		
		$conditions = array();  
		
		foreach($uniqueFields as $field){
			$conditions[$field] = $data[$field];
		}
		
		$exists = $this->find(array("fields"=>array("id"), "conditions"=>$conditions)); 


		$errorCode = 1001;
		$errorMessage = isset($this->uniqueMessage) ? $this->uniqueMessage : $name . ' already exists'; 

		$pkey = isset($this->primaryKey) ? $this->primaryKey : 'id'; 
 


		if(isset($data[$pkey]) && $data[$pkey])
		{
			$id = $data[$pkey];
			unset($data[$pkey]);
			
			if(sizeof($exists))
			{
				$eid = $exists[0]['id'];
				if((int) $eid != (int) $id) 
				{
					$this->lastError = $errorMessage;
					$this->lastErrorCode = $errorCode;
					return false; 
				}
			}
			
			if($this->update($data, $id))
			{
				return $id;
			}
			
			$this->lastError = 'Failed to save: ' . $this->getErrorMessage();
			
			return false;
		}  
		
		if(sizeof($exists)) 
		{ 
			$this->lastError = $errorMessage;
			$this->lastErrorCode = $errorCode;
			return false;  
		}

		$id = $this->insert($data);
		
		if($id) 
				return $id;
			
		$this->lastError = 'Failed to save: ' . $this->getErrorMessage();
		
		return false;
	}

	public function reorder($current, $replace, $id = null){ 
		
		$table = $this->tableName;
		
		$options = $this->__getReorderConditions($current, $replace, $id);
		extract($options);
		
		$query = "UPDATE $table SET $fields WHERE $conditions";
		$this->run($query);
		
		$query = "UPDATE $table SET order_field = $replace WHERE id = $id";
		$this->run($query);  
		
		return array('success'=>true);
	}

	function __getReorderConditions($current, $replace, $id)
	{

		if($current < $replace)
		{
			$SET = "order_field = order_field - 1";
			$CONDITIONS = "order_field <= $replace AND order_field > $current AND id != $id;"; 
		} 
		else
		{
			$SET = "order_field = order_field + 1";
			$CONDITIONS = "order_field >= $replace AND order_field < $current AND id != $id;";  
		} 
		
		return array('fields'=>$SET, 'conditions'=>$CONDITIONS);
	}

	/*
	* returns the last error message encountered by db connection
	*/
	public function getErrorMessage() {

		if($this->dbConnection->lastError)
			return $this->dbConnection->lastError['message'];
		
		return $this->lastError;
	}

	/*
	* returns the last error code encountered by db connection
	*/
	public function getErrorCode(){
		if($this->dbConnection->lastError)
			return $this->dbConnection->lastError['code'];
		
		return $this->lastErrorCode;
	}

}


// END OF FILE 

