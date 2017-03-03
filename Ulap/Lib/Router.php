<?php

/*
 * Class that redirects the requested URL to specific page of application
 * @author Lorelie Defensor
 */
declare(strict_types=1);
namespace Ulap;

require_once('Helpers' . DS. 'helper.php');
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
		//initialize PHP SESSION
		session_start(); 
		$this->path = new Helpers\RouterPath($queryString); 
	}
	
	protected function __instantiateController(){
		
		$this->runtime = new MyRuntimeHelper($this->path->getControllerFilePath(), $this->path->getControllerClassName());
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
	 * throws RedirectException if the method calls a redirect
	 **/
	public function route()
	{ 
		try
		{ 
			$controller = $this->__instantiateController();
			
			if(isset($_SESSION['errors']))
			{
				$controller->errors = $_SESSION['errors'];
				unset($_SESSION['errors']);
			}
			
			$controller->beforeMethodCall();
			
			$method = $this->path->getMethod();
			
			if(strstr($method, '__') !== false || array_search($method, $controller->privateMethods) !== false)
				throw MyRuntimeException::AttemptToCallPrivateMethods($controller->name, $method);
			
			$result = $this->runtime->invokeMethod($method, $this->path->getParameters());
			
			$controller->afterMethodCall($result);
			
			if($controller->isJson)
			{
				header('Content-Type: application/json');
				echo json_encode($result); 
			}
			//renders the method 
			else if($controller->autoRender)
				$controller->render();
		}
		catch(MyRuntimeException $e)
		{
			$exceptionHandler = $this->ExceptionHandler;
			$exceptionHandler::handle($e);
		} 


		unset($_SESSION['debug']);
	}
	
	
} 
 
 
 // END OF FILE 
