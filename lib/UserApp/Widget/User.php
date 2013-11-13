<?php

	namespace UserApp\Widget;

	use \Exception;
    use \UserApp\Exceptions\ServiceException;

    interface ISession {
        public function has($key);
        public function get($key);
        public function set($key, $value);
        public function remove($key);
    }

    class NativeSession implements ISession {
        public function __construct(){
            session_start();
        }

        public function has($key){
            return isset($_SESSION[$key]);
        }

        public function get($key){
            return $_SESSION[$key];
        }

        public function set($key, $value){
            $_SESSION[$key] = $value;
        }

        public function remove($key){
            unset($_SESSION[$key]);
        }
    }

    abstract class UserStaticBase {
        private static $_user;
        private static $_client;
        private static $_session;
        private static $_authenticated;

        public static function current(){
            if(self::$_user == null && self::authenticated()){
                $session = self::getSession();
                self::$_user = new User(
                    self::getClient()->getOption("app_id"),
                    $session->get("ua_token"),
                    $session->get("ua_user_id")
                );
            }
            return self::$_user;
        }

        public static function authenticated(){
            $session = self::getSession();

            if(self::$_authenticated != true && $session->has("ua_token")){
                $authenticated = true;

                /*try {
                    self::getClient()->token->heartbeat();
                }catch(Exception $exception){
                    $authenticated = false;
                }*/

                if(!$authenticated){
                    $session = self::getSession();
                    $session->remove("ua_token");
                    $session->remove("ua_user_id");
                }

                self::$_authenticated = $authenticated;
            }

            return self::$_authenticated;
        }

        public static function signup($username, $password, $email = null, $auto_login = true){
            try{
                self::getClient()->user->save(array(
                    "login" => $username,
                    "password" => $password,
                    "email" => $email,
                    "ip_address" => self::getRemoteAddress() // Provide the IP of the real user
                ));

                if($auto_login){
                    return self::login($username, $password);
                }

                return true;
            }catch(ServiceException $exception){
                throw $exception;
            }
        }

        public static function login($username, $password){
            try{
                $result = self::getClient()->user->login(array(
                    "login" => $username,
                    "password" => $password
                ));

                $session = self::getSession();
                $session->set('ua_token', $result->token);
                $session->set('ua_user_id', $result->user_id);

                return true;
            }catch(ServiceException $exception){
                switch($exception->getErrorCode()){
                    case "INVALID_ARGUMENT_LOGIN":
                    case "INVALID_ARGUMENT_PASSWORD":
                        return false;
                        break;
                    default:
                        throw $exception;
                        break;
                }
            }
        }

        public static function setAppId($app_id){
            self::getClient()->setOption('app_id', $app_id);
        }

        public static function setToken($token){
            self::getClient()->setOption('token', $token);
        }

        public static function getClient(){
            if(self::$_client === null){
                $client = self::$_client = new \UserApp\API();
                $client->setOption("debug", true);
                $client->setTransport(new \UserApp\Http\CurlTransport(false));
            }
            return self::$_client;
        }

        public static function getUserClient(){
            if(self::$_client === null){
                $client = self::$_client = new \UserApp\API();
                $client->setOption("debug", true);
                $client->setTransport(new \UserApp\Http\CurlTransport(false));
            }
            return self::$_client;
        }

        public static function getSession(){
            if(self::$_session === null){
                self::setSession(new NativeSession());
            }
            return self::$_session;
        }

        public static function setSession(ISession $session){
            self::$_session = $session;
        }

        private static function getRemoteAddress(){
            $result = null;

            if(isset($_SERVER["HTTP_X_FORWARDED_FOR"])){
               $result = $_SERVER["HTTP_X_FORWARDED_FOR"];
            }else{
               $result = $_SERVER["REMOTE_ADDR"];
            }

            return $result;
        }
    }

	class User extends UserStaticBase {
        private $_client;
        private $_user_id;
        private $_data = null;
        private $_loaded = false;

		public function __construct($app_id, $session_token, $user_id){
            $client = $this->_client = new \UserApp\API($app_id, $session_token);
            $client->setTransport(new \UserApp\Http\CurlTransport(false));
            $this->_user_id = $user_id;
		}

        public function __get($name){
            if($name == 'user_id'){
                return $this->_user_id;
            }

            $this->load();

            if(!property_exists($this->_data, $name)){
                throw new Exception("Property does not exist");
            }

            return $this->_data->$name;
        }

        /*public function __set($name, $value){
            $this->$name = $value;
        }*/

        public function hasPermission($permission){
            if($this->_loaded){
                return isset($this->permission->$permission)
                    && $this->permission->$permission->value == true;
            }

            $result = $this->_client->user->hasPermission(array(
                "user_id" => $this->user_id,
                "permission" => $permission
            ));

            return count($result->missing_permissions) == 0;
        }

        public function hasFeature($feature){
            if($this->_loaded){
                return isset($this->features->$feature)
                    && $this->features->$feature->value == true;
            }

            $result = $this->_client->user->hasFeature(array(
                "user_id" => $this->user_id,
                "permission" => $permission
            ));

            return count($result->missing_permissions) == 0;
        }

        public function logout(){
            $session = User::getSession();

            $session->remove('ua_token');
            $session->remove('ua_user_id');

            try {
                $this->_client->user->logout();
            }catch(\UserApp\Exceptions\ServiceException $exception){
                if($exception->getErrorCode() == 'INVALID_CREDENTIALS'){
                    return false;
                }
                throw $exception;
            }

            return true;
        }

        private function load(){
            if($this->_loaded){
                return;
            }

            $this->_loaded = true;

            $this->_data = current($this->_client->user->get());
        }
	}

?>