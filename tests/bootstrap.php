<?php

	$loader = require __DIR__ . "/../vendor/autoload.php";
	$loader->addPsr4('UserApp\\Widget\\', __DIR__.'/../lib/');

    require(dirname(__FILE__) . "/Mock/MockTransport.php");
    require(dirname(__FILE__) . "/Mock/MockSession.php");

?>