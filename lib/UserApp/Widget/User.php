<?php

    namespace UserApp\Widget;

    use \stdClass;
    use \Exception;
    use \UserApp\Exceptions\ServiceException;

    class User extends UserStaticBase {
        private $_client;
        private $_user_id;
        private $_data = null;
        private $_data_last_load = null;
        private $_loaded = false;
        private $_changed = array();

        public function __construct($client, $user_id){
            $this->_client = $client;
            $this->_user_id = $user_id;
        }

        public function on($event_name, callable $callback, $priority = 100){
            $this->_client->on($event_name, $callback, $priority);
        }

        public function __get($name){
            if($name == 'user_id'){
                return $this->_user_id;
            }

            $this->load();

            if(!property_exists($this->_data, $name)){
                throw new Exception(sprintf("Property '%s' does not exist.", $name));
            }

            return $this->_data->$name;
        }

        public function __set($name, $value){
            if($name == 'user_id'){
                $this->_user_id = $value;
                return;
            }

            $this->load();

            if($value != $this->_data->$name){
                $this->_changed[$name] = true;
                $this->_data->$name = $value;
            }
        }

        public function hasPermission($permission){
            try {
                if($this->_loaded){
                    return isset($this->permissions->$permission)
                        && $this->permissions->$permission->value == true;
                }

                $result = $this->_client->user->hasPermission(array(
                    "user_id" => "self",
                    "permission" => $permission
                ));

                return count($result->missing_permissions) == 0;
            }catch(ServiceException $exception){
                switch($exception->getErrorCode()){
                    case 'INVALID_CREDENTIALS':
                        return false;
                    default:
                        throw $exception;
                }
            }
        }

        public function hasFeature($feature){
            if($this->_loaded){
                return isset($this->features->$feature)
                    && $this->features->$feature->value == true;
            }

            $result = $this->_client->user->hasFeature(array(
                "user_id" => "self",
                "permission" => $permission
            ));

            return count($result->missing_permissions) == 0;
        }

        public function save(){
            $previous_data = (array)$this->_data_last_load;

            $current_data = self::objectToArray($this->_data);

            $data_diff = self::recursiveArrayDiff($current_data, $previous_data);
            $data_diff['user_id']="self";

            $this->setData($this->_client->user->save($data_diff));
        }

        public function logout(){
            $session = User::getSession();

            $session->remove('ua_token');
            $session->remove('ua_user_id');
            $session->remove('ua_last_heartbeat_at');

            try {
                $this->_client->user->logout();
            }catch(\UserApp\Exceptions\ServiceException $exception){
                // Just discard.
            }
        }

        private function load(){
            if($this->_loaded){
                return;
            }

            $this->_loaded = true;

            $this->setData(current($this->_client->user->get()));
        }

        private function setData($data){
            $this->_data = $data;
            $this->_data_last_load = self::objectToArray($data);
        }

        private static function recursiveArrayDiff($a1, $a2) { 
            $r = array();

            foreach ($a1 as $k => $v) {
                if (array_key_exists($k, $a2)) { 
                    if (is_array($v)) { 
                        $rad = self::recursiveArrayDiff($v, $a2[$k]); 
                        if (count($rad)) { $r[$k] = $rad; } 
                    } else { 
                        if ($v != $a2[$k]) { 
                            $r[$k] = $v; 
                        }
                    }
                } else { 
                    $r[$k] = $v; 
                } 
            }
            return $r; 
        }

        private static function objectToArray($obj) {
            if(is_object($obj)){
                $obj = (array) $obj;
            }

            if(is_array($obj)) {
                $new = array();
                foreach($obj as $key => $val) {
                    $new[$key] = self::objectToArray($val);
                }
            }else{
                $new = $obj;
            }

            return $new;       
        }
    }

?>