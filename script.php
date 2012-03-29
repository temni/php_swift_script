#!/usr/bin/env php
<?php
require 'config.php';
/* $host_; $user; $key*/
require 'includes/cloudfiles.php';
require 'includes/parser.php';

	$argvParser = ArgvParser::getInstance();
	$argc = (count($_SERVER['argv']));
	if ($argc == 0) 
		{
		echo "Ошибка! Не задано ни одного аргумента. Список возможных аргументов: \n";
		echo "\t--dir=папка_для_загрузки\n";
		echo "\t--user=имя_пользователя\n";
		echo "\t--key=ключ\n";
		echo "\t--host=удаленный сервер\n";
		echo "\t--rm-files удалять файлы после загрузки\n";
		echo "\t--container=имя_удаленного_контейнера\n";
		echo "\t-s      только показать список контейнеров\n";
		die();
		}
	if ((!$argvParser->isExistOption('dir'))&&(!$argvParser->isExistFlag('s'))) die("Ошибка! Не введён обязательный параметр dir\n");
	
	if ($argvParser->isExistOption('user')) $user = $argvParser->getOption('user');
	if ($argvParser->isExistOption('key'))  $key = $argvParser->getOption('key');
	if ($argvParser->isExistOption('host')) $host_ = $argvParser->getOption('host');

	$create_new = !$argvParser->isExistOption('container');
	$rm = !$argvParser->isExistOption('rm-files');
	
	$auth = new CF_Authentication($user, $key, '', $host_);
	try 
	{
		$auth->authenticate();
	}
	catch(Exception $e)
	{
		die("Auth error! Check username and password in your config file\n");
	}

	$conn = new CF_Connection($auth);
	if (!$conn) die("Something goes wrong\n");

	if ($argvParser->isExistFlag('s')) 
	{
		$conts = $conn->get_containers();
		echo "Containers count is ".count($conts)."\n";
		$i = 1;
		foreach($conts as $val)
			echo $i++." :'$val->name' => $val->object_count objects\n";
		die();
	}
	
	$dir = $argvParser->getOption('dir');
	$container_name = $argvParser->isExistOption('container')?$argvParser->getOption('container'):$dir;

	if (!$create_new) 
		{
			try
			{
				$container = $conn->get_container($container_name);
			}
			catch(Exception $e)
			{
				die("Указанный контейнер $container_name не существует\n");
			}
		}
		else 
		{
			try
			{
				$container = $conn->create_container($container_name);
			}
			catch(Exception $e)
			{
				die("Не удалось создать указанный контейнер $container_name\n");
			}
		}
	print_r($container->get_objects());
	$list_ = scandir($dir);	
	print_r($list_);	
