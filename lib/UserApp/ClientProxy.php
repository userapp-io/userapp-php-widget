<?php

	namespace UserApp;

	use \ReflectionClass;
	use \InvalidArgumentException;
	use \UserApp\Exceptions\NotSupportedException;
	
	class ClientProxy extends ClientBase {
		private $_client;
		private $_service_version = 1;
		private $_service_name;

		public function __construct(){
			$arguments = func_get_args();
			$argument_count = count($arguments);

			if(($argument_count == 2 || $argument_count == 3) && $arguments[0] instanceof Client){
				$this->_client = $arguments[0];
				$this->_service_name = $arguments[1];
				$this->_service_version = $argument_count == 2 ? 1 : (int)$arguments[2];
			}else{
				$client_reflection = new ReflectionClass('UserApp\Client');
				$this->_client = $client_reflection->newInstanceArgs($arguments);
			}

			parent::__construct($this->_client);
		}

		public function __call($method, $arguments){
			$call_arguments = null;

			if($this->_service_name == null){
				throw new NotSupportedException("Unable to call method on base service.");
			}

			if(count($arguments) == 1 && is_array($arguments[0])){
				$call_arguments = $arguments[0];
			}else{
				$call_arguments = array();
			}

			return $this->_client->call($this->_service_version, $this->_service_name, $method, $call_arguments);
		}

		public function __get($name){
			$target_service = null;
			$target_version = null;

			if(strlen($name) == 0){
				throw new InvalidArgumentException("Name cannot be empty.");
			}

			if($this->_service_name === null && strlen($name) >= 2 && $name[0] == 'v' && is_numeric(substr($name, 1))){
				$target_version = (int)substr($name, 1);
			}

			if($target_version === null){
				$target_version = $this->_service_version;
				if($this->_service_name != null){
					$target_service = sprintf("%s.%s", $this->_service_name, $name);
				}else{
					$target_service = $name;
				}
			}

			return new ClientProxy($this->_client, $target_service, $target_version);
		}

		public function __set($name, $value){
			throw new NotSupportedException("Setting the value of a service is not supported.");
		}
	}

?>