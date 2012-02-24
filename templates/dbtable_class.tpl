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
     * @param $where mixed Where clause to use with the query
     * @return int
     */
    public function countByQuery($where = '')
    {
        $query = $this->select()->from($this->_name, 'count(*) AS all_count');

		if (! empty($where) && is_string($where))
        {
            $query->where($where);
        }
        elseif(is_array($where) && isset($where[0]))
        {
			foreach($where as $i => $v)
			{
				/**
				 * Checks if you're passing an PDO escape statement
				 * ->where('price > ?', $price)
				 */
				if(isset($v[1]) && is_string($v[0]) && count($v) == 2)
				{
					$query->where($v[0], $v[1]);
				}
				elseif(is_string($v))
				{
					$query->where($v);
				}
			}
        }
        else
        {
            throw new Exception("You must pass integer indexes on the select statement array.");
        }


        $row = $this->getAdapter()->query($query)->fetch();

        return $row['all_count'];
    }

    /**
     * Generates a query to fetch a list with the given parameters
     *
     * @param $where mixed Where clause to use with the query
     * @param $order string Order clause to use with the query
     * @param $count int Maximum number of results
     * @param $offset int Offset for the limited number of results
     * @return Zend_Db_Select
     */
    public function fetchList($where = null, $order = null, $count = null, $offset = null)
    {
        $select = $this->select()
                    ->order($order)
                    ->limit($count, $offset);

        if (! empty($where) && is_string($where))
        {
            $select->where($where);
        }
        elseif(is_array($where) && isset($where[0]))
        {
			/**
			 * Adds a where/and statement for each of the inner arrays, and checks if it is a PDO escape statement or a string
			 */
			foreach($where as $i => $v)
			{
				if(isset($v[1]) && is_string($v[0]) && count($v) == 2)
				{
					$select->where($v[0], $v[1]);
				}
				elseif(is_string($v))
				{
					$select->where($v);
				}
			}
        }
        else
        {
            throw new Exception("You must pass integer indexes on the select statement array.");
        }

        return $select;
    }

}
