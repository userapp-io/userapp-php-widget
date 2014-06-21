<?php

    namespace UserApp\Widget\Session;

    class NativeSession implements ISession {
        public function __construct(){
            if(!self::sessionStarted()){
                session_start();
            }
        }

        public function has($key){
            return isset($_SESSION[$key]);
        }

        public function get($key){
            return $this->has($key) ? $_SESSION[$key] : null;
        }

        public function set($key, $value){
            $_SESSION[$key] = $value;
        }

        public function remove($key){
            if($this->has($key)){
                unset($_SESSION[$key]);
            }
        }

        private static function sessionStarted(){
            if(function_exists('session_id') && session_id() != '') {
                return true;
            }

            if(function_exists('session_status') && session_status() != PHP_SESSION_NONE){
                return true;
            }

            return false;
        }
    }

?>