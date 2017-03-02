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
	  * make sure that only fields from table are inside data
	  * @param $table STRING
	  * @param $data ARRAY ($key=>$value)
	  */
	 public function filterTableFieldsInArray(string $table, array $data) 
	 {
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
		if($fields)
		{
			return array_reduce($fields, function($a, $b) use ($data, $key){ 
				$f = $b[$key];
				 
				if(isset($data[$f])) {
					$a[$f] = $data[$f];
				}
				return $a;
			}, array());
		}  
		return array(); 
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
	
	 /*
	  * runs a query and returns results (if any) 
	  * @param $sql STRING
	  * @param $args MIXED
	  */
	 public function run($sql, $args='', bool $debug = true)
	 {
	 	//update last query and arguments
	 	$this->lastQuery = trim($sql);
		$this->lastArgs = $this->makeSureItsArray($args);
		
		//reset last error
		$this->lastError = null;
		
		try
		{
			if($debug)
			{ 
				if($args) \Ulap\Helpers\debug($args);
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
	 
	 
	 //SPECIAL FUNCTIONS here
	 public function select(string $table, string $where='', $fields='*', $args=''){
	 	
		$fields = empty($fields) ? '*' : $fields;
		
		$query = implode(" ", array("SELECT",$fields, "FROM", "`".$table."`" ));
			
		if(!empty($where))
		{
			$query .=  implode(" ", array(" WHERE", $where));
		}
		
		$query .= ";";
		
		\Ulap\Helpers\debug(array(
			'table' => $table,	
			'where' => $where,
			'fields' => $fields, 
			'args' => $args
		), 3, 1);
		\Ulap\Helpers\debug($query);
		
		return $this->run($query, $args, false);  
	 }
	 
	 public function update($table, $data, $where='', $args='')
	 {
		$data = $this->filterTableFieldsInArray($table, $data);
		
		$query = implode(' ', array('UPDATE', "`".$table."`" , 'SET '));
		
		$args  = $this->makeSureItsArray($args);
		
		$set = array();
		foreach($data as $key=>$value)
		{
			$a = ':update_' . $key;
			
			$set[] = $key.' = '.$a;
			$args[$a] = $value;
		}
		
		$query .= implode(', ', $set);
		
		$query .= ' WHERE ' . $where;
		
		return $this->run($query, $args);
	 }
	 
	 public function insert($table, $data)
	 {
	 	$data = $this->filterTableFieldsInArray($table, $data);
		$fields = array_keys($data);
		
		\Ulap\Helpers\debug($data);
		
		
		$query = 'INSERT INTO `' . $table . '` (' . implode(',', $fields)  . ') VALUES (:' . implode(', :', $fields) . ')';
		
		\Ulap\Helpers\debug($data, 2, 1);
		\Ulap\Helpers\debug($query);
		
		$args = array();
		foreach($fields as $field)
		{
			$args[':' . $field] = $data[$field];
		} 
		
		$this->run($query, $args, false);
		
		if(!$this->lastError) return $this->lastInsertId();
		
		return false; 
	 }
	 
	 public function delete($table, $where, $args = '')
	 {
	 	$query = 'DELETE FROM `' . $table . '` WHERE ' . $where . ';';
		$this->run($query, $args);
	 }
	 
}	
/** END OF FILE **/
