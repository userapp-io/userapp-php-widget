<?php

	use \UserApp\Widget\User;
	require('../autoload.php');

	User::setAppId("YOUR APP ID");

	if(!User::authenticated()){
		if(User::login("epic", "catrider11!")){
			echo("Successfully logged in<br />\n");
		}else{
			echo("Invalid username or password.<br />\n");
		}
	}

	if(User::authenticated()){
		$user = User::current();

		if($user->hasPermission("admin")){
			echo("User is admin!\n");
		}

		echo("User id: " . $user->user_id . "<br />\n");
		echo("First name: " . $user->first_name);

		$user->logout();
	}

?>