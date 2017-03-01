<?php 

// error_reporting(E_ALL); // TODO Change to 0 on production MODE
// ini_set("display_errors", "on"); // TODO Change to off on production mode

/**
 * Entry point of Application
 *  
 * @author 	Lorelie Defensor
 * @created	July 2015
 * @last_updated
 */
 
  
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST');  
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");  

if (!defined('DS'))	
	define('DS', DIRECTORY_SEPARATOR);

$FILE = __FILE__;

if (!defined('ROOT')) 
	define('ROOT',  dirname($FILE, 2)); 

if(!defined('APP_ROOT'))
	define('APP_ROOT', ROOT. DS. 'Ulap');

if(!defined('LIB'))
	define('LIB', APP_ROOT. DS. 'Lib'); 
 
 
//URL is usually the container of the ROOT
$URL = str_replace(dirname($FILE, 3) . DS, '', ROOT);
if(!defined('URL'))
	define('URL',  '/'.$URL);

//Application based defaults
require( ROOT . DS . 'Config' . DS . 'defaults.php');
require( LIB . DS . 'Router.php');


$queryString = urldecode(str_replace(URL.'/' , '', $_SERVER["REQUEST_URI"]));  

$MyRouter = new Ulap\Router($queryString); 

// TODO //To override, should extend the MyExceptionHandler Class
$MyRouter->ExceptionHandler = App\ErrorHandler::class;

//** REGISTER A SHUTDOWN FUNCTION turn off reporting system for error  
set_error_handler(function($code, $message){
	// echo 'lorelie';
	App\ErrorHandler::handle(new Ulap\Helpers\MyRuntimeException($message, $code));
});

register_shutdown_function(function(){
	$error = error_get_last(); 
	// echo 'lorelie';
	if($error)
	{
		App\ErrorHandler::handle(new Ulap\Helpers\MyRuntimeException($error['message'], 9001));
	} 
});

$MyRouter->route();   

/** END OF FILE **/