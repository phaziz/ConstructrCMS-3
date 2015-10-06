<?php

    session_start();

    $APP=require_once __DIR__.'/vendor/base.php';
    $CONSTRUCTR_CONFIG=file_get_contents(__DIR__.'/CONSTRUCTR-CMS/CONFIG/constructr_config.json');
    $CONSTRUCTR_CONFIG=json_decode($CONSTRUCTR_CONFIG, true);

    $APP->set('DATABASE_HOSTNAME',$CONSTRUCTR_CONFIG['DATABASE_HOSTNAME']);
    $APP->set('DATABASE_DATABASE',$CONSTRUCTR_CONFIG['DATABASE_DATABASE']);
    $APP->set('DATABASE_PORT',$CONSTRUCTR_CONFIG['DATABASE_PORT']);
    $APP->set('DATABASE_USERNAME',$CONSTRUCTR_CONFIG['DATABASE_USERNAME']);
    $APP->set('DATABASE_PASSWORD',$CONSTRUCTR_CONFIG['DATABASE_PASSWORD']);
    $APP->set('CONSTRUCTR_VERSION',$CONSTRUCTR_CONFIG['CONSTRUCTR_VERSION']);
    $APP->set('CONSTRUCTR_POSTMASTER_EMAIL',$CONSTRUCTR_CONFIG['CONSTRUCTR_POSTMASTER_EMAIL']);
    $APP->set('CONSTRUCTR_USER_SALT',$CONSTRUCTR_CONFIG['CONSTRUCTR_USER_SALT']);
    $APP->set('CONSTRUCTR_BASE_URL',$CONSTRUCTR_CONFIG['CONSTRUCTR_BASE_URL']);
    $APP->set('CONSTRUCTR_REPLACE_BASE_URL',$CONSTRUCTR_CONFIG['CONSTRUCTR_REPLACE_BASE_URL']);
    $APP->set('ENCODING','utf-8');
    $APP->set('AUTOLOAD','CONSTRUCTR-CMS/CONTROLLER/');
    $APP->set('CONSTRUCTR_LOG', $CONSTRUCTR_LOG=new \Log('CONSTRUCTR-CMS/LOGFILES/'.date('Y-m-d').'-constructr.txt'));
    $APP->set('CONSTRUCTR_FE_CACHE', __DIR__.'/CONSTRUCTR-CMS/CACHE/');
    $APP->set('TEMPLATES',$APP->get('CONSTRUCTR_BASE_URL').'/THEMES/');

    $APP->set('MAX_ERROR_LOGIN',5); // (integer) // Standard: 5
    $APP->set('LOGIN_WAITR',600); // (integer) // Standard: 600
    $APP->set('UPLOADS_LIST_PAGINATION',5); // (integer) // Standard: 5
    $APP->set('CONSTRUCTR_CACHE',1); // 0 || 1 // (bool) // Standard: 1
    $APP->set('OUTPUT_COMPRESSION',1); // 0 || 1 // (bool) // Standard: 1
    $APP->set('COMPRESSOR_HTML5',0); // 0 || 1 // (bool) // Standard: 0
    $APP->set('COMPRESSOR_CSS',1); // 0 || 1 // (bool) // Standard: 1
    $APP->set('COMPRESSOR_JS',1); // 0 || 1 // (bool) // Standard: 1
    $APP->set('CONSTRUCTR_BACKEND_LANGUAGE','en'); // de || en // (char) // Standard: en

    try{
        $APP->set('DBCON',$DBCON=new DB\SQL('mysql:host='.$APP->get('DATABASE_HOSTNAME').';port='.$APP->get('DATABASE_PORT').';dbname='.$APP->get('DATABASE_DATABASE'),$APP->get('DATABASE_USERNAME'),$APP->get('DATABASE_PASSWORD')));
    }catch(PDOException $e){
        echo 'Setup ConstructrCMS <a href="'.((empty($_SERVER['HTTPS']))?'http://':'https://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'CONSTRUCTR-CMS-SETUP/">here</a>';
        die();
    }

    $REQUEST=((empty($_SERVER['HTTPS']))?'http://':'https://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    $REQUEST=trim(str_replace($APP->get('CONSTRUCTR_REPLACE_BASE_URL'),'',$REQUEST));
    if($REQUEST == '/'){
        $REQUEST = '';
    }

    if(preg_match("/\bconstructr\b/",$REQUEST)!==1){
        if($APP->get('CONSTRUCTR_CACHE')==1){
            $UNIQUE=$APP->get('CONSTRUCTR_FE_CACHE').md5($REQUEST).'.html';
            if(file_exists($UNIQUE)){
                $CACHE_OUTPUT=@file_get_contents($APP->get('CONSTRUCTR_FE_CACHE').md5($REQUEST).'.html');
                echo $CACHE_OUTPUT;
                die();
            }
        }

        if($REQUEST=='/' || $REQUEST==''){
            $APP->set('ACT_PAGE',$APP->get('DBCON')->exec(
                ['SELECT * FROM constructr_pages WHERE constructr_pages_order=:STARTPAGE_ORDER AND constructr_pages_nav_visible=1 LIMIT 1;'],
                [[':STARTPAGE_ORDER'=>1]]
            ));
        }else{
            $APP->set('ACT_PAGE',$APP->get('DBCON')->exec(
                ['SELECT * FROM constructr_pages WHERE constructr_pages_URL=:REQUEST AND constructr_pages_nav_visible=1 LIMIT 1;'],
                [[':REQUEST'=>$REQUEST]]
            ));
        }

        $ACT_PAGE_COUNTR=0;
        $ACT_PAGE_COUNTR=count($APP->get('ACT_PAGE'));

        if($ACT_PAGE_COUNTR==1){
            $PAGE_EXT_URL=$APP->get('ACT_PAGE.0.constructr_pages_ext_url');

            if($PAGE_EXT_URL!=''){
                header('HTTP/1.1 301 Moved Permanently');
                header('Location: '.$PAGE_EXT_URL);
                die();
            }

            $PAGE_ID=$APP->get('ACT_PAGE.0.constructr_pages_id');
            $PAGE_NAME=$APP->get('ACT_PAGE.0.constructr_pages_name');
            $PAGE_TEMPLATE=$APP->get('ACT_PAGE.0.constructr_pages_template');

            if($APP->get('COMPRESSOR_CSS')==1){
                $PAGE_CSS=$APP->get('ACT_PAGE.0.constructr_pages_css');
            }else{
                $PAGE_CSS=$APP->get('ACT_PAGE.0.constructr_pages_css_uncompressed');
            }

            if($APP->get('COMPRESSOR_JS')==1){
                $PAGE_JS=$APP->get('ACT_PAGE.0.constructr_pages_js');
            }else{
                $PAGE_JS=$APP->get('ACT_PAGE.0.constructr_pages_js_uncompressed');
            }

            $PAGE_TITLE=$APP->get('ACT_PAGE.0.constructr_pages_title');
            $PAGE_DESCRIPTION=$APP->get('ACT_PAGE.0.constructr_pages_description');
            $PAGE_KEYWORDS=$APP->get('ACT_PAGE.0.constructr_pages_keywords');
            $NAVIGATION='';
            $APP->set('PAGES', $APP->get('DBCON')->exec(['SELECT * FROM constructr_pages WHERE constructr_pages_nav_visible=1 ORDER BY constructr_pages_order ASC;']));

            if($APP->get('PAGES')){$NAVIGATION=ConstructrBase::constructrNavGen($APP->get('CONSTRUCTR_BASE_URL'),$APP->get('PAGES'));}

            $TEMPLATE=file_get_contents($APP->get('TEMPLATES').$PAGE_TEMPLATE);

            if($APP->get('PAGES') && preg_match("/\bPAGE_NAVIGATION_UL_LI_CLASSES\b/i", $TEMPLATE)){
                $CONSTRUCTR_CLASSES_NAV=[];
                preg_match_all("/({{@ PAGE_NAVIGATION_UL_LI_CLASSES\((\n|.)*?\)) @}}/",$TEMPLATE,$MATCH_NAV);

                if($MATCH_NAV[0]){
                    $z=0;
                    foreach($MATCH_NAV[0] AS $MATCHR_NAV){
                        $CONSTRUCTR_CLASSES_NAV[$z]=$MATCHR_NAV;
                        $z++;
                    }

                    $TMP_CLASSES_NAV=str_replace('{{@ PAGE_NAVIGATION_UL_LI_CLASSES(','',$CONSTRUCTR_CLASSES_NAV[0]);
                    $CLASSES_NAV=str_replace(') @}}','',$TMP_CLASSES_NAV);
                    $PARTS=explode(',',$CLASSES_NAV);
                    $CLASSES_NAVIGATION=ConstructrBase::constructrNavGenClasses($REQUEST,$APP->get('CONSTRUCTR_BASE_URL'),$APP->get('PAGES'),trim($PARTS[0]),trim($PARTS[1]),trim($PARTS[2]),trim($PARTS[3]));
                    $TEMPLATE=str_replace($MATCH_NAV[0],$CLASSES_NAVIGATION,$TEMPLATE);
                }
            }

            if($APP->get('PAGES') && preg_match("/\bCONSTRUCTR_LINK\b/i",$TEMPLATE)){
                $CONSTRUCTR_LINKS=[];
                preg_match_all("/({{@ CONSTRUCTR_LINK\((\n|.)*?\)) @}}/",$TEMPLATE,$MATCH_LINK);

                if($MATCH_LINK[0]){
                    $z=0;
                    foreach($MATCH_LINK[0] AS $ML){
                        $TL=str_replace('{{@ CONSTRUCTR_LINK(','',$ML);
                        $LINK=str_replace(') @}}','',$TL);
                        $CONSTRUCTR_LINKS[$z]=trim($LINK);
                        $z++;
                    }

                    $LINKS=ConstructrBase::constructrLinkGen($APP,$APP->get('DBCON'),$APP->get('CONSTRUCTR_BASE_URL'),$CONSTRUCTR_LINKS);

                    foreach($LINKS AS $KEY=>$LINK){
                        $TEMPLATE=str_replace('{{@ CONSTRUCTR_LINK('.$KEY.') @}}',$LINK,$TEMPLATE);
                    }
                }
            }

            if($APP->get('PAGES') && preg_match("/\bSUBNAV_PAGE\b/i", $TEMPLATE)){
                $SUBNAV_PAGES='';
                $SUBNAV_PAGES=ConstructrBase::constructrSubnavPages($APP,$REQUEST,$APP->get('DBCON'),$APP->get('CONSTRUCTR_BASE_URL'));
                $TEMPLATE=str_replace('{{@ SUBNAV_PAGE @}}',$SUBNAV_PAGES,$TEMPLATE);
            }

            if($APP->get('PAGES') && preg_match("/\bFIRST_LEVEL_NAV\b/i", $TEMPLATE)){
                $FIRST_LEVEL_NAV='';
                $FIRST_LEVEL_NAV=ConstructrBase::constructrFirstLevelNav($APP,$REQUEST,$APP->get('DBCON'),$APP->get('CONSTRUCTR_BASE_URL'));
                $TEMPLATE=str_replace('{{@ FIRST_LEVEL_NAV @}}',$FIRST_LEVEL_NAV,$TEMPLATE);
            }

            if($APP->get('PAGES') && preg_match("/\bSECOND_LEVEL_NAV\b/i", $TEMPLATE)){
                $SECOND_LEVEL_NAV='';
                $SECOND_LEVEL_NAV=ConstructrBase::constructrSecondLevelNav($APP,$REQUEST,$APP->get('DBCON'),$APP->get('CONSTRUCTR_BASE_URL'));
                $TEMPLATE=str_replace('{{@ SECOND_LEVEL_NAV @}}',$SECOND_LEVEL_NAV,$TEMPLATE);
            }

            if($APP->get('PAGES') && preg_match("/\bTHIRD_LEVEL_NAV\b/i", $TEMPLATE)){
                $THIRD_LEVEL_NAV='';
                $THIRD_LEVEL_NAV=ConstructrBase::constructrThirdLevelNav($APP,$REQUEST,$APP->get('DBCON'),$APP->get('CONSTRUCTR_BASE_URL'));
                $TEMPLATE=str_replace('{{@ THIRD_LEVEL_NAV @}}',$THIRD_LEVEL_NAV,$TEMPLATE);
            }

            $APP->set('CONTENT',$APP->get('DBCON')->exec(
                ['SELECT * FROM constructr_content WHERE constructr_content_page_id=:PAGE_ID AND constructr_content_visible=:VISIBILITY AND constructr_content_tpl_id_mapping=:NULLER ORDER BY constructr_content_order ASC;'],
                [[
                    ':PAGE_ID'=>$PAGE_ID,
                    ':NULLER'=>'',
                    ':VISIBILITY'=>1
                ]]
            ));

            $CONTENT_COUNTR=0;
            $CONTENT_COUNTR=count($APP->get('CONTENT'));
            $PAGE_CONTENT_HTML='';
            $PAGE_CONTENT_RAW='';

            if($CONTENT_COUNTR!=0){
                foreach($APP->get('CONTENT') AS $CONTENT){
                    $PAGE_CONTENT_RAW.=$CONTENT['constructr_content_content_raw'];
                    $PAGE_CONTENT_HTML.=$CONTENT['constructr_content_content_html'];
                }
            }

            $SEARCHR=['{{@ CONSTRUCTR_BASE_URL @}}','{{@ PAGE_ID @}}','{{@ PAGE_TEMPLATE @}}','{{@ PAGE_NAME @}}','{{@ PAGE_CONTENT_RAW @}}','{{@ PAGE_CONTENT_HTML @}}','{{@ PAGE_CSS @}}','{{@ PAGE_JS @}}','{{@ PAGE_NAVIGATION_UL_LI @}}','{{@ CONSTRUCTR_PAGE_TITLE @}}','{{@ CONSTRUCTR_PAGE_KEYWORDS @}}','{{@ CONSTRUCTR_PAGE_DESCRIPTION @}}'];
            $REPLACR=[$APP->get('CONSTRUCTR_BASE_URL'),$PAGE_ID,$PAGE_TEMPLATE,$PAGE_NAME,$PAGE_CONTENT_RAW,$PAGE_CONTENT_HTML,$PAGE_CSS,$PAGE_JS,$NAVIGATION,$PAGE_TITLE,$PAGE_DESCRIPTION,$PAGE_KEYWORDS];
            $TEMPLATE=str_replace($SEARCHR,$REPLACR,$TEMPLATE);

            $APP->set('MAPPING_CONTENT',$APP->get('DBCON')->exec(
                ['SELECT * FROM constructr_content WHERE constructr_content_page_id=:PAGE_ID AND constructr_content_visible=:VISIBILITY AND constructr_content_tpl_id_mapping!=:NULLER ORDER BY constructr_content_order ASC;'],
                [[
                    ':PAGE_ID'=>$PAGE_ID,
                    ':NULLER'=>'',
                    ':VISIBILITY'=>1
                ]]
            ));

            $CONSTRUCTR_TPL_MAPPINGS=[];

            if(count($APP->get('MAPPING_CONTENT'))!=0){
                preg_match_all("/({{@ CONSTRUCTR_MAPPING\()+([\w-])+(\) @}})/",$TEMPLATE,$MATCH);
                $CONSTRUCTR_TPL_MAPPINGS=[];

                if($MATCH[0]){
                    $i=0;
                    foreach($MATCH[0] AS $MATCHR){
                        $CONSTRUCTR_TPL_MAPPINGS[$i]=$MATCHR;
                        $i++;
                    }

                    if($CONSTRUCTR_TPL_MAPPINGS){
                        $MAPPERS=[];

                        foreach($APP->get('MAPPING_CONTENT') AS $KEY=>$MAPPING_CONTENT){
                            if(!isset($MAPPERS[$MAPPING_CONTENT['constructr_content_tpl_id_mapping']]) || $MAPPERS[$MAPPING_CONTENT['constructr_content_tpl_id_mapping']]==''){
                                $MAPPERS[$MAPPING_CONTENT['constructr_content_tpl_id_mapping']]=$MAPPING_CONTENT['constructr_content_content_html'];
                            }else{
                                $MAPPERS[$MAPPING_CONTENT['constructr_content_tpl_id_mapping']]=$MAPPERS[$MAPPING_CONTENT['constructr_content_tpl_id_mapping']].$MAPPING_CONTENT['constructr_content_content_html'];
                            }
                        }

                        $MAPPERS=array_filter($MAPPERS,'strlen');

                        foreach($CONSTRUCTR_TPL_MAPPINGS AS $MAP_NOW_KEY=>$MAP_NOW_VALUE){
                            if(isset($MAPPERS[$MAP_NOW_VALUE])){
                                $TEMPLATE=str_replace($MAP_NOW_VALUE,$MAPPERS[$MAP_NOW_VALUE],$TEMPLATE);
                            } else {
                                $TEMPLATE=str_replace($MAP_NOW_VALUE,'',$TEMPLATE);
                            }
                        }
                    }
                }
            } else {
                preg_match_all("/({{@ CONSTRUCTR_MAPPING\()+([\w-])+(\) @}})/",$TEMPLATE,$MATCH);

                foreach($MATCH[0] AS $MATCHR){
                    $TEMPLATE=str_replace($MATCHR,'',$TEMPLATE);
                }
            }

            if($APP->get('OUTPUT_COMPRESSION')==1){
                $replace=['/\>[^\S ]+/s'=>'>','/[^\S ]+\</s'=>'<','/([\t ])+/s'=>' ','/^([\t ])+/m'=>'','/([\t ])+$/m'=>'','~//[a-zA-Z0-9 ]+$~m'=>'','/[\r\n]+([\t ]?[\r\n]+)+/s'=>"\n",'/\>[\r\n\t ]+\</s'=>'><','/}[\r\n\t ]+/s'=>'}','/}[\r\n\t ]+,[\r\n\t ]+/s'=>'},','/\)[\r\n\t ]?{[\r\n\t ]+/s'=>'){','/,[\r\n\t ]?{[\r\n\t ]+/s'=>',{','/\),[\r\n\t ]+/s'=>'),','~([\r\n\t ])?([a-zA-Z0-9]+)="([a-zA-Z0-9_/\\-]+)"([\r\n\t ])?~s'=>'$1$2=$3$4'];
                $TEMPLATE=preg_replace(array_keys($replace),array_values($replace),$TEMPLATE);

                if($APP->get('COMPRESSOR_HTML5')==1){
                    $remove=array('</option>','</li>','</dt>','</dd>','</tr>','</th>','</td>');
                    $TEMPLATE=str_ireplace($remove,'',$TEMPLATE);
                }

                $TEMPLATE.="\n<!--ConstructrCMS OutputCompression is active-->";
            }

            $TEMPLATE.="\n<!--ConstructrCMS | http://phaziz.com | http://constructr-cms.org-->";

            if($APP->get('CONSTRUCTR_CACHE')==true){@file_put_contents($UNIQUE=$APP->get('CONSTRUCTR_FE_CACHE').md5($REQUEST).'.html',$TEMPLATE."\n".'<!--ConstructrCMS cached '.date('Y-m-d H:i:s').'-->');}

            echo $TEMPLATE;
            die();
        } else {
            $APP->get('CONSTRUCTR_LOG')->write('Frontend: 404->'.$REQUEST);
            $APP->reroute($APP->get('CONSTRUCTR_BASE_URL'));
        }
    } else {
		require_once __DIR__.'/CONSTRUCTR-CMS/LANG/'.$APP->get('CONSTRUCTR_BACKEND_LANGUAGE').'.php';

		foreach($CONSTRUCTR_LANG as $KEY => $SLANG){
			$APP->set('LANG'.$KEY,$SLANG);
		}

        if(!$APP->get('COOKIE.login') || $APP->get('COOKIE.login')=='false'){
            $APP->set('COOKIE.login','false');
            $APP->set('COOKIE.username','');
        }

        $APP->set('NAVIGATION','./CONSTRUCTR-CMS/TEMPLATES/constructr_navigation.html');
        $APP->set('DEBUG',3);
        $APP->set('CACHE',true);
        $APP->set('UPLOADS',__DIR__.'/UPLOADS/');

        require_once __DIR__.'/CONSTRUCTR-CMS/USER_RIGHTS/user_rights.php';
        require_once __DIR__.'/CONSTRUCTR-CMS/ROUTES/constructr_routes.php';

        $APP->set('ALL_CONSTRUCTR_USER_RIGHTS',$CONSTRUCTR_USER_RIGHTS);
    }

    $APP->set('levelIndicator',function($LEVEL,$RET=''){for ($i=1; $i<=$LEVEL; $i++){$RET.='&#160;&#160;&#160;';}return $RET;});
    $APP->set('ONERROR',function($APP){
        while (ob_get_level()){
            ob_end_clean();
        }

        $APP->get('CONSTRUCTR_LOG')->write($APP->get('ERROR.text').' - '.$APP->get('ERROR.code').': '.$APP->get('ERROR.status'));

        if($APP->get('ERROR.code')=='404'){
            $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/404');
        } else {
            $APP->reroute($APP->get('CONSTRUCTR_BASE_URL').'/constructr/error');
        }
    });

    $APP->run();
