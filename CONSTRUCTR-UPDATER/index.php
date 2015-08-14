<?php

	$CONSTRUCTR_CONFIG = file_get_contents('../CONSTRUCTR-CMS/CONFIG/constructr_config.json');
	$CONSTRUCTR_CONFIG = json_decode($CONSTRUCTR_CONFIG, true);

	header('Location: ' . $CONSTRUCTR_CONFIG['CONSTRUCTR_BASE_URL'] . '/constructr');
	