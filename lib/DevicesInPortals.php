<?php

/**
 * Created by PhpStorm.
 * User: mousemaster
 * Date: 16.04.18
 * Time: 15:40
 */
class DevicesInPortals extends BaseWpdb
{
    public static function getTableName()
    {
        global $wpdb;
        return $wpdb->prefix . 'devices_in_portals';
    }

    public static function createTable()
    {
        global $wpdb;
        $table = self::getTableName();
        $sql = "
                CREATE TABLE IF NOT EXISTS $table (
                  `device_id` int(11) unsigned NOT NULL,
                  `portal_id` int(2) unsigned NOT NULL,
                  PRIMARY KEY (`device_id`, `portal_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
              ";
        $wpdb->query($sql);
    }

    public static function findPortalIdByDeviceId($deviceId)
    {
      global $wpdb;
      $table = self::getTableName();
      $sql = "SELECT `portal_id` FROM $table WHERE `device_id` = %d";
      return $wpdb->get_col($wpdb->prepare($sql, intval($deviceId)))[0];
    }

}
