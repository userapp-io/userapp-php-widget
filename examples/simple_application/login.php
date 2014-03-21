<?php

	use \UserApp\Widget\User;
	require("app_init.php");

	$email = null;
	$valid_credentials = null;

	if($_SERVER['REQUEST_METHOD'] === 'POST'){
		if(!User::authenticated()){
			$email = $_POST["email"];
			$password = $_POST["password"];
			
			try{
				$valid_credentials = User::login($email, $password);
			}catch(\UserApp\Exceptions\ServiceException $exception){
				$valid_credentials = true;
			}
		}
	}

	if(User::authenticated()){
		$user = User::current();
		if($user->hasPermission("admin")){
			header('Location: admin/home.php');
		}else{
			header('Location: user/home.php');
		}
		die;
	}

?>

<?php require("app/header.php"); ?>

<form class="form" method="post">
	<h2 class="form-heading">Log in to MyApp</h2>

	<div class="form-fields">
		<input name="email" type="text" class="form-control" placeholder="Email" value="<?php echo(htmlentities($email)) ?>" required autofocus>
		<input name="password" type="password" class="form-control" placeholder="Password" required>
	</div>

	<?php if($valid_credentials !== null){ ?>
		<p class="text-center" style="margin-top:10px;margin-bottom: 10px;">
			Invalid email or password. Please try again.
		</p>
	<?php } ?>

	<button class="btn btn-lg btn-primary btn-block" type="submit">Log in</button>

	<p class="text-center" style="margin-top:10px;">
		or <a href="signup.php">Sign up</a>
	</div>
</form>

<?php require("app/footer.php"); ?>
