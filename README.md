# UserApp PHP Widget

Wraps the UserApp PHP client into a small and user-friendly API.

## Getting started

### Finding your App Id

If you don't have a UserApp account, you need to [create one](https://app.userapp.io/#/sign-up/).

* **App Id**: The App Id identifies your app. After you have logged in, you should see your `App Id` instantly. If you're having trouble finding it, [follow this guide](https://help.userapp.io/customer/portal/articles/1322336-how-do-i-find-my-app-id-).

### Loading the library

UserApp relies on the autoloading features of PHP to load its files when needed. The autoloading complies with the PSR-0 standard which makes it compatible with most of the major frameworks and libraries. Autoloading in your application is handled automatically when managing the dependencies with [Composer](https://packagist.org/packages/userapp/userapp).
    
#### Using Composer? Add this to your `composer.json`

	{
		"require": {
			"userapp/widget": "~0.6.0"
		}
	}

#### Not using Composer? Use the library's own autoloader

    require 'lib/Autoloader.php';
    UserApp\Widget\Autoloader::register();

## Example

### bootstrap.php

	<?php

	use \UserApp\Widget\User;

	// Import composer autoloader
	require_once('vendor/autoload.php');

	User::setAppId("YOUR APP ID");

### must_authenticate.php
	
	<?php

	use \UserApp\Widget\User;

	require_once('bootstrap.php');

	User::onUnauthorized(function ($sender, $call_context, $error){
	    header('Location: /login.php');
	    die();
	});
	
### login.php

	<?php

	use \UserApp\Widget\User;

	require_once('bootstrap.php');

	if($_SERVER['REQUEST_METHOD'] === 'POST'){
	    $redirect_to = 'login.php?error=INVALID_CREDENTIALS';

	    if(User::login($_POST['username'], $_POST['password'])){
	        $redirect_to = 'user/profile.php';
	    }

	    header('Location: /' . $redirect_to);
	    die();
	}

	?>

	<form method="post">
	    Username: <input type="text" name="username" /><br />
	    Password: <input type="password" name="password" /><br />
		
		<?php if(isset($_GET['error']) && $_GET['error'] == 'INVALID_CREDENTIALS'){ ?>
			* Invalid username or password<br />
		<?php } ?>

	    <input type="submit" value="Log in" />
	</form>

### user/profile.php

	<?php

	use \UserApp\Widget\User;

	require_once('../must_authenticate.php');

	$user = User::current();

	echo("User id: " . $user->user_id . "<br /><br />\n\n");
	echo("Username: " . ($user->login ?: '(not specified)'). "<br />\n");
	echo("First name: " . ($user->first_name ?: '(not specified)') . "<br />\n");
	echo("Last name: " . ($user->last_name ?: '(not specified)') . "<br />\n");
	echo("Email: " . ($user->email ?: '(not specified)'). "<br /><br />\n\n");

	echo("<a href='logout.php'>Logout</a>");
	
### user/logout.php

	<?php

	use \UserApp\Widget\User;

	require_once('../must_authenticate.php');

	$user = User::current();
	$user->logout();

	header('Location: ../login.php');

## API

### Callback when unauthorized

    void onUnauthorized(closure($sender, $call_context, $error))
    
#### Example

    User::onUnauthorized(function($sender, $call_context, $error){
        header('Location: /login.php');
        die();
    });

### Logging in

    bool User::login($username, $password)

### Logging in with a token

    bool User::loginWithToken($token)

### Signing up a new user

    bool User::signup($username, $password, $email = null, $first_name = null, $last_name = null, $auto_login = true)

### Checking if a user is authenticated

    bool User::authenticated()

### Getting the currently authenticated user

    User User::current()

#### Example

    $user = User::current();
    $user->logout();

### Reading a property of a user

    $user->user_id
#
    $user->properties->age->value

#### Supported properties

* string user_id
* string first_name
* string last_name
* string email
* string login
* object properties
* object features
* object permissions
* array locks
* string ip_address
* int last\_login_at
* int updated_at
* int created_at

### Checking whether a user has a permission

    bool $user->hasPermission($permission_name)

### Checking whether a user has a feature

    bool $user->hasFeature($feature_name)

### Saving changes on a user

    void $user->save()

#### Example

    $user->first_name = 'John';
    $user->last_name = 'Johnsson';
    $user->properties->my_own_property = 'some value';
    $user->save();

### Logging out a user

    void $user->logout()

### Licence

MIT
