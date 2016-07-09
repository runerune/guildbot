<?php

ini_set('display_errors', 0);
error_reporting(E_ALL);

spl_autoload_register(function($class) {
	require(getcwd().'/classes/'.str_replace('\\', '/', basename($class)).'.php');
});

try {
	$client = new \None\Proxy(new \None\Curl());
	$client->authorize((int)$_GET['code']);

	header('Location: ./');
} catch(\Exception $e) {
	echo $e->getMessage();
}