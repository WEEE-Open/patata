<?php
define('PATABASE_PATH', 'patabase.db');

class MyDB extends SQLite3 {
    public function __construct($rw = true)
    {
        parent::__construct(PATABASE_PATH, $rw ? SQLITE3_OPEN_READWRITE : SQLITE3_OPEN_READONLY);
    }
}
