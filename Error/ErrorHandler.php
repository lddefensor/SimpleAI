<?php

/**
 * Custom error handler for Estate Grabber
 * @author Lorelie Defensor
 */ 
 
namespace App;

require_once (LIB. DS . 'Helpers' . DS. 'MyExceptionHandler.php');

use Ulap\Helpers\MyExceptionHandler as MyExceptionHandler;
use Ulap\Helpers\MyRuntimeException as MyRuntimeException;

class ErrorHandler extends MyExceptionHandler
{
	
	public function handle(MyRuntimeException $exception){
		include ROOT . DS. 'Layouts' . DS . 'top.html';
		include ROOT . DS. 'Layouts' . DS . 'error.html';
		include ROOT . DS. 'Layouts' . DS . 'bottom.html';
	}
}



/** END OF FILE **/
 
 
 /** END OF FILE **/
