<?php

    class ConstructrContent extends ConstructrBase
    {
        public function beforeRoute($APP){
        	$APP->set('ACT_VIEW','pages');

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
                        array('SELECT * FROM constructr_user_rights WHERE constructr_user_rights_user=:LOGIN_USER_ID;'),
                        array(array(':LOGIN_USER_ID'=>$LOGIN_USER_ID))
                    )
                );

                $ITERATOR=new RecursiveIteratorIterator(new RecursiveArrayIterator($APP->get('LOGIN_USER_RIGHTS')));

                $i=1;
                $CLEAN_USER_RIGHTS=[];

                foreach ($ITERATOR as $VALUE){
                    if($i==5){$i=1;}
                    if($i==3){$MODUL_ID=$VALUE;}
                    if($i==4){$RIGHT=$VALUE;}
                    $i++;
                    if ($i==5){$CLEAN_USER_RIGHTS[$MODUL_ID]=$RIGHT;}
                }

                $APP->set('LOGIN_USER_RIGHTS',$CLEAN_USER_RIGHTS);

                if (count($LOGIN_USER)!=1){
                    $APP->get('CONSTRUCTR_LOG')->write('USER NOT FOUND - USERNAME: '.$APP->get('SESSION.username'));
                    $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/login-error');
                }
            } else {
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/logout');
            }
        }

        public function content_init($APP){
            $APP->set('MODUL_ID',50);
            $USER_RIGHTS=parent::checkUserModulRights($APP->get('MODUL_ID'),$APP->get('LOGIN_USER_RIGHTS'));

            if ($USER_RIGHTS==false){
                $APP->get('CONSTRUCTR_LOG')->write('User '.$APP->get('SESSION.username').' missing USER-RIGHTS for modul '.$APP->get('MODUL_ID'));
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/no-rights');
            }

            $PAGE_ID=filter_var($APP->get('PARAMS.page_id'),FILTER_SANITIZE_NUMBER_INT);
            $APP->set('PAGE_ID',$PAGE_ID);

            $APP->set('PAGE',$APP->get('DBCON')->exec(
                ['SELECT * FROM constructr_pages WHERE constructr_pages_id=:PAGE_ID LIMIT 1;'],
                [[':PAGE_ID'=>$PAGE_ID]]
            ));

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

            if(isset($_GET['move'])){
                $APP->set('MOVE',$_GET['move']);
            } else {
                $APP->set('MOVE','');
            }

            $APP->set('CONTENT',$APP->get('DBCON')->exec(
                ['SELECT * FROM constructr_content WHERE constructr_content_page_id=:PAGE_ID ORDER BY constructr_content_order ASC;'],
                [[':PAGE_ID'=>$PAGE_ID]]
            ));

            $APP->set('CONTENT_COUNTR',0);
            $APP->set('CONTENT_COUNTR',count($APP->get('CONTENT')));

            echo Template::instance()->render('CONSTRUCTR-CMS/TEMPLATES/constructr_admin_content.html','text/html');
        }

        public function content_new_before($APP){
            $APP->set('MODUL_ID',51);
            $USER_RIGHTS=parent::checkUserModulRights($APP->get('MODUL_ID'),$APP->get('LOGIN_USER_RIGHTS'));

            if ($USER_RIGHTS==false){
                $APP->get('CONSTRUCTR_LOG')->write('User '.$APP->get('SESSION.username').' missing USER-RIGHTS for modul '.$APP->get('MODUL_ID'));
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/no-rights');
            }

            $PAGE_ID=filter_var($APP->get('PARAMS.page_id'),FILTER_SANITIZE_NUMBER_INT);
			$BEFORE_CONTENT_ID=filter_var($APP->get('PARAMS.content_id'),FILTER_SANITIZE_NUMBER_INT);

            $APP->set('PAGE_ID',$PAGE_ID);
            $APP->set('PAGE',$APP->get('DBCON')->exec(
                ['SELECT * FROM constructr_pages WHERE constructr_pages_id=:PAGE_ID LIMIT 1;'],
                [[':PAGE_ID'=>$PAGE_ID]]
            ));

            $CSRF=parent::csrf();
            $APP->set('CSRF',$CSRF);
            $APP->set('SESSION.csrf',$CSRF);

            $ADDITIVE=parent::additive();
            $APP->set('ADDITIVE',$ADDITIVE);
            $APP->set('SESSION.additive',$ADDITIVE);

            $TRIPPLE_ADDITIVE=($ADDITIVE.$CSRF);
            $APP->set('TRIPPLE_ADDITIVE',$TRIPPLE_ADDITIVE);
            $APP->set('SESSION.tripple_additive',$TRIPPLE_ADDITIVE);

			if($BEFORE_CONTENT_ID==1){
				$APP->set('NEW_POSITION',1);	
			}else{
				$APP->set('NEW_POSITION',($BEFORE_CONTENT_ID-1));
			}

            $APP->set('TEMPLATE',$APP->get('DBCON')->exec(
                ['SELECT constructr_pages_template FROM constructr_pages WHERE constructr_pages_id=:PAGE_ID;'],
                [[':PAGE_ID'=>$PAGE_ID]]
            ));

			$APP->set('TEMPLATE_FILE',$APP->get('TEMPLATE.0.constructr_pages_template'));
			$TEMPLATE_TEXT=file_get_contents($APP->get('TEMPLATES').$APP->get('TEMPLATE_FILE'));
			preg_match_all("/({{@ CONSTRUCTR_MAPPING\()+([\w-])+(\) @}})/",$TEMPLATE_TEXT,$MATCH);
			$CONSTRUCTR_TPL_MAPPINGS=array();

			if($MATCH[0]){
				$i=0;
				foreach($MATCH[0] AS $KEY=>$MATCHR){
					$CONSTRUCTR_TPL_MAPPINGS[$i]=$MATCHR;
					$i++;
				}
			}

			$APP->set('CONSTRUCTR_TPL_MAPPINGS',$CONSTRUCTR_TPL_MAPPINGS);
            $H=opendir($APP->get('UPLOADS'));

			$IMAGES=[];
            $FILES=[];
			$i=0;

            while($FILE=readdir($H)){
                if($FILE!='.' && $FILE!='..' && $FILE!='.empty_file' && $FILE!='index.php'){
					$FT=strtolower(strrchr($FILE,'.'));

					if($FT=='.jpg' || $FT=='.jpeg' || $FT=='.gif' || $FT=='.png' || $FT=='.svg'){
	                    $IMAGES[$i]=$FILE;
	                    $i++;
					} else {
	                    $FILES[$i]=$FILE;
	                    $i++;
					}
                }
            }

            closedir($H);

            uksort($IMAGES,"strnatcmp");
			uksort($FILES,"strnatcmp");

            $APP->set('IMAGES',$IMAGES);
            $APP->set('FILES',$FILES);

            echo Template::instance()->render('CONSTRUCTR-CMS/TEMPLATES/constructr_admin_content_new_before.html','text/html');
        }

        public function content_new_before_verify($APP){
            $APP->set('MODUL_ID',51);
            $USER_RIGHTS=parent::checkUserModulRights($APP->get('MODUL_ID'),$APP->get('LOGIN_USER_RIGHTS'));

            if ($USER_RIGHTS==false){
                $APP->get('CONSTRUCTR_LOG')->write('User '.$APP->get('SESSION.username').' missing USER-RIGHTS for modul '.$APP->get('MODUL_ID'));
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/no-rights');
            }

            $PAGE_ID=filter_var($APP->get('PARAMS.page_id'),FILTER_SANITIZE_NUMBER_INT);
            $APP->set('PAGE_ID',$PAGE_ID);

            $NEW_POSITION=filter_var($APP->get('POST.new_position'),FILTER_SANITIZE_NUMBER_INT);
            $NEW_CONTENT_RAW=$APP->get('POST.new_content_raw');
			$NEW_CONTENT_HTML=\Markdown::instance()->convert($NEW_CONTENT_RAW);
			$NEW_CONTENT_MAPPING=$APP->get('POST.new_content_mapping');

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

            if ($APP->get('PAGE_ID')!='' && $NEW_POSITION!=''){
                $APP->get('DBCON')->exec(
                    ['UPDATE constructr_content SET constructr_content_order=(constructr_content_order+1) WHERE constructr_content_page_id=:PAGE_ID AND constructr_content_order>=:NEW_POSITION;'],
                    [[
                        ':PAGE_ID'=>$PAGE_ID,
                        ':NEW_POSITION'=>$NEW_POSITION
                    ]]
				);

				if($NEW_CONTENT_MAPPING!='')
				{
	                $APP->set('NEW_CONTENT',$APP->get('DBCON')->exec(
                        ['INSERT INTO constructr_content SET constructr_content_page_id=:PAGE_ID,constructr_content_content_raw=:NEW_CONTENT_RAW,constructr_content_content_html=:NEW_CONTENT_HTML,constructr_content_tpl_id_mapping=:NEW_CONTENT_MAPPING,constructr_content_order=:NEW_POSITION;'],
                        [[
                            ':PAGE_ID'=>$PAGE_ID,
                            ':NEW_CONTENT_RAW'=>$NEW_CONTENT_RAW,
                            ':NEW_CONTENT_HTML'=>$NEW_CONTENT_HTML,
                            ':NEW_POSITION'=>$NEW_POSITION,
                            ':NEW_CONTENT_MAPPING'=>$NEW_CONTENT_MAPPING
                        ]]
                    ));
				}
				else
				{
	                $APP->set('NEW_CONTENT',$APP->get('DBCON')->exec(
                        ['INSERT INTO constructr_content SET constructr_content_page_id=:PAGE_ID,constructr_content_content_raw=:NEW_CONTENT_RAW,constructr_content_content_html=:NEW_CONTENT_HTML,constructr_content_order=:NEW_POSITION;'],
                        [[
                            ':PAGE_ID'=>$PAGE_ID,
                            ':NEW_CONTENT_RAW'=>$NEW_CONTENT_RAW,
                            ':NEW_CONTENT_HTML'=>$NEW_CONTENT_HTML,
                            ':NEW_POSITION'=>$NEW_POSITION
                        ]]
                    ));
				}

				parent::clean_up_cache($APP);

                $APP->set('NEW','success');
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/content/'.$PAGE_ID.'/?new=success');
            } else {
                $APP->set('NEW','no-success');
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/content/'.$PAGE_ID.'/?new=no-success');
            }

            $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/content/'.$PAGE_ID);
        }

        public function content_new_after($APP){
            $APP->set('MODUL_ID',51);
            $USER_RIGHTS=parent::checkUserModulRights($APP->get('MODUL_ID'),$APP->get('LOGIN_USER_RIGHTS'));

            if ($USER_RIGHTS==false){
                $APP->get('CONSTRUCTR_LOG')->write('User '.$APP->get('SESSION.username').' missing USER-RIGHTS for modul '.$APP->get('MODUL_ID'));
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/no-rights');
            }

            $PAGE_ID=filter_var($APP->get('PARAMS.page_id'),FILTER_SANITIZE_NUMBER_INT);
			$AFTER_CONTENT_ID=filter_var($APP->get('PARAMS.content_id'),FILTER_SANITIZE_NUMBER_INT);

            $APP->set('PAGE_ID',$PAGE_ID);
            $APP->set('PAGE',$APP->get('DBCON')->exec(
                ['SELECT * FROM constructr_pages WHERE constructr_pages_id=:PAGE_ID LIMIT 1;'],
                [[':PAGE_ID'=>$PAGE_ID]]
            ));

            $CSRF=parent::csrf();
            $APP->set('CSRF',$CSRF);
            $APP->set('SESSION.csrf',$CSRF);

            $ADDITIVE=parent::additive();
            $APP->set('ADDITIVE',$ADDITIVE);
            $APP->set('SESSION.additive',$ADDITIVE);

            $TRIPPLE_ADDITIVE=($ADDITIVE.$CSRF);
            $APP->set('TRIPPLE_ADDITIVE',$TRIPPLE_ADDITIVE);
            $APP->set('SESSION.tripple_additive',$TRIPPLE_ADDITIVE);

            $APP->set('NEW_POSITION',($AFTER_CONTENT_ID+1));

            $APP->set('TEMPLATE',$APP->get('DBCON')->exec(
                    ['SELECT constructr_pages_template FROM constructr_pages WHERE constructr_pages_id=:PAGE_ID;'],
                    [[':PAGE_ID'=>$PAGE_ID]]
            ));

			$APP->set('TEMPLATE_FILE',$APP->get('TEMPLATE.0.constructr_pages_template'));
			$TEMPLATE_TEXT=file_get_contents($APP->get('TEMPLATES').$APP->get('TEMPLATE_FILE'));
			preg_match_all("/({{@ CONSTRUCTR_MAPPING\()+([\w-])+(\) @}})/",$TEMPLATE_TEXT,$MATCH);
			$CONSTRUCTR_TPL_MAPPINGS=array();

			if($MATCH[0]){
				$i=0;
				foreach($MATCH[0] AS $KEY=>$MATCHR){
					$CONSTRUCTR_TPL_MAPPINGS[$i]=$MATCHR;
					$i++;
				}
			}

			$APP->set('CONSTRUCTR_TPL_MAPPINGS',$CONSTRUCTR_TPL_MAPPINGS);
            $H=opendir($APP->get('UPLOADS'));

			$IMAGES=[];
            $FILES=[];
			$i=0;

            while($FILE=readdir($H)){
                if($FILE!='.' && $FILE!='..' && $FILE!='.empty_file' && $FILE!='index.php'){
					$FT=strtolower(strrchr($FILE,'.'));

					if($FT=='.jpg' || $FT=='.jpeg' || $FT=='.gif' || $FT=='.png' || $FT=='.svg'){
	                    $IMAGES[$i]=$FILE;
	                    $i++;
					} else {
	                    $FILES[$i]=$FILE;
	                    $i++;
					}
                }
            }

            closedir($H);

            uksort($IMAGES,"strnatcmp");
			uksort($FILES,"strnatcmp");

            $APP->set('IMAGES',$IMAGES);
            $APP->set('FILES',$FILES);

            echo Template::instance()->render('CONSTRUCTR-CMS/TEMPLATES/constructr_admin_content_new_after.html','text/html');
        }

        public function content_new_after_verify($APP){
            $APP->set('MODUL_ID',51);
            $USER_RIGHTS=parent::checkUserModulRights($APP->get('MODUL_ID'),$APP->get('LOGIN_USER_RIGHTS'));

            if ($USER_RIGHTS==false){
                $APP->get('CONSTRUCTR_LOG')->write('User '.$APP->get('SESSION.username').' missing USER-RIGHTS for modul '.$APP->get('MODUL_ID'));
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/no-rights');
            }

            $PAGE_ID=filter_var($APP->get('PARAMS.page_id'),FILTER_SANITIZE_NUMBER_INT);
            $APP->set('PAGE_ID',$PAGE_ID);

            $NEW_POSITION=filter_var($APP->get('POST.new_position'),FILTER_SANITIZE_NUMBER_INT);
            $NEW_CONTENT_RAW=$APP->get('POST.new_content_raw');
			$NEW_CONTENT_HTML=\Markdown::instance()->convert($NEW_CONTENT_RAW);
			$NEW_CONTENT_MAPPING=$APP->get('POST.new_content_mapping');

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

            if ($APP->get('PAGE_ID')!='' && $NEW_POSITION!=''){
                $APP->get('DBCON')->exec(
		            ['UPDATE constructr_content SET constructr_content_order=(constructr_content_order+1) WHERE constructr_content_page_id=:PAGE_ID AND constructr_content_order>=:NEW_POSITION;'],
					[[
	                    ':PAGE_ID'=>$PAGE_ID,
	                    ':NEW_POSITION'=>$NEW_POSITION
		            ]]
				);

				if($NEW_CONTENT_MAPPING!=''){
	                $APP->set('NEW_CONTENT',$APP->get('DBCON')->exec(
                        ['INSERT INTO constructr_content SET constructr_content_page_id=:PAGE_ID,constructr_content_content_raw=:NEW_CONTENT_RAW,constructr_content_content_html=:NEW_CONTENT_HTML,constructr_content_tpl_id_mapping=:NEW_CONTENT_MAPPING,constructr_content_order=:NEW_POSITION;'],
                        [[
                            ':PAGE_ID'=>$PAGE_ID,
                            ':NEW_CONTENT_RAW'=>$NEW_CONTENT_RAW,
                            ':NEW_CONTENT_HTML'=>$NEW_CONTENT_HTML,
                            ':NEW_POSITION'=>$NEW_POSITION,
                            ':NEW_CONTENT_MAPPING'=>$NEW_CONTENT_MAPPING
                        ]]
                	));
				}else{
	                $APP->set('NEW_CONTENT',$APP->get('DBCON')->exec(
                        ['INSERT INTO constructr_content SET constructr_content_page_id=:PAGE_ID,constructr_content_content_raw=:NEW_CONTENT_RAW,constructr_content_content_html=:NEW_CONTENT_HTML,constructr_content_order=:NEW_POSITION;'],
                        [[
                            ':PAGE_ID'=>$PAGE_ID,
                            ':NEW_CONTENT_RAW'=>$NEW_CONTENT_RAW,
                            ':NEW_CONTENT_HTML'=>$NEW_CONTENT_HTML,
                            ':NEW_POSITION'=>$NEW_POSITION
                        ]]
					));
				}

				parent::clean_up_cache($APP);

                $APP->set('NEW','success');
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/content/'.$PAGE_ID.'/?new=success');
            } else {
                $APP->set('NEW','no-success');
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/content/'.$PAGE_ID.'/?new=no-success');
            }

            $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/content/'.$PAGE_ID);
        }

        public function content_new($APP){
            $APP->set('MODUL_ID',51);
            $USER_RIGHTS=parent::checkUserModulRights($APP->get('MODUL_ID'),$APP->get('LOGIN_USER_RIGHTS'));

            if ($USER_RIGHTS==false){
                $APP->get('CONSTRUCTR_LOG')->write('User '.$APP->get('SESSION.username').' missing USER-RIGHTS for modul '.$APP->get('MODUL_ID'));
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/no-rights');
            }

            $PAGE_ID=filter_var($APP->get('PARAMS.page_id'),FILTER_SANITIZE_NUMBER_INT);

            $APP->set('PAGE_ID',$PAGE_ID);
            $APP->set('PAGE',$APP->get('DBCON')->exec(
                ['SELECT * FROM constructr_pages WHERE constructr_pages_id=:PAGE_ID LIMIT 1;'],
                [[':PAGE_ID'=>$PAGE_ID]]
            ));

            $CSRF=parent::csrf();
            $APP->set('CSRF',$CSRF);
            $APP->set('SESSION.csrf',$CSRF);
            $ADDITIVE=parent::additive();
            $APP->set('ADDITIVE',$ADDITIVE);
            $APP->set('SESSION.additive',$ADDITIVE);
            $TRIPPLE_ADDITIVE=($ADDITIVE.$CSRF);
            $APP->set('TRIPPLE_ADDITIVE',$TRIPPLE_ADDITIVE);
            $APP->set('SESSION.tripple_additive',$TRIPPLE_ADDITIVE);

            $APP->set('CONTENT',$APP->get('DBCON')->exec(
                ['SELECT constructr_content_id FROM constructr_content WHERE constructr_content_page_id=:PAGE_ID;'],
                [[':PAGE_ID'=>$PAGE_ID]]
            ));

            $APP->set('NEW_POSITION',0);
            $APP->set('NEW_POSITION',(count($APP->get('CONTENT'))+1));

            $APP->set('TEMPLATE',$APP->get('DBCON')->exec(
                ['SELECT constructr_pages_template FROM constructr_pages WHERE constructr_pages_id=:PAGE_ID;'],
                [[':PAGE_ID'=>$PAGE_ID]]
            ));

			$APP->set('TEMPLATE_FILE',$APP->get('TEMPLATE.0.constructr_pages_template'));
			$TEMPLATE_TEXT=file_get_contents($APP->get('TEMPLATES').$APP->get('TEMPLATE_FILE'));
			preg_match_all("/({{@ CONSTRUCTR_MAPPING\()+([\w-])+(\) @}})/",$TEMPLATE_TEXT,$MATCH);
			$CONSTRUCTR_TPL_MAPPINGS=array();

			if($MATCH[0]){
				$i=0;
				foreach($MATCH[0] AS $KEY=>$MATCHR){
					$CONSTRUCTR_TPL_MAPPINGS[$i]=$MATCHR;
					$i++;
				}
			}

			$APP->set('CONSTRUCTR_TPL_MAPPINGS',$CONSTRUCTR_TPL_MAPPINGS);
            $H=opendir($APP->get('UPLOADS'));

			$IMAGES=[];
            $FILES=[];
			$i=0;

            while($FILE=readdir($H)){
                if($FILE!='.' && $FILE!='..' && $FILE!='.empty_file' && $FILE!='index.php'){
					$FT=strtolower(strrchr($FILE,'.'));

					if($FT=='.jpg' || $FT=='.jpeg' || $FT=='.gif' || $FT=='.png' || $FT=='.svg'){
	                    $IMAGES[$i]=$FILE;
	                    $i++;
					} else {
	                    $FILES[$i]=$FILE;
	                    $i++;
					}
                }
            }

            closedir($H);

            uksort($IMAGES,"strnatcmp");
			uksort($FILES,"strnatcmp");

            $APP->set('IMAGES',$IMAGES);
            $APP->set('FILES',$FILES);

            echo Template::instance()->render('CONSTRUCTR-CMS/TEMPLATES/constructr_admin_content_new.html','text/html');
        }

        public function content_new_verify($APP){
            $APP->set('MODUL_ID',51);
            $USER_RIGHTS=parent::checkUserModulRights($APP->get('MODUL_ID'),$APP->get('LOGIN_USER_RIGHTS'));

            if ($USER_RIGHTS==false){
                $APP->get('CONSTRUCTR_LOG')->write('User '.$APP->get('SESSION.username').' missing USER-RIGHTS for modul '.$APP->get('MODUL_ID'));
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/no-rights');
            }

            $PAGE_ID=filter_var($APP->get('PARAMS.page_id'),FILTER_SANITIZE_NUMBER_INT);
            $APP->set('PAGE_ID',$PAGE_ID);

            $NEW_POSITION=filter_var($APP->get('POST.new_position'),FILTER_SANITIZE_NUMBER_INT);
            $NEW_CONTENT_RAW=$APP->get('POST.new_content_raw');
			$NEW_CONTENT_HTML=\Markdown::instance()->convert($NEW_CONTENT_RAW);
			$NEW_CONTENT_MAPPING=$APP->get('POST.new_content_mapping');

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

            if ($APP->get('PAGE_ID')!='' && $NEW_POSITION!=''){
				if($NEW_CONTENT_MAPPING!=''){
	                $APP->set('NEW_CONTENT',$APP->get('DBCON')->exec(
                        ['INSERT INTO constructr_content SET constructr_content_page_id=:PAGE_ID,constructr_content_content_raw=:NEW_CONTENT_RAW,constructr_content_content_html=:NEW_CONTENT_HTML,constructr_content_tpl_id_mapping=:NEW_CONTENT_MAPPING,constructr_content_order=:NEW_POSITION;'],
                        [[
                            ':PAGE_ID'=>$PAGE_ID,
                            ':NEW_CONTENT_RAW'=>$NEW_CONTENT_RAW,
                            ':NEW_CONTENT_HTML'=>$NEW_CONTENT_HTML,
                            ':NEW_POSITION'=>$NEW_POSITION,
                            ':NEW_CONTENT_MAPPING'=>$NEW_CONTENT_MAPPING
                        ]]
                    ));
				}else{
	                $APP->set('NEW_CONTENT',$APP->get('DBCON')->exec(
                        ['INSERT INTO constructr_content SET constructr_content_page_id=:PAGE_ID,constructr_content_content_raw=:NEW_CONTENT_RAW,constructr_content_content_html=:NEW_CONTENT_HTML,constructr_content_order=:NEW_POSITION;'],
                        [[
                            ':PAGE_ID'=>$PAGE_ID,
                            ':NEW_CONTENT_RAW'=>$NEW_CONTENT_RAW,
                            ':NEW_CONTENT_HTML'=>$NEW_CONTENT_HTML,
                            ':NEW_POSITION'=>$NEW_POSITION
                        ]]
                    ));
				}

				parent::clean_up_cache($APP);

                $APP->set('NEW','success');
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/content/'.$PAGE_ID.'/?new=success');
            } else {
                $APP->set('NEW','no-success');
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/content/'.$PAGE_ID.'/?new=no-success');
            }

            $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/content/'.$PAGE_ID);
        }

		public function preparse_content_live_preview($APP,$PARSED_HTML=''){
            $LIVE_CONTENT=$APP->get('POST.content');
			if($LIVE_CONTENT!=''){
				$PARSED_HTML=\Markdown::instance()->convert($LIVE_CONTENT);
				echo $PARSED_HTML;	
			}
		}

        public function content_edit($APP){
            $APP->set('MODUL_ID',52);
            $USER_RIGHTS=parent::checkUserModulRights($APP->get('MODUL_ID'),$APP->get('LOGIN_USER_RIGHTS'));

            if ($USER_RIGHTS==false){
                $APP->get('CONSTRUCTR_LOG')->write('User '.$APP->get('SESSION.username').' missing USER-RIGHTS for modul '.$APP->get('MODUL_ID'));
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/no-rights');
            }

            $PAGE_ID=filter_var($APP->get('PARAMS.page_id'),FILTER_SANITIZE_NUMBER_INT);

            $APP->set('PAGE_ID',$PAGE_ID);
            $APP->set('PAGE',$APP->get('DBCON')->exec(
                ['SELECT * FROM constructr_pages WHERE constructr_pages_id=:PAGE_ID LIMIT 1;'],
                [[':PAGE_ID'=>$PAGE_ID]]
            ));

            $CONTENT_ID=filter_var($APP->get('PARAMS.content_id'),FILTER_SANITIZE_NUMBER_INT);
            $APP->set('CONTENT_ID',$CONTENT_ID);
            $CSRF=parent::csrf();
            $APP->set('CSRF',$CSRF);
            $APP->set('SESSION.csrf',$CSRF);
            $ADDITIVE=parent::additive();
            $APP->set('ADDITIVE',$ADDITIVE);
            $APP->set('SESSION.additive',$ADDITIVE);
            $TRIPPLE_ADDITIVE=($ADDITIVE.$CSRF);
            $APP->set('TRIPPLE_ADDITIVE',$TRIPPLE_ADDITIVE);
            $APP->set('SESSION.tripple_additive',$TRIPPLE_ADDITIVE);

            $APP->set('CONTENT',$APP->get('DBCON')->exec(
                ['SELECT * FROM constructr_content WHERE constructr_content_id=:CONTENT_ID AND constructr_content_page_id=:PAGE_ID LIMIT 1;'],
                [[
                    ':CONTENT_ID'=>$CONTENT_ID,
                    ':PAGE_ID'=>$PAGE_ID
				]]
            ));

            $APP->set('CONTENT_COUNTR',0);
            $APP->set('CONTENT_COUNTR',count($APP->get('CONTENT')));
            $APP->set('TEMPLATE',$APP->get('DBCON')->exec(
                ['SELECT constructr_pages_template FROM constructr_pages WHERE constructr_pages_id=:PAGE_ID;'],
                [[':PAGE_ID'=>$PAGE_ID]]
            ));

			$APP->set('TEMPLATE_FILE',$APP->get('TEMPLATE.0.constructr_pages_template'));
			$TEMPLATE_TEXT=file_get_contents($APP->get('TEMPLATES').$APP->get('TEMPLATE_FILE'));
			preg_match_all("/({{@ CONSTRUCTR_MAPPING\()+([\w-])+(\) @}})/",$TEMPLATE_TEXT,$MATCH);

			$CONSTRUCTR_TPL_MAPPINGS=[];

			if($MATCH[0]){
				$i=0;
				foreach($MATCH[0] AS $KEY=>$MATCHR){
					$CONSTRUCTR_TPL_MAPPINGS[$i]=$MATCHR;
					$i++;
				}
			}

			$APP->set('CONSTRUCTR_TPL_MAPPINGS',$CONSTRUCTR_TPL_MAPPINGS);
            $H=opendir($APP->get('UPLOADS'));

			$IMAGES=[];
            $FILES=[];
			$i=0;

            while($FILE=readdir($H)){
                if($FILE!='.' && $FILE!='..' && $FILE!='.empty_file' && $FILE!='index.php'){
					$FT=strtolower(strrchr($FILE,'.'));

					if($FT=='.jpg' || $FT=='.jpeg' || $FT=='.gif' || $FT=='.png' || $FT=='.svg'){
	                    $IMAGES[$i]=$FILE;
	                    $i++;
					} else {
	                    $FILES[$i]=$FILE;
	                    $i++;
					}
                }
            }

            closedir($H);

            uksort($IMAGES,"strnatcmp");
			uksort($FILES,"strnatcmp");

            $APP->set('IMAGES',$IMAGES);
            $APP->set('FILES',$FILES);

            if ($APP->get('CONTENT_COUNTR')==1){
                echo Template::instance()->render('CONSTRUCTR-CMS/TEMPLATES/constructr_admin_content_edit.html','text/html');
            } else {
                $APP->set('EDIT','no-success');
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/content/'.$PAGE_ID.'/?edit=no-success');
            }
        }

        public function content_edit_verify($APP){
            $APP->set('MODUL_ID',52);
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

            $PAGE_ID=filter_var($APP->get('PARAMS.page_id'),FILTER_SANITIZE_NUMBER_INT);
            $APP->set('PAGE_ID',$PAGE_ID);
            $CONTENT_ID=filter_var($APP->get('PARAMS.content_id'),FILTER_SANITIZE_NUMBER_INT);
            $APP->set('CONTENT_ID',$CONTENT_ID);
            $EDIT_CONTENT_RAW=$APP->get('POST.edit_content_raw');
			$EDIT_CONTENT_HTML=\Markdown::instance()->convert($EDIT_CONTENT_RAW);
			$EDIT_CONTENT_MAPPING=$APP->get('POST.edit_content_mapping');

            if ($PAGE_ID!='' && $CONTENT_ID!=''){
				if($EDIT_CONTENT_MAPPING!='' && $EDIT_CONTENT_MAPPING!='666'){
	                $APP->set('EDIT_CONTENT',$APP->get('DBCON')->exec(
                        ['UPDATE constructr_content SET constructr_content_content_raw=:EDIT_CONTENT_RAW,constructr_content_content_html=:EDIT_CONTENT_HTML,constructr_content_tpl_id_mapping=:EDIT_CONTENT_MAPPING WHERE constructr_content_page_id=:PAGE_ID AND constructr_content_id=:CONTENT_ID LIMIT 1;'],
                        [[
                            ':PAGE_ID'=>$PAGE_ID,
                            ':EDIT_CONTENT_RAW'=>$EDIT_CONTENT_RAW,
                            ':EDIT_CONTENT_HTML'=>$EDIT_CONTENT_HTML,
                            ':CONTENT_ID'=>$CONTENT_ID,
                            ':EDIT_CONTENT_MAPPING'=>$EDIT_CONTENT_MAPPING
                        ]]
                    ));
				}else{
	                $APP->set('EDIT_CONTENT',$APP->get('DBCON')->exec(
                        ['UPDATE constructr_content SET constructr_content_content_raw=:EDIT_CONTENT_RAW,constructr_content_content_html=:EDIT_CONTENT_HTML,constructr_content_tpl_id_mapping=:EDIT_CONTENT_MAPPING WHERE constructr_content_page_id=:PAGE_ID AND constructr_content_id=:CONTENT_ID LIMIT 1;'],
                        [[
                            ':PAGE_ID'=>$PAGE_ID,
                            ':EDIT_CONTENT_RAW'=>$EDIT_CONTENT_RAW,
                            ':EDIT_CONTENT_HTML'=>$EDIT_CONTENT_HTML,
                            ':CONTENT_ID'=>$CONTENT_ID,
                            ':EDIT_CONTENT_MAPPING'=>''
                        ]]
                    ));
				}

				parent::clean_up_cache($APP);

                $APP->set('EDIT','success');
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/content/'.$PAGE_ID.'/?edit=success');
            } else {
                $APP->set('EDIT','no-success');
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/content/'.$PAGE_ID.'/?edit=no-success');
            }
        }

		public function content_change_visibility($APP){
            $APP->set('MODUL_ID',52);
            $USER_RIGHTS=parent::checkUserModulRights($APP->get('MODUL_ID'),$APP->get('LOGIN_USER_RIGHTS'));

            if ($USER_RIGHTS==false){
                $APP->get('CONSTRUCTR_LOG')->write('User '.$APP->get('SESSION.username').' missing USER-RIGHTS for modul '.$APP->get('MODUL_ID'));
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/no-rights');
            }

			$WHAT=filter_var($APP->get('PARAMS.what'),FILTER_SANITIZE_FULL_SPECIAL_CHARS);
			$PAGE_ID=filter_var($APP->get('PARAMS.page_id'),FILTER_SANITIZE_NUMBER_INT);
			$CONTENT_ID=filter_var($APP->get('PARAMS.content_id'),FILTER_SANITIZE_NUMBER_INT);

			if($WHAT!='' && $PAGE_ID!=''){
				if($WHAT=='on'){
	                $APP->set('UPDATER',$APP->get('DBCON')->exec(
                        ['UPDATE constructr_content SET constructr_content_visible=1 WHERE constructr_content_id=:CONTENT_ID LIMIT 1;'],
                        [[':CONTENT_ID'=>$CONTENT_ID]]
                    ));

					$APP->set('EDIT','success');
	                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/content/'.$PAGE_ID.'/?edit=success');
				} else {
	                $APP->set('UPDATER',$APP->get('DBCON')->exec(
                        ['UPDATE constructr_content SET constructr_content_visible=0 WHERE constructr_content_id=:CONTENT_ID LIMIT 1;'],
                        [[':CONTENT_ID'=>$CONTENT_ID]]
                    ));

					parent::clean_up_cache($APP);

	                $APP->set('EDIT','success');
	                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/content/'.$PAGE_ID.'/?edit=success');
				}
			} else {
                $APP->set('EDIT','success');
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/content/'.$PAGE_ID.'/?edit=success');
			}
		}

        public function content_delete($APP){
            $APP->set('MODUL_ID',54);
            $USER_RIGHTS=parent::checkUserModulRights($APP->get('MODUL_ID'),$APP->get('LOGIN_USER_RIGHTS'));

            if ($USER_RIGHTS==false){
                $APP->get('CONSTRUCTR_LOG')->write('User '.$APP->get('SESSION.username').' missing USER-RIGHTS for modul '.$APP->get('MODUL_ID'));
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/no-rights');
            }

            $PAGE_ID=filter_var($APP->get('PARAMS.page_id'),FILTER_SANITIZE_NUMBER_INT);
            $APP->set('PAGE_ID',$PAGE_ID);

            $CONTENT_ID=filter_var($APP->get('PARAMS.content_id'),FILTER_SANITIZE_NUMBER_INT);
            $APP->set('CONTENT_ID',$CONTENT_ID);

            if ($PAGE_ID!='' && $CONTENT_ID!=''){
                $APP->set('SELECT_CONTENT',$APP->get('DBCON')->exec(
                    ['SELECT * FROM constructr_content WHERE constructr_content_page_id=:PAGE_ID AND constructr_content_id=:CONTENT_ID LIMIT 1;'],
                    [[
                        ':PAGE_ID'=>$PAGE_ID,
                        ':CONTENT_ID'=>$CONTENT_ID
                    ]]
                ));

                $APP->set('CONTENT_COUNTR',0);
                $APP->set('CONTENT_COUNTR',count($APP->get('SELECT_CONTENT')));

                if ($APP->get('CONTENT_COUNTR')==1){
                    $APP->set('DELETE_CONTENT',$APP->get('DBCON')->exec(
                        ['DELETE FROM constructr_content WHERE constructr_content_page_id=:PAGE_ID AND constructr_content_id=:CONTENT_ID LIMIT 1;'],
                        [[
                            ':PAGE_ID'=>$PAGE_ID,
                            ':CONTENT_ID'=>$CONTENT_ID
                        ]]
                    ));

                    $OLD_CONTENT_POSITION=$APP->get('SELECT_CONTENT.0.constructr_content_order');

                    $APP->set('UPDATE_ORDER',$APP->get('DBCON')->exec(
                        ['UPDATE constructr_content SET constructr_content_order=(constructr_content_order-1) WHERE constructr_content_page_id=:PAGE_ID AND constructr_content_order>:OLD_CONTENT_POSITION;'],
                        [[
                            ':PAGE_ID'=>$PAGE_ID,
                            ':OLD_CONTENT_POSITION'=>$OLD_CONTENT_POSITION
                        ]]
                    ));

					parent::clean_up_cache($APP);

                    $APP->set('DELETE','success');
                    $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/content/'.$PAGE_ID.'/?delete=success');
                } else {
                    $APP->set('DELETE','no-success');
                    $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/content/'.$PAGE_ID.'/?delete=no-success');
                }
            } else {
                $APP->set('DELETE','no-success');
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/content/'.$PAGE_ID.'/?delete=no-success');
            }
        }

        public function content_reorder($APP){
            $APP->set('MODUL_ID',53);
            $USER_RIGHTS=parent::checkUserModulRights($APP->get('MODUL_ID'),$APP->get('LOGIN_USER_RIGHTS'));

            if ($USER_RIGHTS==false){
                $APP->get('CONSTRUCTR_LOG')->write('User '.$APP->get('SESSION.username').' missing USER-RIGHTS for modul '.$APP->get('MODUL_ID'));
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/no-rights');
            }

            $PAGE_ID=filter_var($APP->get('PARAMS.page_id'),FILTER_SANITIZE_NUMBER_INT);
            $APP->set('PAGE_ID',$PAGE_ID);
            $CONTENT_ID=filter_var($APP->get('PARAMS.content_id'),FILTER_SANITIZE_NUMBER_INT);
            $APP->set('CONTENT_ID',$CONTENT_ID);
            $METHOD=filter_var($APP->get('PARAMS.method'),FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $APP->set('METHOD',$METHOD);

            $APP->set('SELECT_CONTENT',$APP->get('DBCON')->exec(
                ['SELECT * FROM constructr_content WHERE constructr_content_page_id=:PAGE_ID AND constructr_content_id=:CONTENT_ID LIMIT 1;'],
                [[
                    ':PAGE_ID'=>$PAGE_ID,
                    ':CONTENT_ID'=>$CONTENT_ID
                ]]
            ));

            $APP->set('CONTENT_COUNTR',0);
            $APP->set('CONTENT_COUNTR',count($APP->get('SELECT_CONTENT')));

            if ($APP->get('CONTENT_COUNTR')==1){
                if ($METHOD=='up'){
                    $ACT_POSITION=$APP->get('SELECT_CONTENT.0.constructr_content_order');
                    $NEW_POSITION=($APP->get('SELECT_CONTENT.0.constructr_content_order')-1);

                    $APP->set('UPDATE_OLD_CONTENT',$APP->get('DBCON')->exec(
                        ['UPDATE constructr_content SET constructr_content_order=:TMP_ORDER WHERE constructr_content_page_id=:PAGE_ID AND constructr_content_order=:NEW_POSITION LIMIT 1;'],
                        [[
                            ':PAGE_ID'=>$PAGE_ID,
                            ':NEW_POSITION'=>$NEW_POSITION,
                            ':TMP_ORDER'=>9999
                        ]]
                    ));

                    $APP->set('UPDATE_NEW_CONTENT',$APP->get('DBCON')->exec(
                        ['UPDATE constructr_content SET constructr_content_order=(constructr_content_order-1) WHERE constructr_content_page_id=:PAGE_ID AND constructr_content_id=:CONTENT_ID LIMIT 1;'],
                        [[
                            ':PAGE_ID'=>$PAGE_ID,
                            ':CONTENT_ID'=>$CONTENT_ID
                        ]]
                    ));

                    $APP->set('UPDATE_OLD_CONTENT2',$APP->get('DBCON')->exec(
                       ['UPDATE constructr_content SET constructr_content_order=:ACT_POSITION WHERE constructr_content_page_id=:PAGE_ID AND constructr_content_order=:TMP_ORDER LIMIT 1;'],
                        [[
                            ':ACT_POSITION'=>$ACT_POSITION,
                            ':PAGE_ID'=>$PAGE_ID,
                            ':TMP_ORDER'=>9999
                        ]]
                    ));

                    $APP->set('MOVE','success');
                    $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/content/'.$PAGE_ID.'/?move=success');
                } elseif ($METHOD=='down'){
                    $ACT_POSITION=$APP->get('SELECT_CONTENT.0.constructr_content_order');
                    $NEW_POSITION=($APP->get('SELECT_CONTENT.0.constructr_content_order')+1);

                    $APP->set('UPDATE_OLD_CONTENT',$APP->get('DBCON')->exec(
                        ['UPDATE constructr_content SET constructr_content_order=:TMP_ORDER WHERE constructr_content_page_id=:PAGE_ID AND constructr_content_order=:NEW_POSITION LIMIT 1;'],
                        [[
                            ':PAGE_ID'=>$PAGE_ID,
                            ':NEW_POSITION'=>$NEW_POSITION,
                            ':TMP_ORDER'=>9999
                        ]]
                    ));

                    $APP->set('UPDATE_NEW_CONTENT',$APP->get('DBCON')->exec(
                        ['UPDATE constructr_content SET constructr_content_order=(constructr_content_order+1) WHERE constructr_content_page_id=:PAGE_ID AND constructr_content_id=:CONTENT_ID LIMIT 1;'],
                        [[
                            ':PAGE_ID'=>$PAGE_ID,
                            ':CONTENT_ID'=>$CONTENT_ID
                        ]]
                    ));

                    $APP->set('UPDATE_OLD_CONTENT2',$APP->get('DBCON')->exec(
                        ['UPDATE constructr_content SET constructr_content_order=:ACT_POSITION WHERE constructr_content_page_id=:PAGE_ID AND constructr_content_order=:TMP_ORDER LIMIT 1;'],
                        [[
                            ':ACT_POSITION'=>$ACT_POSITION,
                            ':PAGE_ID'=>$PAGE_ID,
                            ':TMP_ORDER'=>9999
                        ]]
                    ));

					parent::clean_up_cache($APP);

                    $APP->set('MOVE','success');
                    $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/content/'.$PAGE_ID.'/?move=success');
                } else {
                    $APP->set('MOVE','no-success');
                    $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/content/'.$PAGE_ID.'/?move=no-success');
                }
            } else {
                $APP->set('MOVE','no-success');
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/content/'.$PAGE_ID.'/?move=no-success');
            }
        }
    }
