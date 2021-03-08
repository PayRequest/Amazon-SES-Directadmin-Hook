#!/usr/local/bin/php -c/usr/local/directadmin/plugins/redis_management/php/php.ini
<?php
	require_once dirname(dirname(dirname(__DIR__))) . '/php/bootstrap.php';
	
	$domain = $argv[1];
	$amazonsesController = new \DirectAdmin\AmazonSes\Controllers\AmazonsesController();
	echo $amazonsesController->getFirstStep($domain);
	?>