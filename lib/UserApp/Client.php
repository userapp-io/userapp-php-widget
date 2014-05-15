<?php

	namespace UserApp;

	use \json_encode;

	use \InvalidArgumentException;
	use \BadMethodCallException;

	use \UserApp\Exceptions\UserAppException;
	use \UserApp\Exceptions\ServiceException;
	use \UserApp\Exceptions\TransportException;
	use \UserApp\Exceptions\InvalidConfigurationException;
	
	class Client extends ClientBase {
		const CLIENT_VERSION = "0.1";

		public function __construct(){
			parent::__construct($this);

			$arguments = func_get_args();
			$options = $this->getOptions();

			for($i=0;$i<count($arguments);++$i){
				$argument = $arguments[$i];
				if($i == count($arguments)-1 && is_array($argument)){
					$options->set($argument);
				}else if($i == 0){
					if($argument != null && is_string($argument)){
						$options->app_id = $argument;
					}
				}else if($i == 1){
					if($argument != null && is_string($argument)){
						$options->token = $argument;
					}
				}
			}
		}

		// Call

		public function call($version, $service, $method, $arguments){
			$logs = null;
			$generated_exception = null;

			$this->assertConfiguration();
			$this->assertArguments($version, $service, $method, $arguments);

			$protocol = $this->_options->secure ? 'https' : 'http';
			$service_url = sprintf("%s://%s/v%s/%s.%s", $protocol, $this->_options->base_address, $version, $service, $method);

			if($this->debugMode()){
				$service_url .= "?\$debug";
			}

			$headers = array(
				'Content-Type' => 'application/json',
				'User-Agent' => sprintf("UserApp/%s PHP/%s", self::CLIENT_VERSION, PHP_VERSION),  
				'Authorization' => sprintf("Basic %s", base64_encode(sprintf("%s:%s", $this->_options->app_id, $this->_options->token)))
			);

			if($this->debugMode()){
				$this->log(sprintf("Sending POST Request to '%s' with headers '%s' and body '%s'.",
					$service_url, json_encode($headers), json_encode($arguments)));
			}

			$response = $this->getTransport()->request('POST', $service_url, $headers, json_encode($arguments));
			$this->assertResponse($response);

			if($response->status->code != 200){
				throw new TransportException(sprintf("Expected 200 OK response. Received %s %s.",
					$response->status->code, $response->status->message));
			}

			if($this->debugMode()){
				$this->log(sprintf("Received response with status '%s' and headers '%s'.",
					json_encode($response->status), json_encode($response->headers)));
			}

			$result = $this->processContentType($response->headers["Content-Type"], $response->body);

			if(is_object($result)){
				if(isset($result->error_code)){
					switch($result->error_code){
						case 'INVALID_SERVICE':
							throw new BadMethodCallException("Call to invalid service '$service'.");
							break;
						case 'INVALID_METHOD':
							throw new BadMethodCallException("Call to invalid method '$method()'.");
							break;
						default:
							if($this->_options->throw_errors){
								$generated_exception = new ServiceException($result->error_code, $result->message);
							}
							break;
					}
				}else{
					if($service == 'user' && $method == 'login'){
						if(isset($result->token) && $this->_options->token == null){
							$this->_options->token = $result->token;
						}
					}
				}
				if(isset($result->__logs)){
					$logs = $result->__logs;
					unset($result->__logs);
				}
			}elseif(is_array($result)){
				foreach($result as $key => $item){
					if(is_object($item)){
						if(isset($item->__logs)){
							$logs = $item->__logs;
							unset($result[$key]);
						}
					}
				}
			}

			if($this->debugMode()){
				$this->log("Request result '" . json_encode($result) . "'.");
			}

			if($logs != null){
				foreach($logs as $log){
					$this->log($log->message, $log->type, $log->created_at);
				}
			}

			if($generated_exception != null){
				throw $generated_exception;
			}

			return $result;
		}

		private function processContentType($content_type, $body){
			switch($content_type){
				case 'application/json':
					return json_decode($body);
					break;
				default:
					return $body;
					break;
			}
		}

		private function log($message){
			$logger = $this->getLogger();
			if($logger != null){
				$logger->log($message);
			}
		}

		// Assertion

		private function assertConfiguration(){
			if(!is_string($this->_options->base_address) || strlen($this->_options->base_address) == 0){
				throw new InvalidConfigurationException("Base Address cannot be empty.");
			}

			if(!is_string($this->_options->app_id) || strlen($this->_options->app_id) == 0){
				throw new InvalidConfigurationException("App Id cannot be empty.");
			}
		}

		private function assertArguments($version, $service, $method, $arguments){
			if(!is_integer($version)){
				throw new InvalidArgumentException("Version must be an an integer value.");
			}

			if(!is_string($service) || strlen($service) == 0){
				throw new InvalidArgumentException("Service must be a string value with at least 1 character.");
			}

			if(!is_string($method) || strlen($method) == 0){
				throw new InvalidArgumentException("Method must be a string value with at least 1 character.");
			}

			if(!is_array($arguments)){
				throw new InvalidArgumentException("Arguments must be an array of arguments.");
			}
		}

		private function assertResponse($response){
			if($response == null){
				throw new InvalidArgumentException("Response cannot be null.");
			}

			if($response->status == null){
				throw new InvalidArgumentException("Response status cannot be null.");
			}

			if(!is_integer($response->status->code)){
				throw new InvalidArgumentException("Response status code must be an integer value.");
			}

			if(!is_array($response->headers)){
				throw new InvalidArgumentException("Response headers must be an array.");
			}
		}
	}

?>