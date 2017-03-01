<?php

/**
 * Sample Controller
 * @author Lorelie Defensor
 * 
 */ 
 
 namespace App;
 
 require_once 'AppController.php';
 require_once LIB . DS . 'Helpers' . DS . 'HTMLHelper.php';
 
 use Ulap\Helpers\HTMLHelper as HTMLHelper;
 use App\AppController as AppController;
 
 class LoginController extends AppController
 {
	
	public function beforeMethodCall(){
		$sessionId = $this->__getSessionId();
		
		if($sessionId) $this->redirect('/home');
	}
	
	public function index(){ 
		
	}
	
	
 }

 // END OF FILE 