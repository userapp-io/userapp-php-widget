<?php

	namespace UserApp\Logging;

	class MemoryLogger implements ILogger {
		private $_logs;

		public function __construct(){
			$this->_logs = array();
		}

		public function log($message, $type = 'info', $created_at = null){
			if($created_at === null){
				$created_at = time();
			}
			$this->_logs[] = array("type" => $type, "message" => $message, "created_at" => $created_at);
		}

		public function getLogs(){
			return $this->_logs;
		}
	}

?>