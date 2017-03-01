<?php

/**
 * Sample Controller
 * @author Lorelie Defensor
 * 
 */ 
 
 namespace App;
 
 require_once 'AppController.php';
 
 use App\AppController as AppController;
 
 class HomeController extends AppController
 {
 	
	var $models = array('Users');
	
	public function index(){ 
		
	}
	
	
 }

 // END OF FILE 