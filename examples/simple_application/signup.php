<?php

	use \UserApp\Widget\User;
	require("app_init.php");

	$error_message = null;

	if($_SERVER['REQUEST_METHOD'] === 'POST'){
		if(!User::authenticated()){
			$email = $_POST["email"];
			$password = $_POST["password"];
			try{
				User::signup($email, $password, $email);
			}catch(Exception $exception){
				$error_message = $exception->getMessage();
			}
		}
	}

	if(User::authenticated()){
		header('Location: user/home.php');
		die;
	}

?>

<?php require("app/header.php"); ?>

<form class="form" method="post">
	<h2 class="form-heading">Sign up for MyApp</h2>

	<div class="form-fields">
		<input name="email" type="text" class="form-control" placeholder="Email" required>
		<input name="password" type="password" class="form-control" placeholder="Password" required>
	</div>

	<?php if($error_message != null){ ?>
		<p class="text-center" style="margin-top:10px;margin-bottom:10px;">
			<?php echo($error_message); ?>
		</p>
	<?php } ?>
	
	<button class="btn btn-lg btn-primary btn-block" type="submit">Create my account</button>
	
	<p class="text-center" style="margin-top:10px;">
		or <a href="login.php">Login</a>
	</p>
</form>

<?php require("app/footer.php"); ?>