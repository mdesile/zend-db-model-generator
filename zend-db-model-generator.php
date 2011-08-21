#!/usr/bin/php
<?php
if (!is_file(dirname(__FILE__).'/config/config.php')) {
    die("please copy config/config.php-default to config/config.php and modify.");
}

define('VERSION', '0.6');
define('AUTHOR',  'Kfir Ozer <kfirufk@gmail.com>');

require_once('class/MakeDbTable.php');
require_once('class/ArgvParser.php');
require_once('config/config.php');

if (!ini_get('short_open_tag')) {
    die("please enable short_open_tag directive in php.ini\n");
}

if (!ini_get('register_argc_argv')) {
    die("please enable register_argc_argv directive in php.ini\n");
}

$db_type = $config['db.type'];
$class = 'Make_' . $db_type;
$include = @include_once (__DIR__.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'Make.'. $db_type . '.php');
if (! $include || ! class_exists($class)) {
    die ("Database type specified is not supported\n");
}

$parser = new ArgvParser($argv,AUTHOR,VERSION);
$params=$parser->checkParams();

$namespace=$config['namespace.default'];

if (sizeof($params['--namespace']) == 1) {
    $namespace=$params['--namespace'][0];
}

$dbname=$params['--database'][0];
$cls = new $class($config,$dbname,$namespace);
$tables=array();
if ($params['--all-tables'] || sizeof($params['--tables-regex'])>0) {
    $tables=$cls->getTablesNamesFromDb();
}

$tables=$parser->compileListOfTables($tables, $params);

if (sizeof($tables) == 0) {
    die("error: please provide at least one table to parse.\n");
}

$path='';

if (sizeof($params['--location']) == 1) {
    // Check if a relative path
    if (! realpath($params['--location'][0])) {
        $path = realpath(__DIR__.DIRECTORY_SEPARATOR.$params['--location'][0]);
    } else {
        $path = realpath($params['--location'][0]);
    }
    $cls->setLocation($path);
    $path .= DIRECTORY_SEPARATOR;
} else {
    $cls->setLocation(__DIR__.DIRECTORY_SEPARATOR.$params['--database'][0]);
    $path=__DIR__.DIRECTORY_SEPARATOR.$params['--database'][0].DIRECTORY_SEPARATOR;
}

foreach (array('DbTable', 'mappers') as $name) {
    $dir = $path . $name;
    if (!is_dir($dir)) {
        if (!@mkdir($dir,0755,true)) {
            die("error: could not create directory $dir\n");
        }
    }
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

