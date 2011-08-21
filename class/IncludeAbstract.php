<?php

/**
 * Class name should be (Model|Mapper|DbTable)_(ucfirst(TableName)
 *
 * @author Steven Hadfield
 */
abstract class IncludeAbstract
{
	const TYPE_MODEL = 'Model';
	const TYPE_MAPPER = 'Mapper';
	const TYPE_DBTABLE = 'DbTable';

    protected $_vars;

    protected $_functions;

    protected $_parent_class;

    protected $_namespace;

    public function __construct($namespace)
    {
    	$this->_namespace = $namespace;
        $this->setVars();
        $this->setFunctions();
        $this->setParentClass();
    }

    public abstract function getType();
    public abstract function setVars();
    public abstract function setFunctions();

    /**
     * @return string
     */
    public function getVars()
    {
        if (is_array($this->_vars)) {
            return join("\n\n", $this->_vars);
        }

        return $this->_vars;
    }

    /**
     * @return string
     */
    public function getFunctions()
    {
        if (is_array($this->_functions)) {
            return join("\n\n", $this->_functions);
        }

        return $this->_functions;
    }

    /**
     * @return string
     */
    public function getParentClass()
    {
		return $this->_parent_class;
    }

    /**
     * If not redeclared, sets the default parent class. Note that the parent
     * class should still extend the default parent class to maintain
     * interoperability.
     */
    public function setParentClass()
    {
    	$type = $this->getType();
    	$class = $this->_namespace . '_Model_';
    	switch ($type) {
    		case self::TYPE_MODEL:
    			$class .= 'ModelAbstract';
    			break;
    		case self::TYPE_MAPPER:
    			$class .= 'Mapper_MapperAbstract';
    			break;
    		case self::TYPE_DBTABLE:
    			$class .= 'DbTable_TableAbstract';
    			break;

    		default:
    			throw new Exception('Unknown Type');
    			break;
    	}

		$this->_parent_class = $class;
    }
}
