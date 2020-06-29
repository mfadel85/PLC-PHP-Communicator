<?php 

require 'vendor/autoload.php';
use React\Socket\ConnectionInterface;
use Psr\Http\Message\ServerRequestInterface;


 	$loop = React\EventLoop\Factory::create();
 	$server = new React\Http\Server(function(ServerRequestInterface $request){
 		return new React\Http\Response(200,['Content-Type' => 'text/plain'],'Hello, world');
 	});
 	$socket = new React\Socket\Server('127.0.0.1:5520',$loop);
 	$server->listen($socket);
 	/*$server = new React\Socket\Server('127.0.0.1:9001',$loop);

 	$server->on('connection',function(ConnectionInterface $connect){
 		echo "I love you".PHP_EOL;

 	});*/
 	$loop->run();