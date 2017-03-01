<?php

/*
 * Class that redirects the requested URL to specific page of application
 * @author Lorelie Defensor
 */
declare(strict_types=1);
namespace Ulap;

 
require_once('Helpers' . DS . 'RoutePath.php');
require_once('Helpers' . DS . 'MyRuntimeHelper.php');
require_once('Helpers' . DS . 'MyExceptionHandler.php'); 
require_once('Controller.php');

use Ulap\Helpers\MyRuntimeHelper as MyRuntimeHelper;
use Ulap\Helpers\MyRuntimeException as MyRuntimeException;

class Router
{
	public $path;
	public $runtime;
	
	//if not set defaults to MyExceptionHandler
	public $ExceptionHandler;
	
	public function __construct(string $queryString)
	{
		$this->path = new Helpers\RouterPath($queryString);   
	}
	
	protected function __instantiateController(){
		$className = ucfirst($this->path->getController()) . 'Controller';  
		
		$filePath = ROOT.DS.'Controller'. DS. $className. '.php';
		
		$this->runtime = new MyRuntimeHelper($filePath, 'App\\'.$className);
		$this->runtime->instantiateClass(array($this));
		
		$controller = $this->runtime->instance;
		$controller->currentMethod = $this->path->getMethod();
		$controller->data = $this->path->getData();
		$controller->urlParams = $this->path->getURLParams();
		
		return $controller;
	} 
	
	protected function beforeMethodCall(){
		
	}
	
	/** 
	 * initializes the application
	 * throws MyRuntimeException
	 **/
	public function route()
	{
		try
		{ 
			$controller = $this->__instantiateController(); 
			$controller->beforeMethodCall();
			
			$method = $this->path->getMethod(); 
			
			if(strstr($method, '__') !== false || array_search($method, $controller->privateMethods) !== false)
				throw MyRuntimeException::AttemptToCallPrivateMethods($controller->name, $method);
			
			$result = $this->runtime->invokeMethod($method, $this->path->getParameters());
			
			$controller->afterMethodCall($result);
			
			//renders the method
			if($controller->autoRender)
				$controller->render();
		}
		catch(MyRuntimeException $e)
		{
			$exceptionHandler = $this->ExceptionHandler;
			$exceptionHandler::handle($e);
		}
	}
} 

 
 // END OF FILE 
