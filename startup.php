<?php

ini_set('display_errors', true);
error_reporting(E_ALL);

require('vendor/autoload.php');
$config = require('config.php');

// Require all classes
foreach(glob('engine/*.php') as $file){
    require($file);
}
foreach(glob('classes/*.php') as $file){
    require($file);
}

// Instantiate objects
$registry = \IPBParser\Engine\Registry::getInstance();

$registry->set('config', new \IPBParser\Engine\Config($config));
$registry->set('log', new Katzgrau\KLogger\Logger(__DIR__.'/logs'));
$registry->set('db', voku\db\DB::getInstance($config['db_host'], $config['db_user'], $config['db_password'], $config['db_name']));
$registry->set('request', new IPBParser\Classes\Request($registry));

