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
	
	/**
	 * a list of strings from queryString['path'] exploded
	 */
	public $actions; 
	
	public function __construct(string $queryString = '')
	{
		$this->queryString = parse_url($queryString);   
		 
		$this->actions = $this->hasPath() ? explode('/', $this->queryString['path']) : array();    
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
		return isset($this->actions[0]) && !empty($this->actions[0]) ? $this->actions[0] : DEFAULT_ACTION; 
	}
	
	/*
	 * returns the second part of the query string as the name of the method
	 * but if it is of QueryController, the controller has one single accessPoint
	 */
	public function getMethod(): string{
		
		if($this->isQ()) return 'query';
		
		return isset($this->actions[1]) && !empty($this->actions[1]) ? $this->actions[1] : DEFAULT_METHOD;	
	}
	
	/*
	 * returns the rest of the query string as arguments to the method
	 */
	public function getParameters(): array{
		$count = $this->isQ() ? 1 : 2 ;
		
		return sizeof($this->actions > $count) ? array_splice($this->actions, $count) : array();
	}
	
	/*
	 * $_POST or json_decoded of php:://input
	 */
	public function getData(): array{
		return (empty($_POST)  ? (array) json_decode(file_get_contents('php://input')) : $_POST) ?? array();
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
		if($this->isQ())
			return ROOT . DS . 'Controller' . DS . 'AppQueryController.php';
		
		return ROOT . DS. 'Controller'. DS . ucfirst($this->getController()) . 'Controller.php';   
	}
}

// END OF FILE 