#!/usr/bin/env php
<?php
require 'config.php';
/* $host_; $user; $key*/
require 'includes/cloudfiles.php';
require 'includes/parser.php';
	
	function isConsistZeros($str)
	{
		return (!(strrpos($str,'00000000')===false));
	}
	
	function createStruct($folder, $cont, $fname, $skip, $f=false)
	{
		if (!$f)
		{
		$obj = $cont->create_object($fname);
		$obj->content_type = 'application/directory';
		$obj->write(' ');
		}
		echo "Uploading $fname objects...\n";
		
		$list_ = scandir($folder);	
		unset($list_[0],$list_[1]);
		foreach($list_ as $val)
		{
			if (filetype($folder.'/'.$val)=='dir')
			{
				createStruct($folder.'/'.$val,$cont,(!$f)?$fname.'/'.$val:$val, $skip);
			}
			else
			{
				echo "Uploading $fname/$val file...\n";
				if (!(($skip) && (isConsistZeros($val))))
				{
				$obj = $cont->create_object((!$f)?$fname.'/'.$val:$val);
				$obj->load_from_filename($folder.'/'.$val);
				}
				else echo "File $fname/$val skipped!\n";
			}
		}
	}

	 function rrmdir($dir, $skip) {
	   if (is_dir($dir)) {
	     $objects = scandir($dir);
	     foreach ($objects as $object) {
	       if ($object != "." && $object != "..") {
	         if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else { if (!(($skip) && (isConsistZeros($val)))) unlink($dir."/".$object);}
	       }
	     }
	     reset($objects);
	     rmdir($dir);
	   }
	 }

	$argvParser = ArgvParser::getInstance();
	$argc = (count($_SERVER['argv']));
	if ($argc == 1) 
		{
		echo "Sorry, you have to use this script only with params: \n";
		echo "\t--dir=local_dir_to_upload\n";
		echo "\t--user=username\n";
		echo "\t--key=user_key\n";
		echo "\t--host=remote_cloud_server\n";
		echo "\t--rm-files=[false/true] rm local files after upload\n";
		echo "\t--skip-zeros=[false/true] do not upload files with names terminated 8 zeros\n";
		echo "\t--container=remote_container_name\n";
		echo "\t-s      list remote conrainers\n";
		echo "\t-o      list objects in remote container (USE ONLY WITH --container param)\n";
		die();
		}
	if (!$argvParser->isExistOption('dir')&&!$argvParser->isExistFlag('o')
		&&!$argvParser->isExistFlag('s')) die("Error! argument --dir was not received\n");
	
	if ($argvParser->isExistOption('user')) $user = $argvParser->getOption('user');
	if ($argvParser->isExistOption('key'))  $key = $argvParser->getOption('key');
	if ($argvParser->isExistOption('host')) $host_ = $argvParser->getOption('host');

	$create_new = !$argvParser->isExistOption('container');
	$rm = $argvParser->isExistOption('rm-files');
	
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

	$dir=''; $fname = '';
	if (!$argvParser->isExistFlag('o')) $dir = $argvParser->getOption('dir');
	if (strrpos($dir,'/')==(strlen($dir)-1)) $dir = substr($dir,0,strlen($dir)-1);
	if (!(strpos($dir,'/')===false)) $fname = substr($dir,strrpos($dir,'/')+1);
		else $fname = $dir;
	$container_name = $argvParser->isExistOption('container')?$argvParser->getOption('container'):$fname;

			try
			{
				$container = $conn->create_container($container_name);
			}
			catch(Exception $e)
			{
				die("Couldn't find $container_name on remote host\n");
			}

	if ($argvParser->isExistFlag('o')) 
	{
		$conts = $container->get_objects();
		echo "Container $container->name consists ".count($conts)."\n";
		$i = 1;
		foreach($conts as $val)
			echo $i++." :'$val->name' => $val->content_type\n";
		die();
	}
	$skip = $argvParser->isExistOption('skip-zeros');
	createStruct($dir, $container, $fname, $skip, true);
	echo "All data was uploaded\n";
	$skip = $argvParser->isExistOption('skip-zeros');
	if ($rm) 
	{
		rrmdir($dir,$skip);
		echo "Local data was removed\n";
	}
	
