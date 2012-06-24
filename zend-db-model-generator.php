#!/usr/bin/php
<?php

if (!is_file(dirname(__FILE__).'/config/config-generic.php')){
    die("please copy config/config.php-default to config/config.php and modify.");
}

define('VERSION', '0.8-Beta1');
define('AUTHOR',  'Kfir Ozer <kfirufk@gmail.com>');

require_once('config/config-generic.php');
require_once('class/ArgvParser.php');


if (!ini_get('short_open_tag')) {
    die("please enable short_open_tag directive in php.ini\n");
}

if (!ini_get('register_argc_argv')) {
    die("please enable register_argc_argv directive in php.ini\n");
}


$parser = new ArgvParser($argv,AUTHOR,VERSION);
$params=$parser->checkParams();
if (sizeof($params['--zfv']) == 1) {
	$config['default_zend_framework_version']=$params['--zfv'][0];
}

switch ($config['default_zend_framework_version']) {
	case 1:
		require_once('class/MakeDbTablev1.php');
		break;
	case 2:
		require_once('class/MakeDbTablev2.php');
		break;
	default:
		die("error: default_zend_framework_version - bad parameter");
}

echo "about to create classes for zend framework".$config['default_zend_framework_version']."\n";
$db_type = $config['db.type'];
$class = 'Make_' . $db_type;
$include = @include_once (dirname(__FILE__).DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'Make.'. $db_type . '.php');
if (! $include || ! class_exists($class)) {
    die ("Database type specified is not supported\n");
}

switch ($config['default_zend_framework_version']) {
	case 1:
			$namespace=$config['namespace.default'];
			
			if (sizeof($params['--namespace']) == 1) {
				$namespace=$params['--namespace'][0];
			}
			
			$dbname=$params['--database'][0];
			$cls = new $class($config,$dbname,$namespace);
		break;
	case 2:
		
		$dbname=$params['--database'][0];
		$cls = new $class($config,$dbname);
		
		break;
	default:
		die("error: default_zend_framework_version - bad parameter");
}

$tables=array();
if ($params['--all-tables'] || sizeof($params['--tables-regex'])>0) {
    $tables=$cls->getTablesNamesFromDb();
}

$tables=$parser->compileListOfTables($tables, $params);
if (sizeof($tables) == 0) {
    die("error: please provide at least one table to parse.\n");
}

$path='';
//die(dirname(__FILE__).DIRECTORY_SEPARATOR.$params['--database'][0]);
if (sizeof($params['--location']) == 1) {
    // Check if a relative path
    if (! realpath($params['--location'][0])) {
        $path = realpath(dirname(__FILE__).DIRECTORY_SEPARATOR.$params['--location'][0]);
    } else {
        $path = realpath($params['--location'][0]);
    }
    $cls->setLocation($path);
    $path .= DIRECTORY_SEPARATOR;
} else {
    $cls->setLocation(dirname(__FILE__).DIRECTORY_SEPARATOR.$params['--database'][0]);
    $path=dirname(__FILE__).DIRECTORY_SEPARATOR.$params['--database'][0].DIRECTORY_SEPARATOR;
}

switch ($config['default_zend_framework_version']) {
	case 1:
		foreach (array('DbTable', 'mappers') as $name) {
		    $dir = $path . $name;
		    if (!is_dir($dir)) {
		        if (!@mkdir($dir,0755,true)) {
		            die("error: could not create directory $dir\n");
		        }
		    }
		}
		break;
	case 2:
		if (!is_dir($path)) {
			if (!@mkdir($path,0755,true)) {
				die("error: could not create directory $path\n");
			}
		}
		break;
}


$cls->setTableList($tables);

foreach ($tables as $table) {
    $cls->setTableName($table);
    try {
        $cls->parseTable();
        $cls->doItAll();
    } catch (Exception $e) {
        echo "Warining: Failed to process $table: " . $e->getMessage(). " ... Skipping\n";
    }

}


echo "done!\n";

