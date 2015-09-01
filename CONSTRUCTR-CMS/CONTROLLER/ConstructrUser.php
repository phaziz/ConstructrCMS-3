<?php

    class ConstructrUser extends ConstructrBase
    {
        public function beforeRoute($APP){
        	$APP->set('ACT_VIEW','user');

            if($APP->get('SESSION.login')=='true' && $APP->get('SESSION.username')!='' && $APP->get('SESSION.password')!=''){
                $APP->set('LOGIN_USER',$APP->get('DBCON')->exec(
                    ['SELECT * FROM constructr_backenduser WHERE constructr_user_active=:ACTIVE AND constructr_user_username=:USERNAME AND constructr_user_password=:PASSWORD LIMIT 1;'],
                    [[
                        ':ACTIVE'=>1,
                        ':USERNAME'=>$APP->get('SESSION.username'),
                        ':PASSWORD'=>$APP->get('SESSION.password')
                    ]]
                ));

                $LOGIN_USER=$APP->get('LOGIN_USER');
                $LOGIN_USER_ID=$APP->get('LOGIN_USER.0.constructr_user_id');

                $APP->set('LOGIN_USER_RIGHTS',$APP->get('DBCON')->exec(
                    ['SELECT * FROM constructr_user_rights WHERE constructr_user_rights_user=:LOGIN_USER_ID;'],
                    [[':LOGIN_USER_ID'=>$LOGIN_USER_ID]]
                ));

                $ITERATOR=new RecursiveIteratorIterator(new RecursiveArrayIterator($APP->get('LOGIN_USER_RIGHTS')));
                $i=1;
                $CLEAN_USER_RIGHTS=[];

                foreach ($ITERATOR as $VALUE){
                    if($i==5){$i=1;}
                    if($i==3){$MODUL_ID=$VALUE;}
                    if($i==4){$RIGHT=$VALUE;}
                    $i++;
                    if($i==5){$CLEAN_USER_RIGHTS[$MODUL_ID]=$RIGHT;}
                }

                $APP->set('LOGIN_USER_RIGHTS',$CLEAN_USER_RIGHTS);

                if (count($LOGIN_USER) != 1){
                    $APP->get('CONSTRUCTR_LOG')->write('USER NOT FOUND - USERNAME: '.$APP->get('SESSION.username'));
                    $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/login-error');
                }
            } else {
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/logout');
            }
        }

        public function user_management($APP){
			$APP->set('MODUL_ID',40);
            $USER_RIGHTS=parent::checkUserModulRights($APP->get('MODUL_ID'),$APP->get('LOGIN_USER_RIGHTS'));

            if ($USER_RIGHTS==false){
                $APP->get('CONSTRUCTR_LOG')->write('User '.$APP->get('SESSION.username').' missing USER-RIGHTS for modul '.$APP->get('MODUL_ID'));
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/no-rights');
            }

            $CSRF=parent::csrf();
            $APP->set('CSRF',$CSRF);
            $APP->set('SESSION.csrf',$CSRF);
            $ADDITIVE=parent::additive();
            $APP->set('ADDITIVE',$ADDITIVE);
            $APP->set('SESSION.additive',$ADDITIVE);
            $TRIPPLE_ADDITIVE=($ADDITIVE.$CSRF);
            $APP->set('TRIPPLE_ADDITIVE',$TRIPPLE_ADDITIVE);
            $APP->set('SESSION.tripple_additive',$TRIPPLE_ADDITIVE);

            if(isset($_GET['edit'])){
                $APP->set('EDIT',$_GET['edit']);
            } else {
                $APP->set('EDIT','');
            }

            if(isset($_GET['new'])){
                $APP->set('NEW',$_GET['new']);
            } else {
                $APP->set('NEW','');
            }

            if(isset($_GET['delete'])){
                $APP->set('DELETE',$_GET['delete']);
            } else {
                $APP->set('DELETE','');
            }

            $APP->set('USER',$APP->get('DBCON')->exec(['SELECT * FROM constructr_backenduser;']));

            $APP->set('USER_COUNTR',0);
            $APP->set('USER_COUNTR',count($APP->get('USER')));

            echo Template::instance()->render('CONSTRUCTR-CMS/TEMPLATES/constructr_admin_usermanagement.html','text/html');
        }

		public function user_management_activate($APP){
            $APP->set('MODUL_ID',42);
            $USER_RIGHTS=parent::checkUserModulRights($APP->get('MODUL_ID'),$APP->get('LOGIN_USER_RIGHTS'));

            if ($USER_RIGHTS==false){
                $APP->get('CONSTRUCTR_LOG')->write('User '.$APP->get('SESSION.username').' missing USER-RIGHTS for modul '.$APP->get('MODUL_ID'));
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/no-rights');
            }

            $USER_ID=filter_var($APP->get('PARAMS.user_id'),FILTER_SANITIZE_NUMBER_INT);

			if(isset($USER_ID) && $USER_ID != ''){
	            $APP->set('UPDATE_CONTENT_MAPPING',$APP->get('DBCON')->exec(
                    ['UPDATE constructr_backenduser SET constructr_user_active="1" WHERE constructr_user_id=:USER_ID;'],
                    [[':USER_ID'=>$USER_ID]]
                ));

				$APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/usermanagement?edit=success');
			} else {
				$APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/usermanagement?edit=no-success');
			}
		}

		public function user_management_deactivate($APP){
            $APP->set('MODUL_ID',42);
            $USER_RIGHTS=parent::checkUserModulRights($APP->get('MODUL_ID'),$APP->get('LOGIN_USER_RIGHTS'));

            if ($USER_RIGHTS==false){
                $APP->get('CONSTRUCTR_LOG')->write('User '.$APP->get('SESSION.username').' missing USER-RIGHTS for modul '.$APP->get('MODUL_ID'));
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/no-rights');
            }

            $USER_ID=filter_var($APP->get('PARAMS.user_id'),FILTER_SANITIZE_NUMBER_INT);

			if(isset($USER_ID) && $USER_ID != ''){
	            $APP->set('UPDATE_CONTENT_MAPPING',$APP->get('DBCON')->exec(
                    ['UPDATE constructr_backenduser SET constructr_user_active="0" WHERE constructr_user_id=:USER_ID;'],
                    [[':USER_ID'=>$USER_ID]]
                ));

				$APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/usermanagement?edit=success');
			} else {
				$APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/usermanagement?edit=no-success');
			}
		}

		public function user_management_edit_rights($APP){
            $APP->set('MODUL_ID',44);
            $USER_RIGHTS=parent::checkUserModulRights($APP->get('MODUL_ID'),$APP->get('LOGIN_USER_RIGHTS'));

            if ($USER_RIGHTS==false){
                $APP->get('CONSTRUCTR_LOG')->write('User '.$APP->get('SESSION.username').' missing USER-RIGHTS for modul '.$APP->get('MODUL_ID'));
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/no-rights');
            }

            $USER_ID=filter_var($APP->get('PARAMS.user_id'),FILTER_SANITIZE_NUMBER_INT);
			$APP->set('USER_ID',$USER_ID);

            $APP->set('USER_RIGHTS',$APP->get('DBCON')->exec(
                ['SELECT * FROM constructr_user_rights WHERE constructr_user_rights_user=:USER_ID ORDER BY constructr_user_rights_key ASC;'],
                [[':USER_ID'=>$USER_ID]]
            ));

			$APP->set('USER_RIGHTS_COUNTR',count($APP->get('USER_RIGHTS')));
			$CONSTRUCTR_USER_RIGHTS=$APP->get('ALL_CONSTRUCTR_USER_RIGHTS');
			$THIS_USER_RIGHTS=array();

			foreach($APP->get('USER_RIGHTS') AS $KEY=>$VALUE){
				$THIS_USER_RIGHTS[$CONSTRUCTR_USER_RIGHTS[$VALUE['constructr_user_rights_key']]]=[
					'bezeichnung'=>$CONSTRUCTR_USER_RIGHTS[$VALUE['constructr_user_rights_key']],
					'recht_key'=>$VALUE['constructr_user_rights_key'],
					'recht'=>$VALUE['constructr_user_rights_value'],
					'id'=>$VALUE['constructr_user_rights_id'],
					'benutzer'=>$VALUE['constructr_user_rights_user']
				];
			}

			$APP->set('THIS_USER_RIGHTS',$THIS_USER_RIGHTS);

			echo Template::instance()->render('CONSTRUCTR-CMS/TEMPLATES/constructr_admin_usermanagement_edit_rights.html','text/html');
		}

		public function user_management_update_rights($APP){
			$APP->set('MODUL_ID',44);
            $USER_RIGHTS=parent::checkUserModulRights($APP->get('MODUL_ID'),$APP->get('LOGIN_USER_RIGHTS'));

            if ($USER_RIGHTS==false){
                $APP->get('CONSTRUCTR_LOG')->write('User '.$APP->get('SESSION.username').' missing USER-RIGHTS for modul '.$APP->get('MODUL_ID'));
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/no-rights');
            }

            $RAW_ID=$APP->get('POST.id');

			if($RAW_ID != ''){
				$RAW_ID_PARTS=explode('@',$RAW_ID);
				$RAW_RIGHT_ID=filter_var(str_replace('right_id_','',$RAW_ID_PARTS[0]),FILTER_SANITIZE_NUMBER_INT);
				$RAW_USER_ID=filter_var(str_replace('user_id_','',$RAW_ID_PARTS[0]),FILTER_SANITIZE_NUMBER_INT);
				$RAW_RIGHT=filter_var(str_replace('right_','',$RAW_ID_PARTS[0]),FILTER_SANITIZE_NUMBER_INT);

				if($RAW_RIGHT_ID != '' && $RAW_USER_ID != '' && $RAW_RIGHT != ''){
		            $APP->set('SELECT_RIGHT',$APP->get('DBCON')->exec(
	                    ['SELECT * FROM constructr_user_rights WHERE constructr_user_rights_id=:RAW_RIGHT_ID LIMIT 1;'],
	                    [[':RAW_RIGHT_ID'=>$RAW_RIGHT_ID]]
	                ));

					if($APP->get('SELECT_RIGHT.0.constructr_user_rights_value')==1){
			            $APP->set('UPDATE_RIGHT',$APP->get('DBCON')->exec(
		                    ['UPDATE constructr_user_rights SET constructr_user_rights_value=0 WHERE constructr_user_rights_id=:RAW_RIGHT_ID LIMIT 1;'],
		                    [[':RAW_RIGHT_ID'=>$RAW_RIGHT_ID]]
		                ));

						echo 'true';
					} else {
			            $APP->set('UPDATE_RIGHT',$APP->get('DBCON')->exec(
		                    ['UPDATE constructr_user_rights SET constructr_user_rights_value=1 WHERE constructr_user_rights_id=:RAW_RIGHT_ID LIMIT 1;'],
		                    [[':RAW_RIGHT_ID'=>$RAW_RIGHT_ID]]
		                ));

						echo 'true';
					}
				} else {
					echo 'false';
				}
			}
		}

        public function user_management_new($APP){
            $APP->set('MODUL_ID',41);
            $USER_RIGHTS=parent::checkUserModulRights($APP->get('MODUL_ID'),$APP->get('LOGIN_USER_RIGHTS'));

            if ($USER_RIGHTS==false){
                $APP->get('CONSTRUCTR_LOG')->write('User '.$APP->get('SESSION.username').' missing USER-RIGHTS for modul '.$APP->get('MODUL_ID'));
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/no-rights');
            }

            $CSRF=parent::csrf();
            $APP->set('CSRF',$CSRF);
            $APP->set('SESSION.csrf',$CSRF);
            $ADDITIVE=parent::additive();
            $APP->set('ADDITIVE',$ADDITIVE);
            $APP->set('SESSION.additive',$ADDITIVE);
            $TRIPPLE_ADDITIVE=($ADDITIVE.$CSRF);
            $APP->set('TRIPPLE_ADDITIVE',$TRIPPLE_ADDITIVE);
            $APP->set('SESSION.tripple_additive',$TRIPPLE_ADDITIVE);

            echo Template::instance()->render('CONSTRUCTR-CMS/TEMPLATES/constructr_admin_usermanagement_new.html','text/html');
        }

        public function user_management_new_verify($APP){
            $APP->set('MODUL_ID',41);
            $USER_RIGHTS=parent::checkUserModulRights($APP->get('MODUL_ID'),$APP->get('LOGIN_USER_RIGHTS'));

            if ($USER_RIGHTS==false){
                $APP->get('CONSTRUCTR_LOG')->write('User '.$APP->get('SESSION.username').' missing USER-RIGHTS for modul '.$APP->get('MODUL_ID'));
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/no-rights');
            }

            $POST_CSRF=$APP->get('POST.csrf');
            $POST_ADDITIVE=$APP->get('POST.csrf_additive');
            $POST_TRIPPLE_ADDITIVE=$APP->get('POST.csrf_tripple_additive');

            if ($POST_CSRF != ''){
                if ($POST_CSRF != $APP->get('SESSION.csrf')){
                    $APP->get('CONSTRUCTR_LOG')->write('LOGIN FORM CSRF DON\'T MATCH: '.$POST_USERNAME);
                    $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/logout');
                }
            }

            if ($POST_ADDITIVE != ''){
                if ($POST_ADDITIVE != $APP->get('SESSION.additive')){
                    $APP->get('CONSTRUCTR_LOG')->write('FORM ADDITIVE DON\'T MATCH: '.$POST_USERNAME);
                    $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/logout');
                }
            }

            if ($POST_TRIPPLE_ADDITIVE != ''){
                if ($POST_TRIPPLE_ADDITIVE != $APP->get('SESSION.tripple_additive')){
                    $APP->get('CONSTRUCTR_LOG')->write('FORM TRIPPLE ADDITIVE DON\'T MATCH: '.$POST_USERNAME);
                    $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/logout');
                }
            }

            if ($POST_TRIPPLE_ADDITIVE != $POST_ADDITIVE.$POST_CSRF){
                $APP->get('CONSTRUCTR_LOG')->write('FORM TRIPPLE ADDITIVE COMPARISON DON\'T MATCH: '.$POST_USERNAME);
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/logout');
            }

			$NEW_SALT = '$2a$10$' . strtr(base64_encode(mcrypt_create_iv(50,MCRYPT_DEV_URANDOM)),'+','.') . '$';
            $USER_NAME=filter_var($APP->get('POST.user_name'),FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $USER_EMAIL=filter_var($APP->get('POST.user_email'),FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $USER_PASSWORD=crypt($APP->get('POST.user_password'),$NEW_SALT);

            $APP->set('USER_EXISTS',$APP->get('DBCON')->exec(
                ['SELECT * FROM constructr_backenduser WHERE constructr_user_username=:USER_NAME LIMIT 1;'],
                [[':USER_NAME'=>$USER_NAME]]
            ));

            $USER_EXISTS_COUNTR=count($APP->get('USER_EXISTS'));

            if ($USER_EXISTS_COUNTR != 0){
                $APP->set('NEW','no-success');
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/usermanagement?new=no-success');
            }

            $APP->set('CREATE_USER',$APP->get('DBCON')->exec(
                ['INSERT INTO constructr_backenduser SET constructr_user_username=:USER_NAME,constructr_user_email=:USER_EMAIL,constructr_user_password=:USER_PASSWORD,constructr_user_salt=:USER_SALT,constructr_user_active=:USER_ACTIVE;'],
                [[
                    ':USER_NAME'=>$USER_NAME,
                    ':USER_EMAIL'=>$USER_EMAIL,
                    ':USER_PASSWORD'=>$USER_PASSWORD,
                    ':USER_SALT'=>$NEW_SALT,
                    ':USER_ACTIVE'=>1
                ]]
            ));

            $APP->set('SELECT_NEW_USER',$APP->get('DBCON')->exec(
                ['SELECT constructr_user_id FROM constructr_backenduser WHERE constructr_user_username=:USER_NAME AND constructr_user_email=:USER_EMAIL AND constructr_user_password=:USER_PASSWORD AND constructr_user_active=:USER_ACTIVE LIMIT 1;'],
                [[
                        ':USER_NAME'=>$USER_NAME,
                        ':USER_EMAIL'=>$USER_EMAIL,
                        ':USER_PASSWORD'=>$USER_PASSWORD,
                        ':USER_ACTIVE'=>1
                ]]
            ));

			$NEW_USER_ID=$APP->get('SELECT_NEW_USER.0.constructr_user_id');
			$ALL_CONSTRUCTR_USER_RIGHTS=$APP->get('ALL_CONSTRUCTR_USER_RIGHTS');

			foreach($ALL_CONSTRUCTR_USER_RIGHTS AS $KEY=>$VALUE){
	            $APP->set('INSERT_NEW_USER_RIGHT',$APP->get('DBCON')->exec(
                    ['INSERT INTO constructr_user_rights SET constructr_user_rights_user=:NEW_USER_ID,constructr_user_rights_key=:KEY,constructr_user_rights_value=1;'],
                    [[
                        ':NEW_USER_ID'=>$NEW_USER_ID,
                        ':KEY'=>$KEY
                    ]]
                ));
			}						

            $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/usermanagement?new=success');
        }

        public function user_management_delete($APP){
            $APP->set('MODUL_ID',43);
            $USER_RIGHTS=parent::checkUserModulRights($APP->get('MODUL_ID'),$APP->get('LOGIN_USER_RIGHTS'));

            if ($USER_RIGHTS==false){
                $APP->get('CONSTRUCTR_LOG')->write('User '.$APP->get('SESSION.username').' missing USER-RIGHTS for modul '.$APP->get('MODUL_ID'));
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/no-rights');
            }

            $DELETE_USER_ID=filter_var($APP->get('PARAMS.user_id'),FILTER_SANITIZE_NUMBER_INT);

            $APP->set('DELETE_USER',$APP->get('DBCON')->exec(
                    array('DELETE FROM constructr_backenduser WHERE constructr_user_id=:DELETE_USER_ID LIMIT 1;'),
                    array(array(':DELETE_USER_ID'=>$DELETE_USER_ID))
                )
            );

            $APP->set('DELETE_USER_RIGHT',$APP->get('DBCON')->exec(
                ['DELETE FROM constructr_user_rights WHERE constructr_user_rights_user=:DELETE_USER_ID;'],
                [[':DELETE_USER_ID'=>$DELETE_USER_ID]]
            ));

            $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/usermanagement?delete=success');
        }

        public function user_management_edit($APP){
            $APP->set('MODUL_ID',42);
            $USER_RIGHTS=parent::checkUserModulRights($APP->get('MODUL_ID'),$APP->get('LOGIN_USER_RIGHTS'));

            if ($USER_RIGHTS==false){
                $APP->get('CONSTRUCTR_LOG')->write('User '.$APP->get('SESSION.username').' missing USER-RIGHTS for modul '.$APP->get('MODUL_ID'));
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/no-rights');
            }

            $USER_ID=filter_var($APP->get('PARAMS.user_id'),FILTER_SANITIZE_NUMBER_INT);
            $CSRF=parent::csrf();
            $APP->set('CSRF',$CSRF);
            $APP->set('SESSION.csrf',$CSRF);
            $ADDITIVE=parent::additive();
            $APP->set('ADDITIVE',$ADDITIVE);
            $APP->set('SESSION.additive',$ADDITIVE);
            $TRIPPLE_ADDITIVE=($ADDITIVE.$CSRF);
            $APP->set('TRIPPLE_ADDITIVE',$TRIPPLE_ADDITIVE);
            $APP->set('SESSION.tripple_additive',$TRIPPLE_ADDITIVE);

            $APP->set('USER',$APP->get('DBCON')->exec(
                ['SELECT * FROM constructr_backenduser WHERE constructr_user_id=:USER_ID LIMIT 1;'],
                [[':USER_ID'=>$USER_ID]]
            ));

            $APP->set('USER_COUNTER',count($APP->get('USER')));

            echo Template::instance()->render('CONSTRUCTR-CMS/TEMPLATES/constructr_admin_usermanagement_edit.html','text/html');
        }

        public function user_management_edit_verify($APP){
            $APP->set('MODUL_ID',42);
            $USER_RIGHTS=parent::checkUserModulRights($APP->get('MODUL_ID'),$APP->get('LOGIN_USER_RIGHTS'));

            if ($USER_RIGHTS==false){
                $APP->get('CONSTRUCTR_LOG')->write('User '.$APP->get('SESSION.username').' missing USER-RIGHTS for modul '.$APP->get('MODUL_ID'));
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/no-rights');
            }

            $POST_CSRF=$APP->get('POST.csrf');
            $POST_ADDITIVE=$APP->get('POST.csrf_additive');
            $POST_TRIPPLE_ADDITIVE=$APP->get('POST.csrf_tripple_additive');

            if ($POST_CSRF != ''){
                if ($POST_CSRF != $APP->get('SESSION.csrf')){
                    $APP->get('CONSTRUCTR_LOG')->write('LOGIN FORM CSRF DON\'T MATCH: '.$POST_USERNAME);
                    $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/logout');
                }
            }

            if ($POST_ADDITIVE != ''){
                if ($POST_ADDITIVE != $APP->get('SESSION.additive')){
                    $APP->get('CONSTRUCTR_LOG')->write('FORM ADDITIVE DON\'T MATCH: '.$POST_USERNAME);
                    $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/logout');
                }
            }

            if ($POST_TRIPPLE_ADDITIVE != ''){
                if ($POST_TRIPPLE_ADDITIVE != $APP->get('SESSION.tripple_additive')){
                    $APP->get('CONSTRUCTR_LOG')->write('FORM TRIPPLE ADDITIVE DON\'T MATCH: '.$POST_USERNAME);
                    $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/logout');
                }
            }

            if ($POST_TRIPPLE_ADDITIVE != $POST_ADDITIVE.$POST_CSRF){
                $APP->get('CONSTRUCTR_LOG')->write('FORM TRIPPLE ADDITIVE COMPARISON DON\'T MATCH: '.$POST_USERNAME);
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/logout');
            }

            $USER_ID=filter_var($APP->get('POST.user_id'),FILTER_SANITIZE_NUMBER_INT);
            $USER_NAME=filter_var($APP->get('POST.user_name'),FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $USER_EMAIL=filter_var($APP->get('POST.user_email'),FILTER_SANITIZE_FULL_SPECIAL_CHARS);
			$NEW_SALT = '$2a$10$' . strtr(base64_encode(mcrypt_create_iv(50,MCRYPT_DEV_URANDOM)),'+','.') . '$';
            $USER_PASSWORD=crypt($APP->get('POST.user_password'),$NEW_SALT);

            $APP->set('USER_EXISTS',$APP->get('DBCON')->exec(
                ['SELECT * FROM constructr_backenduser WHERE constructr_user_username=:USER_NAME LIMIT 1;'],
                [[':USER_NAME'=>$USER_NAME]]
            ));

            $USER_EXISTS_COUNTR=count($APP->get('USER_EXISTS'));

            if ($USER_EXISTS_COUNTR>1){
                $APP->set('NEW','no-success');
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/usermanagement?new=no-success');
            }

            $APP->set('UPDATE_USER',$APP->get('DBCON')->exec(
                ['UPDATE constructr_backenduser SET constructr_user_username=:USER_NAME,constructr_user_email=:USER_EMAIL,constructr_user_password=:USER_PASSWORD,constructr_user_salt=:USER_SALT WHERE constructr_user_id=:USER_ID LIMIT 1;'],
                [[
                    ':USER_ID'=>$USER_ID,
                    ':USER_NAME'=>$USER_NAME,
                    ':USER_EMAIL'=>$USER_EMAIL,
                    ':USER_PASSWORD'=>$USER_PASSWORD,
                    ':USER_SALT'=>$NEW_SALT
                ]]
            ));

            $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/usermanagement?new=success');
        }
    }
