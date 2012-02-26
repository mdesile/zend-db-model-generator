<?php

/**
 * MySQL specific class for model creation
 */
class Make_mysql extends MakeDbTable {

    protected function getPDOString($host, $port = 3306, $dbname) {
        return "mysql:host=$host;port=$port;dbname=$dbname";
    }
    
    protected function getPDOSocketString($host, $dbname) {
    	return "mysql:unix_socket=$host;dbname=$dbname";
    }
    

	public function getTablesNamesFromDb() {
		$res = $this->_pdo->query('show tables')->fetchAll();
		$tables = array();
		foreach ($res as $table) {
		    $tables[]=$table[0];
		}

		return $tables;
	}


	/**
	 * converts MySQL data types to PHP data types
	 *
	 * @param string $str
	 * @return string
	 */
	protected function _convertTypeToPhp($str) {
		if (preg_match('/(tinyint\(1\)|bit)/', $str)) {
			$res = 'boolean';
		} elseif(preg_match('/(datetime|timestamp|blob|char|enum|text|date)/', $str)) {
			$res = 'string';
		} elseif (preg_match('/(decimal|numeric|float|double)/', $str)) {
			$res = 'float';
		} elseif (preg_match('#^(?:tiny|small|medium|long|big|var)?(\w+)(?:\(\d+\))?(?:\s\w+)*$#',$str,$matches)) {
			$res = $matches[1];
		} else {
			print "Can't convert column type to PHP - Unrecognized type: $str";
		}

		return $res;
	}

	public function parseForeignKeys() {
		$tbname = $this->getTableName();
		$this->_pdo->query("SET NAMES UTF8");
		$qry = $this->_pdo->query("show create table `$tbname`");

		if (!$qry) {
			throw new Exception("`show create table $tbname` returned false!.");
		}

		$res = $qry->fetchAll();
		if (!isset($res[0]['Create Table'])) {
			throw new Exception("`show create table $tbname` did not provide known output");
		}

		$query = $res[0]['Create Table'];
		$lines = explode("\n",$query);
		$tblinfo = array();
		$keys = array();
		foreach ($lines as $line) {
			preg_match('/^\s*CONSTRAINT `(\w+)` FOREIGN KEY \(`(.+)`\) REFERENCES `(\w+)` \(`(.+)`\)/', $line, $tblinfo);
			if (sizeof($tblinfo) > 0) {
			    if (strpos($tblinfo[2], ',') !== false) {
			        $column_name = explode(',', $tblinfo[2]);
			    } else {
			        $column_name = $tblinfo[2];
			    }

			    if (strpos($tblinfo[4], ',') !== false) {
			        $foreign_column_name = explode('`,`', $tblinfo[4]);
			    } else {
			        $foreign_column_name = $tblinfo[4];
			    }

				$keys[] = array(
                  'key_name' => $tblinfo[1],
                  'column_name' => $column_name,
                  'foreign_tbl_name' => $tblinfo[3],
                  'foreign_tbl_column_name' => $foreign_column_name,
				);

				if ($this->_primaryKey['phptype'] == 'array') {
    				foreach ($this->_primaryKey['fields'] as $pk) {
    				    if ($pk == $column_name) {
    				        $this->_primaryKey['foreign_key'] = true;
    				    }
    				}
				} else {
				    if ($this->_primaryKey['field'] == $column_name) {
				        $this->_primaryKey['foreign_key'] = true;
				    }
				}
			}

		}

		$this->setForeignKeysInfo($keys);

	}

	public function parseDependentTables() {
		$tbname = $this->getTableName();
		$tables = $this->getTableList();
		$this->_pdo->query("SET NAMES UTF8");

		$dependents = array();

		foreach ($tables as $table) {
			$qry=$this->_pdo->query("show create table `$table`");

			if (!$qry) {
			    throw new Exception("`show create table $table` returned false!");
			}

			$res = $qry->fetchAll();
			if (!isset($res[0]['Create Table'])) {
				throw new Exception("`show create table $table` did not provide known output");
			}

			$query = $res[0]['Create Table'];
			$lines = explode("\n",$query);
			$tblinfo = array();
			$pk = '';
			foreach ($lines as $line) {
				if (preg_match('/^\s*PRIMARY KEY \(`(.+)`\)/', $line, $matches)) {
				    $pk_string = $matches[1];
				} elseif (preg_match("/^\s*CONSTRAINT `(\w+)` FOREIGN KEY \(`(.+)`\) REFERENCES `$tbname` \(`(.+)`\)/", $line, $tblinfo)) {
    			    if (strpos($tblinfo[2], ',') !== false) {
    			        $column_name = explode('`,`', $tblinfo[2]);
    			    } else {
    			        $column_name = $tblinfo[2];
    			    }

    			    if (strpos($tblinfo[3], ',') !== false) {
    			        $foreign_column_name = explode(',', $tblinfo[3]);
    			    } else {
    			        $foreign_column_name = $tblinfo[3];
    			    }

					$dependents[] = array(
						'key_name' => $tblinfo[1],
						'tbl_name' => $this->_namespace . '_Model_DbTable_' . $this->_getClassName($table),
						'type' => ($pk_string == $tblinfo[2] ? 'one' : 'many'),
                  		'column_name' => $column_name,
						'foreign_tbl_name' => $table,
                  		'foreign_tbl_column_name' => $foreign_column_name
					);
				}

			}
		}


		$this->setDependentTables($dependents);

	}

	public function parseDescribeTable() {

		$tbname=$this->getTableName();
		$this->_pdo->query("SET NAMES UTF8");

		$qry_create = $this->_pdo->query("show create table `$tbname`");

		if (!$qry_create) {
			throw new Exception("`describe $tbname` returned false!.");
		}

		$res_create = $qry_create->fetchAll();
		if (!isset($res_create[0]['Create Table'])) {
			throw new Exception("`show create table $tbname` did not provide known output");
		}

		$query=$res_create[0]['Create Table'];
		$lines=explode("\n",$query);
		$comments=array();
		foreach ($lines as $line) {
			if (preg_match('/`(\w+)`.+COMMENT\s\'(.+)\'/',$line,$comment)) {
				$comments[$comment[1]] = $comment[2];
			} elseif (preg_match('/\).+COMMENT=\'(.+)\'/',$line,$comment)) {
				$this->_classDesc = $comment[1];
			}
		}

		$qry = $this->_pdo->query("describe `$tbname`");

		if (!$qry) {
			throw new Exception("`describe $tbname` returned false!.");
		}

		$res = $qry->fetchAll();
		$primaryKey = array();

		foreach ($res as $row) {
			if (isset($comments[$row['Field']])) {
				$comment = $comments[$row['Field']];
			} else {
				$comment = null;
			}

			if ($row['Key'] == 'PRI') {
				$primaryKey[] = array(
                    'field'       => $row['Field'],
                    'type'        => $row['Type'],
                    'phptype'     => $this->_convertTypeToPhp($row['Type']),
                    'capital'     => $this->_getCapital($row['Field']),
                	'foreign_key' => false,
				);
			}

			$columns[] = array(
                'field'   => $row['Field'],
                'type'    => $row['Type'],
                'phptype' => $this->_convertTypeToPhp($row['Type']),
                'capital' => $this->_getCapital($row['Field']),
                'comment' => $comment,
			);


			if (in_array(strtolower($row['Field']), $this->_softDeleteColumnNames)) {
			    $this->_softDeleteColumn = $row['name'];
			}
		}

		if (sizeof($primaryKey) == 0) {
			throw new Exception("Did not find any primary keys for table $tbname.");
		} elseif (sizeof($primaryKey) == 1) {
			$primaryKey = $primaryKey[0];
		} else {
			$temp = array(
                'field'       => 'array(',
                'type'        => 'array',
                'phptype'     => 'array',
                'capital'     => '',
                'fields'      => array(),
                'foreign_key' => false,
			);

			$fields = count($primaryKey);
			$i = 0;
			foreach ($primaryKey as $key) {
				$temp['field'] .= "'" . $key['field'] . "'";
				$i++;
				if ($fields != $i) {
					$temp['field'] .= ', ';
				}
				$temp['fields'][] = $key;
			}

			$temp['field'] .= ')';

			$primaryKey = $temp;
		}

		$this->_primaryKey = $primaryKey;
		$this->_columns = $columns;
	}

}
