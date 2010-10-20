<?php
	
// adds elasticsearch to the include path
set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__) . '/../lib'));

function elasticsearch_autoload($class) {
	$file = str_replace('_', '/', $class) . '.php';
	require_once $file;
}

spl_autoload_register('elasticsearch_autoload');

defined('BASE_PATH') || define('BASE_PATH', realpath(dirname(__FILE__)));

