<?php

    namespace UserApp\Widget;
    
    use \UserApp\Widget\Session\ISession;
    use \UserApp\Widget\Session\NativeSession;
    use \UserApp\Exceptions\ServiceException;

    abstract class UserStaticBase {
        private static $_user;
        private static $_client;
        private static $_session;
        private static $_authenticated;
        private static $_heartbeat_handler;

        public static function onUnauthorized($callback){
            return self::getClient()->on('unauthorized', $callback);
        }

        public static function current(){
            if(self::$_user == null && self::authenticated()){
                $session = self::getSession();
                $user_client = new \UserApp\API(self::getClient()->getOption("app_id"), $session->get("ua_token"));

                $user_client->on('success', function($sender, $call_context, $error) use ($session){
                    if(!($call_context->service == 'user' && $call_context->method == 'logout')){
                        $session->set('ua_last_heartbeat_at', time());
                    }
                });

                self::$_user = new User($user_client, $session->get("ua_user_id"));
            }
            return self::$_user;
        }

        public static function authenticated(){
            $session = self::getSession();

            if($session->has("ua_token")){
                $ten_min_in_sec = 60*30;

                if(!self::$_authenticated){
                    self::$_authenticated = true;
                    self::setToken($session->get("ua_token"));
                }

                $last_heartbeat_at = $session->get("ua_last_heartbeat_at");

                if(self::$_heartbeat_handler === null){
                    self::$_heartbeat_handler = self::getClient()->on('success', function($sender, $call_context, $error) use ($session){
                        if(!($call_context->service == 'user' && $call_context->method == 'logout')){
                            $session->set('ua_last_heartbeat_at', time());
                        }
                    });
                }

                // Make a heartbeat request if none is made or last request was made over 10 minutes ago
                if(empty($last_heartbeat_at) || ($last_heartbeat_at+$ten_min_in_sec) < time()){
                    try {
                        self::getClient()->token->heartbeat();
                        $session->set("ua_last_heartbeat_at", time());
                    }catch(ServiceException $exception){
                        switch($exception->getErrorCode()){
                            case "INVALID_CREDENTIALS":
                            case "UNAUTHORIZED":
                                $authenticated = false;
                                break;
                        }
                    }
                }
            }

            return self::$_authenticated;
        }

        public static function signup($username, $password, $email = null, $first_name = null, $last_name = null, $auto_login = true){
            try{
                self::getClient()->user->save(array(
                    "login" => $username,
                    "password" => $password,
                    "email" => $email,
                    "first_name" => $first_name,
                    "last_name" => $last_name,
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
                $session->set('ua_last_heartbeat_at', time());

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
                $session->set('ua_last_heartbeat_at', time());

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
            }
            return self::$_client;
        }

        public static function getUserClient(){
            if(self::$_client === null){
                $client = self::$_client = new \UserApp\API();
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
