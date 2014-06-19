<?php

	namespace UserApp\Tests\Mock;

	use \stdClass;

	use \UserApp\Http\Response;
	use \UserApp\Http\ITransport;

	class MockTransport implements ITransport {
		private $_test;
		private $_assertion_queue;
		private $_request_handlers;

		public function __construct($test){
			$this->_test = $test;
			$this->_assertion_queue = array();
			$this->_request_handlers = array();
		}

		public function request($method, $url, $headers = null, $body = null){
			print_r("\nRecieved new request.\n");
			print_r($method . "\n");
			print_r($url . "\n");
			print_r($headers);
			print_r($body . "\n");

			$result = null;

			if(count($this->_request_handlers) > 0){
				foreach($this->_request_handlers as $handler){
					$result = $handler($method, $url, $headers, $body);
					if($result !== false){
						$result = self::getMockResponse($result);
						break;
					}
				}

				if($result === null){
					$this->_test->assertTrue(false, 'Handlers were added, but none handled the request.');
				}
			}else{
				$result = self::getMockResponse();
			}

			return $result;
		}

		public function addRequestHandler(\Closure $handler){
			$this->_request_handlers[]=$handler;
		}

		public function assertEmptyQueue(){
			$this->_test->assertEmpty($this->_assertion_queue);
		}

		private static function getMockResponse($body = null){
			$response = new Response();

			$status = new stdClass();
			$status->protocol = "HTTP/1.FAKE";
			$status->code = 200;
			$status->message = "OK";

			$response->status = $status;
			$response->headers = array("Content-Type" => "application/json");
			$response->body = json_encode($body === null ? array("error_code" => "FAKE_RESULT", "message" => "This is a fake result.") : $body);

			return $response;
		}
	}

?>