<?php

/*
 * parses a query string into 'PATHS' of our application
 * has methods for determining the name of controller, method and parameters
 * including POST and GET DATA
 *
 * @updated March 2, 2017
 * Add Query Helper when routing for special 'q' routes
 */

declare(strict_types=1);
namespace Ulap\Helpers; 
  
final class RouterPath {
	
	/**
	 * array containing 'path' and 'query'
	 */
	public $queryString;

	public $isOtherApp = false; 
	public $basePath = ROOT . DS ;
	
	/**
	 * a list of strings from queryString['path'] exploded
	 */
	public $actions; 
	
	public function __construct(string $queryString = '')
	{	
		$this->url = $queryString;
		$this->queryString = parse_url($queryString);   
		 
		$this->actions = $this->hasPath() ? explode('/', $this->queryString['path']) : array(); 

		$app = $this->__getActionValue(0);
		$this->isOtherApp = $this->__isOtherApp($app);

		if($this->isOtherApp)
		{
			require_once APPS_DIR . DS . ucFirst($app) . DS . 'index.php';
			$this->basePath = ROOT_OTHER_APP . DS;
		}	


	} 

	private function __getActionValue(int $index, string $defaultValue = ''){
		return isset($this->actions[$index]) && !empty($this->actions[$index]) ? $this->actions[$index] : $defaultValue; 
	}

	private function __isOtherApp(string $path){
		if(sizeof(OTHER_APPS))
		{
			if(in_array($path, OTHER_APPS))
				return true;
		}

		return false;
	}
	
	/**
	 * a special path processed by QueryController
	 */
	public function isQ(){
		return ($this->hasPath() && $this->getController() === 'q');
	}
	
	public function hasPath(){
		return isset($this->queryString['path']);
	}
	
	public function hasQuery(){
		return isset($this->queryString['query']);
	}
	
	/*
	 * returns the first part of the query string as the name of the controller
	 */
	public function getController(): string{ 
		$index = $this->isOtherApp ? 1 : 0;
		return $this->__getActionValue($index, DEFAULT_ACTION);  
	}
	
	/*
	 * returns the second part of the query string as the name of the method
	 * but if it is of QueryController, the controller has one single accessPoint
	 */
	public function getMethod(): string{
		
		if($this->isQ()) return 'query';
		$index = $this->isOtherApp ? 2 : 1;

		return $this->__getActionValue($index, DEFAULT_METHOD);
	}
	
	/*
	 * returns the rest of the query string as arguments to the method
	 */
	public function getParameters(): array{
		$count = $this->isOtherApp ? 3 : 2;

		if($this->isQ()) $count -= 1;
		
		return sizeof($this->actions > $count) ? array_splice($this->actions, $count) : array();
	}
	
	/*
	 * $_POST or json_decoded of php:://input
	 */
	public function getData(): array{  

		$input = file_get_contents('php://input');  

		$jsonData = (array) json_decode($input);   

		if(!empty($_POST))
		{
			if(!$jsonData) $jsonData = [];
			foreach($_POST as $key => $value)
			{
				$jsonData[$key] = $value;
			}
		}

		return $jsonData ;
	}
	
	/*
	 * the $_GET of the query string
	 */
	public function getURLParams(): array{
		
		if(!$this->hasQuery()) return array();
		
		$urlParams = array();
		
		if(isset($this->queryString['query']))
			parse_str($this->queryString['query'], $urlParams);
		
		return $urlParams;
	}
	
	/** returns the name of controller class
	 */
	public function getControllerClassName(){
		if($this->isQ())
			return 'App\AppQueryController';
		
		return 'App\\'. ucfirst($this->getController()) . 'Controller';  
	}
	
	/*
	 * returns the path of the controller class 
	 */
	public function getControllerFilePath(){
		$basePath = $this->basePath ; 


		if($this->isQ())
			return $basePath . 'Controller' . DS . 'AppQueryController.php';
		
		return $basePath . 'Controller'. DS . ucfirst($this->getController()) . 'Controller.php';   
	}
}

// END OF FILE 