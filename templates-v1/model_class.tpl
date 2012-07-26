<?='<?php'?>

/**
 * Application Models
 *
 * @package <?=$this->_namespace?>_Model
 * @subpackage Model
 * @author <?=$this->_author."\n"?>
 * @copyright <?=$this->_copyright."\n"?>
 * @license <?=$this->_license."\n"?>
 */

/**
 * Abstract class that is extended by all base models
 *
 * @package <?=$this->_namespace?>_Model
 * @subpackage Model
 * @author <?=$this->_author."\n"?>
 */
abstract class <?=$this->_namespace?>_Model_ModelAbstract
{
    /**
     * Mapper associated with this model instance
     *
     * @var <?=$this->_namespace?>_Model_ModelAbstract
     */
    protected $_mapper;

<?php if (! empty($this->_loggerName)):?>
    /**
     * $_logger - Zend_Log object
     *
     * @var Zend_Log
     */
    protected $_logger;

<?php endif; ?>
    /**
     * Associative array of columns for this model
     *
     * @var array
     */
    protected $_columnsList;

    /**
     * Associative array of parent relationships for this model
     *
     * @var array
     */
    protected $_parentList;

    /**
     * Associative array of dependent relationships for this model
     *
     * @var array
     */
    protected $_dependentList;

    /**
     * Initializes common functionality in Model classes
     */
    protected function init()
    {
<?php if (! empty($this->_loggerName)):?>
        $this->_logger = Zend_Registry::get('<?=$this->_loggerName?>');

<?php endif; ?>

    }

    /**
     * Set the list of columns associated with this model
     *
     * @param array $data
     * @return <?=$this->_namespace?>_Model_ModelAbstract
     */
    public function setColumnsList($data)
    {
        $this->_columnsList = $data;
        return $this;
    }

    /**
     * Returns columns list array
     *
     * @return array
     */
    public function getColumnsList()
    {
        return $this->_columnsList;
    }

    /**
     * Set the list of relationships associated with this model
     *
     * @param array $data
     * @return <?=$this->_namespace?>_Model_ModelAbstract
     */
    public function setParentList($data)
    {
        $this->_parentList = $data;
        return $this;
    }

    /**
     * Returns relationship list array
     *
     * @return array
     */
    public function getParentList()
    {
        return $this->_parentList;
    }

    /**
     * Set the list of relationships associated with this model
     *
     * @param array $data
     * @return <?=$this->_namespace?>_Model_ModelAbstract
     */
    public function setDependentList($data)
    {
        $this->_dependentList = $data;
        return $this;
    }

    /**
     * Returns relationship list array
     *
     * @return array
     */
    public function getDependentList()
    {
        return $this->_dependentList;
    }

    /**
     * Returns the mapper associated with this model
     *
     * @return <?=$this->_namespace?>_Model_Mapper_MapperAbstract
     */
    public abstract function getMapper();

    /**
     * Converts database column name to php setter/getter function name
     * @param string $column
     */
    public function columnNameToVar($column)
    {
        if (! isset($this->_columnsList[$column])) {
<?php if (! empty($this->_loggerName)):?>
			$this->_logger->log("Column name to variable conversion failed for '$column' in columnNameToVar for " . get_class($this), Zend_Log::ERR);
<?php endif; ?>
            throw new Exception("column '$column' not found!");
        }

        return $this->_columnsList[$column];
    }

    /**
     * Converts database column name to PHP setter/getter function name
     * @param string $column
     */
    public function varNameToColumn($thevar)
    {
        foreach ($this->_columnsList as $column => $var) {
            if ($var == $thevar) {
                return $column;
            }
        }

        return null;
    }

    /**
     * Recognize methods for Belongs-To cases:
     * <code>findBy&lt;field&gt;()</code>
     * <code>findOneBy&lt;field&gt;()</code>
     * <code>load&lt;relationship&gt;()</code>
     *
     * @param string $method
     * @throws Exception if method does not exist
     * @param array $args
     */
    public function __call($method, array $args)
    {
        $matches = array();
        $result = null;

        if (preg_match('/^find(One)?By(\w+)?$/', $method, $matches)) {
            $methods = get_class_methods($this);
            $check = 'set' . $matches[2];

            $fieldName = $this->varNameToColumn($matches[2]);

            if (! in_array($check, $methods)) {
<?php if (! empty($this->_loggerName)):?>
				$this->_logger->log("Invalid field '{$matches[2]}' requested in call for $method in " . get_class($this), Zend_Log::ERR);
<?php endif; ?>
                throw new Exception(
                    "Invalid field {$matches[2]} requested for table"
                );
            }

            if ($matches[1] != '') {
                $result = $this->getMapper()->findOneByField($fieldName, $args[0],
                                                           $this);
            } else {
                $result = $this->getMapper()->findByField($fieldName, $args[0],
                                                        $this);
            }

            return $result;
        } elseif (preg_match('/load(\w+)/', $method, $matches)) {
            $result = $this->getMapper()->loadRelated($matches[1], $this);

            return $result;
        } elseif (preg_match('/^fetchList/', $method, $matches)) {
			return $this->getMapper()->$matches[0]($args[0]);
		}

<?php if (! empty($this->_loggerName)):?>
		$this->_logger->log("Unrecoginized method requested in call for '$method' in " . get_class($this), Zend_Log::ERR);
<?php endif; ?>
        throw new Exception("Unrecognized method '$method()'");
    }

    /**
     *  __set() is run when writing data to inaccessible properties overloading
     *  it to support setting columns.
     *
     * Example:
     * <code>class->column_name='foo'</code> or <code>class->ColumnName='foo'</code>
     *  will execute the function <code>class->setColumnName('foo')</code>
     *
     * @param string $name
     * @param mixed $value
     * @throws Exception if the property/column does not exist
     */
    public function __set($name, $value)
    {
        $name = $this->columnNameToVar($name);

        $method = 'set' . ucfirst($name);

        if (('mapper' == $name) || ! method_exists($this, $method)) {
<?php if (! empty($this->_loggerName)):?>
			$this->_logger->log("Unable to find setter for '$name' in " . get_class($this), Zend_Log::ERR);
<?php endif; ?>
            throw new Exception("name:$name value:$value - Invalid property");
        }

        $this->$method($value);
    }

    /**
     * __get() is utilized for reading data from inaccessible properties
     * overloading it to support getting columns value.
     *
     * Example:
     * <code>$foo=class->column_name</code> or <code>$foo=class->ColumnName</code>
     * will execute the function <code>$foo=class->getColumnName()</code>
     *
     * @param string $name
     * @param mixed $value
     * @throws Exception if the property/column does not exist
     * @return mixed
     */
    public function __get($name)
    {
        $method = 'get' . ucfirst($name);

        if (('mapper' == $name) || ! method_exists($this, $method)) {
            $name = $this->columnNameToVar($name);
            $method = 'get' . ucfirst($name);
            if (('mapper' == $name) || ! method_exists($this, $method)) {
<?php if (! empty($this->_loggerName)):?>
					$this->_logger->log("Unable to find getter for '$name' in " . get_class($this), Zend_Log::ERR);
<?php endif; ?>
                    throw new Exception("name:$name  - Invalid property");
            }
        }

        return $this->$method();
    }

    /**
     * Array of options/values to be set for this model. Options without a
     * matching method are ignored.
     *
     * @param array $options
     * @return <?=$this->_namespace?>_Model_ModelAbstract
     */
    public function setOptions(array $options)
    {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $key = preg_replace_callback('/_(.)/',
	    	   create_function('$matches','return ucfirst($matches[1]);'),
                                          $key);
            $method = 'set' . ucfirst($key);

            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }

     /**
      * Returns an object, keys are the field names.
      *
      * @see <?=$this->_namespace?>_Model_Mapper_MapperAbstract::toArray()
      * @return object
      */
     public function toObject()
     {
        return (object) $this->getMapper()->toArray($this);
     }

    /**
     * Returns the primary key column name
     *
     * @see <?=$this->_namespace?>_Model_DbTable_TableAbstract::getPrimaryKeyName()
     * @return string|array The name or array of names which form the primary key
     */
    public function getPrimaryKeyName()
    {
        return $this->getMapper()->getDbTable()->getPrimaryKeyName();
    }

    /**
     * Returns an associative array of column-value pairings if the primary key
     * is an array of values, or the value of the primary key if not
     *
     * @return any|array
     */
    public function getPrimaryKey()
    {
        $primary_key = $this->getPrimaryKeyName();

        if (is_array($primary_key)) {
            $result = array();
            foreach ($primary_key as $key) {
                $result[$key] = $this->$key;
            }

            return $result;
        } else {
            return $this->$primary_key;
        }

    }

    /**
     * Finds row by primary key
     *
     * @param string|array $primary_key
     * @return <?=$this->_namespace?>_Model_ModelAbstract
     *
     */
    public function find($primary_key)
    {
        $this->getMapper()->find($primary_key, $this);
        return $this;
    }

    /**
     * Returns an array, keys are the field names.
     *
     * @see <?=$this->_namespace?>_Model_Mapper_MapperAbstract::toArray()
     * @return array
     */
    public function toArray()
    {
        return $this->getMapper()->toArray($this);
    }

    /**
     * Sets the mapper class
     *
     * @param <?=$this->_namespace?>_Model_Mapper_MapperAbstract $mapper
     * @return <?=$this->_namespace?>_Model_ModelAbstract
     */
    public function setMapper($mapper)
    {
        $this->_mapper = $mapper;
        return $this;
    }

    /**
     * Saves current loaded row
     *
     *  $ignoreEmptyValues by default is true.
     *  This option will not update columns with empty values or
     *  will insert NULL values if inserting
     *
     * @see <?=$this->_namespace?>_Model_Mapper_MapperAbstract::save()
     * @param boolean $ignoreEmptyValues
     * @param boolean recursive
     * @return boolean If the save was sucessful
     */
    public function save($ignoreEmptyValues = true, $recursive = false, $useTransaction = true)
    {
        return $this->getMapper()->save($this, $ignoreEmptyValues, $recursive, $useTransaction);
    }

    /**
     * Returns the name of the table that this model represents
     *
     * @return string
     */
    public function getTableName() {
        return $this->getMapper()->getDbTable()->getTableName();
    }

    /**
     * Compare 2 objects and returns the values that differ
     *
     * @param <?=$this->_namespace?>_Model_ModelAbstract $model Object to be compared to
     * @param boolean $ignorePrimaryKey If primary keys should be considered
     * @param boolean $relations If should recurse into dependencies and parents
     * @param boolean $load If relations should be loaded if they are not already
     * @return array The values that differ between the two objects, or is in the
     *     			first that is not in the other
     */
/*    public function diff(<?=$this->_namespace?>_Model_ModelAbstract $model,
     $ignorePrimaryKey = true, $relations = false, $load = true
    ) {
        $other_values = $model->toArray();
        if ($ignorePrimaryKey) {
        	$other_pk = $model->getPrimaryKeyName();
        	if (is_array($other_pk)) {
        		foreach ($other_pk as $key) {
        			unset($other_values[$key]);
        		}
        	} else {
        		unset($other_values[$other_pk]);
        	}
        }

        $values = $this->toArray();
        if ($ignorePrimaryKey) {
        	$pk = $this->getPrimaryKeyName();
        	if (is_array($pk)) {
        		foreach ($pk as $key) {
        			unset($values[$key]);
        		}
        	} else {
        		unset($values[$pk]);
        	}
        }

        $result = array_diff_assoc($values, $other_values);

        if ($relations) {
        	$all_relations = array_merge($this->getDependentList(), $this->getParentList());
        	foreach($all_relations as $key => $property) {
        		$method = 'get' . ucfirst($property);

        		$value = $this->$method($load);
        		if (is_null($value)) {
        			continue;
        		} elseif ($value instanceof <?=$this->_namespace?>_Model_ModelAbstract)  {
        			$pk = $value->getPrimaryKeyName();
        			$value = $value->toArray();
        			if ($ignorePrimaryKey) {
    			    	if (is_array($pk)) {
    			    		foreach ($pk as $key) {
    			    			unset($value[$key]);
    			    		}
    			    	} else {
    			    		unset($value[$pk]);
    			    	}
        			}
        		} elseif (is_array($value)) {
        			$array = array();
        			foreach ($value as $val) {
        				if ($val instanceof <?=$this->_namespace?>_Model_ModelAbstract) {
    	    				$pk = $val->getPrimaryKeyName();
        					$val = $val->toArray();
        					if ($ignorePrimaryKey) {
    					    	if (is_array($pk)) {
    					    		foreach ($pk as $key) {
    					    			unset($val[$key]);
    					    		}
    					    	} else {
    					    		unset($val[$pk]);
    					    	}
        					}
    				    	$array[] = $val;
        				}
        			}
        			$value = $array;
        		}

        		if (method_exists($model, $method)) {
        			$other_value = $model->$method($load);
        			if (is_null($other_value)) {
        				$other_value = array();
        			} elseif ($other_value instanceof <?=$this->_namespace?>_Model_ModelAbstract) {
    	    			$pk = $other_value->getPrimaryKeyName();
        				$other_value = $other_value->toArray();
        				if ($ignorePrimaryKey) {
    				    	if (is_array($pk)) {
    				    		foreach ($pk as $key) {
    				    			unset($other_value[$key]);
    				    		}
    				    	} else {
    				    		unset($value[$pk]);
    				    	}
        				}
        			} elseif (is_array($other_value)) {
        				$other_array = array();
        				foreach ($other_value as $val) {
        					if ($val instanceof <?=$this->_namespace?>_Model_ModelAbstract) {
        						$pk = $val->getPrimaryKeyName();
        						$val = $val->toArray();
    		    				if ($ignorePrimaryKey) {
    						    	if (is_array($pk)) {
    						    		foreach ($pk as $key) {
    						    			unset($val[$key]);
    						    		}
    						    	} else {
    						    		unset($val[$pk]);
    						    	}
    		    				}
    					    	$other_array[] = $val;
        					}
        				}
        				$other_value = $other_array;
        			}
        		} else {
        			$other_value = array();
        		}

        		$diff = array_diff_assoc($value, $other_value);
        		if (! empty($diff)) {
        			$result[$property] = $diff;
        		}
        	}
        }

        return $result;
    }
    */
}
