<?php

/**
 * Parent Class Controller of Application
 * @author Lorelie Defensor
 * 
 */ 
 
 namespace App;
	
 require_once (LIB . DS . 'Controller.php');
 require_once (LIB . DS. 'Helpers' . DS . 'MyRuntimeException.php');
 
 use Ulap\Controller as Controller;
 use Ulap\Helpers\MyRuntimeException as MyRuntimeException;
 
 class AppController extends Controller
 {
	function __getSessionId(){
		$sessionId = isset($this->data['session_id']) ? $this->data['session_id'] : (isset($this->urlParams['SID']) ? $this->urlParams['SID'] : null);
		return $sessionId;
	}
	
	public function beforeMethodCall(){
		 
			$this->useSession(true);
			
			$sessionId = $this->__getSessionId();
			
			if(!$sessionId)
			{ 
					return $this->redirect('/login');
			}
			
			$session =	$this->Sessions->findFirst(array("conditions"=>array("session_id" => $sessionId))); 
	}
	
	
 }

 // END OF FILE 