<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;  

use Ulap\Helpers\MyRuntimeException as MyRuntimeException;
use Ulap\Helpers\MyRuntimeHelper as MyRuntimeHelper;

final class MyRuntimeHelperTest extends TestCase
{
	/*
	 * expect a TypeError when no arguments are given
	 */ 
	public function testNoArguments(){
		$this->expectException(TypeError::class);
		$runtime = new MyRuntimeHelper(); 
	}
	
	/*
	 * expect a TypeError when className is not defined
	 */
	 public function testNoClassName(){
		$this->expectException(TypeError::class);
		$runtime = new MyRuntimeHelper('');  
	 }
	
	/*
	 * expect a TypeError when path is not string
	 */
	 public function testPathIsInteger(){
		$this->expectException(TypeError::class);
		$runtime = new MyRuntimeHelper(1);  
	 }
	
	/*
	 * expect a TypeError when path is not string
	 */
	 public function testPathClassNameIsInteger(){
		$this->expectException(TypeError::class);
		$runtime = new MyRuntimeHelper(1, 3);  
	 }
	 
	
	/*
	 * expect a MissingFile Exception when path points 
	 * to a controller file that does not exists
	 * error code is 1002
	 */
	 public function testMisisngFile(){ 
		try
		{ 
			$runtime = new MyRuntimeHelper(ROOT.DS.'Controller'.DS.'Unknown.php', 'Unknown');
		}  
		catch (MyRuntimeException $e)
		{
			$this->assertEquals(1002, $e->getCode());
		}
	 }
	 
	 
	/*
	 * expect a ClassNotFound Exception when path points 
	 * to a controller file that exists but class is not defined
	 * error code is 1001
	 */
	 public function testClassNotFound(){
 
 		require_once LIB . DS . 'Controller.php';
 
	 	$path = ROOT. DS. 'Controller/HomeController.php'; 
		try
		{ 
			$runtime = new MyRuntimeHelper($path, 'UnknownController');
		}  
		catch (MyRuntimeException $e)
		{
			$this->assertEquals(1001, $e->getCode());
		}
	 }
	 
	/*
	 * expect a ClassNotFound Exception when path points 
	 * to a controller file that exists but class is not defined
	 * error code is 1001
	 */
	 public function testNoExceptionOnConstruction(){
 
 		require_once LIB . DS . 'Controller.php';
 
	 	$path = ROOT. DS. 'Controller/HomeController.php'; 
		$className = 'HomeController';
		$runtime = new MyRuntimeHelper($path, $className); 
		$this->assertEquals($className, $runtime->className);
	 }
	 
	 
	 /**
	  *  load a class tru runtime helper with no required arguments
	  * and check if class has loaded properly by checking a property
	  * Load Users Model Class
	  * check if instance = instance of Users Model
	  * check if instance->tableName == red_users
	  */
	 public function testInstantiateModelClass(){
	 	require_once ( LIB . DS . 'Model.php');
		
	 	$path = ROOT. DS. 'Model/UsersModel.php'; 
		$className = 'Users';
		$runtime = new MyRuntimeHelper($path, $className); 
		
		$instance = $runtime->instantiateClass();
		
		$this->assertInstanceOf(App\Users::class, $instance);
		
		$this->assertEquals($instance->tableName, 'red_users');
	 }
	 
	 
	 /**
	  *  load a class tru runtime helper with no required arguments
	  * and check if class has loaded properly by checking a property
	  * Load Home Controller Class
	  * check if instance = instance of HomeController
	  * check if instance has method 'landing'
	  * check if instance has no method 'display'
	  * invokeMethod - not existing - exception Reflection
	  * invokeMethod - existing - insufficient parameters - MyRuntimeException - error Code 1008 
	  * invokeMethod with sufficient arguments no exception and check if response is right
	  * */
	 public function testInstantiateControllerClass(){
	 	require_once ( LIB . DS . 'Controller.php');
		
	 	$path = ROOT. DS. 'Controller/HomeController.php'; 
		$className = 'HomeController';
		$runtime = new MyRuntimeHelper($path, $className); 
		
		$instance = $runtime->instantiateClass();
		
		$this->assertInstanceOf(App\HomeController::class, $instance);
		
		$this->assertTrue($runtime->hasMethod('landing'));
		$this->assertFalse($runtime->hasMethod('display'));
		
		$this->expectException(\ReflectionException::class);
		$runtime->invokeMethod('display');
		
		try 
		{ 
			$runtime->invokeMethod('landing');
		}
		catch (MyRuntimeException $e)
		{
			$this->assertEquals(1008, $e->getCode());
		}
		
		$arguments = 'Administrator';
		
		$response = $runtime->invokeMethod('landing', array($arguments));
		
		$this->assertEquals($response, $arguments);
	 }
	 
}

// END OF FILE
	