<?="<?php\n"?>

/**
 * Application Models
 *
 * @package <?=$this->_namespace?>_Model
 * @subpackage Model
 * @author <?=$this->_author."\n"?>
 * @copyright <?=$this->_copyright."\n"?>
 * @license <?=$this->_license."\n"?>
 */
<? if ($this->_addRequire):?>

/**
 * Data Mapper implementation for this class
 * @see <?=$this->_namespace?>_Model_Mapper_<?=$this->_className . "\n"?>
 */
require_once dirname(__FILE__) . '/mappers/<?=$this->_className?>.php';

/**
 * Abstract class for models
 */
require_once 'ModelAbstract.php';
<? endif; ?>


/**
 * <?=$this->_classDesc."\n"?>
 *
 * @package <?=$this->_namespace?>_Model
 * @subpackage Model
 * @author <?=$this->_author."\n"?>
 */
class <?=$this->_namespace?>_Model_<?=$this->_className?> extends <?=$this->_includeModel->getParentClass() . "\n"?>
{

<?php foreach ($this->_columns as $column): ?>
    /**
<?php if (! empty($column['comment'])) : ?>
     * <?=$column['comment'] . "\n"?>
<?php endif; ?>
     * Database var type <?=$column['type'] . "\n"?>
     *
     * @var <?=$column['phptype'] . "\n"?>
     */
    protected $_<?=$column['capital']?>;

<?php endforeach;?>

<?php foreach ($this->getForeignKeysInfo() as $key): ?>
    /**
     * Parent relation <?=$key['key_name'] . "\n"?>
     *
     * @var <?=$this->_namespace?>_Model_<?=$this->_getClassName($key['foreign_tbl_name']) . "\n"?>
     */
    protected $_<?=$this->_getRelationName($key, 'parent')?>;

<?php endforeach;?>

<?php foreach ($this->getDependentTables() as $key): ?>
    /**
     * Dependent relation <?=$key['key_name'] . "\n"?>
     * Type: <?=($key['type'] == 'one') ? 'One-to-One' : 'One-to-Many'?> relationship
     *
     * @var <?=$this->_namespace?>_Model_<?=$this->_getClassName($key['foreign_tbl_name']) . "\n"?>
     */
    protected $_<?=$this->_getRelationName($key, 'dependent')?>;

<?php endforeach;?>
<?php $vars = $this->_includeModel->getVars();
if (! empty($vars)) {
echo "$vars\n\n";
}
?>
    /**
     * Sets up column and relationship lists
     */
    public function __construct()
    {
        parent::init();
        $this->setColumnsList(array(
<?php foreach ($this->_columns as $column): ?>
            '<?=$column['field']?>'=>'<?=$column['capital']?>',
<?php endforeach;?>
        ));

        $this->setParentList(array(
<?php foreach ($this->getForeignKeysInfo() as $key): ?>
            '<?=$this->_getCapital($key['key_name'])?>'=> array(
                    'property' => '<?=$this->_getRelationName($key, 'parent')?>',
                    'table_name' => '<?=$this->_getClassName($key['foreign_tbl_name'])?>',
                ),
<?php endforeach;?>
        ));

        $this->setDependentList(array(
<?php foreach ($this->getDependentTables() as $key): ?>
            '<?=$this->_getCapital($key['key_name'])?>' => array(
                    'property' => '<?=$this->_getRelationName($key, 'dependent')?>',
                    'table_name' => '<?=$this->_getClassName($key['foreign_tbl_name'])?>',
                ),
<?php endforeach;?>
        ));
    }
<?php foreach ($this->_columns as $column): ?>

    /**
     * Sets column <?=$column['field']?><?php if (strpos($column['type'], 'datetime') !== false): ?>. Stored in ISO 8601 format.<?php endif; echo "\n";?>
     *
<?php if (strpos($column['type'], 'datetime') !== false): ?>
     * @param string|Zend_Date $date
<?php else: ?>
     * @param <?=$column['phptype']?> $data
<?php endif; ?>
     * @return <?=$this->_namespace?>_Model_<?=$this->_className . "\n"?>
     */
    public function set<?=$column['capital']?>($data)
    {
<?php if (strpos($column['type'], 'datetime') !== false): ?>
        if (! empty($data)) {
            if (! $data instanceof Zend_Date) {
                $data = new Zend_Date($data);
            }

<?php
$db = get_class($this);
if (stripos($db, 'mssql') !== false || stripos($db, 'dblib') !== false || stripos($db, 'sqlsrv') !== false): ?>
            $data = $data->toString('YYYY-MM-ddTHH:mm:ss.S');
<?php else: ?>
            $data = $data->toString(Zend_Date::ISO_8601);
<?php endif; ?>
        }

<?php endif; ?>
        $this->_<?=$column['capital']?> = $data;
        return $this;
    }

    /**
     * Gets column <?=$column['field'] . "\n"?>
     *
<?php if (strpos($column['type'], 'datetime') !== false): ?>
     * @param boolean $returnZendDate
     * @return Zend_Date|null|string Zend_Date representation of this datetime if enabled, or ISO 8601 string if not
<?php else: ?>
     * @return <?=$column['phptype'] . "\n"?>
<?php endif; ?>
     */
    public function get<?=$column['capital']?>(<?php if (strpos($column['type'], 'datetime') !== false): ?>$returnZendDate = false<?php endif;?>)
    {
<?php if (strpos($column['type'], 'datetime') !== false): ?>
        if ($returnZendDate) {
            if ($this->_<?=$column['capital']?> === null) {
                return null;
            }

<?php
$db = get_class($this);
if (stripos($db, 'mssql') !== false || stripos($db, 'dblib') !== false || stripos($db, 'sqlsrv') !== false): ?>
            return new Zend_Date($this->_<?=$column['capital']?>, 'YYYY-MM-ddTHH:mm:ss.S');
<?php else: ?>
            return new Zend_Date($this->_<?=$column['capital']?>, Zend_Date::ISO_8601);
<?php endif; ?>
        }

        return $this->_<?=$column['capital']?>;
<?php elseif ($column['phptype'] == 'boolean'): ?>
        return $this->_<?=$column['capital']?> ? true : false;
<?php else: ?>
        return $this->_<?=$column['capital']?>;
<?php endif; ?>
    }
<?php endforeach; ?>
<?php foreach ($this->getForeignKeysInfo() as $key): ?>

    /**
     * Sets parent relation <?=$this->_getClassName($key['column_name']) . "\n"?>
     *
     * @param <?=$this->_namespace?>_Model_<?=$this->_getClassName($key['foreign_tbl_name'])?> $data
     * @return <?=$this->_namespace?>_Model_<?=$this->_className . "\n"?>
     */
    public function set<?=$this->_getRelationName($key, 'parent')?>(<?=$this->_namespace?>_Model_<?=$this->_getClassName($key['foreign_tbl_name'])?> $data)
    {
        $this->_<?=$this->_getRelationName($key, 'parent')?> = $data;

        $primary_key = $data->getPrimaryKey();
<?php if (is_array($key['foreign_tbl_column_name']) && is_array($key['column_name'])) : ?>
<?php while ($column = next($key['foreign_tbl_column_name'])) :
        $foreign_column = next($key['column_name']); ?>
        $this->set<?=$this->_getCapital($column)?>($primary_key['<?php echo $foreign_column ?>']);
<?php endwhile;
else : ?>
        if (is_array($primary_key)) {
            $primary_key = $primary_key['<?=$key['foreign_tbl_column_name']?>'];
        }

        $this->set<?=$this->_getCapital($key['column_name'])?>($primary_key);
<?php endif; ?>

        return $this;
    }

    /**
     * Gets parent <?=$this->_getClassName($key['column_name']) . "\n"?>
     *
     * @param boolean $load Load the object if it is not already
     * @return <?=$this->_namespace?>_Model_<?=$this->_getClassName($key['foreign_tbl_name']) . "\n"?>
     */
    public function get<?=$this->_getRelationName($key, 'parent')?>($load = true)
    {
        if ($this->_<?=$this->_getRelationName($key, 'parent')?> === null && $load) {
            $this->getMapper()->loadRelated('<?=$this->_getCapital($key['key_name'])?>', $this);
        }

        return $this->_<?=$this->_getRelationName($key, 'parent')?>;
    }
<?php endforeach; ?>
<?php foreach ($this->getDependentTables() as $key): ?>

<?php if ($key['type'] == 'one') :?>
    /**
     * Sets dependent relation <?=$key['key_name'] . "\n"?>
     *
     * @param <?=$this->_namespace?>_Model_<?=$this->_getClassName($key['foreign_tbl_name'])?> $data
     * @return <?=$this->_namespace?>_Model_<?=$this->_className . "\n"?>
     */
    public function set<?=$this->_getRelationName($key, 'dependent')?>(<?=$this->_namespace?>_Model_<?=$this->_getClassName($key['foreign_tbl_name'])?> $data)
    {
        $this->_<?=$this->_getRelationName($key, 'dependent')?> = $data;
        return $this;
    }

    /**
     * Gets dependent <?=$key['key_name'] . "\n"?>
     *
     * @param boolean $load Load the object if it is not already
     * @return <?=$this->_namespace?>_Model_<?=$this->_getClassName($key['foreign_tbl_name']) . "\n"?>
     */
    public function get<?=$this->_getRelationName($key, 'dependent')?>($load = true)
    {
        if ($this->_<?=$this->_getRelationName($key, 'dependent')?> === null && $load) {
            $this->getMapper()->loadRelated('<?=$this->_getCapital($key['key_name'])?>', $this);
        }

        return $this->_<?=$this->_getRelationName($key, 'dependent')?>;
    }
<?php else: ?>
    /**
     * Sets dependent relations <?=$key['key_name'] . "\n"?>
     *
     * @param array $data An array of <?=$this->_namespace?>_Model_<?=$this->_getClassName($key['foreign_tbl_name']) . "\n"?>
     * @return <?=$this->_namespace?>_Model_<?=$this->_className . "\n"?>
     */
    public function set<?=$this->_getRelationName($key, 'dependent')?>(array $data)
    {
        $this->_<?=$this->_getRelationName($key, 'dependent')?> = array();

        foreach ($data as $object) {
            $this->add<?=$this->_getRelationName($key, 'dependent')?>($object);
        }

        return $this;
    }

    /**
     * Sets dependent relations <?=$key['key_name'] . "\n"?>
     *
     * @param <?=$this->_namespace?>_Model_<?=$this->_getClassName($key['foreign_tbl_name'])?> $data
     * @return <?=$this->_namespace?>_Model_<?=$this->_className . "\n"?>
     */
    public function add<?=$this->_getRelationName($key, 'dependent')?>(<?=$this->_namespace?>_Model_<?=$this->_getClassName($key['foreign_tbl_name'])?> $data)
    {
        $this->_<?=$this->_getRelationName($key, 'dependent')?>[] = $data;
        return $this;
    }

    /**
     * Gets dependent <?=$key['key_name'] . "\n"?>
     *
     * @param boolean $load Load the object if it is not already
     * @return array The array of <?=$this->_namespace?>_Model_<?=$this->_getClassName($key['foreign_tbl_name']) . "\n"?>
     */
    public function get<?=$this->_getRelationName($key, 'dependent')?>($load = true)
    {
        if ($this->_<?=$this->_getRelationName($key, 'dependent')?> === null && $load) {
            $this->getMapper()->loadRelated('<?=$this->_getCapital($key['key_name'])?>', $this);
        }

        return $this->_<?=$this->_getRelationName($key, 'dependent')?>;
    }
<?php endif; ?>
<?php endforeach; ?>

    /**
     * Returns the mapper class for this model
     *
     * @return <?=$this->_namespace?>_Model_Mapper_<?=$this->_className . "\n"?>
     */
    public function getMapper()
    {
        if ($this->_mapper === null) {
            $this->setMapper(new <?=$this->_namespace?>_Model_Mapper_<?=$this->_className?>());
        }

        return $this->_mapper;
    }

    /**
     * Deletes current row by deleting the row that matches the primary key
     *
	 * @see <?=$this->_namespace?>_Model_Mapper_<?=$this->_className?>::delete
     * @return int|boolean Number of rows deleted or boolean if doing soft delete
     */
    public function deleteRowByPrimaryKey()
    {
<?php if ($this->_primaryKey['phptype'] == 'array') { ?>
        $primary_key = array();
<?php foreach ($this->_primaryKey['fields'] as $key) { ?>
        if (! $this->get<?php echo $key['capital']; ?>()) {
<?php if (! empty($this->_loggerName)):?>
        	$this->_logger->log('The value for <?=$key['capital']?> cannot be empty in deleteRowByPrimaryKey for ' . get_class($this), Zend_Log::ERR);

<?php endif; ?>
            throw new Exception('Primary Key <?php echo $key['capital']; ?> does not contain a value');
        } else {
            $primary_key['<?php echo $key['field']?>'] = $this->get<?php echo $key['capital']?>();
        }

<?php } ?>
        return $this->getMapper()->getDbTable()->delete('<?php
        $fields = count($this->_primaryKey['fields']);
                $i = 0;
                foreach ($this->_primaryKey['fields'] as $key) {
                    echo $key['field'] . ' = \'
                    . $this->getMapper()->getDbTable()->getAdapter()->quote($primary_key[\'' . $key['field'] . '\'])';
                    $i++;
                    if ($i != $fields) {
                        echo "
                    . ' AND ";
                    }
                }
        ?>);
<?php } else { ?>
        if ($this->get<?=$this->_primaryKey['capital']?>() === null) {
<?php if (! empty($this->_loggerName)):?>
        	$this->_logger->log('The value for <?=$this->_primaryKey['capital']?> cannot be null in deleteRowByPrimaryKey for ' . get_class($this), Zend_Log::ERR);

<?php endif; ?>
            throw new Exception('Primary Key does not contain a value');
        }

        return $this->getMapper()
                    ->getDbTable()
                    ->delete('<?=$this->_primaryKey['field']?> = ' .
                             $this->getMapper()
                                  ->getDbTable()
                                  ->getAdapter()
                                  ->quote($this->get<?=$this->_primaryKey['capital']?>()));
<?php } ?>
    }
<?php $functions = $this->_includeModel->getFunctions();
if (! empty($functions)) {
echo "\n$functions\n";
} ?>
}
