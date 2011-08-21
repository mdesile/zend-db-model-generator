<?php

/**
 * main class for files creation
 */
abstract class MakeDbTable {

	/**
	 *  @var String $_tbname;
	 */
	protected $_tbname;

	/**
	 *
	 *  @var String $_dbname;
	 */
	protected $_dbname;

	/**
	 *  @var PDO $_pdo;
	 */
	protected $_pdo;


	/**
	 *   @var Array $_columns;
	 */
	protected $_columns;


	/**
	 * @var String $_className;
	 */
	protected $_className;

	/**
	 * @var String $_classDesc;
	 */
	protected $_classDesc;

	/**
	 *   @var String|array $_primaryKey;
	 */
	protected $_primaryKey;

	/**
	 *   @var String $_namespace;
	 */
	protected $_namespace;

	/**
	 *  @var Array $_config;
	 */
	protected $_config;

	/**
	 *   @var Boolean $_addRequire;
	 */
	protected $_addRequire;

	/**
	 *   @var String $_author;
	 */
	protected $_author;

	/**
	 *   @var String $_license;
	 */
	protected $_license;

	/**
	 *   @var String $_copyright;
	 */
	protected $_copyright;

	/**
	 *   @var String $_includePath;
	 */
	protected $_includePath;


	/**
	 *   @var String $_includeModel;
	 */
	protected $_includeModel;

	/**
	 *   @var String $_includeTable
	 */
	protected $_includeTable;

	/**
	 *   @var String $_includeMapper
	 */
	protected $_includeMapper;

	/**
	 *
	 * @var String $_location;
	 */
	protected $_location;

	/**
	 * @var array $_tableList
	 */
	protected $_tableList;

	/**
	 *
	 * @var Array $_foreignKeysInfo
	 */
	protected $_foreignKeysInfo;

	/**
	 *
	 * @var Array $_dependentTables
	 */
	protected $_dependentTables;

	/**
	 * List of table name prefixes to automatically remove
	 * @var array
	 */
	protected $_tablePrefixes = array('tbl_', 'tbl', 't_', 'table');

	/**
	 * List of column name suffixes to automatically remove
	 * @var array
	 */
	protected $_columnSuffixes = array('_id', 'id', '_ident', 'ident', '_col', 'col');

	/**
	 * List of column names that indiciate the column is to be used as a soft-delete
	 * @var array
	 */
	protected $_softDeleteColumnNames = array('deleted', 'is_deleted');

	/**
	 * Name of the column to be used for soft-delete purposes
	 * @var string
	 */
	protected $_softDeleteColumn = null;

	/**
	 * Name of the Cache Manager to use. Left blank if the feature is to be disabled
	 * @var string
	 */
	protected $_cacheManagerName = '';

	/**
	 * Name of the cache to use
	 * @var string
	 */
	protected $_cacheName = 'model';

	/**
	 * Name of the Zend Log to use. Left blank if the feature is to be disabled
	 * @var string
	 */
	protected $_loggerName = '';

	/**
	 *
	 * @param array $info
	 */
	public function setForeignKeysInfo($info) {
		$this->_foreignKeysInfo=$info;
	}

	/**
	 *
	 * @return array
	 */
	public function getForeignKeysInfo() {
		return $this->_foreignKeysInfo;
	}

	/**
	 *
	 * @param string $location
	 */
	public function setLocation($location) {
		$this->_location=$location;
	}

	/**
	 *
	 * @return string
	 */
	public function getLocation() {
		return $this->_location;
	}

	/**
	 *
	 * @param string $table
	 */
	public function setTableName($table) {
		$this->_tbname=$table;
		$this->_className=$this->_getClassName($table);
	}

	/**
	 *
	 * @return string
	 */
	public function getTableName() {
		return $this->_tbname;
	}

	/**
	 *
	 * @param array $list
	 */
	public function setTableList($list) {
		$this->_tableList = $list;
	}

	/**
	 * @return array
	 */
	public function getTableList() {
		return $this->_tableList;
	}

	/**
	 *
	 * @param array $list
	 */
	public function setDependentTables($tables) {
		$this->_dependentTables = $tables;
	}

	/**
	 * @return array
	 */
	public function getDependentTables() {
		return $this->_dependentTables;
	}

	/**
	 *
	 * @param string $location
	 */
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
	 *  removes underscores and capital the letter that was after the underscore
	 *  example: 'ab_cd_ef' to 'AbCdEf'
	 *
	 * @param String $str
	 * @return String
	 */
	protected function _getCapital($str) {
		$temp='';
		foreach (explode("_",$str) as $part) {
			$temp.=ucfirst($part);
		}
		return $temp;
	}

	/**
	 *	Removes underscores and capital the letter that was after the underscore
	 *  example: 'ab_cd_ef' to 'AbCdEf'
	 *
	 * @param string $str
	 * @return string
	 */
	protected function _getClassName($str) {
		$temp='';
		// Remove common prefixes
		foreach ($this->_tablePrefixes as $prefix) {
		    if (preg_match("/^$prefix/i", $str)) {
		        // Only replace a single prefix
		        $str = preg_replace("/^$prefix/i", '', $str);
		        break;
		    }
		}

		// Remove common suffixes
		foreach ($this->_columnSuffixes as $suffix) {
		    if (preg_match("/$suffix$/i", $str)) {
		        // Only replace a single prefix
		        $str = preg_replace("/$suffix$/i", '', $str);
		        break;
		    }
		}

		foreach (explode("_",$str) as $part) {
			$temp.=ucfirst($part);
		}
		return $temp;
	}

	/**
	 *	Removes underscores and capital the letter that was after the underscore
	 *  example: 'ab_cd_ef' to 'AbCdEf'
	 *
	 * @param string $str
	 * @return string
	 */
	protected function _getRelationName(array $relation_info, $type = 'parent') {
		if ($type == 'parent') {
			// Check if a column exists with the same resulting name
		    $str = $this->_getClassName($relation_info['column_name']);
			foreach ($this->_columns as $column) {
				if ($column['capital'] == $str) {
					$conflict = false;
					// Check if should use the table name so long as there is not another conflict
					foreach ($this->_dependentTables as $relation) {
						$conflict = $conflict || $this->_getClassName($relation['column_name']) == $str;
					}

					if ($conflict) {
						$str = $this->_getClassName($relation_info['foreign_tbl_name']) . 'By' . $str;
					} else {
						$str = $this->_getClassName($relation_info['foreign_tbl_name']);
					}
				}
			}
		    //$relations = $this->_foreignKeysInfo;
		} else {

    		$table_count = 0;
    		// Determine if there are multiple fields that link to a single table
    		foreach ($this->_dependentTables as $relation) {
    		    if ($relation_info['foreign_tbl_name'] == $relation['foreign_tbl_name']) {
    		        $table_count++;
    		    }
    		}

    		$str = $this->_getClassName($relation_info['foreign_tbl_name']);
    		if ($table_count > 1) {
		        $str .= 'By' . $this->_getClassName($relation_info['column_name']);
		    }
		}

		return $str;
	}

	abstract public function getTablesNamesFromDb();

	/**
	 * converts database specific data types to PHP data types
	 *
	 * @param string $str
	 * @return string
	 */
	abstract protected function _convertTypeToPhp($str);

	public function parseTable() {
		$this->parseDescribeTable();
		$this->parseForeignKeys();
		$this->parseDependentTables();
	}

	abstract public function parseForeignKeys();

	abstract public function parseDependentTables();

	abstract public function parseDescribeTable();

    abstract protected function getPDOString($host, $port, $dbname);

	/**
	 *
	 *  the class constructor
	 *
	 * @param Array $config
	 * @param String $dbname
	 * @param String $namespace
	 */
	function __construct($config,$dbname,$namespace) {

		$columns=array();
		$primaryKey=array();


		$this->_config=$config;
		$this->_addRequire=$config['include.addrequire'];
		
		try {
		 $pdo = new PDO($this->getPDOString($this->_config['db.host'], $this->_config['db.port'], $dbname),
		    $this->_config['db.user'],
		    $this->_config['db.password']
		 );
		 $this->_pdo=$pdo;
		} catch (Exception $e) {
			die("pdo error: ".$e->getMessage()."\n");
		}

		//$this->_tbname=$tbname;
		$this->_namespace=$namespace;

		//docs section
		$this->_author = $this->_config['docs.author'];
		$this->_license = $this->_config['docs.license'];
		$this->_copyright = $this->_config['docs.copyright'];

		$this->_cacheManagerName = $this->_config['cache.manager_name'];
		$this->_cacheName = $this->_config['cache.name'];

		$this->_loggerName = $this->_config['log.logger_name'];

		$path = $this->_config['include.path'];
		if ( ! is_dir($path)) {
		    // Use path relative to root of the application
		    $path = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $this->_config['include.path'];
		}

		$this->setIncludePath($path . DIRECTORY_SEPARATOR);

		if (file_exists($this->getIncludePath() . 'IncludeDefault.php')) {
		    require_once $this->getIncludePath() . 'IncludeDefault.php';
		} else {
		    require_once __DIR__.DIRECTORY_SEPARATOR.'IncludeDefault.php';
		}
	}

	/**
	 *
	 * parse a tpl file and return the result
	 *
	 * @param String $tplFile
	 * @return String
	 */
	public function getParsedTplContents($tplFile, $vars = array()) {
		extract($vars);
		ob_start();
		require(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$tplFile);
		$data=ob_get_contents();
		ob_end_clean();
		return $data;
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

		$dbTableData=$this->getParsedTplContents('dbtable.tpl', $vars);

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

		$mapperData=$this->getParsedTplContents('mapper.tpl');

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

		$modelData=$this->getParsedTplContents('model.tpl');

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
		$modelData=$this->getParsedTplContents('model_class.tpl');

		if (!file_put_contents($modelFile, $modelData))
			die("Error: could not write model file $modelFile.");

		$paginatorFile=$this->getLocation().DIRECTORY_SEPARATOR.'Paginator.php';
		$paginatorData=$this->getParsedTplContents('paginator_class.tpl');

		if (!file_put_contents($paginatorFile, $paginatorData))
			die("Error: could not write model file $paginatorFile.");

		$mapperFile=$this->getLocation().DIRECTORY_SEPARATOR.'mappers'.DIRECTORY_SEPARATOR.'MapperAbstract.php';
		$mapperData=$this->getParsedTplContents('mapper_class.tpl');

		if (!file_put_contents($mapperFile, $mapperData))
			die("Error: could not write mapper file $mapperFile.");

		$tableFile=$this->getLocation().DIRECTORY_SEPARATOR.'DbTable'.DIRECTORY_SEPARATOR.'TableAbstract.php';
		$tableData=$this->getParsedTplContents('dbtable_class.tpl');

		if (!file_put_contents($tableFile, $tableData))
			die("Error: could not write model file $tableFile.");

		// Copy all files in include paths
		if (is_dir($this->getIncludePath() . 'model')) {
			$this->copyIncludeFiles($this->getIncludePath() . 'model', $this->getLocation());
		} else {
		    echo $this->getIncludePath() . 'model';
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
