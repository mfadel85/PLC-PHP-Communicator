<?php 

require 'vendor/autoload.php';

use ModbusTcpClient\Composer\Read\ReadRegistersBuilder;
use ModbusTcpClient\Network\BinaryStreamConnection;

use ModbusTcpClient\Packet\ModbusFunction\WriteSingleCoilRequest;
use ModbusTcpClient\Packet\ModbusFunction\WriteSingleCoilResponse;
use ModbusTcpClient\Packet\ModbusFunction\ReadCoilsRequest;
use ModbusTcpClient\Packet\ModbusFunction\ReadCoilsResponse;
use ModbusTcpClient\Packet\ModbusFunction\WriteSingleRegisterRequest;
use ModbusTcpClient\Packet\ModbusFunction\WriteSingleRegisterResponse;
use ModbusTcpClient\Packet\ResponseFactory;

use React\EventLoop\Factory;
use React\Socket\ConnectionInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
  * 
  */
 class PLCController 
 {
 	protected $loop;
 	protected $connection;
 	protected $socket;
 	protected $server;
 	
 	function __construct()
 	{
 		echo "Program Started \n";
 		$this->loop = React\EventLoop\Factory::create();


		$this->server = new React\Socket\Server('127.0.0.1:9000',$this->loop);
		$this->server->on('connection',function(ConnectionInterface $connection){
			echo $connection->getRemoteAddress().PHP_EOL;
			$connection->on('data',function($data) use ($connection){
				$this->handleSignal($data);
			});
		});

		$this->connection = BinaryStreamConnection::getBuilder()
		    ->setPort(502)
		    ->setHost('127.0.0.1')
		    ->build(); 	 		
 	}

 	function checkCoil($address){
 		$packet = new ReadCoilsRequest($address, 1);
 		$binaryData = $this->connection->connect()->sendAndReceive($packet);
 		$response = ResponseFactory::parseResponseOrThrow($binaryData);
 		$coils= $response->getCoils();
 		return $coils[0];
 	}

 	function writeSingleCoil($address,$coilValue){
		$packet = new WriteSingleCoilRequest($address, $coilValue);
    	$binaryData = $this->connection->connect()
	        ->sendAndReceive($packet);
    	$response = ResponseFactory::parseResponseOrThrow($binaryData);
    	//print_r($response->isCoil());
 	}

 	function checkPLCWorking($address){
 		return $this->checkCoil($address);
 		
 		/*$coil = $this->checkCoil($address);
    	if($coil == 1 )   		//print_r('PLC is working we have to wait: a procedure will come here!!!');
    		return true;
    	 else 		
    		return false;//print_r('PLC is not working we can send it the order!!');*/
 	}

 	function writeRegister($address,$value){
 		$packet = new WriteSingleRegisterRequest($address,$value);
 		try{
	 		$binaryData = $this->connection->connect()
	 			->sendAndReceive($packet);
	 		$response = ResponseFactory::parseResponseOrThrow($binaryData); 			
	 		//print_r($response);
 		} catch(Exception $exception) {
		    echo 'An exception occurred' . PHP_EOL;
		    echo $exception->getMessage() . PHP_EOL;
		    echo $exception->getTraceAsString() . PHP_EOL; 			
		    return fasle;
 		}
 		return true;
 	}

 	function handleSignal($data){
 		$order = 0;
 		// an order has come time to handle it
 		// if we can work: PLC is not working then let's write on the registers
 		$working = $this->checkPLCWorking(100);
 		$working= false;
 		if(!$working){
 			// get $order how? many ways for that we will see that
 			$this->writeOrderData($order);
 			echo "Order received, note: ".$data.PHP_EOL;
 			// when to use this???
 			$this->writeSingleCoil(100,true);
 			// we have to inform the Store that the order sent and waiting for the results
 			//
 		}
 	}

 	function writeOrderData($order){
 		//$order is json data: right now we can read it from myFile.json
 		// send a signal that everything is done start working PLC.
		$string = file_get_contents("data.json");
		if ($string === false) {
		    print_r("The JSON Data is not valid".PHP_EOL);
		    return fasle;
		}

		$order = json_decode($string, true);
		$productsCount = $order['productsCount'];
		$products = $order['products'];
		print_r("Products Count: ".$productsCount);
 		print_r("\n");	
 		$i = 1;
 		foreach ($products as $product) {
 			$address = $i*100+1;
 			print_r("Product $i quantity: ".$product[0].PHP_EOL);
 			$result = $this->writeRegister($address,$product[0]);
 			print_r($result.PHP_EOL);
 			$result =  $this->writeRegister($address+1,$product[1]);
 			print_r($result.PHP_EOL); 			
 			$result = $this->writeRegister($address+2,$product[2]);
 			print_r($result.PHP_EOL); 			
 			$result = $this->writeRegister($address+3,$product[4]);
 			print_r($result.PHP_EOL); 			
 			$result = $this->writeRegister($address+4,$product[3]);
 			print_r($result.PHP_EOL); 			
 			print_r("Product x$i: ".$product[1].PHP_EOL);
 			print_r("Product y$i: ".$product[2].PHP_EOL);
 			print_r("Product $i bent count: ".$product[4].PHP_EOL);
 			print_r("Product $i unit id: ".$product[3].PHP_EOL);
 		}

 		return true; // the data written successfully.
 	}
 	// here the main loop will have read coils/write coils read registers/write registers
 	public function runLoop()
 	{
 		//$order = 0;
		//$startAddress = 100;
		//$quantity = 1;
		//$packet = new ReadCoilsRequest($startAddress, $quantity);	
			    
 		$mainLoop = $this->loop;
 		//$mainConnection = $this->connection;
		
 		try {
 			$timer = $mainLoop->addPeriodicTimer(1, function () {

			});
			$mainLoop->addTimer(480, function () use ($mainLoop, $timer) {
			    $mainLoop->cancelTimer($timer);
			    echo 'Done' . PHP_EOL;
			});	
			$mainLoop->run();

 		} catch(Exception $exception){
 			echo 'An exception occurred' . PHP_EOL;
    		echo $exception->getMessage() . PHP_EOL;
    		echo $exception->getTraceAsString() . PHP_EOL;
 		}
 	}
 	function closeConnection(){
		$this->connection->close(); 		
 	} 	

 } 
 ?>