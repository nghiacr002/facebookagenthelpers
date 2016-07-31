<?php
error_reporting(E_ALL);
 // Autoload files using Composer autoload
include 'cli.php';
require_once "log.php";
require_once "loader.php";
global $server;
require_once __DIR__ . '/vendor/autoload.php';
set_time_limit(0);
// variables
//$address = '192.99.244.166';
//$port = 5000;
$verboseMode = true;
$server = new \PushWebSocket\Server(SERVER_IP, SERVER_PORT, $verboseMode);

$server->run();