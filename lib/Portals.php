<?php

/**
 * Created by PhpStorm.
 * User: mousemaster
 * Date: 16.04.18
 * Time: 15:39
 */
class Portals extends BaseWpdb
{
    public static function getTableName()
    {
        global $wpdb;
        return $wpdb->prefix . 'portals';
    }

    public static function createTable()
    {
        global $wpdb;
        $table = self::getTableName();
        $sql = "
                CREATE TABLE IF NOT EXISTS $table (
                    `id` int(2) unsigned NOT NULL AUTO_INCREMENT,
                    `name` varchar(255) NOT NULL,
                    `api_url` varchar(255) DEFAULT NULL,
                    `api_login` varchar(255) DEFAULT NULL,
                    `api_pass` varchar(255) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `id` (`id`),
                    UNIQUE KEY `name` (`name`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $wpdb->query($sql);
    }

    public static function findById($id)
    {
        global $wpdb;
        return $wpdb->get_row("SELECT * FROM " . self::getTableName() . " WHERE id = " . intval($id), ARRAY_A);
    }

    public static function findByDeviceTypeId($id)
    {
        global $wpdb;
        $table = self::getTableName();
        $deviceTypesInPortals = DeviceTypesInPortals::getTableName();
        return $wpdb->get_row("  SELECT id, api_url, api_login, api_pass
                                    FROM $table
                                    JOIN $deviceTypesInPortals
                                      ON $table.id = $deviceTypesInPortals.portal_id
                                    WHERE $deviceTypesInPortals.device_type_id = " . intval($id),
            ARRAY_A);

    }

}
