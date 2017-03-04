<?php
/**
 *Model interface for table eg-visitors
 * @author Lorelie Defensor
 */

namespace App;

require_once 'AppModel.php';

use App\AppModel as AppModel;

class Visitors extends AppModel{

	var $tableName = 'eg-visitors';
	
	function check(string $client, string $app, string $ip, string $visitor_id='') : string
	{
		$data = array(
			'client'=>$client,
			'app'=>$app,
			'ip'=>$ip
		);
		
		if(empty($visitor_id))
		{ 
			$visitor_id = substr(md5(microtime()*rand(0,9999)),0,20);
			
			$data['visitor_id'] = $visitor_id; 
			
			if($this->insert($data))
			{
				return $visitor_id;
			}  
			
			return '';
		} 
		else 
		{
			$count = $this->findCount(array('conditions'=>array('visitor_id'=>$visitor_id)));
			  
			if($count)
			{
				return $visitor_id;
			}
			
			$d = $this->findFirst(array('conditions'=>array('ip'=>$ip, 'app'=>$app))); 
			
			if($d)
			{
				return $d['visitor_id'];
			}
			else
			{
				$visitor_id = substr(md5(microtime()*rand(0,9999)),0,20);
				$data['visitor_id'] = $visitor_id; 
				
				if($this->insert($data))
				{
					return $visitor_id;
				} 

				var_dump( array('success'=>false, 'error'=>$this->dbConnection->lastError));
			}
			
			return $visitor_id;
	
		}
	} 
	  
}
