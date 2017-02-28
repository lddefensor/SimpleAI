<?php

/*
 * parses a query string into 'PATHS' of our application
 * has methods for determining the name of controller, method and parameters
 * including POST and GET DATA
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
	 */
	public function getMethod(): string{
		return isset($this->actions[1]) && !empty($this->actions[1]) ? $this->actions[1] : DEFAULT_METHOD;	
	}
	
	/*
	 * returns the rest of the query string as arguments to the method
	 */
	public function getParameters(): array{
		return sizeof($this->actions > 2) ? array_splice($this->actions, 2) : array();
	}
	
	/*
	 * $_POST or json_decoded of php:://input
	 */
	public function getData(): array{
		return (empty($_POST)  ? json_decode(file_get_contents('php://input')) : $_POST) ?? array();
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
	
}

// END OF FILE 