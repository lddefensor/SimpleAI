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
 
function debugQuery()
{ 
	//if(!debug) return;
	pr($_SESSION['queries']);
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

function randomNumbers(int $length) : string
{
	$string = "1234567890"; 
	
	$array = array();
	
	while ($length)
	{
		$charIndex = rand(0, strlen($string)-1);
		$array[] = $string[$charIndex];
		$length--;
	}
	
	return implode("", $array);
	
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

function sanitizeString($string){
	if(get_magic_quotes_gpc()) $string = stripslashes($string);
	$string = htmlentities($string);
	$string = strip_tags($string);
	$string = trim($string);
	return $string;
} 


function startsWith($haystack, $needle)
{
     $length = strlen($needle);
     return (substr($haystack, 0, $length) === $needle);
}

function toUTC($date = null){
	if(!$date){
		$date =  new \DateTime('now', new \DateTimeZone('UTC')) ;
	}
	return  $date->format('Y-m-d') . 'T' . $date->format('H:i:s') . 'Z';
}

/** 
* source: https://stackoverflow.com/questions/173400/how-to-check-if-php-array-is-associative-or-sequential
*/
function isAssoc(array $arr){ 
	if (array() === $arr) return false;
    return array_keys($arr) !== range(0, count($arr) - 1);
}

/** END OF FILE **/