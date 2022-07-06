<?php
namespace DeltaTools\Db;

global $base_dir;
require_once $base_dir . 'db/db_mssql.inc';

class Optima
{
    public static $instance = null;
    private $db;

    private function __construct()
    {
        if(!$this->db){
            $this->db = new \ps_mssqlDB();
            $this->db->connect();
        }
    }

    public static function connect()
    {
        if(!self::$instance){
            self::$instance = new self();
        }
        return self::$instance->db;
    }

}