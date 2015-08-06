<?php

    class ConstructrBase
    {
		public static function constructrNavGen($BASE_URL,$PAGES, $MOTHER = 0){
	        $TREE = '';
	        $TREE = '<ul>';
	        for($i=0, $ni=count($PAGES); $i < $ni; $i++){
	            if($PAGES[$i]['constructr_pages_mother'] == $MOTHER){
	                $TREE .= '<li>';
	                $TREE .= $PAGES[$i]['constructr_pages_name'];
	                $TREE .= self::constructrNavGen($BASE_URL,$PAGES, $PAGES[$i]['constructr_pages_id']);
	                $TREE .= '</li>';
	            }
	        }
	        $TREE .= '</ul>';
			$TREE = str_replace('<ul></ul>','',$TREE);
	        return $TREE;
		}

        public function no_rights($APP)
        {
            echo Template::instance()->render('CONSTRUCTR-CMS/TEMPLATES/constructr_admin_no_rights.html', 'text/html');
        }

        public function checkUserModulRights($MODUL_ID, $USER_RIGHTS)
        {
            $USER_RIGHTS_COUNTR = 0;

            foreach ($USER_RIGHTS as $KEY => $VALUE){
                if ($KEY == $MODUL_ID && $VALUE == 1){
                    $USER_RIGHTS_COUNTR ++;
                }
            }

            return $USER_RIGHTS_COUNTR;
        }

        public function init($APP)
        {
            $CSRF = self::csrf();
            $APP->set('CSRF', $CSRF);
            $APP->set('SESSION.csrf', $CSRF);

            $ADDITIVE = self::additive();
            $APP->set('ADDITIVE', $ADDITIVE);
            $APP->set('SESSION.additive', $ADDITIVE);

            $TRIPPLE_ADDITIVE = ($ADDITIVE.$CSRF);
            $APP->set('TRIPPLE_ADDITIVE', $TRIPPLE_ADDITIVE);
            $APP->set('SESSION.tripple_additive', $TRIPPLE_ADDITIVE);

			if($APP->get('SESSION.login') == 'true' && $APP->get('SESSION.password') != '' && $APP->get('SESSION.username') != ''){
				$APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/pagemanagement');
			}

            echo Template::instance()->render('CONSTRUCTR-CMS/TEMPLATES/index.html', 'text/html');
        }

        public function login($APP)
        {
            $POST_CSRF = filter_var($APP->get('POST.csrf'), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $POST_ADDITIVE = filter_var($APP->get('POST.csrf_additive'), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $POST_TRIPPLE_ADDITIVE = filter_var($APP->get('POST.csrf_tripple_additive'), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $POST_USERNAME = filter_var($APP->get('POST.username'), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $POST_PASSWORD = crypt(filter_var($APP->get('POST.password'), FILTER_SANITIZE_FULL_SPECIAL_CHARS), $APP->get('CONSTRUCTR_USER_SALT'));
			$EMAIL = $APP->get('POST.email');

            if ($EMAIL != ''){
            	$APP->get('CONSTRUCTR_LOG')->write('SPAM LOGIN: '.$EMAIL);
                die();
            }

            if ($POST_CSRF != ''){
                if ($POST_CSRF != $APP->get('SESSION.csrf')){
                    $APP->get('CONSTRUCTR_LOG')->write('LOGIN FORM CSRF DON\'T MATCH: '.$POST_USERNAME);
                    $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/login-error');
                }
            }

            if ($POST_ADDITIVE != ''){
                if ($POST_ADDITIVE != $APP->get('SESSION.additive')){
                    $APP->get('CONSTRUCTR_LOG')->write('LOGIN FORM ADDITIVE DON\'T MATCH: '.$POST_USERNAME);
                    $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/login-error');
                }
            }

            if ($POST_TRIPPLE_ADDITIVE != ''){
                if ($POST_TRIPPLE_ADDITIVE != $APP->get('SESSION.tripple_additive')){
                    $APP->get('CONSTRUCTR_LOG')->write('LOGIN FORM TRIPPLE ADDITIVE DON\'T MATCH: '.$POST_USERNAME);
                    $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/login-error');
                }
            }

            if ($POST_TRIPPLE_ADDITIVE != $POST_ADDITIVE.$POST_CSRF){
                $APP->get('CONSTRUCTR_LOG')->write('LOGIN FORM TRIPPLE ADDITIVE COMPARISON DON\'T MATCH: '.$POST_USERNAME);
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/login-error');
            }

            if ($POST_USERNAME != '' && $POST_PASSWORD != ''){
                $APP->set('LOGIN_USER', $APP->get('DBCON')->exec(
                        array('SELECT * FROM constructr_backenduser WHERE constructr_user_active=:ACTIVE AND constructr_user_username=:USERNAME AND constructr_user_password=:PASSWORD LIMIT 1;',),
                        array(
                            array(
                                ':ACTIVE' => (int) 1,
                                ':USERNAME' => $POST_USERNAME,
                                ':PASSWORD' => $POST_PASSWORD
                            )
                        )
                    )
                );

                $LOGIN_USER = $APP->get('LOGIN_USER');

                if (count($LOGIN_USER) == 1){
                    $APP->set('UPDATE_LOGIN_USER', $APP->get('DBCON')->exec(
                            array('UPDATE constructr_backenduser SET constructr_user_last_login=:LAST_LOGIN WHERE constructr_user_active=:ACTIVE AND constructr_user_username=:USERNAME AND constructr_user_password=:PASSWORD LIMIT 1;',),
                            array(
                                array(
                                    ':ACTIVE' => (int) 1,
                                    ':LAST_LOGIN' => date('Y-m-d H:i:s'),
                                    ':USERNAME' => $POST_USERNAME,
                                    ':PASSWORD' => $POST_PASSWORD
                                )
                            )
                        )
                    );

                    $APP->set('SESSION.login', 'true');
                    $APP->set('SESSION.username', $POST_USERNAME);
                    $APP->set('SESSION.password', $POST_PASSWORD);
                    $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/pagemanagement');
                } else {
                    $APP->set('SESSION.login', 'false');
                    $APP->get('CONSTRUCTR_LOG')->write('LOGIN USER CREDENTIALS DONT\'T MATCH - USERNAME: '.$POST_USERNAME);

                    $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/login-error');
                }
            }
        }

        public function login_error($APP)
        {
            $APP->get('CONSTRUCTR_LOG')->write('LOGIN_ERROR!');

            $CSRF = self::csrf();
            $APP->set('CSRF', $CSRF);
            $APP->set('SESSION.csrf', $CSRF);

            $ADDITIVE = self::additive();
            $APP->set('ADDITIVE', $ADDITIVE);
            $APP->set('SESSION.additive', $ADDITIVE);

            $TRIPPLE_ADDITIVE = ($ADDITIVE.$CSRF);
            $APP->set('TRIPPLE_ADDITIVE', $TRIPPLE_ADDITIVE);
            $APP->set('SESSION.tripple_additive', $TRIPPLE_ADDITIVE);

            echo Template::instance()->render('CONSTRUCTR-CMS/TEMPLATES/index_login_error.html', 'text/html');
        }

	    public function flatten_array($array)
	    {
	        $flat_array = array();
	        $size = sizeof($array);
	        $keys = array_keys($array);
	
	        for ($x = 0; $x < $size; $x++){
	            $element = $array[$keys[$x]]; if (is_array($element)){
	    			$results = self::flatten_array($element);
					$sr = sizeof($results);
	    			$sk = array_keys($results);
	
	    			for ($y = 0; $y < $sr; $y++){
	        			$flat_array[$sk[$y]] = $results[$sk[$y]];
	    			}
	
				} else {
	    			$flat_array[$keys[$x]] = $element;
				}
	        }
	
	        return $flat_array;
	    }

	    public function gffd($dir){
	        $files = array();
	        if ($handle = opendir($dir)){
	            while (false !== ($file = readdir($handle))){
	                if ($file != "." && $file != ".."){
	                    if (is_dir($dir.'/'.$file)){
	                        $dir2 = $dir.'/'.$file;
	                        $files[] = self::gffd($dir2);
	                    } else {
	                        $files[] = $dir.'/'.$file;
	                    }
	                }
	            }
	            closedir($handle);
	        }
	
	        return self::flatten_array($files);
	    }

        public function logout($APP)
        {
            $APP->clear('SESSION.username');
            $APP->clear('SESSION.password');
            $APP->clear('SESSION.login');

            $_SESSION = array();

            if (ini_get("session.use_cookies")){
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
            }

            session_destroy();
            session_regenerate_id(true);

            $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/');
        }

        public function retrieve_password($APP)
        {
            $USERNAME = filter_var($APP->get('PARAMS.username'), FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            if ($USERNAME != ''){
                $APP->set('USER_BASE_DATA', $APP->get('DBCON')->exec(
                        array('SELECT * FROM constructr_backenduser WHERE constructr_user_username=:USERNAME AND constructr_user_active=:ACTIVE LIMIT 1;',),
                        array(
                            array(
                                ':USERNAME' => $USERNAME,
                                ':ACTIVE' => (int) 1
                            )
                        )
                    )
                );

                $USER = $APP->get('USER_BASE_DATA');

                if (count($USER) == 1){
                    $TMP_PASSWORD = self::csrf();
                    $APP->set('NEW_PASSWORD', $TMP_PASSWORD);

                    if ($APP->get('NEW_PASSWORD') != ''){
                        $NEW_CRYPTED_PASSWORD = crypt(filter_var($APP->get('NEW_PASSWORD'), FILTER_SANITIZE_FULL_SPECIAL_CHARS), $APP->get('CONSTRUCTR_USER_SALT'));

                        $APP->set('USER', $APP->get('DBCON')->exec(
                                array('UPDATE constructr_backenduser SET constructr_user_password=:NEW_CRYPTED_PASSWORD WHERE constructr_user_username=:USERNAME AND constructr_user_active=:ACTIVE LIMIT 1;',),
                                array(
                                    array(
                                        ':USERNAME' => $USERNAME,
                                        ':NEW_CRYPTED_PASSWORD' => $NEW_CRYPTED_PASSWORD,
                                        ':ACTIVE' => (int) 1
                                    )
                                )
                            )
                        );

                        @mail($APP->get('USER_BASE_DATA.0.constructr_user_email'), 'Constructr Password-Reset', date('d.m.Y, H:i').' Uhr //  New password for you: '.$APP->get('NEW_PASSWORD').' - update as soon as possible! ' . $APP->get('CONSTRUCTR_BASE_URL'));

                        $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/updated-user-credentials');
                    } else {
                        $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/login-error');
                    }
                } else {
                    $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/login-error');
                }
            }
        }

        public function updated_user_credentials($APP)
        {
            $CSRF = self::csrf();
            $APP->set('CSRF', $CSRF);

            $APP->set('SESSION.csrf', $CSRF);
            $ADDITIVE = self::additive();

            $APP->set('ADDITIVE', $ADDITIVE);
            $APP->set('SESSION.additive', $ADDITIVE);

            $TRIPPLE_ADDITIVE = ($ADDITIVE.$CSRF);
            $APP->set('TRIPPLE_ADDITIVE', $TRIPPLE_ADDITIVE);
            $APP->set('SESSION.tripple_additive', $TRIPPLE_ADDITIVE);

            echo Template::instance()->render('CONSTRUCTR-CMS/TEMPLATES/updated-user-credentials.html', 'text/html');
        }

        public static function additive($LENGTH = 10, $CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz')
        {
            for ($S = '', $CL = strlen($CHARS)-1, $i = 0; $i < $LENGTH; $S .= $CHARS[mt_rand(0, $CL)], ++$i);

            return $S;
        }

        public static function csrf()
        {
            return mt_rand().time();
        }
		
        public static function clean_up_cache($APP)
        {
			if(@is_dir($APP->get('CONSTRUCTR_FE_CACHE'))) {
				if ($H=@opendir($APP->get('CONSTRUCTR_FE_CACHE'))) {
					while(($F=@readdir($H))!==false) {
						if ($F!='.' && $F!='..' && $F!='.empty_file'){
							@unlink($APP->get('CONSTRUCTR_FE_CACHE').$F);
						}
					}
					@closedir($H);
				}
			}
        }
    }
