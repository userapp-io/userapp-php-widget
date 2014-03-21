# UserApp PHP Widget

Wraps the UserApp PHP client into a small and user-friendly API.

## Example
	
	use \UserApp\Widget\User;
	
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

## API

### Logging in

    bool User::login($username, $password)

### Logging in with a token

    bool User::loginWithToken($token)

### Signing up a new user

    bool User::signup($username, $password, $email = null, $auto_login = true)

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
* object lock
* string ip_address
* int last\_login_at
* int updated_at
* int created_at

### Checking whether a user has a permission

    bool $user->hasPermission($permission_name)

### Checking whether a user has a feature

    bool $user->hasFeature($feature_name)

### Setting a property on a user

	$user->first_name = 'John';
	$user->save();

### Setting a feature on a user

	$user->features->my_feature = true;
	$user->save();

### Setting a custom property on a user

	$user->properties->my_property = 'Some value';
	$user->save();

### Logging out a user

    void $user->logout()

### Licence

MIT
