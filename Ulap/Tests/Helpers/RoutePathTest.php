<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;  

use Ulap\Helpers\RouterPath as RouterPath;

final class RouterPathTest extends TestCase
{
	//helper functions for testing paths
	
	/*
	 * returns an empty string
	 */
	function emptyPath () : string{
		return '';
	} 
	
	/*
	 * returns ?
	 */
	function emptyQuery () : string {
		return '?';
	}
	/*
	 * returns random string specified by count
	 */
	function pathByCount(int $count) : array {
		$i = 0;
		$paths = array();
		while ($i < $count){
			$paths[] = randomString(5);
			$i ++;
		}
		
		return $paths;
	}
	/*
	 * returns nameValue pair by count
	 */
	function queryByCount(int $count) : array {
		$i = 0;
		$paths = array();
		while ($i < $count){
			$paths[] = randomString(5) . '=' . randomString(5);
			$i ++;
		}
		
		return $paths;
	}
	
	/**
	 * tests that an empty path 
	 * hasPath = true
	 * hasQuery = false
	 * controller = default action
	 * method = default method
	 * parameters = 0
	 * data = 0
	 * urlParams = 0
	 */
	public function testEmptyPath(){
		 	
		$path = $this->emptyPath();
		$PATH = new RouterPath($path);
		
		$this->assertTrue($PATH->hasPath());
		$this->assertFalse($PATH->hasQuery());  
		
		$this->assertEquals($PATH->getController(), DEFAULT_ACTION); 
		$this->assertEquals($PATH->getMethod(), DEFAULT_METHOD);
		 
		$this->assertCount(0, $PATH->getParameters());
		$this->assertCount(0, $PATH->getData());
		$this->assertCount(0, $PATH->getURLParams());
	}
	
	/**
	 * tests a random string single path 
	 * hasPath = true
	 * hasQuery = false
	 * controller != default action
	 * method = default method
	 * parameters = 0
	 * data = 0
	 * urlParams = 0
	 */
	public function testSinglePath(){
		$path = implode('/', $this->pathByCount(1));
		
		$PATH = new RouterPath($path);
		
		$this->assertTrue($PATH->hasPath());
		$this->assertFalse($PATH->hasQuery());  
		
		$controller = $PATH->getController();
		$this->assertNotEquals($controller, DEFAULT_ACTION); 
		$this->assertEquals($controller, $path);
		$this->assertEquals($PATH->getMethod(), DEFAULT_METHOD);
		 
		$this->assertCount(0, $PATH->getParameters());
		$this->assertCount(0, $PATH->getData());
		$this->assertCount(0, $PATH->getURLParams()); 
	}
	
	/**
	 * tests a random string -> two paths 
	 * hasPath = true
	 * hasQuery = false
	 * controller != default action
	 * method = !default method
	 * parameters = 0
	 * data = 0
	 * urlParams = 0
	 */
	public function testTwoPaths(){
		
		$paths = $this->pathByCount(2);
		$path = implode('/', $paths);
		
		$PATH = new RouterPath($path);
		
		$this->assertTrue($PATH->hasPath());
		$this->assertFalse($PATH->hasQuery());  
		
		$controller = $PATH->getController();
		$this->assertNotEquals($controller, DEFAULT_ACTION); 
		$this->assertEquals($controller, $paths[0]);
		
		$method = $PATH->getMethod();
		$this->assertNotEquals($method, DEFAULT_METHOD); 
		$this->assertEquals($method, $paths[1]);
		 
		$this->assertCount(0, $PATH->getParameters());
		$this->assertCount(0, $PATH->getData());
		$this->assertCount(0, $PATH->getURLParams()); 
	}
	
	/**
	 * tests a random count of paths
	 * hasPath = true
	 * hasQuery = false
	 * controller != default action
	 * method = !default method
	 * parameters = size of random paths - 2
	 * data = 0
	 * urlParams = 0
	 */
	public function testRandomPaths(){
		
		$count = rand(3, 10);
		$paths = $this->pathByCount($count);
		
		$path = implode('/', $paths); 
		
		$PATH = new RouterPath($path);
		
		$this->assertTrue($PATH->hasPath());
		$this->assertFalse($PATH->hasQuery());  
		
		
		$controller = $PATH->getController();
		$this->assertNotEquals($controller, DEFAULT_ACTION); 
		$this->assertEquals($controller, $paths[0]);
		
		$method = $PATH->getMethod();
		$this->assertNotEquals($method, DEFAULT_METHOD); 
		$this->assertEquals($method, $paths[1]);
		
		 
		$this->assertCount( ($count - 2), $PATH->getParameters());
		$this->assertCount(0, $PATH->getData());
		$this->assertCount(0, $PATH->getURLParams()); 
	}
	
	
	
	/**
	 * tests that a string = '?'
	 * hasPath = false
	 * hasQuery = false
	 * controller = default action
	 * method = default method
	 * parameters = 0
	 * data = 0
	 * urlParams = 0
	 */
	public function testEmptyPathWithEmptyQuery(){
		 	
		$path = $this->emptyQuery();
		$PATH = new RouterPath($path);
		
		$this->assertFalse($PATH->hasPath());
		$this->assertFalse($PATH->hasQuery());  
		
		$this->assertEquals($PATH->getController(), DEFAULT_ACTION); 
		$this->assertEquals($PATH->getMethod(), DEFAULT_METHOD);
		 
		$this->assertCount(0, $PATH->getParameters());
		$this->assertCount(0, $PATH->getData());
		$this->assertCount(0, $PATH->getURLParams());
	} 
	
	/**
	 * tests that an empty path followed by random count of name=value pair
	 * hasPath = false
	 * hasQuery = true
	 * controller = default action
	 * method = default method
	 * parameters = 0
	 * data = 0
	 * urlParams = random count of name=value pair
	 */
	public function testEmptyPathWithQuery(){
		
		$count = rand(1,10);
		$query = $this->queryByCount($count);
		$path = '?' . implode('&', $query);
		$PATH = new RouterPath($path);
		
		$this->assertFalse($PATH->hasPath());
		$this->assertTrue($PATH->hasQuery());  
		
		$this->assertEquals($PATH->getController(), DEFAULT_ACTION); 
		$this->assertEquals($PATH->getMethod(), DEFAULT_METHOD);
		 
		$this->assertCount(0, $PATH->getParameters());
		$this->assertCount(0, $PATH->getData());
		$this->assertCount($count, $PATH->getURLParams());  
	}
	
	
	
	/**
	 * tests a random string single path with random number of query
	 * hasPath = true
	 * hasQuery = true
	 * controller != default action
	 * method = default method
	 * parameters = 0
	 * data = 0
	 * urlParams = random number of query
	 */
	public function testSinglePathWithQuery(){
		$path = implode('/', $this->pathByCount(1));
		
		$queryCount = rand(1, 10);
		$query = $this->queryByCount($queryCount);
		
		$PATH = new RouterPath($path . '?' . implode('&', $query));
		
		$this->assertTrue($PATH->hasPath());
		$this->assertTrue($PATH->hasQuery());  
		
		$controller = $PATH->getController();
		$this->assertNotEquals($controller, DEFAULT_ACTION); 
		$this->assertEquals($controller, $path);
		$this->assertEquals($PATH->getMethod(), DEFAULT_METHOD);
		 
		$this->assertCount(0, $PATH->getParameters());
		$this->assertCount(0, $PATH->getData());
		$this->assertCount($queryCount, $PATH->getURLParams()); 
	}
	
	
	/**
	 * tests a random string -> two paths  with random query
	 * hasPath = true
	 * hasQuery = true
	 * controller != default action
	 * method = !default method
	 * parameters = 0
	 * data = 0
	 * urlParams = random query
	 */
	public function testTwoPathsWithQuery(){
		
		$paths = $this->pathByCount(2);
		$path = implode('/', $paths);
		$queryCount = rand(1, 10);
		$query = $this->queryByCount($queryCount);
		
		$PATH = new RouterPath($path . '?' . implode('&', $query));
		
		$this->assertTrue($PATH->hasPath());
		$this->assertTrue($PATH->hasQuery());  
		
		$controller = $PATH->getController();
		$this->assertNotEquals($controller, DEFAULT_ACTION); 
		$this->assertEquals($controller, $paths[0]);
		
		$method = $PATH->getMethod();
		$this->assertNotEquals($method, DEFAULT_METHOD); 
		$this->assertEquals($method, $paths[1]);
		 
		$this->assertCount(0, $PATH->getParameters());
		$this->assertCount(0, $PATH->getData());
		$this->assertCount($queryCount, $PATH->getURLParams()); 
	}
	
	/**
	 * tests a random count of paths with random query count
	 * hasPath = true
	 * hasQuery = true
	 * controller != default action
	 * method = !default method
	 * parameters = size of random paths - 2
	 * data = 0
	 * urlParams = random query count
	 */
	public function testRandomPathsWithQuery(){
		
		$count = rand(3, 10);
		$paths = $this->pathByCount($count);
		
		$path = implode('/', $paths); 
		
		$queryCount = rand(1, 10);
		$query = $this->queryByCount($queryCount);
		
		$PATH = new RouterPath($path . '?' . implode('&', $query));
		
		$this->assertTrue($PATH->hasPath());
		$this->assertTrue($PATH->hasQuery());  
		
		
		$controller = $PATH->getController();
		$this->assertNotEquals($controller, DEFAULT_ACTION); 
		$this->assertEquals($controller, $paths[0]);
		
		$method = $PATH->getMethod();
		$this->assertNotEquals($method, DEFAULT_METHOD); 
		$this->assertEquals($method, $paths[1]);
		
		 
		$this->assertCount( ($count - 2), $PATH->getParameters());
		$this->assertCount(0, $PATH->getData());
		$this->assertCount($queryCount, $PATH->getURLParams()); 
	} 
	
}


/** END OF FILE **/