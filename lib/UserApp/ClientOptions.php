<?php

	namespace UserApp;

	use \InvalidArgumentException;

	class ClientOptions {
		private static $_global;

		private $_options = array(
			"debug" => false,
			"secure" => true,
			"version" => 1,
			"app_id" => null,
			"token" => null,
			"base_address" => "api.userapp.io",
			"throw_errors" => true
		);

		public function __construct($options = null){
			if(is_array($options)){
				$this->_options = $options;
			}
		}

		public function __set($name, $value){
			if(!array_key_exists($name, $this->_options)){
				throw new InvalidArgumentException("Unable to set option. Option '".$name."' does not exist.");
			}

			if(is_bool($this->_options[$name]) && !is_bool($value)){
				throw new InvalidArgumentException("Unable to set option '".$name."'. Value must be boolean.");
			}else if(is_string($this->_options[$name]) && $value != null && !is_string($value)){
				throw new InvalidArgumentException("Unable to set option '".$name."'. Value must be a string.");
			}

			$this->_options[$name] = $value;
		}

		public function __get($name){
			if(!array_key_exists($name, $this->_options)){
				throw new InvalidArgumentException("Unable to get option. Option '".$name."' does not exist.");
			}
			return $this->_options[$name];
		}

		public function set($options){
			if(!is_array($options)){
				throw new InvalidArgumentException("Options must be an array.");
			}

			foreach($options as $option => $value){
				$this->$option = $value;
			}
		}

		public function createCopy(){
			return new ClientOptions($this->_options);
		}

		public static function getGlobal(){
			if(self::$_global === null){
				self::$_global = new ClientOptions();
			}
			return self::$_global;
		}
	}

?>