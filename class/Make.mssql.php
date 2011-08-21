<?php

/**
 * Microsoft SQL specific class for model creation
 */
class Make_mssql extends MakeDbTable {

    protected $_server_version = null;

    protected function getPDOString($host, $port = 1433, $dbname) {
        $seperator = ':';
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $seperator = ',';
        }

        return "mssql:host=$host$seperator$port;dbname=$dbname";
    }

    protected function getServerVersion() {
        if ($this->_server_version === null) {
            // Get Server version. Try the SQL Server 2000+ method first
            $version = $this->_pdo->query("select CAST(SERVERPROPERTY('productversion') as VARCHAR) as version");

            // For SQL Server 7 and older that don't have SERVERPROPERTY, try @@VERSION
            if (! $version) {
                $version = $this->_pdo->query("select CAST(@@VERSION as VARCHAR)");
            }

            if ($version) {
                $version = $version->fetchAll(PDO::FETCH_NUM);
                if (count($version)) {
                    $version = $version[0][0];
                }
            }

            $this->_server_version = $version;
        }

        return $this->_server_version;
    }

    public function getTablesNamesFromDb() {
        $res = $this->_pdo->query("select name
                from sysobjects
                where xtype = 'U' and uid = 1 and
                name not in ('sysdiagrams', 'dtproperties')")->fetchAll();
        $tables = array();
        foreach ($res as $table){
            $tables[] = $table[0];
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
        if (preg_match('/(tinyint|bit)/', $str)) {
            $res = 'boolean';
        } elseif(preg_match('/(date|time|text|binary|char|xml|uniqueidentifier)/', $str)) {
            $res = 'string';
        } elseif (preg_match('/(decimal|numeric|real|float|money)/', $str)) {
            $res = 'float';
        } elseif (preg_match('#^(?:tiny|small|medium|long|big|var)?(\w+)(?:\(\d+\))?(?:\s\w+)*$#',$str,$matches)) {
            $res = $matches[1];
        }

        return $res;
    }

    public function parseForeignKeys() {
        $tbname = $this->getTableName();
        // $this->_pdo->query("SET NAMES UTF8");
        $qry = $this->_pdo->query("select
        so_ftable.name as foreign_table, sc_fcol.name as foreign_column, sc_col.name as local_column, so_key.name as fk_name
            from sysforeignkeys as fk
            join sysobjects as so_table on so_table.name = '$tbname' and fk.fkeyid = so_table.id and xtype = 'U'
            join sysobjects as so_key on fk.constid = so_key.id
            join sysobjects as so_ftable on fk.rkeyid = so_ftable.id
            join syscolumns as sc_fcol on fk.rkey = sc_fcol.colid and fk.rkeyid = sc_fcol.id
            join syscolumns as sc_col on fk.fkey = sc_col.colid and fk.fkeyid = sc_col.id
            order by fk.keyno");

        if (!$qry) {
            throw new Exception("Unable to get list of Foreign keys for $tbname.");
        }

        $res = $qry->fetchAll();

        $keys = array();
        foreach ($res as $fkey) {
            if (! isset($keys[$fkey['fk_name']])) {
                $keys[$fkey['fk_name']] = array(
                  'key_name' => $fkey['fk_name'],
                  'column_name' => $fkey['local_column'],
                  'foreign_tbl_name' => $fkey['foreign_table'],
                  'foreign_tbl_column_name' => $fkey['foreign_column']
                );
            } else {
                if (! is_array($keys[$fkey['fk_name']]['column_name'])) {
                    $keys[$fkey['fk_name']]['column_name'] = array($keys[$fkey['fk_name']]['column_name'], $fkey['local_column']);
                } else {
                    $keys[$fkey['fk_name']]['column_name'][] = $keys[$fkey['fk_name']]['column_name'];
                }

                if (! is_array($keys[$fkey['fk_name']]['foreign_tbl_column_name'])) {
                    $keys[$fkey['fk_name']]['foreign_tbl_column_name'] = array($keys[$fkey['fk_name']]['foreign_tbl_column_name'], $fkey['foreign_tbl_column_name']);
                } else {
                    $keys[$fkey['fk_name']]['foreign_tbl_column_name'][] = $fkey['foreign_column'];
                }
            }

            if ($this->_primaryKey['phptype'] == 'array') {
    			foreach ($this->_primaryKey['fields'] as $pk) {
    			    if ($pk == $fkey['local_column']) {
    			        $this->_primaryKey['foreign_key'] = true;
    			    }
    			}
    		} else {
    		    if ($this->_primaryKey['field'] == $fkey['local_column']) {
    		        $this->_primaryKey['foreign_key'] = true;
    		    }
    		}
        }

        $this->setForeignKeysInfo($keys);
    }

    public function parseDependentTables() {
        $tbname = $this->getTableName();

        $qry = $this->_pdo->query("select so_ftable.name as foreign_table, sc_fcol.name as foreign_column, sc_col.name as local_column, so_key.name as fk_name
            from sysforeignkeys as fk
            join sysobjects as so_table on so_table.name = '$tbname' and fk.rkeyid = so_table.id and xtype = 'U'
            join sysobjects as so_key on fk.constid = so_key.id
            join sysobjects as so_ftable on fk.fkeyid = so_ftable.id
            join syscolumns as sc_fcol on fk.rkey = sc_fcol.colid and fk.rkeyid = sc_fcol.id
            join syscolumns as sc_col on fk.fkey = sc_col.colid and fk.fkeyid = sc_col.id
            order by fk.keyno");

        if (!$qry) {
            throw new Exception("Unable to get list of dependencies for $tbname.");
        }

        $res = $qry->fetchAll();
        $dependents = array();

        foreach ($res as $fkey) {
            if (! isset($dependents[$fkey['fk_name']])) {
                $dependents[$fkey['fk_name']] = array(
                    'key_name' => $fkey['fk_name'],
                    'tbl_name' => $this->_namespace . '_Model_DbTable_' . $this->_getClassName($fkey['foreign_table']),
                    'column_name' => $fkey['local_column'],
                    'foreign_tbl_name' => $fkey['foreign_table'],
                    'foreign_tbl_column_name'=> $fkey['foreign_column'],
                );
            } else {
                if (! is_array($dependents[$fkey['fk_name']]['column_name'])) {
                    $dependents[$fkey['fk_name']]['column_name'] = array($dependents[$fkey['fk_name']]['column_name'], $fkey['local_column']);
                } else {
                    $dependents[$fkey['fk_name']]['column_name'][] = $dependents[$fkey['fk_name']]['column_name'];
                }

                if (! is_array($dependents[$fkey['fk_name']]['foreign_tbl_column_name'])) {
                    $dependents[$fkey['fk_name']]['foreign_tbl_column_name'] = array($dependents[$fkey['fk_name']]['foreign_tbl_column_name'], $fkey['foreign_tbl_column_name']);
                } else {
                    $dependents[$fkey['fk_name']]['foreign_tbl_column_name'][] = $dependents['foreign_column'];
                }
            }
        }

        // Determine if this is a one to many or one to one by comparing the primary key columns to the key columns
        foreach ($dependents as &$key) {
            $pk_query = $this->_pdo->query("select c.name
                from sysindexes i
                    join sysobjects o ON i.id = o.id
                    join sysobjects pk ON i.name = pk.name AND pk.parent_obj = i.id AND pk.xtype = 'PK'
                    join sysindexkeys ik on i.id = ik.id and i.indid = ik.indid
                    join syscolumns c ON ik.id = c.id AND ik.colid = c.colid
                where o.name = '{$key['foreign_tbl_name']}'
                order by ik.keyno");

            if (!$pk_query) {
                throw new Exception("Unable to retrieve primary key information for $tbname");
            }

            $res_pk = $pk_query->fetchAll();
            $primaryKey = array();
            foreach ($res_pk as $pkey) {
                $primaryKey[] = $pkey['name'];
                if (! is_array($key['column_name'])) {
                    if ($pkey['name'] == $key['column_name']) {
                        // Assign to be one now, but if there are more primary keys, this may be reassigned
                        $key['type'] = 'one';
                    } else {
                        // If they don't match, recognize this as one-to-many or many-to-many early
                        $key['type'] = 'many';
                        continue 2;
                    }

                } elseif (! in_array($pkey['name'], $key['column_name'])) {
                    // If they don't match, recognize this as one-to-many or many-to-many early
                    $key['type'] = 'many';
                    continue 2;
                }
            }

            // If the foreign key is an array and all of them were found
            if (count($primaryKey) == count($key['column_name'])) {
                $key['type'] = 'one';
            } else {
                $key['type'] = 'many';
            }
        }

        $this->setDependentTables($dependents);

    }

    public function parseDescribeTable() {

        $tbname = $this->getTableName();

        // Works with SQL Server < 2005
        $version = $this->getServerVersion();
        if (! $version || $version < 9) {
            $columns_query = $this->_pdo->query("select
               sc.name, st.name as type,sc.length as type_length,sp.value as comment
                from syscolumns as sc
                join sysobjects as so on sc.id = so.id and so.type = 'U' and so.name = '$tbname'
                join systypes as st on sc.xtype=st.xusertype
                left join sysproperties sp on sp.id = sc.id and sp.name = 'MS_Description' and sc.colid = sp.smallid
               order by sc.colorder");
        } else {
            // Works with SQL Server >= 2005
            $columns_query = $this->_pdo->query("select
                sc.name, st.name as type,sc.length as type_length, ep.value as comment
                from syscolumns as sc
                 join sysobjects as so on sc.id = so.id and so.type = 'U' and so.name = '$tbname'
                 join systypes as st on sc.xtype=st.xusertype
                 left join sys.extended_properties ep on ep.major_id = sc.id and sc.colid = ep.minor_id and ep.name = 'MS_Description'
                order by sc.colorder");
        }

       if (!$columns_query) {
           throw new Exception("Unable to retrieve column information for $tbname");
       }

        $res_columns = $columns_query->fetchAll();
        if (! count($res_columns)) {
            throw new Exception("No columns found for $tbname");
        }

        foreach ($res_columns as $row) {
            $columns[$row['name']] = array(
                'field' => $row['name'],
                'type'  => $row['type'] . (empty($row['type_length']) ? '' : '(' . $row['type_length'] . ')'),
                'phptype' => $this->_convertTypeToPhp($row['type']),
                'capital' => $this->_getCapital($row['name']),
            );

            if (in_array(strtolower($row['name']), $this->_softDeleteColumnNames)) {
                $this->_softDeleteColumn = $row['name'];
            }
        }

        $pk_query = $this->_pdo->query("select c.name
            from sysindexes i
                join sysobjects o ON i.id = o.id
                join sysobjects pk ON i.name = pk.name AND pk.parent_obj = i.id AND pk.xtype = 'PK'
                join sysindexkeys ik on i.id = ik.id and i.indid = ik.indid
                join syscolumns c ON ik.id = c.id AND ik.colid = c.colid
            where o.name = '$tbname'
            order by ik.keyno");

        if (!$pk_query) {
            throw new Exception("Unable to retrieve primary key information for $tbname");
        }

        $res_pk = $pk_query->fetchAll();
        $primaryKey = array();
        foreach ($res_pk as $key) {
            $primaryKey[] = array(
                'field' => $key['name'],
                'type'  => $columns[$key['name']]['type'],
                'phptype' => $columns[$key['name']]['phptype'],
                'capital' => $columns[$key['name']]['capital'],
                'foreign_key' => false,
            );
        }

        if (sizeof($primaryKey) == 0) {
            throw new Exception("Didn't find primary keys in table $tbname.");
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
