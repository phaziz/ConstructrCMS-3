<?php

    class ConstructrUploads extends ConstructrBase
    {
        public function beforeRoute($APP){
        	$APP->set('ACT_VIEW','uploads');

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
                    )
                );

                $ITERATOR=new RecursiveIteratorIterator(new RecursiveArrayIterator($APP->get('LOGIN_USER_RIGHTS')));

                $i=1;
                $CLEAN_USER_RIGHTS=[];

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

        public function uploads_init($APP){
            $APP->set('MODUL_ID',60);
            $USER_RIGHTS=parent::checkUserModulRights($APP->get('MODUL_ID'),$APP->get('LOGIN_USER_RIGHTS'));

            if ($USER_RIGHTS==false){
                $APP->get('CONSTRUCTR_LOG')->write('User '.$APP->get('SESSION.username').' missing USER-RIGHTS for modul '.$APP->get('MODUL_ID'));
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/no-rights');
            }

			$APP->set('ORIGIN_NEEDLE','');

            if (isset($_GET['new'])){$APP->set('NEW',$_GET['new']);} else {$APP->set('NEW','');}
            if (isset($_GET['delete'])){$APP->set('DELETE',$_GET['delete']);} else {$APP->set('DELETE','');}
			if (isset($_GET['edit'])){$APP->set('EDIT',$_GET['edit']);} else {$APP->set('EDIT','');}

			$APP->set('PAGINATION_FILES',[]);
			$PAGINATION_FILES=[];

			if($APP->get('GET.needle')){
				$APP->set('FILES_COUNTR',0);
				$APP->set('ORIGIN_NEEDLE',$APP->get('GET.needle'));
				$NEEDLES=explode(' ',strtolower($APP->get('GET.needle')));
	            $H=opendir($APP->get('UPLOADS'));
				$i=0;

	            while($FILE=readdir($H)){
	                if($FILE!='.' && $FILE!='..' && $FILE!='index.php' && $FILE!='.empty_file' && $FILE!='TMP'){
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
	                if($FILE!='.' && $FILE!='..' && $FILE!='index.php' && $FILE!='.empty_file' && $FILE!='TMP'){
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
					$PAGINATION_FILES=[];

					foreach($TEMP_PAGINATION_FILES AS $KEY=>$VALUE){
						if($KEY>=$START && $KEY<$END){
							if($VALUE!='.' && $VALUE!='..' && $VALUE!='index.php' && $VALUE!='.empty_file' && $FILE!='TMP'){
								$FT=strtolower(strrchr( $VALUE,'.'));
								if($FT=='.jpg' || $FT=='.jpeg' || $FT=='.gif' || $FT=='.png' || $FT=='.svg'){
									$PAGINATION_FILES[$KEY]=$VALUE;
								} else {
									$PAGINATION_FILES[$KEY]=$VALUE;
								}
							}
						}
					}

					$APP->set('PAGINATION_FILES',$PAGINATION_FILES);
				}

				echo Template::instance()->render('CONSTRUCTR-CMS/TEMPLATES/constructr_admin_uploads.html','text/html');
			}
        }

        public function uploads_edit_file($APP){
            $APP->set('MODUL_ID',60);
            $USER_RIGHTS=parent::checkUserModulRights($APP->get('MODUL_ID'),$APP->get('LOGIN_USER_RIGHTS'));

            if ($USER_RIGHTS==false){
                $APP->get('CONSTRUCTR_LOG')->write('User '.$APP->get('SESSION.username').' missing USER-RIGHTS for modul '.$APP->get('MODUL_ID'));
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/no-rights');
            }

			if(isset($_GET['edit'])){$APP->set('EDIT',$_GET['edit']);}else{$APP->set('EDIT','');}

			$APP->set('FILE_TO_EDIT',$APP->get('PARAMS.file'));
			@chmod($APP->get('UPLOADS').$APP->get('FILE_TO_EDIT'),0777);
			$PATH_PARTS= pathinfo($APP->get('UPLOADS').$APP->get('FILE_TO_EDIT'));
			$APP->set('FILE_TO_EDIT_EXTENSION',strtolower($PATH_PARTS['extension']));
			$APP->set('FILE_TO_EDIT_FILENAME',$PATH_PARTS['filename']);

			if($APP->get('FILE_TO_EDIT_EXTENSION') == 'jpg' || $APP->get('FILE_TO_EDIT_EXTENSION') == 'jpeg' || $APP->get('FILE_TO_EDIT_EXTENSION') == 'gif' || $APP->get('FILE_TO_EDIT_EXTENSION') == 'png'|| $APP->get('FILE_TO_EDIT_EXTENSION') == 'svg'){
				$APP->set('IS_IMAGE','true');
			} else {
				$APP->set('IS_IMAGE','false');
			}

			$APP->set('IMAGE_WIDTH',0);
			$APP->set('IMAGE_HEIGHT',0);
			$IMAGE_WIDTH=0;
			$IMAGE_HEIGHT=0;

			if($APP->get('FILE_TO_EDIT_EXTENSION')=='png' || $APP->get('FILE_TO_EDIT_EXTENSION')=='jpg' || $APP->get('FILE_TO_EDIT_EXTENSION')=='jpeg' || $APP->get('FILE_TO_EDIT_EXTENSION')=='gif' || $APP->get('FILE_TO_EDIT_EXTENSION')=='wbmp'){
				$img=new Image($APP->get('FILE_TO_EDIT'),false,$APP->get('UPLOADS'));
				$IMAGE_WIDTH=$img->width();
				$IMAGE_HEIGHT=$img->height();
				$APP->set('IMAGE_WIDTH',$IMAGE_WIDTH);
				$APP->set('IMAGE_HEIGHT',$IMAGE_HEIGHT);
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

            echo Template::instance()->render('CONSTRUCTR-CMS/TEMPLATES/constructr_admin_uploads_edit.html','text/html');
        }

		public function uploads_edit_file_verify($APP){
            $APP->set('MODUL_ID',60);
            $USER_RIGHTS=parent::checkUserModulRights($APP->get('MODUL_ID'),$APP->get('LOGIN_USER_RIGHTS'));

            if ($USER_RIGHTS==false){
                $APP->get('CONSTRUCTR_LOG')->write('User '.$APP->get('SESSION.username').' missing USER-RIGHTS for modul '.$APP->get('MODUL_ID'));
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/no-rights');
            }

            $FILE_TO_EDIT=$APP->get('POST.edit_file');
			$FILE_TO_EDIT_NAME=$APP->get('POST.edit_file_origin_filename');
			$FILE_TO_EDIT_NEW_NAME=$APP->get('POST.edit_file_name_new');
			$FILE_TO_EDIT_NEW_NAME=self::icleanName($FILE_TO_EDIT_NEW_NAME);
			$FILE_TO_EDIT_EXTENSION=$APP->get('POST.edit_file_origin_extension');
			$PROCESS_FILTER = '';

			if($FILE_TO_EDIT_EXTENSION=='jpg'){
				$PROCESS_FILTER='jpeg';
			} else if($FILE_TO_EDIT_EXTENSION=='gif'){
				$PROCESS_FILTER='gif';
			} else if($FILE_TO_EDIT_EXTENSION=='png'){
				$PROCESS_FILTER='png';
			} else if($FILE_TO_EDIT_EXTENSION=='wbmp'){
				$PROCESS_FILTER='wbmp';
			}

			$SPECIAL_EFFECT = $APP->get('POST.image-effect');

			if($FILE_TO_EDIT == '' || $FILE_TO_EDIT_NAME == '' || $FILE_TO_EDIT_NEW_NAME == '' || $FILE_TO_EDIT_EXTENSION == ''){
                $APP->set('EDIT','no-success');
                $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/uploads/?edit=no-success');
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

			@sleep(1);
			$RET=false;
			$RET=@rename($APP->get('UPLOADS').$FILE_TO_EDIT,$APP->get('UPLOADS').$FILE_TO_EDIT_NEW_NAME.'.'.$FILE_TO_EDIT_EXTENSION);

			if($SPECIAL_EFFECT!=''){
				$MASTER_FILE=$APP->get('UPLOADS').$FILE_TO_EDIT;				
				$COPY_FILE=$APP->get('UPLOADS').$FILE_TO_EDIT_NEW_NAME.'-COPY-'.date('Y-m-d-h-i-s').'.'.$FILE_TO_EDIT_EXTENSION;
				copy($MASTER_FILE,$COPY_FILE);
				chmod($COPY_FILE,0777);
				$EFFECT_COPY_IMAGE=file_get_contents($SPECIAL_EFFECT);
				file_put_contents($COPY_FILE, $EFFECT_COPY_IMAGE);
			}

            $H=opendir($APP->get('UPLOADS').'TMP/');

            while($FILE=readdir($H)){
				@unlink($APP->get('UPLOADS').'TMP/'.$FILE);
            }

			if($RET==true){
				$APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/uploads?edit=success');
			}else{
				$APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/uploads?edit=no-success');	
			}
		}
		public function image_special($APP){
			$FILE=$APP->get('POST.f');
			$PREVIEW=$APP->get('POST.p');
			$SPECIALE=$APP->get('POST.e');
			$HEIGHT=$APP->get('POST.h');
			$WIDTH=$APP->get('POST.w');
			$PATH_PARTS=pathinfo($APP->get('UPLOADS').$FILE);
			$APP->set('FILE_EXTENSION',strtolower($PATH_PARTS['extension']));
			$APP->set('FILE_FILENAME',$PATH_PARTS['filename']);
			$PROCESS_FILTER='';
			
			if($APP->get('FILE_EXTENSION')=='jpg'){
				$PROCESS_FILTER='jpeg';
			} else if($APP->get('FILE_EXTENSION')=='gif'){
				$PROCESS_FILTER='gif';
			} else if($APP->get('FILE_EXTENSION')=='png'){
				$PROCESS_FILTER='png';
			} else if($APP->get('FILE_EXTENSION')=='wbmp'){
				$PROCESS_FILTER='wbmp';
			}

			if($APP->get('FILE_EXTENSION')=='png' || $APP->get('FILE_EXTENSION')=='jpg' || $APP->get('FILE_EXTENSION')=='jpeg' || $APP->get('FILE_EXTENSION')=='gif' || $APP->get('FILE_EXTENSION')=='wbmp'){
				$img=new Image($FILE,false,$APP->get('UPLOADS'));
				$IMAGE_WIDTH=0;
				$IMAGE_HEIGHT=0;
				$IMAGE_WIDTH=$img->width();
				$IMAGE_HEIGHT=$img->height();

				if($HEIGHT && $WIDTH && $HEIGHT != $IMAGE_HEIGHT && $WIDTH != $IMAGE_WIDTH){
					$img->resize($WIDTH,$HEIGHT,false,false);
				}

				switch ($SPECIALE){
					case 'invert':
						$img->invert();
						break;
					case 'grayscale':
						$img->grayscale();
						break;
					case 'emboss':
						$img->emboss();
						break;
					case 'sepia':
						$img->sepia();
						break;
					case 'pixelate':
						$img->pixelate(10);
						break;
					case 'rotate90':
						$img->rotate(-90);
						break;
					case 'rotate180':
						$img->rotate(-180);
						break;
					case 'rotate270':
						$img->rotate(-270);
						break;
					case 'hflip':
						$img->hflip();
						break;
					case 'vflip':
						$img->vflip();
						break;
					case 'origin':
						echo $APP->get('CONSTRUCTR_BASE_URL').'/UPLOADS/' . $FILE;
						die();
						break;
				}

				$img->save();
				$TS=time();
				@file_put_contents($APP->get('UPLOADS').'TMP/'.$TS.'.png',$img->dump($PROCESS_FILTER));
				echo $APP->get('CONSTRUCTR_BASE_URL').'/UPLOADS/TMP/'.$TS.'.png';
			}
		}

        public function uploads_delete_file($APP){
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

        public function uploads_new($APP){
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

        public function uploads_new_verify($APP){
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

		public function cleanName($APP){
			$MESSY_NAME=$APP->get('POST.messy_name');
			echo self::icleanName($MESSY_NAME);
		}

        public function icleanName($str){
            $str=str_replace('À','-',$str);
            $str=str_replace('Á','-',$str);
            $str=str_replace('Â','-',$str);
            $str=str_replace('Ã','-',$str);
            $str=str_replace('Ä','-',$str);
            $str=str_replace('Å','-',$str);
            $str=str_replace('Æ','-',$str);
            $str=str_replace('Ç','-',$str);
            $str=str_replace('È','-',$str);
            $str=str_replace('É','-',$str);
            $str=str_replace('Ê','-',$str);
            $str=str_replace('Ë','-',$str);
            $str=str_replace('Ì','-',$str);
            $str=str_replace('Í','-',$str);
            $str=str_replace('Î','-',$str);
            $str=str_replace('Ï','-',$str);
            $str=str_replace('Ð','-',$str);
            $str=str_replace('Ñ','-',$str);
            $str=str_replace('Ò','-',$str);
            $str=str_replace('Ó','-',$str);
            $str=str_replace('Ô','-',$str);
            $str=str_replace('Õ','-',$str);
            $str=str_replace('Ö','-',$str);
            $str=str_replace('×','-',$str);
            $str=str_replace('Ø','-',$str);
            $str=str_replace('Ù','-',$str);
            $str=str_replace('Ú','-',$str);
            $str=str_replace('Û','-',$str);
            $str=str_replace('Ü','-',$str);
            $str=str_replace('Ý','-',$str);
            $str=str_replace('Þ','-',$str);
            $str=str_replace('ß','-',$str);
            $str=str_replace('à','-',$str);
            $str=str_replace('á','-',$str);
            $str=str_replace('â','-',$str);
            $str=str_replace('ã','-',$str);
            $str=str_replace('ä','-',$str);
            $str=str_replace('å','-',$str);
            $str=str_replace('æ','-',$str);
            $str=str_replace('ç','-',$str);
            $str=str_replace('è','-',$str);
            $str=str_replace('é','-',$str);
            $str=str_replace('ê','-',$str);
            $str=str_replace('ë','-',$str);
            $str=str_replace('ì','-',$str);
            $str=str_replace('í','-',$str);
            $str=str_replace('î','-',$str);
            $str=str_replace('ï','-',$str);
            $str=str_replace('ð','-',$str);
            $str=str_replace('ñ','-',$str);
            $str=str_replace('ò','-',$str);
            $str=str_replace('ó','-',$str);
            $str=str_replace('ô','-',$str);
            $str=str_replace('õ','-',$str);
            $str=str_replace('ö','-',$str);
            $str=str_replace('÷','-',$str);
            $str=str_replace('ø','-',$str);
            $str=str_replace('ù','-',$str);
            $str=str_replace('ú','-',$str);
            $str=str_replace('û','-',$str);
            $str=str_replace('ü','-',$str);
            $str=str_replace('ý','-',$str);
            $str=str_replace('þ','-',$str);
            $str=str_replace('ÿ','-',$str);
			$str=str_replace(' ','_',$str);
            return $str;
        }
    }
