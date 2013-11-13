<?php

	namespace UserApp;

	class API extends ClientProxy {
		private static $_instance;

		public static function getInstance($options = null){
			if(self::$_instance == null){
				self::$_instance = new ClientProxy($options);
			}else{
				if($options != null){
					throw new InvalidArgumentException("Cannot provide options since instance has already been set.");
				}
			}
			return self::$_instance;
		}

		public static function setGlobalOptions($options){
			ClientOptions::getGlobal()->set($options);
		}
	}

?>