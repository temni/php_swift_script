<?php
require 'config.php';
/* $host_; $user; $key*/
require 'includes/cloudfiles.php';

	$auth = new CF_Authentication($user, $key, '', $host_);
	try 
	{
		$auth->authenticate();
	}
	catch(Exception $e)
	{
		die('Auth error! Check username and password in your config file');
	}

	$conn = new CF_Connection($auth);
	if (!$conn) die('Something goes wrong');

	$container_list = $conn->list_containers();
	print_r($container_list);
