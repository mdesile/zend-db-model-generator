<?php

class ArgvParser {

    private $_argv;
    private $_author;
    private $_version;

    function __construct($argv,$author,$version) {

        $this->_argv=$argv;
        $this->_author=$author;
        $this->_version=$version;

    }

    function getUsage() {
        return <<<USAGE
parameters:
    --database            : database name
    --location            : specify where to create the files (default is current directory)
    --namespace           : override config file's default namespace
 *  --table               : table name (parameter can be used more then once)
    --all-tables          : create classes for all the scripts in the database
 *  --ignore-table        : not to create a class for a specific table
 *  --ignore-tables-regex : ignore tables by perl regular expression
 *  --tables-regex        : add tables by perl regular expression

                    parameters with * can be used more then once.
 example: zdmg.php --database foo --table foobar --tables-regex ^bar_ --ignore-table bar_abc

zend-db-model-generator By: $this->_author Version: $this->_version

USAGE;
    }

    public function checkParams() {
        $params=array(
                '--database'=>array(),
                '--namespace'=>array(),
                '--location'=>array(),
                '--table'=>array(),
                '--all-tables'=>false,
                '--ignore-table'=>array(),
                '--ignore-tables-regex'=>array(),
                '--tables-regex'=>array()
            );
        $argv=$this->_argv;
        array_shift($argv);
        while(sizeof($argv)>0) {
                $param=array_shift($argv);
            if (in_array($param,array_keys($params))) {
                if ($param == '--all-tables')
                    $params['--all-tables']=true;
                else
                    $params[$param][]=array_shift($argv);
            } else die ("error: unknown parameter '$param'\n".$this->getUsage());
        }
        if (sizeof($params['--database']) != 1)
                die("error: please provide one database parameter\n".$this->getUsage());
        if (sizeof($params['--namespace'])>1)
                die("error: namespace parameter can't be used more than once\n".$this->getUsage());
        if (sizeof($params['--location'])>1)
                die("error: location parameter can't be used more than once\n".$this->getUsage());
        return $params;

    }

    public function compileListOfTables($thetables,$params) {
        $tables=array();
        if ($params['--all-tables'])
            $tables=array_flip($thetables);
        foreach ($params['--table'] as $table)
            $tables[$table]=1;
        foreach ($params['--tables-regex'] as $regex) {
            foreach ($thetables as $table)
                if (preg_match("/$regex/",$table)>0) {
                 //   die("regex $regex for table $table");
                        $tables[$table]=1;
                }
        }
        foreach ($params['--ignore-table'] as $table)
            if (isset($tables[$table]))
                unset($tables[$table]);
        foreach ($params['--ignore-tables-regex'] as $regex) {
            foreach (array_keys($tables) as $table) {
                if (preg_match("/$regex/",$table)>0) {
                    unset($tables[$table]);
                }
            }
        }

        $res=array();
        
        foreach (array_keys($tables)  as $table)
            $res[]=$table;
       
        return $res;
    }

}