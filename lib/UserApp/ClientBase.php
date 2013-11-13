<?php

	namespace UserApp;

	use \InvalidArgumentException;
	
	abstract class ClientBase {
		private $_source;

		protected $_options;
		protected $_transport;
		protected $_logger;

		public function __construct(Client $source){
			$this->_source = $source;
		}

		// Options

		public function getOption($name){
			return $this->_source->_options->$name;
		}

		public function getOptions(){
			if($this->_source->_options == null){
				$this->_source->_options = ClientOptions::getGlobal()->createCopy();
			}
			return $this->_source->_options;
		}

		public function setOption($name, $value){
			$this->_source->_options->$name = $value;
		}

		public function setOptions($options){
			$this->_source->_options->set($options);
		}

		// Debug

		protected function debugMode(){
			return $this->_source->_options->debug;
		}

		// Logger

		public function hasLogger(){
			return $this->_source->_logger != null;
		}

		public function getLogger(){
			if($this->debugMode() && $this->_source->_logger === null){
				$this->setLogger(new Logging\MemoryLogger());
			}
			return $this->_source->_logger;
		}

		public function setLogger(Logging\ILogger $logger){
			if($logger === null){
				throw new InvalidArgumentException("Logger cannot be null.");
			}
			$this->_source->_logger = $logger;
		}

		// Transport

		public function getTransport(){
			// If not set, then set the default transport.
			if($this->_source->_transport == null){
				$this->setTransport(new Http\CurlTransport());
			}
			return $this->_source->_transport;
		}

		public function setTransport(Http\ITransport $transport){
			if($transport == null){
				throw new InvalidArgumentException("Transport cannot be null.");
			}
			$this->_source->_transport = $transport;
		}
	}

?>