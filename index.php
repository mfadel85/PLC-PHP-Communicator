<?php 

require 'vendor/autoload.php';
require 'PLCController.php';

$contorller = new PLCController();
$contorller->runLoop();


 ?>