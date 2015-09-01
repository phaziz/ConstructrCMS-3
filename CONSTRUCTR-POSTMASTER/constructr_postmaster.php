<?php

	$CONSTRUCTR_CONFIG = file_get_contents('../CONSTRUCTR-CMS/CONFIG/constructr_config.json');
	$CONSTRUCTR_CONFIG = json_decode($CONSTRUCTR_CONFIG, true);

	define('CONSTRUCTR_POSTMASTER_EMAIL',$CONSTRUCTR_CONFIG['CONSTRUCTR_POSTMASTER_EMAIL']);
	define('CONSTRUCTR_BASE_URL',$CONSTRUCTR_CONFIG['CONSTRUCTR_BASE_URL']);

	if(isset($_POST['constructr_postmaster']) && $_POST['constructr_postmaster']!=''){
		$SPAM=false;

        if(preg_match("/bcc:|cc:|multipart|\[url|Content-Type:/i",implode($_POST))){$SPAM=true;}

        if(preg_match_all("/<a|http:/i",implode($_POST),$out)>3){$SPAM=true;}

        if($SPAM==false){
            if($_POST['constructr_postmaster_guid']!=''){
                $_MAILTEXT=date('d.m.Y, H:i:s')." Uhr\n\n";

                foreach($_POST as $_POSTER_KEY=>$_POSTER_VALUE){
                    if(stristr($_POSTER_VALUE,"[link=") || stristr($_POSTER_VALUE,"[url=")){die();}

                    $_MAILTEXT.=$_POSTER_KEY.': '.$_POSTER_VALUE."\n";
                }

                @mail(CONSTRUCTR_POSTMASTER_EMAIL,'eMail Kontaktformular',$_MAILTEXT);

				if(!isset($_POST['constructr_redirect']) || $_POST['constructr_redirect']==''){
					header('Location: '.CONSTRUCTR_BASE_URL);
					die();
				}

				header('Location: '.$_POST['constructr_redirect']);
				die();
            }
        }
    }