# UserApp PHP Widget

Wraps the UserApp PHP client into a small and user-friendly API.

### Logging in

    bool User::login($username, $password);

### Checking if a user is authenticated

    bool User::authenticated();

### Getting the currently authenticated user

    $user = User::current();

### Reading a property off a user

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

    $user->hasPermission("admin");

### Checking whether a user has a feature

    $user->hasPermission("sms");

### Logging out a user

    $user->logout()

### Licence

MIT