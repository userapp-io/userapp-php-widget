<?php

	namespace UserApp\Logging;

	interface ILogger {
		public function log($message, $type = 'info', $created_at = null);
	}

?>