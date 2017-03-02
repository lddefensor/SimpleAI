<?php

/** 
 * Session Handling
 * 
 * @author Lorelie Defensor
 */
 
 namespace Ulap\Helpers;
 
 require_once(LIB . DS . 'Model.php');
 
 use Ulap\Helpers\MyRuntimeException as MyRuntimeException;
 use Ulap\Model as Model;
 
 class MySessionHelper extends Model {
		
		public $primaryKey = 'session_id';
	 
		public function __construct(string $name = null, string $connection = null)
		{
			$dbSession = defined('DB_SESSION') ? DB_SESSION : array();
			$tableName = isset($dbSession['tableName']) ? $dbSession['tableName'] : 'simple-ai-session';
			$connection = isset($dbSession['connection']) ? $dbSession['connection'] :  'default'; 
			
			parent::__construct($tableName, $connection);
			
			$this->primaryKey = 'session_id';
		}
		  
	protected function __tableExists() : bool{
		$exists = parent::__tableExists();
		
		if(!$exists)
		{
			//create sql
			$QUERY = "
		CREATE TABLE `".$this->tableName."` (
				`user_id` int(12) UNSIGNED NOT NULL DEFAULT '0',
				`session_id` varchar(20) NOT NULL,
				`user` text NOT NULL,
				`last_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
				`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`device_key` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
				`ip_address` varchar(25) NOT NULL DEFAULT '',
				`user_agent` varchar(255) NOT NULL DEFAULT ''
		) ENGINE=InnoDB DEFAULT CHARSET=latin1;
		
	ALTER TABLE `".$this->tableName."`
			ADD PRIMARY KEY (`session_id`);

		";

			$this->dbConnection->run($QUERY);
			
			return true;
		}
		
		return $exists;
	}
	
	
	function createSession($user)
	{ 
		//create session
		$id = randomString(20);  
		
		$found = true;
		
		while($found)
		{  
			$data = $this->find(array("conditions"=>array("session_id"=>$id)));
			$found = isset($data[0]);
		} 
		
		if(isset($user['hashed_password'])) unset($user['hashed_password']);
		
		$data = array(
			"session_id" => $id,
			"user_id" => $user["id"],
			"user" => json_encode($user)
		);
		
		$this->insert($data);
		
		$_SESSION['session_id'] = $id;
		
		$user["session_id"] = $id;   
		
		return $id;
	}
		
	function destroySession($sessionId)
	{
		unset($_SESSION['session_id']);
		$this->id = $sessionId;
		$this->delete();
	}
		
		
 
	}
 /** END OF FILE */
