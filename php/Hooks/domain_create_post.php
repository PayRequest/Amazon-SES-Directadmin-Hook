#!/usr/local/bin/php -c/usr/local/directadmin/plugins/amazon_ses/php/php.ini
<?php
	error_reporting(0);
	require_once '/usr/local/directadmin/plugins/amazon_ses/php/bootstrap.php';
	
	$domain = $argv[1];
	$amazonsesController = new \DirectAdmin\AmazonSes\Controllers\AmazonsesController();
	echo $amazonsesController->getFirstStep($domain);
	?>