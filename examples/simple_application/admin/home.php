<?php

	use \UserApp\Widget\User;
	require("../app_init.php");

	// In order to get 'admin' to work you need to create a permission called 'admin'
	// and activate it on your user.

	// Read more about it here:
	// https://help.userapp.io/customer/portal/articles/1245681-setting-up-your-permissions

	if(!User::authenticated() || !User::current()->hasPermission("admin")){
		header('Location: ../login.php');
		die;
	}

	$user = User::current();

?>

<?php require("../app/header.php"); ?>

<div>
	<h1>Welcome! <small>(you are an administrator)</small></h1>
	<p>
		<strong>User id:</strong> <?php echo($user->user_id) ?><br />
		<strong>Name:</strong> <?php echo(htmlentities($user->first_name)) ?> <?php echo(htmlentities($user->last_name)) ?>
	</p>
	<a href="../logout.php">Logout</a>
</div>

<?php require("../app/footer.php"); ?>