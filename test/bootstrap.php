<?php

defined('BASE_PATH') || define('BASE_PATH', realpath(dirname(__FILE__)));

// adds elasticsearch to the include path
set_include_path(
    get_include_path() . PATH_SEPARATOR .
    BASE_PATH . '/../lib' . PATH_SEPARATOR .
    BASE_PATH . '/lib'
);

function elasticaAutoload($class)
{
    if (substr($class, 0, 9) == 'Elastica_') {
        $file = str_replace('_', '/', $class) . '.php';
        require_once $file;
    }
}

spl_autoload_register('elasticaAutoload');
