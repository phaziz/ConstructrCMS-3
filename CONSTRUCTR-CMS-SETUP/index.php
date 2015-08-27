<?php

    if (version_compare(phpversion(), '5.3.0', '<=')) {
        die('PHP ist kleiner als Version 5.3.0');
    }

    $TEST_CONFIG_FILE = '../CONSTRUCTR-CMS/CONFIG/constructr_config.json';
    fopen($TEST_CONFIG_FILE, 'w+') or die('FILE NOT FOUND ERROR: Please create file: CONSTRUCTR-CMS/CONFIG/constructr_config.json');

    function getCurrentUrl()
    {
        $ACT_URL = ((empty($_SERVER['HTTPS'])) ? 'http://' : 'https://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $ACT_URL = str_replace('/CONSTRUCTR-CMS-SETUP/', '', $ACT_URL);

        return $ACT_URL;
    }

    require_once '../CONSTRUCTR-CMS/USER_RIGHTS/user_rights.php';

    session_start();
    error_reporting(-1);

    function create_guid() {
        return mt_rand();
    }

    $NEW_GUID1 = create_guid();
    $NEW_GUID2 = create_guid();
    $NEW_GUID3 = create_guid();
    $NEW_GUID5 = create_guid();
    $NEW_GUID6 = create_guid();
    $NEW_GUID7 = create_guid();
    $NEW_GUID9 = create_guid();
    $NEW_GUID10 = create_guid();
    $NEW_GUID11 = create_guid();

if (isset($_POST['setup'])) {
$NL = "\n";
$_CONFIG_FILE_CONTENT = '
{
"CONSTRUCTR_VERSION":"3.0 / 2015-08-27",
"DATABASE_HOSTNAME":"'.$_POST['db_host'].'",
"DATABASE_DATABASE":"'.$_POST['db_database'].'",
"DATABASE_PORT":3306,
"DATABASE_USERNAME":"'.$_POST['db_user'].'",
"DATABASE_PASSWORD":"'.$_POST['db_password'].'",
"CONSTRUCTR_POSTMASTER_EMAIL":"'.$_POST['contact_email'].'",
"CONSTRUCTR_USER_SALT":"$2y$10$'.$_POST['salt1'].''.$_POST['salt2'].''.$_POST['salt3'].'$",
"CONSTRUCTR_BASE_URL":"'.$_POST['base_url'].'",
"CONSTRUCTR_REPLACE_BASE_URL":"'.$_POST['base_url'].'/"
}
';

    $FILE = '../CONSTRUCTR-CMS/CONFIG/constructr_config.json';
    $CREATE_CONFIG = fopen($FILE, 'w+') or die('FILE NOT FOUND ERROR: Please create file: CONSTRUCTR-CMS/CONFIG/constructr_config.json');
    fwrite($CREATE_CONFIG, trim($_CONFIG_FILE_CONTENT)) or die('ERROR 2');
    fclose($CREATE_CONFIG) or die('ERROR 3');

    try {
        $DBCON = new PDO('mysql:host='.$_POST['db_host'].';dbname='.$_POST['db_database'], $_POST['db_user'], $_POST['db_password'], array(PDO::ATTR_PERSISTENT => true));
        $DBCON->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $QUERY = "
CREATE TABLE `constructr_backenduser` (
  `constructr_user_id` int(25) NOT NULL,
  `constructr_user_username` varchar(255) NOT NULL,
  `constructr_user_password` varchar(255) NOT NULL,
  `constructr_user_salt` varchar(255) NOT NULL DEFAULT '',
  `constructr_user_factor` varchar(255) NOT NULL,
  `constructr_user_email` varchar(255) NOT NULL,
  `constructr_user_last_login` datetime NOT NULL,
  `constructr_user_active` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `constructr_backenduser` (`constructr_user_id`, `constructr_user_username`, `constructr_user_password`, `constructr_user_factor`, `constructr_user_email`, `constructr_user_last_login`, `constructr_user_active`) VALUES
(1, :USERNAME, :PASSWORD, '', :EMAIL, :LAST_LOGIN, 1);

CREATE TABLE `constructr_content` (
  `constructr_content_id` int(255) NOT NULL,
  `constructr_content_page_id` int(255) NOT NULL,
  `constructr_content_content_raw` text NOT NULL,
  `constructr_content_content_html` text NOT NULL,
  `constructr_content_tpl_id_mapping` varchar(255) DEFAULT '',
  `constructr_content_order` int(255) NOT NULL DEFAULT '0',
  `constructr_content_visible` int(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `constructr_pages` (
  `constructr_pages_id` int(255) NOT NULL,
  `constructr_pages_mother` int(255) NOT NULL DEFAULT '0',
  `constructr_pages_level` int(255) NOT NULL DEFAULT '1',
  `constructr_pages_order` int(255) NOT NULL DEFAULT '0',
  `constructr_pages_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `constructr_pages_name` varchar(255) NOT NULL DEFAULT '',
  `constructr_pages_url` varchar(255) NOT NULL DEFAULT '',
  `constructr_pages_ext_url` varchar(255) NOT NULL DEFAULT '',
  `constructr_pages_css` text NOT NULL,
  `constructr_pages_js` text NOT NULL,
  `constructr_pages_template` varchar(255) NOT NULL DEFAULT 'index.php',
  `constructr_pages_title` varchar(255) NOT NULL,
  `constructr_pages_description` text NOT NULL,
  `constructr_pages_keywords` text NOT NULL,
  `constructr_pages_active` int(1) NOT NULL DEFAULT '0',
  `constructr_pages_nav_visible` int(1) NOT NULL DEFAULT '1',
  `constructr_pages_temp_marker` int(10) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `constructr_user_rights` (
  `constructr_user_rights_id` int(255) NOT NULL,
  `constructr_user_rights_user` int(255) NOT NULL DEFAULT '0',
  `constructr_user_rights_key` int(255) NOT NULL DEFAULT '0',
  `constructr_user_rights_value` int(255) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=666 DEFAULT CHARSET=utf8;

INSERT INTO `constructr_user_rights` (`constructr_user_rights_id`, `constructr_user_rights_user`, `constructr_user_rights_key`, `constructr_user_rights_value`) VALUES
(1, 1, 10, 1),
(2, 1, 20, 1),
(3, 1, 30, 1),
(4, 1, 31, 1),
(5, 1, 32, 1),
(6, 1, 33, 1),
(7, 1, 34, 1),
(8, 1, 35, 1),
(9, 1, 40, 1),
(10, 1, 41, 1),
(11, 1, 42, 1),
(12, 1, 43, 1),
(13, 1, 44, 1),
(14, 1, 31, 1),
(15, 1, 50, 1),
(16, 1, 51, 1),
(17, 1, 52, 1),
(18, 1, 53, 1),
(19, 1, 54, 1),
(20, 1, 60, 1),
(21, 1, 61, 1),
(22, 1, 62, 1);

ALTER TABLE `constructr_backenduser`
  ADD PRIMARY KEY (`constructr_user_id`),
  ADD UNIQUE KEY `constructr_user_id` (`constructr_user_id`);

ALTER TABLE `constructr_content`
  ADD PRIMARY KEY (`constructr_content_id`),
  ADD UNIQUE KEY `constructr_content_id` (`constructr_content_id`);

ALTER TABLE `constructr_pages`
  ADD PRIMARY KEY (`constructr_pages_id`);

ALTER TABLE `constructr_user_rights`
  ADD PRIMARY KEY (`constructr_user_rights_id`),
  ADD UNIQUE KEY `constructr_user_rights_id` (`constructr_user_rights_id`);

ALTER TABLE `constructr_backenduser`
  MODIFY `constructr_user_id` int(25) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;

ALTER TABLE `constructr_content`
  MODIFY `constructr_content_id` int(255) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;

ALTER TABLE `constructr_pages`
  MODIFY `constructr_pages_id` int(255) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;

ALTER TABLE `constructr_user_rights`
  MODIFY `constructr_user_rights_id` int(255) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
";

        $STMT = $DBCON->prepare($QUERY);

        $USERNAME = trim($_POST['admin_username']);
        $PASSWORD = crypt(trim($_POST['admin_password']), '$2y$10$'.$_POST['salt1'].''.$_POST['salt2'].''.$_POST['salt3'].'$');
        $EMAIL = filter_var(trim($_POST['admin_email'], FILTER_VALIDATE_EMAIL));

        $STMT->execute(array(':USERNAME' => $USERNAME, ':PASSWORD' => $PASSWORD, ':EMAIL' => $EMAIL, ':LAST_LOGIN' => '0000-00-00 00:00:00'));

    } catch (PDOException $e) {
        die('<div class="alert alert-danger" role="alert">Fehler bei der Datenbankverbindung - Bitte Daten &uuml;berpr&uuml;fen ('.$e.')!</div>');
    }

    header('Location: '.$_POST['base_url'].'/constructr/');
    die();
}
?>

<!DOCTYPE html>
	<html>
	<head>
	    <title>Constructr CMS Installation</title>
	    <meta charset="UTF-8">
	    <meta name="description" content="Installation von Constructr CMS">
	    <meta name="keywords" content="Constructr CMS, phaziz.com, Christian Becher">
	    <link rel="stylesheet" href="<?php echo getCurrentUrl(); ?>/CONSTRUCTR-CMS/ASSETS/css/constructr.css">
	    <link rel="stylesheet" href="<?php echo getCurrentUrl(); ?>/CONSTRUCTR-CMS/ASSETS/materialize/css/materialize.min.css">
	    <style>
	    	#container{width: 65%;margin:5em auto;}
	    	a:link, a:active, a:visited{text-decoration: none;color:#444;}
	    	a:hover{text-decoration: none;color:#ff0066;}
	    	h1{font-weight: 200;}
	    </style>
	</head>
		<body>

			<div id="container">

				<div class="row">
					<div class="col-md-12" style="text-align:center;">
				  		<h1>ConstructrCMS</h1>
				  		<h3>Installation</h3>
					</div>
				</div>
				<br><br>
				<form class="form-horizontal" action="index.php" method="post" enctype="application/x-www-form-urlencoded" autocomplete="off">
				<input type="hidden" name="setup" value="try">
				<input type="hidden" name="salt1" id="salt1" value="<?php echo $NEW_GUID1.$NEW_GUID2.$NEW_GUID3; ?>" required="required">
				<input type="hidden" name="salt2" id="salt2" value="<?php echo $NEW_GUID5.$NEW_GUID6.$NEW_GUID7; ?>" required="required">
				<input type="hidden" name="salt3" id="salt3" value="<?php echo $NEW_GUID9.$NEW_GUID10.$NEW_GUID11; ?>" required="required">
				<div class="form-group">
					<label for="db_host" class="col-sm-2 control-label">Datenbank-Host:</label>
					<div class="col-sm-10">
						<input class="form-control" type="text" name="db_host" id="db_host" size="50" required="required" value="localhost" autofocus placeholder="Host der Datenbank">
						<small><span class="helpBlock" class="help-block">Zum Beispiel: <em><strong>localhost</strong></em></span></small>
						<br><br><br>
					</div>
				</div>
				<div class="form-group">
					<label for="db_database" class="col-sm-2 control-label">Datenbank-Name:</label>
					<div class="col-sm-10">
						<input class="form-control" type="text" name="db_database" id="db_database" size="50" required="required" placeholder="Name der Datenbank">
						<small><span class="helpBlock" class="help-block">Der Name der vorhandenen Datenbank - zum Beispiel: <em><strong>constructrcms</strong></em></span></small>
						<br><br><br>
					</div>
				</div>
				<div class="form-group">
					<label for="db_user" class="col-sm-2 control-label">Datenbank-Benutzer:</label>
					<div class="col-sm-10">
						<input class="form-control" type="text" name="db_user" id="db_user" size="50" required="required" placeholder="Datenbank-Benutzername">
						<small><span class="helpBlock" class="help-block">Der Name des vorhandenen Benutzers für die Datenbank.</span></small>
						<br><br><br>
					</div>
				</div>
				<div class="form-group">
					<label for="db_password" class="col-sm-2 control-label">Datenbank-Passwort:</label>
					<div class="col-sm-10">
						<input class="form-control" type="text" name="db_password" id="db_password" size="50" required="required" placeholder="Datenbank-Passwort">
						<small><span class="helpBlock" class="help-block">Das Passwort des vorhandenen Benutzers für die Datenbank.</span></small>
						<br><br><br>
					</div>
				</div>
				<div class="form-group">
					<label for="base_url" class="col-sm-2 control-label">Basis-URL:</label>
					<div class="col-sm-10">
						<?php

							$CURRENT_URL = getCurrentUrl();

						?>
						<input class="form-control" type="url" name="base_url" id="base_url" value="<?php echo $CURRENT_URL; ?>" size="50" required="required" placeholder="Basis-URL der Installation">
						<small><span class="helpBlock" class="help-block">Die Basis-URL für das künftige Frontend deiner ConstructrCMS-Installation.</span></small>
						<br><br><br>
					</div>
				</div>
				<div class="form-group">
					<label for="admin_username" class="col-sm-2 control-label">Administrator-Benutzername:</label>
					<div class="col-sm-10">
						<input class="form-control" type="text" name="admin_username" id="admin_username" value="" size="50" required="required" placeholder="Benutzername Administrator Account">
						<small><span class="helpBlock" class="help-block">Der gewünschte Benutzername für das Backend von deinem ConstructrCMS.</span></small>
						<br><br><br>
					</div>
				</div>
				<div class="form-group">
					<label for="admin_password" class="col-sm-2 control-label">Administrator-Passwort:</label>
					<div class="col-sm-10">
						<input class="form-control" type="text" name="admin_password" id="admin_password" value="" size="50" required="required" placeholder="Passwort Administrator Account">
						<small><span class="helpBlock" class="help-block">Das gewünschte Passwort für das Backend von deinem ConstructrCMS.</span></small>
						<br><br><br>
					</div>
				</div>
				<div class="form-group">
					<label for="admin_email" class="col-sm-2 control-label">Administrator-eMail:</label>
					<div class="col-sm-10">
						<input class="form-control" type="email" name="admin_email" id="admin_email" value="" size="50" required="required" placeholder="eMail Administrator Account">
						<small><span class="helpBlock" class="help-block">Die gewünschte eMail-Adresse für das Administrator-Account.</span></small>
						<br><br><br>
					</div>
				</div>
				<div class="form-group">
					<label for="admin_email" class="col-sm-2 control-label">eMail Kontaktformulare:</label>
					<div class="col-sm-10">
						<input class="form-control" type="email" name="contact_email" id="contact_email" value="" size="50" required="required" placeholder="eMail Kontaktformulare">
						<small><span class="helpBlock" class="help-block">Die gewünschte eMail-Adresse für Kontaktformulare.</span></small>
						<br><br><br>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12" style="text-align:center;">
					  	<input class="btn btn-primary btn-lg" type="submit" value="ConstructrCMS installieren">
					</div>
				</div>
				</form>
				<br><br><br><br>
				<div class="row">
					<div class="col-md-12" style="text-align:center;">
				  		<p><small>ConstructrCMS von <a href="http://phaziz.com">phaziz.com</a></small></p>
					</div>
				</div>
			</div>
			<script src="<?php echo getCurrentUrl(); ?>/CONSTRUCTR-CMS/ASSETS/jquery/jquery-2.1.4.min.js"></script>
			<script src="<?php echo getCurrentUrl(); ?>/CONSTRUCTR-CMS/ASSETS/materialize/js/materialize.min.js"></script>
		</body>
	</html>
