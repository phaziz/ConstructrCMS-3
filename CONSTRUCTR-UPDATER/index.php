<?php

	$CONSTRUCTR_CONFIG = file_get_contents('../CONSTRUCTR-CMS/CONFIG/constructr_config.json');
	$CONSTRUCTR_CONFIG = json_decode($CONSTRUCTR_CONFIG, true);

	define('DATABASE_HOSTNAME',$CONSTRUCTR_CONFIG['DATABASE_HOSTNAME']);
	define('DATABASE_DATABASE',$CONSTRUCTR_CONFIG['DATABASE_DATABASE']);
	define('DATABASE_USERNAME',$CONSTRUCTR_CONFIG['DATABASE_USERNAME']);
	define('DATABASE_PASSWORD',$CONSTRUCTR_CONFIG['DATABASE_PASSWORD']);

    try{
        $DBCON=new PDO('mysql:host='.DATABASE_HOSTNAME.';dbname='.DATABASE_DATABASE, DATABASE_USERNAME, DATABASE_PASSWORD, array(PDO::ATTR_PERSISTENT => true));
        $DBCON->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$QUERY="ALTER TABLE `constructr_pages` ADD `constructr_pages_css_uncompressed` TEXT NOT NULL AFTER `constructr_pages_css`; ALTER TABLE `constructr_pages` ADD `constructr_pages_js_uncompressed` TEXT NOT NULL AFTER `constructr_pages_js`;";
		$STMT=$DBCON->prepare($QUERY);
		$STMT->execute();

		header('Location: ' . $CONSTRUCTR_CONFIG['CONSTRUCTR_BASE_URL'] . '/constructr');
    } catch (PDOException $e){
    	header('Location: ' . $CONSTRUCTR_CONFIG['CONSTRUCTR_BASE_URL'] . '/constructr?error=true');    
    }