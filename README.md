# pms
Opuzen Product Management System

File needed: index-set-env.php

Also add:

git clone git@github.com:jpillora/notifyjs.git
git clone git@github.com:pawelczak/EasyAutocomplete.git
git clone git@github.com:FortAwesome/Font-Awesome.git


<?php
/*
 * This can be set to anything, but default usage is:
 *
 *     development
 *     testing
 *     production
 *
 */
define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'development');
switch( $_SERVER['SERVER_ADDR'] ){
	case '205.186.152.193':
		$s = 'DV';
		break;
	case '72.47.244.137':
		$s = 'GRID';
		break;
}
define('MT_SERVER',  $s);
?>
