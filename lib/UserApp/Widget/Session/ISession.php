<?php

	namespace UserApp\Widget\Session;

    interface ISession {
        public function has($key);
        public function get($key);
        public function set($key, $value);
        public function remove($key);
    }
   	
?>