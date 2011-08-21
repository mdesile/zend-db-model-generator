<?="<?php\n"?>

/**
 * Application Model DbTables
 *
 * @package <?=$this->_namespace?>_Model
 * @subpackage DbTable
 * @author <?=$this->_author."\n"?>
 * @copyright <?=$this->_copyright."\n"?>
 * @license <?=$this->_license."\n"?>
 */
<? if ($this->_addRequire): ?>

/**
 * Abstract class for <?=$this->_namespace?>_Model_DbTables
 * @see <?=$this->_includeTable->getParentClass() . "\n"?>
 */
require_once 'TableAbstract.php';
<? endif; ?>

/**
 * Table definition for <?=$this->getTableName()."\n"?>
 *
 * @package <?=$this->_namespace?>_Model
 * @subpackage DbTable
 * @author <?=$this->_author . "\n"?>
 */
class <?=$this->_namespace?>_Model_DbTable_<?=$this->_className?> extends <?=$this->_includeTable->getParentClass() . "\n"?>
{
    /**
     * $_name - name of database table
     *
     * @var string
     */
    protected $_name = '<?=$this->_tbname?>';

    /**
     * $_id - this is the primary key name
     *
     * @var <?=$this->_primaryKey['phptype'] . "\n"?>
     */
    protected $_id = <?php
    if ($this->_primaryKey['phptype'] !== 'array') {
        echo '\'' . $this->_primaryKey['field'] . '\'';
    } else {
        echo $this->_primaryKey['field'];
    }
    ?>;

    protected $_sequence = <?=($this->_primaryKey['phptype'] !== 'array') ? 'true' : 'false'; ?>;

    <?=$referenceMap?>

    <?=$dependentTables?>

<?=$this->_includeTable->getVars() . "\n"?>

<?=$this->_includeTable->getFunctions() . "\n"?>
}
