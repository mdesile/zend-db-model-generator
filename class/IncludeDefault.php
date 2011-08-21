<?php

require_once 'IncludeAbstract.php';

/**
 * @author Steven Hadfield
 *
 *
 */
class Model_Default extends IncludeAbstract {

	/**
	 *
	 * @see IncludeAbstract::getType()
	 */
	public function getType() {
		return self::TYPE_MODEL;
	}

	/**
	 *
	 * @see IncludeAbstract::setFunctions()
	 */
	public function setFunctions() {
	}

	/**
	 *
	 * @see IncludeAbstract::setVars()
	 */
	public function setVars() {
	}
}

/**
 * @author Steven Hadfield
 *
 *
 */
class Mapper_Default extends IncludeAbstract {

	/**
	 *
	 * @see IncludeAbstract::getType()
	 */
	public function getType() {
		return self::TYPE_MAPPER;
	}

	/**
	 *
	 * @see IncludeAbstract::setFunctions()
	 */
	public function setFunctions() {
	}

	/**
	 *
	 * @see IncludeAbstract::setVars()
	 */
	public function setVars() {
	}
}

/**
 * @author Steven Hadfield
 *
 *
 */
class DbTable_Default extends IncludeAbstract {

	/**
	 *
	 * @see IncludeAbstract::getType()
	 */
	public function getType() {
		return self::TYPE_DBTABLE;
	}

	/**
	 *
	 * @see IncludeAbstract::setFunctions()
	 */
	public function setFunctions() {
	}

	/**
	 *
	 * @see IncludeAbstract::setVars()
	 */
	public function setVars() {
	}
}
