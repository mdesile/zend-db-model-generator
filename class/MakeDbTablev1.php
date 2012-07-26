<?php

require_once('MakeDbTableAbstract.php');

/**
 * main class for files creation
 */
abstract class MakeDbTable extends MakeDbTableAbstract {

	/**
	 *   @var Boolean $_addRequire;
	 */
	protected $_addRequire;

	
	/**
	 *   @var String $_includePath;
	 */
	protected $_includePath;
	
	
	public function setIncludePath($path) {
		$this->_includePath = $path;
	}
	
	/**
	 *
	 * @return string
	 */
	public function getIncludePath() {
		return $this->_includePath;
	}
	
	
	
	/**
	 *
	 *  the class constructor
	 *
	 * @param Array $config
	 * @param String $dbname
	 * @param String $namespace
	 */
	function __construct($config,$dbname,$namespace) {
		parent::__construct($config, $dbname,$namespace);
		$this->_addRequire = $config['include.addrequire'];
		$path = $this->_config['include.path'];
		
		
		if ( ! is_dir($path)) {
			// Use path relative to root of the application
			$path = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $this->_config['include.path'];
		}
		
		$this->setIncludePath($path . DIRECTORY_SEPARATOR);
		
		if (file_exists($this->getIncludePath() . 'IncludeDefault.php')) {
			require_once $this->getIncludePath() . 'IncludeDefault.php';
		} else {
			require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'IncludeDefault.php';
		}
		
	}
	

	/**
	 * creates the DbTable class file
	 */
	function makeDbTableFile() {

		$class = 'DbTable_' . $this->_className;
		$file = $this->getIncludePath() . $class . '.inc.php';
		if (file_exists($file)) {
			include_once $file;
			$include = new $class($this->_namespace);
			$this->_includeTable = $include;
		} else {
			$this->_includeTable = new DbTable_Default($this->_namespace);
		}

		$referenceMap='';
		$dbTableFile=$this->getLocation().DIRECTORY_SEPARATOR.'DbTable'.DIRECTORY_SEPARATOR.$this->_className.'.php';

		$foreignKeysInfo=$this->getForeignKeysInfo();
		$references=array();
		foreach ($foreignKeysInfo as $info) {
			$refTableClass = $this->_namespace . '_Model_DbTable_' . $this->_getClassName($info['foreign_tbl_name']);
			$key = $this->_getCapital($info['key_name']);
			if (is_array($info['column_name'])) {
			    $columns = 'array(\'' . implode("', '", $info['column_name']) . '\')';
			} else {
			    $columns = "'" . $info['column_name'] . "'";
			}
			if (is_array($info['foreign_tbl_column_name'])) {
			    $refColumns = 'array(\'' . implode("', '", $info['foreign_tbl_column_name']) . '\')';
			} else {
			    $refColumns = "'" . $info['foreign_tbl_column_name'] . "'";
			}

			$references[]="
        '$key' => array(
          	'columns' => {$columns},
            'refTableClass' => '{$refTableClass}',
            'refColumns' => {$refColumns}
        )";
		}

		if (sizeof($references)>0) {
			$referenceMap="protected \$_referenceMap = array(".
			join(',',$references). "\n    );";
		}

		$dependentTables = '';
		$dependents = array();
		foreach ($this->getDependentTables() as $info) {
			$dependents[] = $this->_getClassName($info['foreign_tbl_name']);
		}

		if (sizeof($dependents) > 0) {
			$dependentTables = "protected \$_dependentTables = array(\n        '".
			join("',\n        '",$dependents). "'\n    );";
		}

		$vars = array('referenceMap' => $referenceMap, 'dependentTables' => $dependentTables);

		$dbTableData=$this->getParsedTplContents('dbtable.tpl', 1,$vars);

		if (!file_put_contents($dbTableFile,$dbTableData))
			die("Error: could not write db table file $dbTableFile.");

	}

	/**
	 * creates the Mapper class file
	 */
	function makeMapperFile() {

		$class = 'Mapper_' . $this->_className;
		$file = $this->getIncludePath() . $class . '.inc.php';
		if (file_exists($file)) {
			include_once $file;
			$include = new $class($this->_namespace);
			$this->_includeMapper = $include;
		} else {
			$this->_includeMapper = new Mapper_Default($this->_namespace);
		}

		$mapperFile=$this->getLocation().DIRECTORY_SEPARATOR.'mappers'.DIRECTORY_SEPARATOR.$this->_className.'.php';

		$mapperData=$this->getParsedTplContents('mapper.tpl',1);

		if (!file_put_contents($mapperFile,$mapperData)) {
			die("Error: could not write mapper file $mapperFile.");
		}
	}

	/**
	 * creates the model class file
	 */
	function makeModelFile() {

		$class = 'Model_' . $this->_className;
		$file = $this->getIncludePath() . $class . '.inc.php';
		if (file_exists($file)) {
			include_once $file;
			$include = new $class($this->_namespace);
			$this->_includeModel = $include;
		} else {
			$this->_includeModel = new Model_Default($this->_namespace);
		}

		$modelFile=$this->getLocation().DIRECTORY_SEPARATOR.$this->_className.'.php';

		$modelData=$this->getParsedTplContents('model.tpl',1);

		if (!file_put_contents($modelFile,$modelData)) {
			die("Error: could not write model file $modelFile.");
		}
	}

	/**
	 *
	 * creates all class files
	 *
	 * @return Boolean
	 */
	function doItAll() {

		$this->makeDbTableFile();
		$this->makeMapperFile();
		$this->makeModelFile();

		$modelFile=$this->getLocation().DIRECTORY_SEPARATOR.'ModelAbstract.php';
		$modelData=$this->getParsedTplContents('model_class.tpl',1);

		if (!file_put_contents($modelFile, $modelData))
			die("Error: could not write model file $modelFile.");

		$paginatorFile=$this->getLocation().DIRECTORY_SEPARATOR.'Paginator.php';
		$paginatorData=$this->getParsedTplContents('paginator_class.tpl',1);

		if (!file_put_contents($paginatorFile, $paginatorData))
			die("Error: could not write model file $paginatorFile.");

		$mapperFile=$this->getLocation().DIRECTORY_SEPARATOR.'mappers'.DIRECTORY_SEPARATOR.'MapperAbstract.php';
		$mapperData=$this->getParsedTplContents('mapper_class.tpl',1);

		if (!file_put_contents($mapperFile, $mapperData))
			die("Error: could not write mapper file $mapperFile.");

		$tableFile=$this->getLocation().DIRECTORY_SEPARATOR.'DbTable'.DIRECTORY_SEPARATOR.'TableAbstract.php';
		$tableData=$this->getParsedTplContents('dbtable_class.tpl',1);

		if (!file_put_contents($tableFile, $tableData))
			die("Error: could not write model file $tableFile.");

		// Copy all files in include paths
		if (is_dir($this->getIncludePath() . 'model')) {
			$this->copyIncludeFiles($this->getIncludePath() . 'model', $this->getLocation());
		}

		if (is_dir($this->getIncludePath() . 'mapper')) {
			$this->copyIncludeFiles($this->getIncludePath() . 'mapper', $this->getLocation() . 'mappers');
		}

		if (is_dir($this->getIncludePath() . 'dbtable')) {
			$this->copyIncludeFiles($this->getIncludePath() . 'dbtable', $this->getLocation() . 'DbTable');
		}

/*		$templatesDir=realpath(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'templates').DIRECTORY_SEPARATOR;

		if (!file_put_contents($modelFile,$modelData))
			die("Error: could not write model file $modelFile.");

		if (!copy($templatesDir.'model_class.tpl',$this->getLocation().DIRECTORY_SEPARATOR.'MainModel.php'))
			die("could not copy model_class.tpl as MainModel.php");
		if (!copy($templatesDir.'dbtable_class.tpl',$this->getLocation().DIRECTORY_SEPARATOR.'DbTable'.DIRECTORY_SEPARATOR.'MainDbTable.php'))
			die("could not copy dbtable_class.php as MainDbTable.php");
*/
		return true;

	}

	protected function copyIncludeFiles($dir, $dest)
	{
	    $files = array();
	    $directory = opendir($dir);

	    while ($item = readdir($directory)){
		    // Ignore hidden files ('.' as first character)
	    	if (preg_match('/^\./', $item)) {
	        	continue;
	        }

	        copy($dir . DIRECTORY_SEPARATOR . $item, $dest . DIRECTORY_SEPARATOR . $item);
	    }
	    closedir($directory);
	}

}
