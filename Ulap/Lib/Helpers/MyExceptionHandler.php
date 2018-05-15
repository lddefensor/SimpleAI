<?php

/**
 * handles the runtime exception produced on route
 */
 
namespace Ulap\Helpers;
 
class MyExceptionHandler
{
	
	public static function Handle(MyRuntimeException $exception)
	{
		$title = 'Runtime Exception';
		$code = $exception->getCode(); 
		$message = $exception->getMessage();
		
		include(APP_ROOT.DS.'Lib'.DS.'Layouts'.DS.'error.html');
	}  
}

// END OF FILE 
