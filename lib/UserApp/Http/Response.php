<?php

	namespace UserApp\Http;

	use \stdClass;

	class Response {
		public $status;
		public $headers;
		public $body;
		
		// Parse a raw HTTP response

		public static function fromRaw($raw){
			$response = new Response();

			$header_end_offset = strpos($raw, "\r\n\r\n");
			$raw_head = substr($raw, 0, $header_end_offset);
			$raw_body = substr($raw, $header_end_offset+4);
			$raw_headers = explode("\r\n", $raw_head);
			$status_head = array_shift($raw_headers);
			$status_segments = explode(" " , $status_head);

			$status = new stdClass();
			$status->protocol = $status_segments[0];
			$status->code = (int)$status_segments[1];
			$status->message = $status_segments[2];

			$headers = array();
			foreach($raw_headers as $header){
				$segments = explode(": ", $header, 2);
				$headers[$segments[0]] = $segments[1];
			}

			$response->status = $status;
			$response->headers = $headers;
			$response->body = $raw_body;

			return $response;
		}
	}

?>