<?php

ini_set('display_errors', 0);
error_reporting(E_ALL);

header('content-type: text/plain');

spl_autoload_register(function($class) {
	require(getcwd().'/classes/'.str_replace('\\', '/', basename($class)).'.php');
});

require(getcwd().DIRECTORY_SEPARATOR.'config.php');

try {
	$cache = new \None\Cache(
		getcwd().DIRECTORY_SEPARATOR.'cache',
		900
	);

	if($cache->check('players')) {
		echo $cache->get('players');
		exit();
	}

	$client = new \None\Proxy(new \None\Curl());
	$client->setGuildName($guild_name);

	$client->login($login, $password);
	$result = $client->get();

	$parser = new \None\Parser();
	$parser->setIgnoreUser($bot_handle);

	$leaderboard_json = $parser->toJson($parser->parse($result));

	$cache->store('players', $leaderboard_json);
	echo $leaderboard_json;

} catch(\Exception $e) {
	echo json_encode([
		'error' => $e->getMessage(),
	]);
}