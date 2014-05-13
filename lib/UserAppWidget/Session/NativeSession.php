<?php

	namespace UserApp\Widget\Session;

    class NativeSession implements ISession {
        public function __construct(){
            if(session_status() == PHP_SESSION_NONE){
                session_start();
            }
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

?>
