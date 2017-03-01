<?php

//returns an invalid json response when encountered an error
require_once (APP_ROOT . DS . 'Lib' . DS . 'Helpers' . DS. 'MyExceptionHandler.php');

use Ulap\Helpers\MyExceptionHandler as MyExceptionHandler;
use Ulap\Helpers\MyRuntimeException as MyRuntimeException;

class ApiErrorHandler extends MyExceptionHandler
{
	
	public static function handle(MyRuntimeException $exception){
		$error = array('success'=>false, 'error'=>$exception->getMessage(), 'code'=>$exception->getCode());
	
		echo json_encode($error);
	}
}



/** END OF FILE **/