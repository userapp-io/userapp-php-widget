<?php

	namespace UserApp\Http;

	use \stdClass;

	class FakeTransport implements ITransport {
		public function request($method, $url, $headers = null, $body = null){
			echo(sprintf("Sending request %s %s with headers %s and body %s", $method, $url, $headers, $body));

			$response = new Response();

			$status = new stdClass();
			$status->protocol = "HTTP/1.FAKE";
			$status->code = 200;
			$status->message = "OK";

			$response->status = $status;
			$response->headers = array("Content-Type" => "application/json");
			$response->body = json_encode(array("error_code" => "FAKE_RESULT", "message" => "This is a fake result."));

			return $response;
		}
	}

?>