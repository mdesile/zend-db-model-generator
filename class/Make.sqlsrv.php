<?php
require_once 'Make.mssql.php';

class Make_sqlsqrv extends Make_mssql {

    protected function getPDOString($host, $port, $dbname) {
        $seperator = ':';
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $seperator = ',';
        }

        return "sqlsrv:server=$host; port=$port; Database=$dbname";
    }
}