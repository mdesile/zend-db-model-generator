<?php

require_once('MakeDbTableAbstract.php');

abstract class MakeDbTable extends  MakeDbTableAbstract {

	function __construct($config,$dbname) {
		parent::__construct($config, $dbname);
	}
	
	function doItAll() {
		$fooFile=$this->getLocation().DIRECTORY_SEPARATOR.$this->_className.".php";
		$fooData=$this->getParsedTplContents('Foo.phtml', 2);
		$fooTableFile=$this->getLocation().DIRECTORY_SEPARATOR.$this->_className."Table.php";
		$fooTableData=$this->getParsedTplContents('FooTable.phtml', 2);
	
		$templatesDir=realpath(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'templates-v2').DIRECTORY_SEPARATOR;
		
		if (!file_put_contents($fooFile,$fooData))
			die("Error: could not write model file $fooFile.");
		if (!file_put_contents($fooTableFile,$fooTableData))
			die("Error: could not write model file $fooFile.");
		
		return true;
	
	}
	
	
}