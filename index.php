<?php

require_once('startup.php');

if(isset($_GET['app'])){
    $app = $_GET['app'];
} elseif(isset($argv[1])) {
    $app = $argv[1];
} else {
    die("You should provide app name to run\n");
}

$file = __DIR__ . '/apps/' . $app . '.php';

if($app && file_exists($file)){
    include($file);
} else {
    echo('App not found');
}

die("\n");