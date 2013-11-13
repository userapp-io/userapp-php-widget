<?php

	namespace UserApp\Http;

	use \InvalidArgumentException;
	use \UserApp\Exceptions\TransportException;
	use \UserApp\Exceptions\NotSupportedException;

	class CurlTransport implements ITransport {
		// Check the existence of a common name and also verify that it matches the hostname provided.
		const SSL_VERIFY_NAME_AND_HOST = 2;

		private $_handle;
		private $_verify_ssl;

		public function __construct($verify_ssl = true){
			if (!function_exists('curl_init')) {
				throw new NotSupportedException('This transport requires that the cURL PHP extension is installed.');
			}
			$this->_verify_ssl = (bool)$verify_ssl;
		}

		private function getHandle(){
			if($this->_handle == null){
				$this->_handle = curl_init();
			}
			return $this->_handle;
		}

		public function request($method, $url, $headers = null, $body = null){
			$handle = $this->getHandle();

			$this->assertRequest($method, $url, $headers, $body);

			if($headers == null){
				$headers = array();
			}

			if($body != null){
				$headers["Content-Length"] = strlen($body);
			}

			$encoded_headers = array();
			foreach($headers as $header => $value){
				$encoded_headers[] = sprintf("%s: %s", $header, $value);
			}

			$options = array(
				CURLOPT_URL => $url,
				CURLOPT_CUSTOMREQUEST => strtoupper($method),
				CURLOPT_POSTFIELDS => $body,
				CURLOPT_HTTPHEADER => $encoded_headers,
				CURLOPT_HEADER => true,
				CURLOPT_CONNECTTIMEOUT => 20, // 20s timeout
				CURLOPT_RETURNTRANSFER => true
			);

			if(strpos($url, "https://") === 0){
				$options[CURLOPT_SSL_VERIFYHOST] = $this->_verify_ssl ? self::SSL_VERIFY_NAME_AND_HOST : false;
				$options[CURLOPT_SSL_VERIFYPEER] = $this->_verify_ssl;
			}

			foreach($options as $option => $value){
				curl_setopt($handle, $option, $value);
			}

			$result = curl_exec($handle);

			if($result === false){
				throw new TransportException("cURL error: " . curl_error($handle));
			}

			return Response::fromRaw($result);
		}

		// Assertion

		private function assertRequest($method, $url, $headers, $body){
			if(strlen($method) == 0){
				throw new InvalidArgumentException("Method must at least be 1 character.");
			}

			if(strlen($url) == 0){
				throw new InvalidArgumentException("Url must at least be 1 character.");
			}

			if(strpos($url, "http://") === 0 && strpos($url, "https://") === 0){
				throw new InvalidArgumentException("Url protocol must be either 'https' or 'http'.");
			}

			if($headers != null && !is_array($headers)){
				throw new InvalidArgumentException("Headers must be an array.");
			}

			if($body != null && !is_string($body)){
				throw new InvalidArgumentException("Body must be a string.");
			}
		}

		// Cleanup

		public function __destruct() {
			if($this->_handle != null){
				curl_close($this->_handle);
				$this->_handle = null;
			}
		}
	}

?>