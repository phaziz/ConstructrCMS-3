<?php

    class ConstructrUploads extends ConstructrBase
    {
        public function beforeRoute($APP)
        {
        	$APP->set('ACT_VIEW','uploads');

            if($APP->get('SESSION.login')=='true' && $APP->get('SESSION.username')!='' && $APP->get('SESSION.password')!=''){
                $APP->set('LOGIN_USER',$APP->get('DBCON')->exec(
                        array('SELECT * FROM constructr_backenduser WHERE constructr_user_active=:ACTIVE AND constructr_user_username=:USERNAME AND constructr_user_password=:PASSWORD LIMIT 1;'),
                        array(
                            array(
                                ':ACTIVE'=>1,
                                ':USERNAME'=>$APP->get('SESSION.username'),
                                ':PASSWORD'=>$APP->get('SESSION.password')
                            )
                        )
                    )
                );

                $LOGIN_USER=$APP->get('LOGIN_USER');
                $LOGIN_USER_ID=$APP->get('LOGIN_USER.0.constructr_user_id');

                $APP->set('LOGIN_USER_RIGHTS',$APP->get('DBCON')->exec(
                        array('SELECT * FROM constructr_user_rights WHERE constructr_user_rights_user=:LOGIN_USER_ID;'),
                        array(array(':LOGIN_USER_ID'=>$LOGIN_USER_ID))
                    )
                );

                $ITERATOR=new RecursiveIteratorIterator(new RecursiveArrayIterator($APP->get('LOGIN_USER_RIGHTS')));

                $i=1;
                $CLEAN_USER_RIGHTS=array();

                foreach ($ITERATOR as $VALUE){
                    if($i==5){
                        $i=1;
                    }
                    if($i==3){
                        $MODUL_ID=$VALUE;
                    }
                    if($i==4){
                        $RIGHT=$VALUE;
                    }
                    $i++;
                    if($i==5){
                        $CLEAN_USER_RIGHTS[$MODUL_ID]=$RIGHT;
                    }
                }

                $APP->set('LOGIN_USER_RIGHTS',$CLEAN_USER_RIGHTS);

                if (count($LOGIN_USER)!=1){
                    $APP->get('CONSTRUCTR_LOG')->write('USER NOT FOUND-USERNAME: '.$APP->get('SESSION.username'));
                    $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/login-error');
                }
            } else {
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/logout');
            }
        }

        public function uploads_init($APP)
        {
            $APP->set('MODUL_ID',60);
            $USER_RIGHTS=parent::checkUserModulRights($APP->get('MODUL_ID'),$APP->get('LOGIN_USER_RIGHTS'));

            if ($USER_RIGHTS==false){
                $APP->get('CONSTRUCTR_LOG')->write('User '.$APP->get('SESSION.username').' missing USER-RIGHTS for modul '.$APP->get('MODUL_ID'));
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/no-rights');
            }

			$APP->set('ORIGIN_NEEDLE','');

            if (isset($_GET['new'])){$APP->set('NEW',$_GET['new']);} else {$APP->set('NEW','');}
            if (isset($_GET['delete'])){$APP->set('DELETE',$_GET['delete']);} else {$APP->set('DELETE','');}

			$APP->set('PAGINATION_FILES',array());
			$PAGINATION_FILES=array();

			if($APP->get('GET.needle')){
				$APP->set('FILES_COUNTR',0);
				$APP->set('ORIGIN_NEEDLE',$APP->get('GET.needle'));
				$NEEDLES=explode(' ',strtolower($APP->get('GET.needle')));
	            $H=opendir($APP->get('UPLOADS'));
				$i=0;

	            while($FILE=readdir($H)){
	                if($FILE!='.' && $FILE!='..'){
						foreach($NEEDLES AS $NEEDLE){
							if(strpos(strtolower($FILE),$NEEDLE)!==false){
								$FT=strtolower(strrchr( $FILE,'.' ));
								if($FT=='.jpg' || $FT=='.jpeg' || $FT=='.gif' || $FT=='.png' || $FT=='.svg'){
									$PAGINATION_FILES[$i]=$FILE.'#true';
								} else {
									$PAGINATION_FILES[$i]=$FILE.'#false';
								}
			                    $i++;
							}
						}
	                }
	            }

				$APP->set('FILES_COUNTR',count($PAGINATION_FILES));
				$APP->set('SHOW_PAGINATION','false');
				$APP->set('PAGINATION_FILES',$PAGINATION_FILES);

				echo Template::instance()->render('CONSTRUCTR-CMS/TEMPLATES/constructr_admin_uploads.html','text/html');
			} else {
	            $H=opendir($APP->get('UPLOADS'));
				$i=0;
	
	            while($FILE=readdir($H)){
	                if($FILE!='.' && $FILE!='..'){
						$FT=strtolower(strrchr( $FILE,'.' ));
						if($FT=='.jpg' || $FT=='.jpeg' || $FT=='.gif' || $FT=='.png' || $FT=='.svg'){
							$PAGINATION_FILES[$i] = $FILE.'#true';
						} else {
							$PAGINATION_FILES[$i] = $FILE.'#false';
						}
	                    $i++;
	                }
	            }

	            closedir($H);
	            uksort($PAGINATION_FILES,"strnatcmp");

				$APP->set('SHOW_PAGINATION','false');
				$APP->set('PAGINATION_FILES',$PAGINATION_FILES);
				$ALL_FILES=$PAGINATION_FILES;
				$APP->set('FILES_COUNTR',0);
				$APP->set('FILES_COUNTR',(count($PAGINATION_FILES)));
				$OFFSET = $APP->get('PARAMS.offset');

				if(!isset($OFFSET) || $OFFSET==0){
					$OFFSET = $APP->get('UPLOADS_LIST_PAGINATION');
				}

				$APP->set('OFFSET',$OFFSET);
				$APP->set('PAGINATION',0);
				$APP->set('PAGINATION_STRING','');
				$PAGINATION=ceil($APP->get('FILES_COUNTR')/$APP->get('UPLOADS_LIST_PAGINATION'));
				$APP->set('PAGINATION',$PAGINATION);

				if($PAGINATION > 1){
					$APP->set('SHOW_PAGINATION','true');
					$PAGINATION_STRING = '<ul class="pagination">';

					for($i=1;$i <= $PAGINATION;$i++){
						if($APP->get('OFFSET')==($i*$APP->get('UPLOADS_LIST_PAGINATION'))){
							$PAGINATION_STRING.='<li class="active"><a href="'.$APP->get('CONSTRUCTR_BASE_URL').'/constructr/uploads/'.($i*$APP->get('UPLOADS_LIST_PAGINATION')).'">'.$i.'</a></li>';
						} else {
							$PAGINATION_STRING.='<li class="waves-effect"><a href="'.$APP->get('CONSTRUCTR_BASE_URL').'/constructr/uploads/'.($i*$APP->get('UPLOADS_LIST_PAGINATION')).'">'.$i.'</a></li>';	
						}
					}

					$PAGINATION_STRING.='</ul>';
					$APP->set('PAGINATION_STRING',$PAGINATION_STRING);
					$START=$APP->get('OFFSET')-$APP->get('UPLOADS_LIST_PAGINATION');
					$END=$APP->get('OFFSET');
					$TEMP_PAGINATION_FILES = $PAGINATION_FILES;
					$PAGINATION_FILES = array();

					foreach($TEMP_PAGINATION_FILES AS $KEY=>$VALUE){
						if($KEY>=$START && $KEY<$END){
							$FT=strtolower(strrchr( $VALUE,'.'));
							if($FT=='.jpg' || $FT=='.jpeg' || $FT=='.gif' || $FT=='.png' || $FT=='.svg'){
								$PAGINATION_FILES[$KEY]=$VALUE;
							} else {
								$PAGINATION_FILES[$KEY]=$VALUE;
							}
						}
					}

					$APP->set('PAGINATION_FILES',$PAGINATION_FILES);
				}

				echo Template::instance()->render('CONSTRUCTR-CMS/TEMPLATES/constructr_admin_uploads.html','text/html');
			}
        }

        public function uploads_delete_file($APP)
        {
            $APP->set('MODUL_ID',62);
            $USER_RIGHTS=parent::checkUserModulRights($APP->get('MODUL_ID'),$APP->get('LOGIN_USER_RIGHTS'));

            if ($USER_RIGHTS==false){
                $APP->get('CONSTRUCTR_LOG')->write('User '.$APP->get('SESSION.username').' missing USER-RIGHTS for modul '.$APP->get('MODUL_ID'));
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/no-rights');
            }

            $DELETE_FILE=$APP->get('PARAMS.file');
            @chmod($APP->get('UPLOADS').$DELETE_FILE,0777);

            if (@unlink($APP->get('UPLOADS').$DELETE_FILE)){
                $APP->set('DELETE','success');
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/uploads/?delete=success');
            } else {
                $APP->set('DELETE','success');
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/uploads/?delete=success');
            }
        }

        public function uploads_new($APP)
        {
            $APP->set('MODUL_ID',61);
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

            echo Template::instance()->render('CONSTRUCTR-CMS/TEMPLATES/constructr_admin_uploads_new.html','text/html');
        }

        public function uploads_new_verify($APP)
        {
            $APP->set('MODUL_ID',61);
            $USER_RIGHTS=parent::checkUserModulRights($APP->get('MODUL_ID'),$APP->get('LOGIN_USER_RIGHTS'));

            if ($USER_RIGHTS==false){
                $APP->get('CONSTRUCTR_LOG')->write('User '.$APP->get('SESSION.username').' missing USER-RIGHTS for modul '.$APP->get('MODUL_ID'));
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/no-rights');
            }

            $POST_CSRF=$APP->get('POST.csrf');
            $POST_ADDITIVE=$APP->get('POST.csrf_additive');
            $POST_TRIPPLE_ADDITIVE=$APP->get('POST.csrf_tripple_additive');

            if ($POST_CSRF!=''){
                if ($POST_CSRF!=$APP->get('SESSION.csrf')){
                    $APP->get('CONSTRUCTR_LOG')->write('FORM CSRF DON\'T MATCH: '.$POST_USERNAME);
                    $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/logout');
                }
            }

            if ($POST_ADDITIVE!=''){
                if ($POST_ADDITIVE!=$APP->get('SESSION.additive')){
                    $APP->get('CONSTRUCTR_LOG')->write('FORM ADDITIVE DON\'T MATCH: '.$POST_USERNAME);
                    $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/logout');
                }
            }

            if ($POST_TRIPPLE_ADDITIVE!=''){
                if ($POST_TRIPPLE_ADDITIVE!=$APP->get('SESSION.tripple_additive')){
                    $APP->get('CONSTRUCTR_LOG')->write('FORM TRIPPLE ADDITIVE DON\'T MATCH: '.$POST_USERNAME);
                    $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/logout');
                }
            }

            if ($POST_TRIPPLE_ADDITIVE!=$POST_ADDITIVE.$POST_CSRF){
                $APP->get('CONSTRUCTR_LOG')->write('FORM TRIPPLE ADDITIVE COMPARISON DON\'T MATCH: '.$POST_USERNAME);
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/logout');
            }

			$COUNTR=count($_FILES['new_file']['name']);

			if($COUNTR!=0){
				for($i=0;$i<$COUNTR;$i++){
					$NEW_UPLOAD='';
					$NEW_UPLOAD=$APP->get('UPLOADS').$_FILES['new_file']['name'][$i];

	                if (copy($_FILES['new_file']['tmp_name'][$i],$NEW_UPLOAD)){
	                    @chmod($NEW_UPLOAD,0777);
	                } else {
	                    $APP->set('NEW','no-success');
	                    $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/uploads/?new=no-success');
					}
				}
			}

            $APP->set('NEW','success');
            $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/uploads/?new=success');

        }
    }
