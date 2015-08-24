<?php

    class ConstructrBase
    {
		public static function constructrNavGenClasses($REQUEST,$BASE_URL,$PAGES,$UL_CLASS,$UL_CLASS_SUB,$LI_CLASS_INACTIVE,$LI_CLASS_ACTIVE,$MOTHER=0)
		{
	        $TREE='';

			if($MOTHER!=0){
				$TREE='<ul class="'.$UL_CLASS_SUB.'">';	
			} else {
				$TREE='<ul class="'.$UL_CLASS.'">';
			}

	        for($i=0,$ni=count($PAGES);$i<$ni;$i++){
	            if($PAGES[$i]['constructr_pages_mother']==$MOTHER){
					if($REQUEST == $PAGES[$i]['constructr_pages_url']){
						$TREE.='<li class="'.$LI_CLASS_ACTIVE.'">';
					} else {
						$TREE.='<li class="'.$LI_CLASS_INACTIVE.'">';	
					}

	                $TREE.='<a href="'.$BASE_URL.'/'.$PAGES[$i]['constructr_pages_url'].'">'.$PAGES[$i]['constructr_pages_name'].'</a>';
	                $TREE.=self::constructrNavGenClasses($REQUEST,$BASE_URL,$PAGES,$UL_CLASS,$UL_CLASS_SUB,$LI_CLASS_INACTIVE,$LI_CLASS_ACTIVE,$PAGES[$i]['constructr_pages_id']);
	                $TREE.='</li>';
	            }
	        }

	        $TREE.='</ul>';
			$TREE=str_replace('<ul class="'.$UL_CLASS.'"></ul>','',$TREE);
			$TREE=str_replace('<ul class="'.$UL_CLASS_SUB.'"></ul>','',$TREE);
	        return $TREE;
		}

		public static function constructrNavGen($BASE_URL,$PAGES,$MOTHER=0)
		{
	        $TREE='';
	        $TREE='<ul>';
	        for($i=0,$ni=count($PAGES);$i<$ni;$i++){
	            if($PAGES[$i]['constructr_pages_mother']==$MOTHER){
	                $TREE.='<li>';
	                $TREE.='<a href="'.$BASE_URL.'/'.$PAGES[$i]['constructr_pages_url'].'">'.$PAGES[$i]['constructr_pages_name'].'</a>';
	                $TREE.=self::constructrNavGen($BASE_URL,$PAGES,$PAGES[$i]['constructr_pages_id']);
	                $TREE.='</li>';
	            }
	        }
	        $TREE.='</ul>';
			$TREE=str_replace('<ul></ul>','',$TREE);
	        return $TREE;
		}

		public static function constructrFirstLevelNav($APP,$REQUEST,$DBCON,$CONSTRUCTR_BASE_URL,$PAGES_NAV='')
		{
            $APP->set('PAGES_NAV',$DBCON->exec(array('SELECT * FROM constructr_pages WHERE constructr_pages_mother=0 AND constructr_pages_active=1 ORDER BY constructr_pages_order ASC;')));

            $APP->set('ACT_MOTHER',$DBCON->exec(
                    array('SELECT constructr_pages_mother FROM constructr_pages WHERE constructr_pages_url=:ACT_URL LIMIT 1;'),
                    array(array(':ACT_URL'=>$REQUEST))
                )
            );

			if($APP->get('PAGES_NAV')){
				$PAGES_NAV.='<ul class="pagesnav">';

				foreach($APP->get('PAGES_NAV') AS $PAGE){
					if($REQUEST == $PAGE['constructr_pages_url'] || $APP->get('ACT_MOTHER.0.constructr_pages_mother') == $PAGE['constructr_pages_id']){
						$PAGES_NAV.='<li class="active"><a href="' . $CONSTRUCTR_BASE_URL . '/' . $PAGE['constructr_pages_url'] . '">' . $PAGE['constructr_pages_name'] . '</a></li>';
					} else {
						$PAGES_NAV.='<li class="inactive"><a href="' . $CONSTRUCTR_BASE_URL . '/' . $PAGE['constructr_pages_url'] . '">' . $PAGE['constructr_pages_name'] . '</a></li>';
					}
				}

				$PAGES_NAV.='</ul>';
			}

			return $PAGES_NAV;
		}

		public static function constructrSecondLevelNav($APP,$REQUEST,$DBCON,$CONSTRUCTR_BASE_URL,$PAGES_NAV='')
		{
            $APP->set('PAGES_NAV',$DBCON->exec(array('SELECT * FROM constructr_pages WHERE constructr_pages_level=2 AND constructr_pages_mother!=0 AND constructr_pages_active=1 ORDER BY constructr_pages_order ASC;')));

            $APP->set('ACT_MOTHER',$DBCON->exec(
                    array('SELECT constructr_pages_mother FROM constructr_pages WHERE constructr_pages_url=:ACT_URL LIMIT 1;'),
                    array(array(':ACT_URL'=>$REQUEST))
                )
            );

			if($APP->get('PAGES_NAV')){
				$PAGES_NAV.='<ul class="pagesnav">';

				foreach($APP->get('PAGES_NAV') AS $PAGE){
					if($REQUEST == $PAGE['constructr_pages_url'] || $APP->get('ACT_MOTHER.0.constructr_pages_mother') == $PAGE['constructr_pages_id']){
						$PAGES_NAV.='<li class="active"><a href="' . $CONSTRUCTR_BASE_URL . '/' . $PAGE['constructr_pages_url'] . '">' . $PAGE['constructr_pages_name'] . '</a></li>';
					} else {
						$PAGES_NAV.='<li class="inactive"><a href="' . $CONSTRUCTR_BASE_URL . '/' . $PAGE['constructr_pages_url'] . '">' . $PAGE['constructr_pages_name'] . '</a></li>';
					}
				}

				$PAGES_NAV.='</ul>';
			}

			return $PAGES_NAV;
		}

		public static function constructrThirdLevelNav($APP,$REQUEST,$DBCON,$CONSTRUCTR_BASE_URL,$PAGES_NAV = '')
		{
            $APP->set('PAGES_NAV',$DBCON->exec(array('SELECT * FROM constructr_pages WHERE constructr_pages_level=3 AND constructr_pages_mother!=0 AND constructr_pages_active=1 ORDER BY constructr_pages_order ASC;')));

            $APP->set('ACT_MOTHER',$DBCON->exec(
                    array('SELECT constructr_pages_mother FROM constructr_pages WHERE constructr_pages_url=:ACT_URL LIMIT 1;'),
                    array(array(':ACT_URL'=>$REQUEST))
                )
            );

			if($APP->get('PAGES_NAV')){
				$PAGES_NAV.='<ul class="pagesnav">';

				foreach($APP->get('PAGES_NAV') AS $PAGE){
					if($REQUEST == $PAGE['constructr_pages_url'] || $APP->get('ACT_MOTHER.0.constructr_pages_mother') == $PAGE['constructr_pages_id']){
						$PAGES_NAV.='<li class="active"><a href="' . $CONSTRUCTR_BASE_URL . '/' . $PAGE['constructr_pages_url'] . '">' . $PAGE['constructr_pages_name'] . '</a></li>';
					} else {
						$PAGES_NAV.='<li class="inactive"><a href="' . $CONSTRUCTR_BASE_URL . '/' . $PAGE['constructr_pages_url'] . '">' . $PAGE['constructr_pages_name'] . '</a></li>';
					}
				}

				$PAGES_NAV.='</ul>';
			}

			return $PAGES_NAV;
		}

		public static function constructrSubnavPages($APP,$REQUEST,$DBCON,$CONSTRUCTR_BASE_URL,$SUB_NAV = '')
		{
            $APP->set('ACT_REQUEST',$DBCON->exec(
                    array('SELECT constructr_pages_id,constructr_pages_mother FROM constructr_pages WHERE constructr_pages_url=:ACT_URL LIMIT 1;'),
                    array(array(':ACT_URL'=>$REQUEST))
                )
            );

			if($APP->get('ACT_REQUEST')){
	            $APP->set('SUB_PAGES',$DBCON->exec(
	                    array('SELECT * FROM constructr_pages WHERE constructr_pages_active=1 AND constructr_pages_mother=:MOTHER ORDER BY constructr_pages_order ASC;'),
	                    array(array(':MOTHER'=>$APP->get('ACT_REQUEST.0.constructr_pages_id')))
	                )
	            );

				if($APP->get('SUB_PAGES')){
					$SUB_NAV.='<ul class="subnav">';

					foreach($APP->get('SUB_PAGES') AS $PAGE){
						if($REQUEST == $PAGE['constructr_pages_url']){
							$SUB_NAV.='<li class="active"><a href="' . $CONSTRUCTR_BASE_URL . '/' . $PAGE['constructr_pages_url'] . '">' . $PAGE['constructr_pages_name'] . '</a></li>';
						} else {
							$SUB_NAV.='<li class="inactive"><a href="' . $CONSTRUCTR_BASE_URL . '/' . $PAGE['constructr_pages_url'] . '">' . $PAGE['constructr_pages_name'] . '</a></li>';
						}
					}

					$SUB_NAV.='</ul>';
				} else {
					if($APP->get('ACT_REQUEST.0.constructr_pages_mother') != 0){
			            $APP->set('SUB_PAGES',$DBCON->exec(
			                    array('SELECT * FROM constructr_pages WHERE constructr_pages_active=1 AND constructr_pages_mother=:MOTHER ORDER BY constructr_pages_order ASC;'),
			                    array(array(':MOTHER'=>$APP->get('ACT_REQUEST.0.constructr_pages_mother')))
			                )
			            );

						$SUB_NAV.='<ul class="subnav">';

						foreach($APP->get('SUB_PAGES') AS $PAGE){
							if($REQUEST == $PAGE['constructr_pages_url']){
								$SUB_NAV.='<li class="active"><a href="' . $CONSTRUCTR_BASE_URL . '/' . $PAGE['constructr_pages_url'] . '">' . $PAGE['constructr_pages_name'] . '</a></li>';
							} else {
								$SUB_NAV.='<li class="inactive"><a href="' . $CONSTRUCTR_BASE_URL . '/' . $PAGE['constructr_pages_url'] . '">' . $PAGE['constructr_pages_name'] . '</a></li>';
							}
						}

						$SUB_NAV.='</ul>';
					}
				}
			}

			return $SUB_NAV;
		}

        public function no_rights($APP)
        {
        	$APP->set('ACT_VIEW','');
            echo Template::instance()->render('CONSTRUCTR-CMS/TEMPLATES/constructr_admin_no_rights.html','text/html');
        }

        public function checkUserModulRights($MODUL_ID,$USER_RIGHTS,$USER_RIGHTS_COUNTR=0)
        {
            foreach ($USER_RIGHTS as $KEY=>$VALUE){
                if ($KEY==$MODUL_ID && $VALUE==1){
                    $USER_RIGHTS_COUNTR ++;
                }
            }

            return $USER_RIGHTS_COUNTR;
        }

        public function init($APP)
        {
            $CSRF=self::csrf();
            $APP->set('CSRF',$CSRF);
            $APP->set('SESSION.csrf',$CSRF);

            $ADDITIVE=self::additive();
            $APP->set('ADDITIVE',$ADDITIVE);
            $APP->set('SESSION.additive',$ADDITIVE);

            $TRIPPLE_ADDITIVE=($ADDITIVE.$CSRF);
            $APP->set('TRIPPLE_ADDITIVE',$TRIPPLE_ADDITIVE);
            $APP->set('SESSION.tripple_additive',$TRIPPLE_ADDITIVE);

			if($APP->get('SESSION.login') == 'true' && $APP->get('SESSION.password')!='' && $APP->get('SESSION.username')!=''){
				$APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/pagemanagement');
			}

            echo Template::instance()->render('CONSTRUCTR-CMS/TEMPLATES/index.html','text/html');
        }

        public function login_step_1($APP)
        {
            $POST_CSRF=filter_var($APP->get('POST.csrf'),FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $POST_ADDITIVE=filter_var($APP->get('POST.csrf_additive'),FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $POST_TRIPPLE_ADDITIVE=filter_var($APP->get('POST.csrf_tripple_additive'),FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $POST_USERNAME=trim($APP->get('POST.username'));
			$EMAIL=$APP->get('POST.email');

            if ($EMAIL!=''){
            	$APP->get('CONSTRUCTR_LOG')->write('SPAM LOGIN: '.$EMAIL);
                die();
            }

            if ($POST_CSRF!=''){
                if ($POST_CSRF!=$APP->get('SESSION.csrf')){
                    $APP->get('CONSTRUCTR_LOG')->write('LOGIN FORM CSRF DON\'T MATCH: '.$POST_USERNAME);
                    $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/login-error');
                }
            }

            if ($POST_ADDITIVE!=''){
                if ($POST_ADDITIVE!=$APP->get('SESSION.additive')){
                    $APP->get('CONSTRUCTR_LOG')->write('LOGIN FORM ADDITIVE DON\'T MATCH: '.$POST_USERNAME);
                    $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/login-error');
                }
            }

            if ($POST_TRIPPLE_ADDITIVE!=''){
                if ($POST_TRIPPLE_ADDITIVE!=$APP->get('SESSION.tripple_additive')){
                    $APP->get('CONSTRUCTR_LOG')->write('LOGIN FORM TRIPPLE ADDITIVE DON\'T MATCH: '.$POST_USERNAME);
                    $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/login-error');
                }
            }

            if ($POST_TRIPPLE_ADDITIVE!=$POST_ADDITIVE.$POST_CSRF){
                $APP->get('CONSTRUCTR_LOG')->write('LOGIN FORM TRIPPLE ADDITIVE COMPARISON DON\'T MATCH: '.$POST_USERNAME);
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/login-error');
            }

            if ($POST_USERNAME!='')
            {
            	$APP->set('SESSION.post_username',$POST_USERNAME);
                $APP->set('LOGIN_USER',$APP->get('DBCON')->exec(
                        array('SELECT * FROM constructr_backenduser WHERE constructr_user_active=:ACTIVE AND constructr_user_username=:USERNAME LIMIT 1;'),
                        array(
                            array(
                                ':ACTIVE'=>1,
                                ':USERNAME'=>$POST_USERNAME
                            )
                        )
                    )
                );

                $LOGIN_USER=$APP->get('LOGIN_USER');
				$OLD_USER_SALT=$APP->get('LOGIN_USER.0.constructr_user_salt');

				if($OLD_USER_SALT == '')
				{
					$OLD_USER_SALT=$APP->get('CONSTRUCTR_USER_SALT');
				}

				$APP->set('SESSION.OLD_USER_SALT',$OLD_USER_SALT);

                if (count($LOGIN_USER) == 1){
                	$APP->set('SESSION.login_step_1','true');
                    $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/login-step-2');
                } else {
                    $APP->set('SESSION.login','false');
                    $APP->get('CONSTRUCTR_LOG')->write('LOGIN USER CREDENTIALS DONT\'T MATCH - USERNAME: '.$POST_USERNAME);
                    $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/login-error');
                }
            }
			else
			{
                $APP->set('SESSION.login','false');
                $APP->get('CONSTRUCTR_LOG')->write('LOGIN USER CREDENTIALS DONT\'T MATCH - EMPTY USERNAME');
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/login-error');				
			}
        }

        public function login_step_2($APP)
        {
            $CSRF=self::csrf();
            $APP->set('CSRF',$CSRF);
            $APP->set('SESSION.csrf',$CSRF);

            $ADDITIVE=self::additive();
            $APP->set('ADDITIVE',$ADDITIVE);
            $APP->set('SESSION.additive',$ADDITIVE);

            $TRIPPLE_ADDITIVE=($ADDITIVE.$CSRF);
            $APP->set('TRIPPLE_ADDITIVE',$TRIPPLE_ADDITIVE);
            $APP->set('SESSION.tripple_additive',$TRIPPLE_ADDITIVE);

			if($APP->get('SESSION.login') == 'true' && $APP->get('SESSION.password')!='' && $APP->get('SESSION.username')!=''){
				$APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/pagemanagement');
			}
	
			if($APP->get('SESSION.post_username') == ''){
				$APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/login-error');
			}

            echo Template::instance()->render('CONSTRUCTR-CMS/TEMPLATES/index_step_2.html','text/html');
        }

        public function login_step_2_verify($APP)
        {
            $POST_CSRF=filter_var($APP->get('POST.csrf'),FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $POST_ADDITIVE=filter_var($APP->get('POST.csrf_additive'),FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $POST_TRIPPLE_ADDITIVE=filter_var($APP->get('POST.csrf_tripple_additive'),FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $POST_USERNAME=$APP->get('SESSION.post_username');
			$POST_PASSWORD=crypt(trim($APP->get('POST.password')),$APP->get('SESSION.OLD_USER_SALT'));
			$EMAIL=$APP->get('POST.email');

            if ($EMAIL!=''){
            	$APP->get('CONSTRUCTR_LOG')->write('SPAM LOGIN: '.$EMAIL);
                die();
            }

            if ($POST_CSRF!=''){
                if ($POST_CSRF!=$APP->get('SESSION.csrf')){
                    $APP->get('CONSTRUCTR_LOG')->write('LOGIN FORM CSRF DON\'T MATCH: '.$POST_USERNAME);
                    $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/login-error');
                }
            }

            if ($POST_ADDITIVE!=''){
                if ($POST_ADDITIVE!=$APP->get('SESSION.additive')){
                    $APP->get('CONSTRUCTR_LOG')->write('LOGIN FORM ADDITIVE DON\'T MATCH: '.$POST_USERNAME);
                    $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/login-error');
                }
            }

            if ($POST_TRIPPLE_ADDITIVE!=''){
                if ($POST_TRIPPLE_ADDITIVE!=$APP->get('SESSION.tripple_additive')){
                    $APP->get('CONSTRUCTR_LOG')->write('LOGIN FORM TRIPPLE ADDITIVE DON\'T MATCH: '.$POST_USERNAME);
                    $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/login-error');
                }
            }

            if ($POST_TRIPPLE_ADDITIVE!=$POST_ADDITIVE.$POST_CSRF){
                $APP->get('CONSTRUCTR_LOG')->write('LOGIN FORM TRIPPLE ADDITIVE COMPARISON DON\'T MATCH: '.$POST_USERNAME);
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/login-error');
            }

            if ($POST_USERNAME !='' && $POST_PASSWORD!=''){
                $APP->set('LOGIN_USER',$APP->get('DBCON')->exec(
                        array('SELECT * FROM constructr_backenduser WHERE constructr_user_active=:ACTIVE AND constructr_user_username=:USERNAME AND constructr_user_password=:PASSWORD LIMIT 1;',),
                        array(
                            array(
                                ':ACTIVE'=>1,
                                ':USERNAME'=>$POST_USERNAME,
                                ':PASSWORD'=>$POST_PASSWORD
                            )
                        )
                    )
                );

	            $APP->clear('SESSION.OLD_USER_SALT');
                $LOGIN_USER=$APP->get('LOGIN_USER');
				$COUNTR=count($LOGIN_USER);
				$NEW_SALT='$2a$10$'.strtr(base64_encode(mcrypt_create_iv(50,MCRYPT_DEV_URANDOM)),'+','.').'$';
				$NEW_PASSWORD_HASH=crypt($APP->get('POST.password'),$NEW_SALT);

              	if ($COUNTR == 1){
                    $APP->set('UPDATE_LOGIN_USER',$APP->get('DBCON')->exec(
                            array('UPDATE constructr_backenduser SET constructr_user_password=:NEW_PASSWORD_HASH,constructr_user_last_login=:LAST_LOGIN,constructr_user_salt=:NEW_SALT WHERE constructr_user_active=:ACTIVE AND constructr_user_username=:USERNAME AND constructr_user_password=:PASSWORD LIMIT 1;',),
                            array(
                                array(
                                    ':ACTIVE'=>1,
                                    ':LAST_LOGIN'=>date('Y-m-d H:i:s'),
                                    ':USERNAME'=>$POST_USERNAME,
                                    ':PASSWORD'=>$POST_PASSWORD,
                                    ':NEW_PASSWORD_HASH'=>$NEW_PASSWORD_HASH,
                                    ':NEW_SALT'=>$NEW_SALT
                                )
                            )
                        )
                    );

                    $APP->set('SESSION.login','true');
					$APP->set('SESSION.username',$POST_USERNAME);
                    $APP->set('SESSION.password',$NEW_PASSWORD_HASH);
                    $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/');
                } else {
                    $APP->set('SESSION.login','false');
                    $APP->get('CONSTRUCTR_LOG')->write('LOGIN USER CREDENTIALS DONT\'T MATCH - USERNAME: '.$POST_USERNAME);
                    $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/login-error');
                }
            }
        }

        public function login_error($APP)
        {
            $APP->get('CONSTRUCTR_LOG')->write('LOGIN_ERROR!');

            $APP->clear('SESSION.post_username');
			$APP->clear('SESSION.username');
            $APP->clear('SESSION.password');
            $APP->clear('SESSION.login');

            $_SESSION=array();

            if (ini_get("session.use_cookies")){
                $params=session_get_cookie_params();
                setcookie(session_name(),'',time() - 42000,$params["path"],$params["domain"],$params["secure"],$params["httponly"]);
            }

            session_destroy();
            session_regenerate_id(true);

            $CSRF=self::csrf();
            $APP->set('CSRF',$CSRF);
            $APP->set('SESSION.csrf',$CSRF);

            $ADDITIVE=self::additive();
            $APP->set('ADDITIVE',$ADDITIVE);
            $APP->set('SESSION.additive',$ADDITIVE);

            $TRIPPLE_ADDITIVE=($ADDITIVE.$CSRF);
            $APP->set('TRIPPLE_ADDITIVE',$TRIPPLE_ADDITIVE);
            $APP->set('SESSION.tripple_additive',$TRIPPLE_ADDITIVE);

            echo Template::instance()->render('CONSTRUCTR-CMS/TEMPLATES/index_login_error.html','text/html');
        }

	    public function flatten_array($array)
	    {
	        $flat_array=array();
	        $size=sizeof($array);
	        $keys=array_keys($array);

	        for ($x=0; $x < $size; $x++){
	            $element=$array[$keys[$x]]; if (is_array($element)){
	    			$results=self::flatten_array($element);
					$sr=sizeof($results);
	    			$sk=array_keys($results);
	
	    			for ($y=0; $y < $sr; $y++){
	        			$flat_array[$sk[$y]]=$results[$sk[$y]];
	    			}
				} else {
	    			$flat_array[$keys[$x]]=$element;
				}
	        }

	        return $flat_array;
	    }

	    public function gffd($dir){
	        $files=array();
	        if ($handle=opendir($dir)){
	            while (false !== ($file=readdir($handle))){
	                if ($file!="." && $file!=".."){
	                    if (is_dir($dir.'/'.$file)){
	                        $dir2=$dir.'/'.$file;
	                        $files[]=self::gffd($dir2);
	                    } else {
	                        $files[]=$dir.'/'.$file;
	                    }
	                }
	            }
	            closedir($handle);
	        }
	        return self::flatten_array($files);
	    }

        public function logout($APP)
        {
            $APP->clear('SESSION.post_username');
			$APP->clear('SESSION.username');
            $APP->clear('SESSION.password');
            $APP->clear('SESSION.login');

            $_SESSION=array();

            if (ini_get("session.use_cookies")){
                $params=session_get_cookie_params();
                setcookie(session_name(),'',time() - 42000,$params["path"],$params["domain"],$params["secure"],$params["httponly"]);
            }

            session_destroy();
            session_regenerate_id(true);

            $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/');
        }

        public function retrieve_password($APP)
        {
            $USERNAME=filter_var($APP->get('PARAMS.username'),FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            if ($USERNAME!=''){
                $APP->set('USER_BASE_DATA',$APP->get('DBCON')->exec(
                        array('SELECT * FROM constructr_backenduser WHERE constructr_user_username=:USERNAME AND constructr_user_active="1" LIMIT 1;',),
                        array(array(':USERNAME'=>$USERNAME))
                    )
                );

                $USER=$APP->get('USER_BASE_DATA');

                if (count($USER) == 1) {
                    $TMP_PASSWORD=self::csrf();

                    if ($TMP_PASSWORD!=''){
						$NEW_SALT='$2a$10$'.strtr(base64_encode(mcrypt_create_iv(50,MCRYPT_DEV_URANDOM)),'+','.').'$';
                        $NEW_CRYPTED_PASSWORD=crypt($TMP_PASSWORD,$NEW_SALT);

                        $APP->set('USER',$APP->get('DBCON')->exec(
                                array('UPDATE constructr_backenduser SET constructr_user_password=:NEW_CRYPTED_PASSWORD,constructr_user_salt=:NEW_SALT WHERE constructr_user_username=:USERNAME AND constructr_user_active="1" LIMIT 1;',),
                                array(
                                    array(
                                        ':USERNAME'=>$USERNAME,
                                        ':NEW_CRYPTED_PASSWORD'=>$NEW_CRYPTED_PASSWORD,
                                        ':NEW_SALT'=>$NEW_SALT
                                    )
                                )
                            )
                        );

                        mail($APP->get('USER_BASE_DATA.0.constructr_user_email'),'Constructr Password-Reset',date('d.m.Y,H:i').' Uhr //  New password for you: '.$TMP_PASSWORD.' - update as soon as possible! '.$APP->get('CONSTRUCTR_BASE_URL'));

                        $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/updated-user-credentials');
                    } else {
                        $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/login-error#1');
                    }
                } else {
                    $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/login-error#2');
                }
            }
        }

        public function updated_user_credentials($APP)
        {
            $CSRF=self::csrf();
            $APP->set('CSRF',$CSRF);

            $APP->set('SESSION.csrf',$CSRF);
            $ADDITIVE=self::additive();

            $APP->set('ADDITIVE',$ADDITIVE);
            $APP->set('SESSION.additive',$ADDITIVE);

            $TRIPPLE_ADDITIVE=($ADDITIVE.$CSRF);
            $APP->set('TRIPPLE_ADDITIVE',$TRIPPLE_ADDITIVE);
            $APP->set('SESSION.tripple_additive',$TRIPPLE_ADDITIVE);

            echo Template::instance()->render('CONSTRUCTR-CMS/TEMPLATES/updated-user-credentials.html','text/html');
        }

        public static function additive($LENGTH=10,$CHARS='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz')
        {
            for($S='',$CL=strlen($CHARS)-1,$i=0;$i<$LENGTH;$S.=$CHARS[mt_rand(0,$CL)],++$i);
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
