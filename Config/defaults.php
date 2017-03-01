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


if(!defined('SESSION_HANDLING'))
	define('SESSION_HANDLING', 'xDB');
 


// END OF FILE 