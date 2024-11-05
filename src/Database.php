<?php 
namespace Core;

final class DB
{
    public static function prefix($prefix = null) : Database
    {
        return Database::get()->prefix($prefix);
    }

    public static function select($columns = null) : Database
    {
        return Database::get()->select($columns);
    }

    public static function from($table) : Database
    {
        return Database::get()->from($table);
    }

    public static function query($query) : Database
    {
        return Database::get()->query($query);
    }
}
    
?>
