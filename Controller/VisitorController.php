<?php

/**
 * Visitor Controller
 * checks if a visitor's broser and ip address were already recorded in the database
 * 
 */ 
 
 namespace App;
 
 require_once LIB . DS . 'Controller.php'; 
 require_once LIB . DS . 'Model.php'; 
 
 use Ulap\Helpers\HTMLHelper as HTMLHelper;
 use Ulap\Controller as Controller;
 
 class VisitorController extends Controller
 {
	public $isJson = true;
	 
	
	public function check(){
		$this->importModel('Visitors');
		
		extract($this->data);
		
		if(!isset($client) && !isset($ip) && !isset($app))  $key = $visitor_id;
		 
		else $key = $this->Visitors->check($client, $app, $ip, $visitor_id ?? '');
		
		return array('success'=>true, 'visitor_id'=>$key);
	}
	
 }

 // END OF FILE 