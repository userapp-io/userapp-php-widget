<?php

	use \UserApp\Widget\User;
	require("app_init.php");

	if(User::authenticated()){
		$user = User::current();
		$user->logout();
	}

	header('Location: login.php');

?>