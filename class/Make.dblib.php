<?php
require_once 'Make.mssql.php';

class Make_dblib extends Make_mssql {

    protected function getPDOString($host, $port = 1433, $dbname) {
        $seperator = ':';
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $seperator = ',';
        }

        return "dblib:host=$host$seperator$port;dbname=$dbname";
    }
}