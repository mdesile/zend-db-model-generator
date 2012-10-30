<?php

/**
 * main class for files creation
 */
abstract class MakeDbTableAbstract {

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
     * @var String charset;
     */
    protected $_charset = 'utf8';

    /**
     *   @var Boolean $_returnId;
     */
    protected $_returnId;

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

    private $_schema = '';

        public function setSchema($schema)
        {
            $this->_schema = $schema;
        }
        
        public function getSchema()
        {
            return $this->_schema;
        }
    
    /**
     *
     * @param array $info
     */
    public function setForeignKeysInfo($info) {
        $this->_foreignKeysInfo = $info;
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
        $this->_location = $location;
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
        $this->_tbname = $table;
        $this->_className = $this->_getClassName($table);
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
     *  removes underscores and capital the letter that was after the underscore
     *  example: 'ab_cd_ef' to 'AbCdEf'
     *
     * @param String $str
     * @return String
     */
    protected function _getCapital($str) {
        $temp = '';
        foreach (explode("_", $str) as $part) {
            $temp .= ucfirst($part);
        }
        return $temp;
    }
    
    protected function _getVarCapital($str) {
        $temp = '';
        $first = true;
        foreach (explode("_", $str) as $part) {
            if($first){
                $first = false;
                $temp .= strtolower($part);
            } else {
                $temp .= ucfirst($part);
            }
        }

        return $temp;
    }

    /**
     * 	Removes underscores and capital the letter that was after the underscore
     *  example: 'ab_cd_ef' to 'AbCdEf'
     *
     * @param string $str
     * @return string
     */
    protected function _getClassName($str) {
        $temp = '';
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

        foreach (explode("_", $str) as $part) {
            $temp .= ucfirst($part);
        }
        return $temp;
    }

    /**
     * 	Removes underscores and capital the letter that was after the underscore
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

    abstract protected function getPDOSocketString($socket, $dbname);

    /**
     *
     *  the class constructor
     *
     * @param Array $config
     * @param String $dbname
     * @param String $namespace
     */
    function __construct($config, $dbname, $namespace) {

        $columns = array();
        $primaryKey = array();
        $this->_namespace = $namespace;


        $this->_config = $config;
        $this->_returnId = $config['save.return_id'];
        $pdoString = "";
        if ($this->_config['db.socket']) {
            $pdoString = $this->getPDOSocketString($this->_config['db.socket'], $dbname);
        } else {
            $pdoString = $this->getPDOString($this->_config['db.host'], $this->_config['db.port'], $dbname);
        }
        try {
            $pdo = new PDO($pdoString,
                            $this->_config['db.user'],
                            $this->_config['db.password']
            );
            $this->_pdo = $pdo;
        } catch (Exception $e) {
            die("pdo error: " . $e->getMessage() . "\n");
        }

        //$this->_tbname=$tbname;
        //docs section
        $this->_author = $this->_config['docs.author'];
        $this->_license = $this->_config['docs.license'];
        $this->_copyright = $this->_config['docs.copyright'];

        $this->_cacheManagerName = $this->_config['cache.manager_name'];
        $this->_cacheName = $this->_config['cache.name'];

        $this->_loggerName = $this->_config['log.logger_name'];
    }

    /**
     *
     * parse a tpl file and return the result
     *
     * @param String $tplFile
     * @return String
     */
    public function getParsedTplContents($tplFile, $version, $vars = array()) {
        extract($vars);
        ob_start();
        require(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'templates-v' . $version . DIRECTORY_SEPARATOR . $tplFile);
        $data = ob_get_contents();
        ob_end_clean();
        return $data;
    }

}
