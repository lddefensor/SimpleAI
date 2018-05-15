<?php

/**
 * Handles Runtime Exception when instantiating classes from query string
 * @author Lorelie Defensor
 */ 
 
namespace Ulap\Helpers;

class MyRuntimeException extends \Exception{
	
	public static function UndefinedSessionHandling($type){
		
		return new MyRuntimeException(
			'Unable to define session handling of type: ' . $type .'. Not yet supported',
			3001);
	} 
	
	public static function ViewFolderNotFound($className, $method)
	{
		return new MyRuntimeException(
			'View folder not found for '. $className . ' - '. $method,
			2001);
	}
	
	public static function InsufficientParameters($className, $method)
	{
		return new MyRuntimeException(
			'Method ' . $method . ' in class '. $className . ' failed to invoke because of insufficient parameters',
			1008);
	}
	
	public static function NoMethod($className, $method)
	{
		return new MyRuntimeException(
			'Method ' . $method . ' in class '. $className . ' not available.',
			1007);
	}
	
	public static function NoConstructor($className)
	{
		return new MyRuntimeException(
			'Failed to create instance of '.$className. '. Class has no constructor ',
			1006);
	}
	
	public static function InsufficientConstructorParameters($className)
	{
		return new MyRuntimeException(
			'Failed to create instance of '.$className. '. Insufficient parameters for constructor', 
			1005);
	}  
	
	public static function ClassNotInstantiable($className)
	{
		return new MyRuntimeException(
			'Failed to create instance of '.$className. '. Class is not instantiable ', 
			1004);
	} 
	
	public static function FailedToInstantiateClass($className, $message)
	{
		return new MyRuntimeException(
			'Failed to instantiate class '.$className. ' with message: '. $message, 
			1003);
	} 
	
	public static function MissingFile($path)
	{
		return new MyRuntimeException(
			'Class file is not found on path: ' . $path, 
			1002); 
	}	
	
	public static function ClassNotFound($className)
	{
		return new MyRuntimeException(
			'Class '. $className. ' not found. Failed to include.',
			1001); 
	}
	
	public function __construct($message, $code = 0, Exception $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
	
	public function __toString()
	{
		return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	}
}
 
 
 
// END OF FILE 