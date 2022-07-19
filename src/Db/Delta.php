<?php
namespace DeltaTools\Db;

global $base_dir;
require_once $base_dir . 'db/db_mysql.inc';

class Delta
{
    public static $instance = null;
    private $db;

    private function __construct()
    {
        if(!$this->db){
            $this->db = new \ps_DB();
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