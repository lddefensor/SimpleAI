<?php
/**
 * @author Lorelie Defensor
 */
 namespace Ulap;
class DatabaseConnection extends \PDO {
	 
	 // Data Source Name
	 public $dsn;
	 
	 public $lastQuery;
	 public $lastArgs;
	 public $lastError = null;
	 
	 /**
	  * creates a PDO Instance where set error mode to EXCEPTION
	  * and disable persistent connection
	  */
	 public function __construct($dsn, $user='', $password)
	 {
	 	$this->dsn = $dsn;
		$options = array(
			\PDO::ATTR_PERSISTENT => false,
			\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
		); 
		
		try{
			parent::__construct($dsn, $user, $password, $options);
		}
		catch (\PDOException $e)
		{
			throw($e);
		} 
	 } 
	 
	 /**
	 * retrieves the columns and the type of the field
	 * given by  table name
	 */
	 public function getFields($table){ 
		$driver = $this->getAttribute(\PDO::ATTR_DRIVER_NAME);
		$key = ''; 
		switch(strtoupper($driver)){
			case 'SQLITE':
				$query = 'PRAGMA table_info(`'.$table.'`)';
				$key = 'name';
				break;
			case 'MYSQL':
				$query = 'DESCRIBE `' . $table . '`;';
				$key = 'Field';
				break;
			default:
				$query = 'SELECT column_name FROM information_schema.columns WHERE table_name=`'.$tbale.'`';
				$key = 'column_name';
				break; 
		}
		
		$fields = $this->run($query); 
		$_fields = array();
		
		$key = 'id';

		if(is_array($fields))
		{  
			array_walk($fields, function(&$field) use (&$_fields, &$key){
				$type = 'string';
				$t = strtoupper($field['Type']);  
				 
				if(strstr($t, 'INT(')) $type = 'INT';
				else if($t === 'DOUBLE') $type = 'FLOAT';
				else if($t === 'TIMESTAMP' || $t == 'DATETIME' || $t == 'DATE') $type='DATETIME';	
				else if(strstr($t, 'YEAR'))  $type= 'YEAR';
				 
				$_fields[$field['Field']] = $type;
				 
				if($field['Key'] == 'PRI') 
				{
					$key = $field['Field'];
				}
				
			});  

			$fields = $_fields;
		}

		return array('fields'=>$fields, 'primary'=>$key);
	} 
	
	 /**
	  * @param $args MIXED
	  */
	 private function makeSureItsArray($args)
	 {
	 	if(!is_array($args)) //can be  a string or int
		{
			if(!empty($args)) $args = array($args);
			else $args = array();
		}
		
		return $args;
	 }
	 
	 /**
	  * for queries that expects a response usually in array
	  */ 
	 private function hasReturn()
	 {
	 	return (preg_match("/^(" . implode("|", array("select", "call",  "describe", "pragma", "show")) . ") /i", $this->lastQuery));
	 }
	 
	 /* 
	  * for queries with no response but only rows affected
	  */
	 private function hasAffectedRows(){
	 	return (preg_match("/^(" . implode("|", array("delete", "insert", "update")) . ") /i", $this->lastQuery));
	 }

	 private function __saveQueryInSession($sql, $args = '')
	 {

		if(!isset($_SESSION['queries']))
			$_SESSION['queries'] = array();

 		
 		//do not include show and describe queries

		if(!Helpers\startsWith($sql, 'SHOW TABLES LIKE') && !Helpers\startsWith($sql, 'DESCRIBE'))	
		{ 
			if($args)
			{ 
				$sql = self::__interpolateQuery($sql, $args);

			}

			$_SESSION['queries'][] = $sql; 
		}
	 }

	 /**
	 * https://stackoverflow.com/questions/210564/getting-raw-sql-query-string-from-pdo-prepared-statements
	 * Replaces any parameter placeholders in a query with the value of that
	 * parameter. Useful for debugging. Assumes anonymous parameters from 
	 * $params are are in the same order as specified in $query
	 *
	 * @param string $query The sql query with parameter placeholders
	 * @param array $params The array of substitution parameters
	 * @return string The interpolated query
	 */
	public static function __interpolateQuery($query, $params) {
	    $keys = array();

	    # build a regular expression for each parameter
	    foreach ($params as $key => $value) {
	    	$key = str_replace(':', '', $key);
	        if (is_string($key)) {
	            $keys[] = '/:'.$key.'/';
	        } else {
	            $keys[] = '/[?]/';
	        }
	    } 

	    $query = preg_replace($keys, $params, $query, 1, $count);

	    #trigger_error('replaced '.$count.' keys');

	    return $query;
	}

	/*
	  * runs a query and returns results (if any) 
	  * @param $sql STRING
	  * @param $args MIXED
	  */
	 public function run(string $sql, $args='', bool $debug = true)
	 { 
	 	//update last query and arguments
	 	$this->lastQuery = trim($sql);
		$this->lastArgs = $this->makeSureItsArray($args);
		
		//reset last error
		$this->lastError = null;

		$this->__saveQueryInSession($sql, $args);
		
		try
		{
			if($debug)
			{ 
				\Ulap\Helpers\debug($args);
				\Ulap\Helpers\debug($sql, 2, 1);
			} 
			
			$statement = $this->prepare($this->lastQuery); 
			$response = $statement->execute($this->lastArgs);  
			
			if($response !== false)
			{
				if($this->hasReturn()) return $statement->fetchAll(\PDO::FETCH_ASSOC);
				
				if($this->hasAffectedRows()) return $statement->rowCount();
				
				$this->lastError = null;
				return $statement;
			}  
		}
		catch (\PDOException $e)
		{
			$this->lastError = array(
				'message' => $e->getMessage(),
				'code' => $e->getCode()
			);
			
			return false;
		}
	 }


	 
}	
/** END OF FILE **/
