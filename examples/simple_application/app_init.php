<?php

	use \UserApp\Widget\User;

	// Missing vendor/autoload.php? Run `$ composer install` to install the dependencies.
	require(dirname(__FILE__) . '/vendor/autoload.php');

	// If unauthorized, then redirect to our login page
	User::onUnauthorized(function($sender, $call_context, $error){
	    header('Location: /login.php');
	    die();
	});

	// Find your App Id
	// App Id: https://help.userapp.io/customer/portal/articles/1322336-how-do-i-find-my-app-id-
	User::setAppId("YOUR APP ID");

?>
