<?php

	namespace UserApp\Widget;

	use \Exception;
    use \UserApp\Exceptions\ServiceException;

	class User extends UserStaticBase {
        private $_client;
        private $_user_id;
        private $_data = null;
        private $_loaded = false;

		public function __construct($app_id, $session_token, $user_id){
            $client = $this->_client = new \UserApp\API($app_id, $session_token);
            $client->setTransport(new \UserApp\Http\CurlTransport(false));
            $this->_user_id = $user_id;
		}

        public function __get($name){
            if($name == 'user_id'){
                return $this->_user_id;
            }

            $this->load();

            if(!property_exists($this->_data, $name)){
                throw new Exception("Property does not exist");
            }

            return $this->_data->$name;
        }

        /*public function __set($name, $value){
            $this->$name = $value;
        }*/

        public function hasPermission($permission){
            if($this->_loaded){
                return isset($this->permission->$permission)
                    && $this->permission->$permission->value == true;
            }

            $result = $this->_client->user->hasPermission(array(
                "user_id" => $this->user_id,
                "permission" => $permission
            ));

            return count($result->missing_permissions) == 0;
        }

        public function hasFeature($feature){
            if($this->_loaded){
                return isset($this->features->$feature)
                    && $this->features->$feature->value == true;
            }

            $result = $this->_client->user->hasFeature(array(
                "user_id" => $this->user_id,
                "permission" => $permission
            ));

            return count($result->missing_permissions) == 0;
        }

        public function logout(){
            $session = User::getSession();

            $session->remove('ua_token');
            $session->remove('ua_user_id');

            try {
                $this->_client->user->logout();
            }catch(\UserApp\Exceptions\ServiceException $exception){
                if($exception->getErrorCode() == 'INVALID_CREDENTIALS'){
                    return false;
                }
                throw $exception;
            }

            return true;
        }

        private function load(){
            if($this->_loaded){
                return;
            }

            $this->_loaded = true;

            $this->_data = current($this->_client->user->get());
        }
	}

?>