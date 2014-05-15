<?php

    namespace UserApp\Widget;
    use \UserApp\Widget\Session\ISession;
    use \UserApp\Widget\Session\NativeSession;

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

                if($authenticated){
                    self::setToken($session->get("ua_token"));
                }else{
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

        public static function loginWithToken($token){
            try{
                self::setToken($token);

                $result = self::getClient()->user->get(array(
                    "user_id" => 'self'
                ));

                $session = self::getSession();
                $session->set('ua_token', $token);
                $session->set('ua_user_id', $result[0]->user_id);

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

        public static function getAppId(){
            self::getClient()->getOption('app_id');
        }

        public static function setToken($token){
            self::getClient()->setOption('token', $token);
        }

        public static function getToken(){
            return self::getClient()->getOption('token');
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
    
?>
