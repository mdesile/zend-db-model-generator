<?='<?php'?>

/**
 * Application Model DbTables
 *
 * @package <?=$this->_namespace?>_Model
 * @subpackage DbTable
 * @author <?=$this->_author."\n"?>
 * @copyright <?=$this->_copyright."\n"?>
 * @license <?=$this->_license."\n"?>
 */
<?php if ($this->_addRequire): ?>

/**
 * Zend DB Table Abstract class
 */
require_once 'Zend<?=DIRECTORY_SEPARATOR?>Db<?=DIRECTORY_SEPARATOR?>Table<?=DIRECTORY_SEPARATOR?>Abstract.php';
<?php endif; ?>

/**
 * Abstract class that is extended by all tables
 *
 * @package <?=$this->_namespace?>_Model
 * @subpackage DbTable
 * @author <?=$this->_author."\n"?>
 */
abstract class <?=$this->_namespace?>_Model_DbTable_TableAbstract extends Zend_Db_Table_Abstract
{
    /**
     * $_name - Name of database table
     *
     * @return string
     */
    protected $_name;

    /**
     * $_id - The primary key name(s)
     *
     * @return string|array
     */
    protected $_id;

    /**
     * Returns the primary key column name(s)
     *
     * @return string|array
     */
    public function getPrimaryKeyName()
    {
        return $this->_id;
    }

    /**
     * Returns the table name
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->_name;
    }

    /**
     * Returns the number of rows in the table
     *
     * @return int
     */
    public function countAllRows()
    {
        $query = $this->select()->from($this->_name, 'count(*) AS all_count');
        $numRows = $this->fetchRow($query);

        return $numRows['all_count'];
    }

    /**
     * Returns the number of rows in the table with optional WHERE clause
     *
     * @param $where string Where clause to use with the query
     * @return int
     */
    public function countByQuery($where = '')
    {
        $query = $this->select()->from($this->_name, 'count(*) AS all_count');

        if (! empty($where)) {
            $query->where($where);
        }

        $row = $this->getAdapter()->query($query)->fetch();

        return $row['all_count'];
    }

    /**
     * Generates a query to fetch a list with the given parameters
     *
     * @param $where string Where clause to use with the query
     * @param $order string Order clause to use with the query
     * @param $count int Maximum number of results
     * @param $offset int Offset for the limited number of results
     * @return Zend_Db_Select
     */
    public function fetchList($where = null, $order = null, $count = null,
        $offset = null
    ) {
        $select = $this->select()
            				->order($order)
            				->limit($count, $offset);

        if (! empty($where)) {
            $select->where($where);
        }

        return $select;
    }

}
