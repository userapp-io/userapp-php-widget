<?php

	use \UserApp\Widget\User;
	require(dirname(__FILE__) . '\\..\..\autoload.php');

	// Find your App Id and Token:
	// App Id: https://help.userapp.io/customer/portal/articles/1322336-how-do-i-find-my-app-id-
	// Token: https://help.userapp.io/customer/portal/articles/1364103-how-do-i-create-an-api-token-

	User::setAppId("YOUR APP ID");
	User::setToken("YOUR TOKEN");

?>