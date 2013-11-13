<?php

	namespace UserApp\Exceptions;

	class InvalidConfigurationException extends UserAppException {
		public function __construct($message, $code = 0, $previous = null){
			parent::__construct($message, $code, $previous);
		}
	}

?>