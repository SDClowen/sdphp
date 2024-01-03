<?php 
namespace Core;

final class DB extends Database
{
    public static function select($columns = null) : Database
    {
        return parent::get()->select($columns);
    }

    public static function from($table) : Database
    {
        return parent::get()->from($table);
    }
}
    
?>