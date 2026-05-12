<?php
require_once __DIR__ . '/../config.php';

class Database
{
    public static function getConnection()
    {
        return config::getConnexion();
    }
    
    public static function pdo()
    {
        return config::getConnexion();
    }
}
?>