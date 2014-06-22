<?php

    namespace UserApp\Tests;

    use \UserApp\Widget\User;
    use \PHPUnit_Framework_TestCase;

    use \UserApp\Tests\Mock\MockTransport;
    use \UserApp\Tests\Mock\MockSession;

    require_once(__DIR__ . "/../bootstrap.php");

    class UserTest extends PHPUnit_Framework_TestCase
    {
        private static $_singleton = array();

        private $_proxy;
        private $_session;
        private $_transport;

        public function setup(){
            if(!isset(self::$_singleton['transport'])){
                $transport = $this->_transport = self::$_singleton['transport'] = new MockTransport($this);
            }

            if(!isset(self::$_singleton['proxy'])){
                $proxy = $this->_proxy = self::$_singleton['proxy'] = User::getClient();

                \UserApp\ClientOptions::getGlobal()->transport = self::$_singleton['transport'];
                $proxy->setOption('throw_errors', true);

                User::setAppId('MY_APP_ID');
            }

            if(!isset(self::$_singleton['session'])){
                $session = $this->_session = self::$_singleton['session'] = new MockSession();
                User::setSession($session);
            }

            $this->_transport = self::$_singleton['transport'];
            $this->_proxy = self::$_singleton['proxy'];
            $this->_session = self::$_singleton['session'];
        }
        
        public function testCanSuccessfullyLogin(){
            $this->_transport->addRequestHandler(function($method, $url, $headers, $body){
                $data = json_decode($body);

                if($url == 'https://api.userapp.io/v1/user.login'){
                    if($data->login == 'root' && $data->password == 'pwnz!'){
                        return array('token' => 'bobo', 'user_id' => '666');
                    }else{
                        return array('error_code' => 'INVALID_CREDENTIALS', 'message' => 'Blah blah blah, could not login.');
                    }   
                }

                return false;
            });

            $login_success = User::login('root', 'pwnz!');

            $this->assertTrue($login_success);
            $this->assertTrue($this->_session->has('ua_token'));
            $this->assertEquals($this->_session->get('ua_token'), 'bobo');
            $this->assertTrue($this->_session->has('ua_last_heartbeat_at'));
            $this->assertTrue($this->_session->get('ua_last_heartbeat_at') > 0);
            $this->assertTrue($this->_session->has('ua_user_id'));
            $this->assertEquals($this->_session->get('ua_user_id'), '666');
        }

        /**
         * @depends testCanSuccessfullyLogin
         */
        public function testThatSessionHeartbeatIsUpdatedWhenMakingApiCall(){
            $this->_transport->addRequestHandler(function($method, $url, $headers, $body){
                $data = json_decode($body);

                if($url == 'https://api.userapp.io/v1/user.get'){
                    return array(
                        array(
                            'user_id' => '666',
                            'login' => 'root',
                            'properties' => array(
                                'age' => array(
                                    'value' => 25,
                                    'override' => false
                                )
                            ),
                            'permissions' => array(
                                'admin' => array(
                                    'value' => true,
                                    'override' => true
                                )
                            ),
                            'features' => array(
                                'sms' => array(
                                    'value' => true,
                                    'override' => true
                                )
                            ),
                            'updated_at' => time(),
                            'created_at' => time()
                        )
                    );
                }

                return false;
            });

            $user = User::current();
            $this->assertTrue($user !== null);

            $previous_heartbeat_at = $this->_session->get("ua_last_heartbeat_at");
            sleep(1); // Delay call with 1 sec so that the heartbeat will differ

            $this->assertEquals($user->login, 'root');
            $this->assertNotEquals($this->_session->get("ua_last_heartbeat_at"), $previous_heartbeat_at);
        }

        /**
         * @depends testThatSessionHeartbeatIsUpdatedWhenMakingApiCall
         */
        public function testThatUserPropertyCanBeRetrieved(){
            $user = User::current();

            $this->assertEquals($user->login, 'root');
            $this->assertEquals($user->properties->age->value, 25);
        }

        /**
         * @depends testThatUserPropertyCanBeRetrieved
         */
        public function testThatUserPermissionCanBeVerified(){
            $user = User::current();

            $this->assertTrue($user->hasPermission('admin'));
            $this->assertFalse($user->hasPermission('guest'));
        }

        /**
         * @depends testThatSessionHeartbeatIsUpdatedWhenMakingApiCall
         */
        public function testThatUserFeatureCanBeVerified(){
            $user = User::current();

            $this->assertTrue($user->hasFeature('sms'));
            $this->assertFalse($user->hasFeature('gold'));
        }

        /**
         * @depends testThatUserFeatureCanBeVerified
         */
        public function testThatUserPropertiesCanBeSet(){
            $user = User::current();

            $this->_transport->addRequestHandler(function($method, $url, $headers, $body){
                $data = json_decode($body);

                $user_save_data_compare = array(
                    "properties" => array(
                        "age" => 26
                    ), "features" => array(
                        "sms" => array(
                            "override" => false
                    )),
                    "user_id" => "self"
                );

                if($url == 'https://api.userapp.io/v1/user.save'){
                    if($body == json_encode($user_save_data_compare)){
                        return array(
                            'user_id' => 'self',
                            'login' => 'root',
                            'properties' => array(
                                'age' => array(
                                    'value' => $data->properties->age,
                                    'override' => true
                                )
                            ),
                            'permissions' => array(
                                'admin' => array(
                                    'value' => true,
                                    'override' => true
                                )
                            ),
                            'features' => array(
                                'sms' => array(
                                    'value' => false,
                                    'override' => false
                                )
                            ),
                            'updated_at' => time(),
                            'created_at' => time()
                        );
                    }
                }

                return false;
            });

            $old_updated_at = $user->updated_at;

            sleep(1); // Delay call with 1 sec so that updated_at differs

            $user->properties->age = 26;
            $user->features->sms->override = false;
            $save_result = $user->save();

            $this->assertNull($save_result);
            $this->assertEquals($user->properties->age->value, 26);
            $this->assertEquals($user->features->sms->value, false);
            $this->assertEquals($user->features->sms->override, false);
            $this->assertNotEquals($user->updated_at, $old_updated_at);
        }

        /**
         * @depends testThatUserPropertiesCanBeSet
         */
        public function testThatALoggedInUserCanBeLoggedOut(){
            $user = User::current();

            $this->_transport->addRequestHandler(function($method, $url, $headers, $body){
                $data = json_decode($body);

                if($url == 'https://api.userapp.io/v1/user.logout'){
                    return array();
                }

                return false;
            });

            $logout_result = $user->logout();

            $this->assertNull($logout_result);

            $this->assertFalse($this->_session->has("ua_token"));
            $this->assertFalse($this->_session->has("ua_user_id"));
            $this->assertFalse($this->_session->has("ua_last_heartbeat_at"));
        }
    }

?>