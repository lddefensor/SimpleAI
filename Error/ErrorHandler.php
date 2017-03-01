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
	
	public static function handle(MyRuntimeException $exception){ 
		 
		$title = 'Error!';
		$message = $exception->getMessage();
		 
		$code = $exception->getCode() || '';
		include ROOT . DS. 'Layouts' . DS . 'top.html';
		include ROOT . DS. 'Layouts' . DS . 'error.html';
		include ROOT . DS. 'Layouts' . DS . 'bottom.html';
	}
	
	public static function handleError($error){
		
		$title = 'Error!';
		$code = 9001;
		$message = array('MESSAGE: ' . $error['message'], 'FILE: '. $error['file'], 'LINE: '. $error['line']);
		$message = implode("\n", $message);
		include ROOT . DS. 'Layouts' . DS . 'top.html';
		include ROOT . DS. 'Layouts' . DS . 'error.html';
		include ROOT . DS. 'Layouts' . DS . 'bottom.html';
	}
}
 
 
 /** END OF FILE **/
