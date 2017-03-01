<?php

/**
 * Sample Controller
 * @author Lorelie Defensor
 * 
 */ 
 
 namespace App;
 
 require_once 'AppController.php';
 require_once LIB . DS. 'Helpers' . DS . 'helper.php';
 require_once LIB . DS . 'Helpers' . DS . 'HTMLHelper.php';
 
 use Ulap\Helpers\HTMLHelper as HTMLHelper;
 use App\AppController as AppController;
 
 class LoginController extends AppController
 {
	public function beforeMethodCall(){}
	
	public function index(){ 
		// no logic here just render //can be removed actually 
		$sessionId = $this->__getSessionId();
		
		if($sessionId) $this->redirect('/home');
	}
	
	/**
	 *checks if a given field is in data
	 */
	private function __hasField(string $field) : bool{ 
		return isset($this->data[$field]) && !empty($this->data[$field]);  
	}
	
	/**
	 *updates $error for required fields
	 */
	private function __checkForRequiredField($field) 
	{
		if(!$this->__hasField($field)) $this->errors[$field] = 'Please fill up this field.';
	}
	
	/*
	 * is called by the Login Form in index
	 */
	public function login(){
		
		//reset errors
		$this->errors = array();
		
		$this->__checkForRequiredField('username');
		$this->__checkForRequiredField('passwrd');
		
		if($this->__hasErrors())
		{
			return $this->redirect('/login');
		}
		
		extract($this->data);
		
		$conditions = array('username' => $username, 'user_status'=>'Active');
		
		$this->importModel('Users');
		
		$user = $this->Users->findFirst(array("conditions"=>$conditions));
		
		if($user)
		{
			$this->useSession(true);
			$this->Session->createSession($user);
			
			$this->message = 'You have logged in.';
			return $this->redirect('/home'); 
		}
		
		$this->errors['username'] = 'User is not found or inactive';
		$this->redirect('/login');
	}
	
	/**
	 * is called by logout link
	 */
	public function logout(){
		$sessionId = $this->__getSessionId();
		
		if($sessionId)
		{
			$this->useSession(true);
			$this->Session->destroySession($sessionId);
			 
		}
		unset($_SESSION['debug']);
		$this->redirect('/login');
	}
	
	
 }

 // END OF FILE 