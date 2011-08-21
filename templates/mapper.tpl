<?="<?php\n"?>

/**
 * Application Model Mappers
 *
 * @package <?=$this->_namespace?>_Model
 * @subpackage Mapper
 * @author <?=$this->_author . "\n"?>
 * @copyright <?=$this->_copyright . "\n"?>
 * @license <?=$this->_license . "\n"?>
 */
<?php if ($this->_addRequire):?>

/**
 * Table definition for this class
 * @see <?=$this->_namespace?>_Model_DbTable_<?=$this->_className."\n"?>
 */
require_once dirname(__FILE__) . '/../DbTable/<?=$this->_className?>.php';
<?php endif ?>

/**
 * Data Mapper implementation for <?=$this->_namespace?>_Model_<?=$this->_className."\n"?>
 *
 * @package <?=$this->_namespace?>_Model
 * @subpackage Mapper
 * @author <?=$this->_author . "\n"?>
 */
class <?=$this->_namespace?>_Model_Mapper_<?=$this->_className?> extends <?=$this->_includeMapper->getParentClass() . "\n"?>
{
<?php $vars = $this->_includeMapper->getVars();
if (! empty($vars)) {
echo "\n$vars\n";
}
?>
    /**
     * Returns an array, keys are the field names.
     *
     * @param <?=$this->_namespace?>_Model_<?=$this->_className?> $model
     * @return array
     */
    public function toArray($model)
    {
        if (! $model instanceof <?=$this->_namespace?>_Model_<?=$this->_className?>) {
<?php if (! empty($this->_loggerName)):?>
            if (is_object($model)) {
                $message = get_class($model) . " is not a <?=$this->_namespace?>_Model_<?=$this->_className?> object in toArray for " . get_class($this);
            } else {
                $message = "$model is not a <?=$this->_namespace?>_Model_<?=$this->_className?> object in toArray for " . get_class($this);
            }

            $this->_logger->log($message, Zend_Log::ERR);

<?php endif; ?>
            throw new Exception('Unable to create array: invalid model passed to mapper');
        }

        $result = array(<?php echo "\n";
foreach ($this->_columns as $column):
            ?>
            '<?=$column['field']?>' => $model->get<?=$column['capital']?>(),
<?php endforeach;?>
        );

        return $result;
    }

    /**
     * Returns the DbTable class associated with this mapper
     *
     * @return <?=$this->_namespace?>_Model_DbTable_<?=$this->_className . "\n"?>
     */
    public function getDbTable()
    {
        if ($this->_dbTable === null) {
            $this->setDbTable('<?=$this->_namespace?>_Model_DbTable_<?=$this->_className?>');
        }

        return $this->_dbTable;
    }

    /**
     * Deletes the current model
     *
     * @param <?=$this->_namespace?>_Model_<?=$this->_className?> $model The model to <?php if ($this->_softDeleteColumn != null): ?>mark as deleted
<?php else: ?>delete
<?php endif;?>
<?php if ($this->_softDeleteColumn == null): ?>
     * @see <?=$this->_namespace?>_Model_DbTable_TableAbstract::delete()
<?php endif;?>
     * @return int
     */
    public function delete($model)
    {
        if (! $model instanceof <?=$this->_namespace?>_Model_<?=$this->_className?>) {
<?php if (! empty($this->_loggerName)):?>
            if (is_object($model)) {
                $message = get_class($model) . " is not a <?=$this->_namespace?>_Model_<?=$this->_className?> object in delete for " . get_class($this);
            } else {
                $message = "$model is not a <?=$this->_namespace?>_Model_<?=$this->_className?> object in delete for " . get_class($this);
            }

            $this->_logger->log($message, Zend_Log::ERR);

<?php endif; ?>
            throw new Exception('Unable to delete: invalid model passed to mapper');
        }

        $this->getDbTable()->getAdapter()->beginTransaction();
        try {
<?php if ($this->_softDeleteColumn != null):
        foreach ($this->_columns as $column):
            if ($column['field'] == $this->_softDeleteColumn) {?>
            $model->set<?=$column['capital']?>(<?php
                if ($column['phptype'] == 'boolean') {
                    echo 'true';
                } elseif (preg_match('/date/', $column['type'])) {
                        echo 'Zend_Date::now()';
                } else {
                    echo '1';
                }
                break;
            }
        endforeach;?>);
            $result = $model->save();
<?php else: ?>
<?php if ($this->_primaryKey['phptype'] == 'array') : ?>
            $where = array();
        <?php foreach ($this->_primaryKey['fields'] as $key) : ?>

            $pk_val = $model->get<?=$key['capital']?>();
            if ($pk_val === null) {
<?php if (! empty($this->_loggerName)):?>
                $this->_logger->log('The value for <?=$key['capital']?> cannot be null in delete for ' . get_class($this), Zend_Log::ERR);

<?php endif; ?>
                throw new Exception('The value for <?=$key['capital']?> cannot be null');
            } else {
                $where[] = $this->getDbTable()->getAdapter()->quoteInto('<?=$key['field']?> = ?', $pk_val);
            }
<?php endforeach; ?>
<?php else :?>
            $where = $this->getDbTable()->getAdapter()->quoteInto('<?=$this->_primaryKey['field']?> = ?', $model->get<?=$this->_primaryKey['capital']?>());
<?php endif; ?>
            $result = $this->getDbTable()->delete($where);

            $this->getDbTable()->getAdapter()->commit();
        } catch (Exception $e) {
<?php if (! empty($this->_loggerName)):?>
            $message = 'Exception encountered while attempting to delete ' . get_class($this);
            if (! empty($where)) {
                $message .= ' Where: ';
<?php if ($this->_primaryKey['phptype'] == 'array') : ?>
                foreach ($where as $where_clause) {
                    $message .= $where_clause;
                }
<?php else: ?>
                $message .= $where;
<?php endif; ?>
            } else {
                $message .= ' with an empty where';
            }

            $message .= ' Exception: ' . $e->getMessage();
            $this->_logger->log($message, Zend_Log::ERR);
            $this->_logger->log($e->getTraceAsString(), Zend_Log::DEBUG);

<?php endif; ?>
            $this->getDbTable()->getAdapter()->rollback();
            $result = false;
        }

        return $result;
<?php endif; ?>
    }

    /**
     * Saves current row, and optionally dependent rows
     *
     * @param <?=$this->_namespace?>_Model_<?=$this->_className?> $model
     * @param boolean $ignoreEmptyValues Should empty values saved
     * @param boolean $recursive Should the object graph be walked for all related elements
     * @param boolean $useTransaction Flag to indicate if save should be done inside a database transaction
     * @return boolean If the save action was successful
     */
    public function save(<?=$this->_namespace?>_Model_<?=$this->_className?> $model,
        $ignoreEmptyValues = true, $recursive = false, $useTransaction = true
    ) {
        $data = $model->toArray();
        if ($ignoreEmptyValues) {
            foreach ($data as $key => $value) {
                if ($value === null or $value === '') {
                    unset($data[$key]);
                }
            }
        }

<?php if ($this->_primaryKey['phptype'] == 'array') : ?>
        $primary_key = array();
<?php foreach ($this->_primaryKey['fields'] as $key) : ?>

        $pk_val = $model->get<?=$key['capital']?>();
        if ($pk_val === null) {
<?php if (! empty($this->_loggerName)):?>
            $this->_logger->log('The value for <?=$key['capital']?> cannot be null in save for ' . get_class($this), Zend_Log::ERR);
<?php endif; ?>
            return false;
        } else {
            $primary_key['<?=$key['field']?>'] = $pk_val;
        }
<?php endforeach; ?>

        $exists = $this->find($primary_key, null);
        $success = true;

        if ($useTransaction) {
            $this->getDbTable()->getAdapter()->beginTransaction();
        }

        try {
            // Check for current existence to know if needs to be inserted
            if ($exists === null) {
                $this->getDbTable()->insert($data);
<?php else :?>
        $primary_key = $model->get<?=$this->_primaryKey['capital']?>();
        $success = true;

        if ($useTransaction) {
            $this->getDbTable()->getAdapter()->beginTransaction();
        }

<?php if (! $this->_primaryKey['foreign_key']): ?>
        unset($data['<?=$this->_primaryKey['field']?>']);

        try {
            if ($primary_key === null) {
<?php else: ?>
        $exists = $this->find($primary_key, null);

        try {
            if ($exists === null) {
<?php endif; ?>
                $primary_key = $this->getDbTable()->insert($data);
                if ($primary_key) {
                    $model->set<?=$this->_primaryKey['capital']?>($primary_key);
                } else {
                    $success = false;
                }
<?php endif;?>
            } else {
                $this->getDbTable()
                     ->update($data,
                              array(<?php echo "\n                                 ";
            if ($this->_primaryKey['phptype'] == 'array') {
                $fields = count($this->_primaryKey['fields']);
                $i = 0;
                foreach ($this->_primaryKey['fields'] as $key) {
                    echo '\'' . $key['field'] . ' = ?\' => $primary_key[\'' . $key['field'] . '\']';
                    $i++;
                    if ($i != $fields) {
                        echo ",\n                                 ";
                    }
                }
            } else {
                echo '\'' . $this->_primaryKey['field'] . ' = ?\' => $primary_key';
            }
            echo "\n";?>
                              )
                );
            }
<?php if (count($this->getDependentTables()) > 0) :?>

            if ($recursive) {
<?php foreach ($this->getDependentTables() as $key) : ?>
                if ($success && $model->get<?=$this->_getRelationName($key, 'dependent')?>(false) !== null) {
<?php if ($key['type'] !== 'many') : ?>
                    $success = $success &&
                        $model->get<?=$this->_getRelationName($key, 'dependent')?>()
<?php if ($this->_primaryKey['phptype'] !== 'array') : ?>
                              ->set<?=$this->_getCapital($key['column_name'])?>($primary_key)
<?php endif;?>
                              ->save($ignoreEmptyValues, $recursive, false);
<?php else: ?>
                    $<?=$this->_getClassName($key['foreign_tbl_name'])?> = $model->get<?=$this->_getRelationName($key, 'dependent')?>();
                    foreach ($<?=$this->_getClassName($key['foreign_tbl_name'])?> as $value) {
                        $success = $success &&
                            $value<?php if ($this->_primaryKey['phptype'] !== 'array') : ?>
->set<?=$this->_getCapital($key['column_name'])?>($primary_key)
<?php elseif (is_array($key['column_name'])) :
    foreach ($key['column_name'] as $column) : ?>
->set<?=$this->_getCapital($column)?>($primary_key['<?php echo $column ?>'])
<?php endforeach; ?>
<?php endif;?>
                                  ->save($ignoreEmptyValues, $recursive, false);

                        if (! $success) {
                            break;
                        }
                    }
<?php endif; ?>
                }

<?php endforeach; ?>
            }
<?php endif; ?>

            if ($useTransaction && $success) {
                $this->getDbTable()->getAdapter()->commit();
            } elseif ($useTransaction) {
                $this->getDbTable()->getAdapter()->rollback();
            }

<?php if (! empty($this->_loggerName)):?>
            if (! $success) {
                $message = 'Unable to save ' . get_class($this) . ' and all dependents';
                $this->_logger->log($message, Zend_Log::WARN);
            }

<?php endif; ?>
        } catch (Exception $e) {
<?php if (! empty($this->_loggerName)):?>
            $message = 'Exception encountered while attempting to save ' . get_class($this);
            if (! empty($primary_key)) {
<?php if ($this->_primaryKey['phptype'] == 'array') : ?>
                $message .= ' id:';
<?php foreach ($this->_primaryKey['fields'] as $key) : ?>
                $message .= ' <?=$key['field']?> => ' . $primary_key['<?=$key['field']?>'];
<?php endforeach; ?>
<?php else: ?>
                $message .= ' id: ' . $primary_key;
<?php endif; ?>
            } else {
                $message .= ' with an empty primary key ';
            }

            $message .= ' Exception: ' . $e->getMessage();
            $this->_logger->log($message, Zend_Log::ERR);
            $this->_logger->log($e->getTraceAsString(), Zend_Log::DEBUG);

<?php endif; ?>
            if ($useTransaction) {
                $this->getDbTable()->getAdapter()->rollback();
            }

            $success = false;
        }

        return $success;
    }

    /**
     * Finds row by primary key
     *
     * @param <?=$this->_primaryKey['phptype']?> $primary_key
     * @param <?=$this->_namespace?>_Model_<?=$this->_className?>|null $model
     * @return <?=$this->_namespace?>_Model_<?=$this->_className?>|null The object provided or null if not found
     */
    public function find($primary_key, $model)
    {
        $result = $this->getRowset($primary_key);

        if (is_null($result)) {
            return null;
        }

        $row = $result->current();

        $model = $this->loadModel($row, $model);

        return $model;
    }

    /**
     * Loads the model specific data into the model object
     *
     * @param Zend_Db_Table_Row_Abstract|array $data The data as returned from a Zend_Db query
     * @param <?=$this->_namespace?>_Model_<?=$this->_className?>|null $entry The object to load the data into, or null to have one created
     * @return <?=$this->_namespace?>_Model_<?=$this->_className?> The model with the data provided
     */
    public function loadModel($data, $entry)
    {
        if ($entry === null) {
            $entry = new <?=$this->_namespace?>_Model_<?=$this->_className?>();
        }

        if (is_array($data)) {
            $entry<?php
                $count = count($this->_columns);
                foreach ($this->_columns as $column):
                $count--;
              ?>->set<?=$column['capital']?>($data['<?=$column['field']?>'])<?if ($count> 0) echo "\n                  ";
              endforeach; ?>;
        } elseif ($data instanceof Zend_Db_Table_Row_Abstract || $data instanceof stdClass) {
            $entry<?php
                $count = count($this->_columns);
                foreach ($this->_columns as $column):
                $count--;
              ?>->set<?=$column['capital']?>($data-><?=$column['field']?>)<?if ($count> 0) echo "\n                  ";
              endforeach; ?>;
        }

        $entry->setMapper($this);

        return $entry;
    }
<?php $functions = $this->_includeMapper->getFunctions();
if (! empty($functions)) {
echo "\n$functions\n";
} ?>
}
