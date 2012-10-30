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

    public function __construct($namespace, $schema=null)
    {
    	$this->_namespace = $namespace;
        $this->setVars();
        $this->setFunctions();
        $this->setParentClass($schema);
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
    public function setParentClass($schema=null)
    {
    	$type = $this->getType();
    	$class = $this->_namespace . '_Db_'; //. '_Model_';
    if(! is_null($schema) and $schema != ''){ $class .= ucfirst($schema) . '_'; }
    	switch ($type) {
    		case self::TYPE_MODEL:
    			$class .= 'Model_Abstract';
    			break;
    		case self::TYPE_MAPPER:
    			$class .= 'Mapper_Abstract';
    			break;
    		case self::TYPE_DBTABLE:
    			$class .= 'Table_Abstract';
    			break;

    		default:
    			throw new Exception('Unknown Type');
    			break;
    	}

		$this->_parent_class = $class;
    }
}
