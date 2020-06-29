<?php 
require 'vendor/autoload.php';
use React\EventLoop\Factory;
use React\Socket\ConnectionInterface;
use Psr\Http\Message\ServerRequestInterface;


	$loop = React\EventLoop\Factory::create();

	$server = new React\Socket\Server('127.0.0.1:9000',$loop);
	$server->on('connection',function(ConnectionInterface $connection){
		echo $connection->getRemoteAddress().PHP_EOL;
		$connection->on('data',function($data) use ($connection){
			echo ' our life starts '.$data.PHP_EOL;
		});
	});

	$loop->run();