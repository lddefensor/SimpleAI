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
		return $this->dbConnection->run($query, $args);
	}


	/*
	* calls a select query 
	*/
	public function find(array $options = array()){

		$query = $this->queryBuilder->buildSelectQuery($options); 
		
		return  $this->run($query);
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

	/*
	* calls a find but field count
	*/
	public function findCount(array $options = array()) : int{
		$options['fields'] = 'COUNT('.$this->primaryKey . ') as count_id';

		$result = $this->findFirst($options); 

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

		$this->run($query, $args, false);

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
		if(!$this->queryParams) return false;

		// BUILD OPTIONS FOR FETCHING
		$options = array(); 
 

		//allow search
		if(isset($this->queryParams['searchPhrase']))
		{
		}

		if(isset($this->queryParams['rowCount']))
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

		$rows = $this->find($options);

		unset($options['limit']);
		unset($options['page']);
		$totalCount = $this->findCount($options);


		return array('rows' => $rows , 'total' => $totalCount);

	}


}


// END OF FILE 

