<?php

	use \UserApp\Widget\User;
	require("../app_init.php");

	if(!User::authenticated()){
		header('Location: /login.php');
		die;
	}

	$user = User::current();

?>

<?php require("../app/header.php"); ?>

<div>
	<h1>Welcome!</h1>
	<p>
		<strong>User id:</strong> <?php echo($user->user_id) ?><br />
		<strong>Name:</strong> <?php echo(htmlentities($user->first_name)) ?> <?php echo(htmlentities($user->last_name)) ?>
	</p>
	<a href="../logout.php">Logout</a>
</div>

<?php require("../app/footer.php"); ?>