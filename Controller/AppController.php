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
		
		if(isset($this->data['session_id'])) return $this->data['session_id'];
		
		if(isset($this->urlParams['SID'])) return $this->urlParams['SID'];
		
		if(isset($_SESSION['session_id'])) return $_SESSION['session_id'];
		
		return null;
	}
	
	public function beforeMethodCall(){
		 
		$this->useSession(true);
		
		$sessionId = $this->__getSessionId(); 
		
		if(!$sessionId)
		{ 
				return $this->redirect('/login');
		}
		
		$session =	$this->Session->findFirst(array("conditions"=>array("session_id" => $sessionId))); 
	}
	
	public function beforeRenderView(){
		$this->viewData['session_id'] = $this->__getSessionId();
	}
	
 }

 // END OF FILE 