<?php
/**
 * some global helpers
 */

namespace Ulap\Helpers;

//** helper functions **/

function pr($k)
{ 
	//if(!debug) return;
	echo "<pre>";
	print_r($k);
	echo "</pre>";
}
 

function debug($obj, int $depth = 0, int $min = 0)
{  
	$trace = debug_backtrace(); 
	if(!isset($_SESSION['debug']))
		$_SESSION['debug'] = array();
		
	while ($depth >= $min)
	{ 
		$include = array(
			'class'=>  isset($trace[$depth]['class']) ? $trace[$depth]['class'].' ::' : '', 
			'function'=>  $trace[$depth]['function'], 
			'line'=>  $trace[$depth]['line'], 
			'file'=> isset( $trace[$depth]['file']) ? $trace[$depth]['file'] : '' 
		);
		if($depth == $min)
			$include['value'] = $obj;
		$depth--;
		
		$_SESSION['debug'][] = $include;
	}  

}


function randomString(int $length) : string
{
	$string = "ABCDEFGHIJKLMNOPQRSTUVQXYZabcdefghijklmnopqrstuvqxyz1234567890_*"; 
	
	$array = array();
	
	while ($length)
	{
		$charIndex = rand(0, strlen($string)-1);
		$array[] = $string[$charIndex];
		$length--;
	}
	
	return implode("", $array);
	
}

/** END OF FILE **/