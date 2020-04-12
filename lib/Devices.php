<?php

/**
 * Created by PhpStorm.
 * User: mousemaster
 * Date: 16.04.18
 * Time: 15:39
 */
class Devices extends BaseWpdb
{
    public static function getTableName()
    {
        global $wpdb;
        return $wpdb->prefix . 'devices';
    }

    public static function createTable()
    {
        global $wpdb;
        $table = self::getTableName();
        $sql = "
                CREATE TABLE IF NOT EXISTS $table (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `user_id` int(11) unsigned DEFAULT NULL,
                  `reseller_id` int(11) unsigned DEFAULT NULL,
                  `login` varchar(64) DEFAULT NULL,
                  `pass` varchar(64) DEFAULT NULL,
                  `name` varchar(64) DEFAULT NULL,
                  `status` tinyint(1) DEFAULT '1',
                  `ip` varchar(64) DEFAULT NULL,
                  `device_type_id` int(1) DEFAULT NULL,
                  `mac` varchar(64) DEFAULT NULL,
                  `expire_date` varchar(64) DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `login` (`login`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
              ";
        $wpdb->query($sql);
    }

    public static function getUserDevicesWithType($userId)
    {
      global $wpdb;
      $table = self::getTableName();
      $deviceTypes = DeviceTypes::getTableName();

      $sql = "SELECT
                `wp_device_types`.`name` AS device_type,
                `wp_devices`.*
              FROM wp_devices
              JOIN wp_device_types
              ON `wp_devices`.`device_type_id` = `wp_device_types`.`id`
              WHERE `user_id` = %d";

      return $wpdb->get_results($wpdb->prepare($sql, [intval($userId)]), ARRAY_A);
    }

    public static function getTestDevicesCountByUserId($id)
    {
        global $wpdb;
        return $wpdb->get_col('SELECT COUNT(1) FROM ' . self::getTableName() . ' WHERE user_id = '. intval($id) . ' AND name LIKE "%days test%"')[0];
    }

    public static function isLoginUnique($login)
    {
      global $wpdb;
      $sql = $wpdb->prepare('SELECT COUNT(1) FROM '.self::getTableName().' WHERE login = %s', $login);
      if(current($wpdb->get_col($sql)))
      {
        return false;
      }
      return true;
    }

    public static function addNewDevice(StalkerPortalUser $user, $userId, $deviceTypeId, $portalId, $resellerId = null)
    {
      global $wpdb;
      $data = [
        'user_id' => intval($userId),
        'login' => $user->getLogin(),
        'pass' => $user->getPassword(),
        'name' => $user->getFullName(),
        'device_type_id' => intval($deviceTypeId),
        'reseller_id' => intval($resellerId)
      ];

      $wpdb->insert(self::getTableName(), $data, ['%d', '%s', '%s', '%s', '%d', '%d']);

      $data = [
        'device_id' => $wpdb->insert_id,
        'portal_id' => intval($portalId)
      ];
      $wpdb->insert(DevicesInPortals::getTableName(), $data, ['%d', '%d']);
    }

}
