<?php

	namespace UserApp\Http;

	interface ITransport {
		public function request($method, $url, $headers = null, $body = null);
	}

?>