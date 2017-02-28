<?php

/**
 * Sample Controller
 * @author Lorelie Defensor
 * 
 */ 
 
 namespace App;
 
 use Ulap\Controller as Controller;
 
 class HomeController extends Controller
 {
 	
	var $models = array('Users');
	
	
	public function landing(string $type){
		
		return $type;
	} 
 }

 // END OF FILE 