<?php

	namespace UserApp\Exceptions;

	class NotSupportedException extends UserAppException {
		public function __construct($message, $code = 0, $previous = null){
			parent::__construct($message, $code, $previous);
		}
	}

?>