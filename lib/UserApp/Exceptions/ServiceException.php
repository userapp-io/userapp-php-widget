<?php

	namespace UserApp\Exceptions;

	class ServiceException extends UserAppException {
		private $_error_code;
		
		public function __construct($error_code, $message){
			$this->_error_code = $error_code;
			parent::__construct($message);
		}

		public function getErrorCode(){
			return $this->_error_code;
		}
	}

?>