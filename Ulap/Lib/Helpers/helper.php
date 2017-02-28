<?php

//** helper functions **/

function pr($k)
{ 
	//if(!debug) return;
	echo "<pre>";
	print_r($k);
	echo "</pre>";
}

function debug ($obj)
{
	if(!debug) return; 
	$callers=debug_backtrace();
	
	$d = array();

	if(  isset($callers[0]))
	{
		 $caller = $callers[0]; $k = array();
		 foreach($caller as $key=>$value)
		 {
		 	if(!is_string($key)) $key = json_encode($key);
			if(!is_string($value)) $value = json_encode($value); 
		 	$k[] = $key . ": " . $value . " ";
		 }
		$d["caller"] = "<b>".implode(", ", $k)."</b>";

	}  
	
	$d["obj"] = $obj;  
	
	if(!(Session::$SESSION->has('debug'))) Session::$SESSION->set("debug", array());
	$debug = Session::$SESSION->get("debug");
	$debug[] = $d;
	Session::$SESSION->set("debug", $debug); 

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