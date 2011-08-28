<?php

/**
 * PostgreSQL specific class for model creation
 */
class Make_pgsql extends MakeDbTable {
	private $_dataTypes = array(
			/* Numeric Types */
			'smallint' => 'int', 'integer' => 'int', 'bigint' => 'float', 'decimal' => 'float', 'numeric' => 'float', 'real' => 'float', 'double precision' => 'float', 'serial' => 'int', 'bigserial' => 'float',
			/* Monetary Types */
			'money' => 'float',
			/* Character Types */
			'character varyin' => 'string', 'varchar' => 'string', 'character' => 'string', 'char' => 'string', 'text' => 'string',
			/* Binary Data Types */
			'bytea' => 'string',
			/* Date/Time Types */
			
			/* Boolean Type */
	'boolean' => 'boolean' );
	
	protected function getPDOString($host, $port = 3306, $dbname) {
		return "pgsql:host=$host;port=$port;dbname=$dbname";
	}
	
	protected function getPDOSocketString($host, $dbname) {
		return "pgsql:unix_socket=$host;dbname=$dbname";
	}
	
	public function getTablesNamesFromDb($schema = 'public') {
		$res = $this->_pdo->query ( "select * from information_schema.tables where table_schema='public' and table_type='BASE TABLE'" )->fetchAll ();
		$tables = array ();
		foreach ( $res as $table ) {
			$tables [] = $table ['table_name'];
		}
		
		return $tables;
	}
	
	/**
	 * converts PostgreSQL data types to PHP data types
	 *
	 * @param string $str
	 * @return string
	 */
	protected function _convertTypeToPhp($str) {
		if (isset($this->_dataTypes[$str])) {
			return $this->_dataTypes[$str];
		}
		return 'string';
	}
	
	public function parseForeignKeys() {
		$tbname = $this->getTableName ();
		$query = $this->_pdo->query ( "SELECT tc.constraint_name, tc.table_name, kcu.column_name, ccu.table_name AS foreign_table_name,
    		ccu.column_name AS foreign_column_name 
			FROM 
			    information_schema.table_constraints AS tc 
			    JOIN information_schema.key_column_usage AS kcu ON tc.constraint_name = kcu.constraint_name
			    JOIN information_schema.constraint_column_usage AS ccu ON ccu.constraint_name = tc.constraint_name
			WHERE constraint_type = 'FOREIGN KEY' AND tc.table_name='$tbname'" );
		
		if (!$query) {
			throw new Exception ( "SELECT tc.constraint_name, tc.table_name, kcu.column_name, ccu.table_name AS foreign_table_name,
    		ccu.column_name AS foreign_column_name 
			FROM 
			    information_schema.table_constraints AS tc 
			    JOIN information_schema.key_column_usage AS kcu ON tc.constraint_name = kcu.constraint_name
			    JOIN information_schema.constraint_column_usage AS ccu ON ccu.constraint_name = tc.constraint_name
			WHERE constraint_type = 'FOREIGN KEY' AND tc.table_name='$tbname' returned false!." );
		}
		
		if (count ( $res = $query->fetchAll () ) <= 0) {
			throw new Exception ( "SELECT tc.constraint_name, tc.table_name, kcu.column_name, ccu.table_name AS foreign_table_name,
    		ccu.column_name AS foreign_column_name 
			FROM 
			    information_schema.table_constraints AS tc 
			    JOIN information_schema.key_column_usage AS kcu ON tc.constraint_name = kcu.constraint_name
			    JOIN information_schema.constraint_column_usage AS ccu ON ccu.constraint_name = tc.constraint_name
			WHERE constraint_type = 'FOREIGN KEY' AND tc.table_name='$tbname' did not provide known output" );
		}
		
		$keys = array ();
		foreach ( $res as $row ) {
			$keys [] = array ('key_name' => $row ['constraint_name'], 'column_name' => $row ['column_name'], 'foreign_tbl_name' => $row ['foreign_table_name'], 'foreign_tbl_column_name' => $row ['foreign_column_name'] );
			
			if ($this->_primaryKey ['phptype'] == 'array') {
				foreach ( $this->_primaryKey ['fields'] as $pk ) {
					if ($pk == $row ['column_name']) {
						$this->_primaryKey ['foreign_key'] = true;
					}
				}
			} else {
				if ($this->_primaryKey ['field'] == $row ['column_name']) {
					$this->_primaryKey ['foreign_key'] = true;
				}
			}
		}
		
		$this->setForeignKeysInfo ( $keys );
	}
	
	public function parseDependentTables() {
		$tbname = $this->getTableName();
		$tables = $this->getTableList();
		
		$dependents = array ();
		
		foreach ($tables as $table) {
			$query = $this->_pdo->query ( "SELECT tc.constraint_name, tc.table_name, tc.constraint_type ,kcu.column_name, ccu.table_name AS foreign_table_name,
	    	ccu.column_name AS foreign_column_name 
			FROM 
			information_schema.table_constraints AS tc 
			JOIN information_schema.key_column_usage AS kcu ON tc.constraint_name = kcu.constraint_name
			JOIN information_schema.constraint_column_usage AS ccu ON ccu.constraint_name = tc.constraint_name
			WHERE tc.table_name='$table'" );
			
			if (! $query) {
				throw new Exception ( "SELECT tc.constraint_name, tc.table_name, tc.constraint_type ,kcu.column_name, ccu.table_name AS foreign_table_name,
	    		ccu.column_name AS foreign_column_name 
				FROM 
				    information_schema.table_constraints AS tc 
				    JOIN information_schema.key_column_usage AS kcu ON tc.constraint_name = kcu.constraint_name
				    JOIN information_schema.constraint_column_usage AS ccu ON ccu.constraint_name = tc.constraint_name
				WHERE tc.table_name='$table' returned false!." );
			}
			
			if (count ( $res = $query->fetchAll () ) <= 0) {
				throw new Exception ( "SELECT tc.constraint_name, tc.table_name, tc.constraint_type ,kcu.column_name, ccu.table_name AS foreign_table_name,
	    		ccu.column_name AS foreign_column_name 
				FROM 
				    information_schema.table_constraints AS tc 
				    JOIN information_schema.key_column_usage AS kcu ON tc.constraint_name = kcu.constraint_name
				    JOIN information_schema.constraint_column_usage AS ccu ON ccu.constraint_name = tc.constraint_name
				WHERE tc.table_name='$table' did not provide known output" );
			}
			
			$pk_string = '';
			foreach ( $res as $row ) {
				if ($row ['constraint_type'] == 'PRIMARY KEY') {
					$pk_string = $row ['column_name'];
				} elseif($row ['constraint_type'] == 'FOREIGN KEY' && $row['foreign_table_name']==$tbname) {
					$dependents[] = array (
						'key_name' => $row ['constraint_name'], 
						'tbl_name' => $this->_namespace . '_Model_DbTable_' . $this->_getClassName ( $table ), 
						'type' => ($pk_string == $row ['column_name'] ? 'one' : 'many'), 
						'column_name' => $row ['column_name'], 
						'foreign_tbl_name' => $table, 
						'foreign_tbl_column_name' => $row ['foreign_column_name'] );
				}
			}
		}
		
		$this->setDependentTables ( $dependents );
	}
	
	public function parseDescribeTable() {
		$tbname = $this->getTableName ();
		
		// Comments
		$query = $this->_pdo->query ( "SELECT
			relname AS table_name,
		  	attname AS column_name,
		  	description AS column_comment
		FROM 
			pg_catalog.pg_attribute, 
			pg_catalog.pg_description, 
			pg_catalog.pg_class
		WHERE 
		  	pg_attribute.attnum = pg_description.objsubid AND
		  	pg_class.oid = pg_attribute.attrelid AND
		  	pg_class.relname = '$tbname'" );
		
		if (! $query) {
			throw new Exception ( "get comments query from '$tbname' returned false!." );
		}
		
		$comments = array ();
		foreach ( $query->fetchAll () as $comment ) {
			$comments [$comment ['column_name']] = $comment ['column_comment'];
		}
		
		// Describe
		$query = $this->_pdo->query ( "SELECT c.column_name, c.data_type, tc.constraint_type
        FROM
	        information_schema.columns AS c
	        LEFT JOIN information_schema.key_column_usage AS kcu ON c.table_name=kcu.table_name AND c.column_name = kcu.column_name
	        LEFT JOIN information_schema.table_constraints AS tc ON tc.constraint_name = kcu.constraint_name
        WHERE c.table_name = '$tbname'" );
		
		if (! $query) {
			throw new Exception ( "describe query from '$tbname' returned false!." );
		}
		
		$primaryKey = $columns = array ();
		foreach ( $query->fetchAll () as $column ) {
			if (isset ( $comments [$column ['column_name']] )) {
				$comment = $column ['column_name'];
			} else {
				$comment = null;
			}
			
			if ($column ['constraint_type'] == 'PRIMARY KEY') {
				$primaryKey [] = array (
					'field'       => $column['column_name'],
                    'type'        => $column['data_type'],
                    'phptype'     => $this->_convertTypeToPhp($column['data_type']),
                    'capital'     => $this->_getCapital($column['column_name']),
                	'foreign_key' => false,
				);
			}
			
			$columns[] = array(
				'field'       => $column['column_name'],
                'type'        => $column['data_type'],
                'phptype'     => $this->_convertTypeToPhp($column['data_type']),
                'capital'     => $this->_getCapital($column['column_name']),
                'comment' => $comment,
			);
			
			if (in_array(strtolower($column['column_name']), $this->_softDeleteColumnNames)) {
			    $this->_softDeleteColumn = $column['column_name'];
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

?>