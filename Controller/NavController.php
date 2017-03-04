<?php

/** TEST CONTROLLER
 *
 */

 namespace App;
 
 require_once 'AppController.php';
 require_once ROOT . DS . 'Model' . DS . 'AppModel.php'; 

 use App\AppModel as AppModel;
 
 /*
 * a public API
 */
 class NavController extends AppController {
	 
	var $name = 'NavController';
 	var $isJson = true;

	//override beforeMethodCall to do nothing
 	public function beforeMethodCall(){}

 	public function getDetails($id)
 	{
 		$this->importModel('Listings');
 		return $this->Listings->getDetails($id);
 	}

 }
 
 
 //END OF FILE 