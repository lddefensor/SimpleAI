<?php

require_once ROOT.DS. 'Error'. DS . 'ErrorHandler.php';

if(!defined('URL')) 
	define("URL", '/SimpleAI');  

if(!defined('DEFAULT_ACTION'))
	define('DEFAULT_ACTION', 'Home');

if(!defined('DEFAULT_METHOD'))
	define('DEFAULT_METHOD', 'index');

if(!defined('MULTIPLE_APPS'))
	define('MULTIPLE_APPS', true);

if(!defined('APPS'))
	define('APPS', array('admin'));


//if(!defined('DB_SESSION'))
//	define('DB_SESSION', array('tableName'=>'eg-sessions', 'connection'=>'default'));
    
if(!defined('SESSION_HANDLER'))
	define('SESSION_HANDLER', Ulap\Helpers\MySessionHelper::class); 
 


// END OF FILE 