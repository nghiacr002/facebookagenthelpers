<?php
@error_reporting(E_ALL);
@ini_set('display_errors', -1);

include 'cli.php';

require_once "log.php";
require_once "loader.php";
//require_once 'socket_client.php';
require_once __DIR__ . '/vendor/autoload.php';
//global $server_sock;
try
{
// 	$server_sock = new WebsocketClient();
// 	$server_sock->connect(SERVER_IP, SERVER_PORT, "/",true);
	$oRequest = new \Facebook\Request();
	$oBot = new \Facebook\Bot($oRequest);
	$oBot->handle();
}
catch(Exception $ex)
{
	//$server_sock->disconnect();
	\Log::write("error.txt",$ex->getCode()."-". $ex->getMessage(). " line:". $ex->getLine().":".$ex->getFile());
}