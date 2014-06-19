<?php

    namespace UserApp\Tests\Mock;

    use \UserApp\Widget\Session\ISession;

    class MockSession implements ISession {
        protected $_data;

        public function __construct(){
            $this->_data = array();
        }

        public function has($key){
            return isset($this->_data[$key]);
        }

        public function get($key){
            return isset($this->_data[$key]) ? $this->_data[$key] : null;
        }

        public function set($key, $value){
            $this->_data[$key] = $value;
        }

        public function remove($key){
            if($this->has($key)){
                unset($this->_data[$key]);
            }
        }
    }

?>