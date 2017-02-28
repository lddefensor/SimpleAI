<?php 


/**
 * Helps check if a class exists
 * contains a list of static helpers
 */
 
declare(strict_types=1);
 
namespace Ulap\Helpers;

use Ulap\Helpers\MyRuntimeException as MyRuntimeException;

require_once('MyRuntimeException.php'); 
 
final class MyRuntimeHelper
{
	var $className;  
	var $reflection;
	
	public function __construct(string $path, string $className)
	{  
		if(!file_exists($path))
		{
			throw MyRuntimeException::MissingFile($path); 
		} 
		
		//include the file  
		require_once($path);  
		
		if(!class_exists($className))
		{
			throw MyRuntimeException::ClassNotFound($className);
		}
		
		$this->className = $className; 
	}
	
	/**
	 * instantiates a class given a name and arguments
	 */
	public function instantiateClass(array $args = null)
	{
		try 
		{ 
			$this->reflection = $reflector = new \ReflectionClass($this->className);
			
			if(!$reflector->isInstantiable())
				throw MyRuntimeException::ClassNotInstantiable($this->className);
			
			$constructor = $reflector->getConstructor();
			
			if(!$constructor)
			{
				$this->instance = $reflector->newInstanceWithoutConstructor();
			}
			else
			{
				//check if constructor has arguments
				$constructorRequiredArgs = $constructor->getNumberOfRequiredParameters();
				$constructorArgs = $constructor->getNumberOfParameters();
				 
				if($constructorArgs === 0 || ($constructorRequiredArgs == 0 && !$args))
				{
					$this->instance = $reflector->newInstance(); 
				}
				else 
				{ 
					if(sizeof($constructorRequiredArgs) > sizeof($args))
						throw new \Exception("Insufficient Parameters", 1);
						 
					$this->instance = $reflector->newInstanceArgs($args);
				}
				
			}
			
			return $this->instance;
			
		}
		catch (Exception $e)
		{
			throw MyRuntimeException::FailedToInstantiateClass($this->className, $e->getMessage());
		}
	}
	
	/**
	 * checks if the class has method
	 */
	public function hasMethod(string $method)
	{
		return $this->reflection->hasMethod($method);
	}
	
	/**
	 * calls a method
	 */
	public function invokeMethod(string $method, array $args = null)
	{
		try 
		{ 
			$reflector = $this->reflection->getMethod($method);
			
			$args = $reflector->getNumberOfParameters();
			$requiredArgs = $reflector->getNumberOfRequiredParameters();
			
			if($args == 0 || ($requiredArgs == 0 && $args == null))
			{
				return $this->instance->$method();
			}
			
			if($requiredArgs > sizeof($args))
				throw MyRuntimeException::InsufficientParameters($this->className, $method);
			
			return call_user_func_array(array($this->instance, $method), $args);
		}
		catch (ReflectionException $e)
		{ 
			throw MyRuntimeException::NoMethod($this->className, $method);
		}
	}
} 

/** END OF FILE **/
