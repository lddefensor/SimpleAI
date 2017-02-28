<?php

if (!defined('DS'))	
	define('DS', DIRECTORY_SEPARATOR);

$FILE = __FILE__;

if (!defined('ROOT')) 
	define('ROOT',  dirname($FILE, 3)); 
 

if(!defined('APP_ROOT'))
	define('APP_ROOT', ROOT. DS. 'Ulap');

if(!defined('LIB'))
	define('LIB', APP_ROOT. DS. 'Lib'); 
 
 
//URL is usually the container of the ROOT
$URL = str_replace(dirname($FILE, 3) . DS, '', ROOT);
if(!defined('URL'))
	define('URL',  '/'.$URL); 

require( ROOT . DS . 'Config' . DS . 'defaults.php');

require (LIB . DS . 'Helpers' . DS . 'RoutePath.php');
require (LIB . DS . 'Helpers' . DS . 'helper.php');


// FOR RoutePathTest.php require RoutePath


/** END OF FILE **/