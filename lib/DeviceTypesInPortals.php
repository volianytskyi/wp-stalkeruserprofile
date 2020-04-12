<?php

/**
 * Created by PhpStorm.
 * User: mousemaster
 * Date: 16.04.18
 * Time: 15:40
 */
class DeviceTypesInPortals extends BaseWpdb
{
    public static function getTableName()
    {
        global $wpdb;
        return $wpdb->prefix . 'device_types_in_portals';
    }

    public static function createTable()
    {
        global $wpdb;
        $table = self::getTableName();
        $sql = "
                CREATE TABLE IF NOT EXISTS $table (
                  `device_type_id` int(2) unsigned NOT NULL,
                  `portal_id` int(2) unsigned NOT NULL,
                  PRIMARY KEY (`device_type_id`),
                  UNIQUE KEY `device_type_id` (`device_type_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
              ";
        $wpdb->query($sql);
    }

    public static function add($deviceTypeId, $portalId)
    {
      global $wpdb;
      $data = [
        'device_type_id' => intval($deviceTypeId),
        'portal_id' => intval($portalId)
      ];
      $wpdb->insert(self::getTableName(), $data, ['%d', '%d']);
    }

    public static function update($deviceTypeId, $portalId)
    {
      global $wpdb;
      $update = [
        'portal_id' => intval($portalId)
      ];
      $where = [
        'device_type_id' => intval($deviceTypeId)
      ];
      $wpdb->update(self::getTableName(), $update, $where, ['%d'], ['%d']);
    }

    public static function deleteByDeviceTypeId($id)
    {
      global $wpdb;
      $wpdb->delete(self::getTableName(), ['device_type_id' => intval($id)], ['%d']);
    }

}
