<?php

/**
 * Created by PhpStorm.
 * User: mousemaster
 * Date: 16.04.18
 * Time: 15:38
 */
use Models\IDeviceType;

class DeviceTypes extends BaseWpdb
{
    public static function getTableName()
    {
        global $wpdb;
        return $wpdb->prefix . 'device_types';
    }

    public static function createTable()
    {
        global $wpdb;
        $table = self::getTableName();
        $sql = "CREATE TABLE IF NOT EXISTS $table (
                  `id` int(2) NOT NULL AUTO_INCREMENT,
                  `name` varchar(64) DEFAULT NULL,
                  `auth_type` enum('mac','login-password') NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
              ";
        $wpdb->query($sql);
    }

    public static function add(IDeviceType $type)
    {
      global $wpdb;
      $table = self::getTableName();

      $sql = "INSERT INTO `$table`(`name`, `auth_type`) VALUES(%s, %s)";
      $args = [
        $type->getName(),
        $type->getAuthType()
      ];

      $wpdb->query($wpdb->prepare($sql, $args));

      return $wpdb->insert_id;
    }

    public static function update(IDeviceType $type)
    {
      global $wpdb;
      $table = self::getTableName();

      $data = [
        'name' => $type->getName(),
        'auth_type' => $type->getAuthType()
      ];
      $where = [
        'id' => $type->getId()
      ];
      $wpdb->update($table, $data, $where, ['%s', '%s'], ['%d']);

    }

    public static function deleteById($id)
    {
      global $wpdb;
      $wpdb->delete(self::getTableName(), ['id' => intval($id)], ['%d']);
    }

}
