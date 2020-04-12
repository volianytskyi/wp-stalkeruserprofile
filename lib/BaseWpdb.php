<?php

/**
 * Created by PhpStorm.
 * User: mousemaster
 * Date: 17.04.18
 * Time: 16:03
 */
abstract class BaseWpdb
{
    public static function getTableName()
    {
        throw new Exception(__METHOD__ . ' must be redefined');
    }

    public static function createTable()
    {
        throw new Exception(__METHOD__ . 'must be redefined');
    }

    public static final function dropTable()
    {
        global $wpdb;
        $wpdb->query('DROP TABLE IF EXISTS ' . static::getTableName());
    }

    public static final function selectAll()
    {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM " . static::getTableName(), ARRAY_A);
    }

    public static function findById($id)
    {
        global $wpdb;
        return $wpdb->get_row("SELECT * FROM " . self::getTableName() . " WHERE $key = " . intval($id));
    }
}
