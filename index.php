
<?php
/*
Plugin Name: IPTV SMS User Profile
Plugin URI: http://iptvsms.com/wordpress
Author: Sergey Volyanytsky
Author URI: http://github.com/volyanytsky
Version: 0.2.1
*/

require_once __DIR__ . '/autoload.php';

function add_options()
{
  add_option('test_devices_limit', '3');
  add_option('days_test', '7');
}

function iptvsms_install()
{
    DeviceTypes::createTable();
    DeviceTypesInPortals::createTable();
    DevicesInPortals::createTable();
    Portals::createTable();
    Devices::createTable();
    add_options();
}

function iptvsms_deactivate()
{
  delete_option('test_devices_limit');
  delete_option('days_test');
}

function iptvsms_uninstall()
{
    DeviceTypes::dropTable();
    DeviceTypesInPortals::dropTable();
    DevicesInPortals::dropTable();
    Portals::dropTable();
    Devices::dropTable();
}

register_activation_hook(__FILE__, 'iptvsms_install');
register_deactivation_hook(__FILE__, 'iptvsms_deactivate');
register_uninstall_hook(__FILE__, 'iptvsms_uninstall');

function route()
{
  if(isset($_GET['page']))
  {
    $slug = $_GET['page'];
    $parts = explode('-', $slug);
    if(count($parts) === 2)
    {
      $file = 'index';
    }
    elseif(count($parts) === 3)
    {
      $file = array_pop($parts);
    }
    else
    {
      exit('Неверный URL');
    }
    $dir = array_pop($parts);
    include_once "$dir/$file.php";
  }
}



function add_billing_settings_tab()
{
  $slug = 'iptvsms-settings';
  add_menu_page('Биллинг', 'Биллинг', 'manage_options', $slug, 'route');
  add_submenu_page($slug, 'Порталы', 'Порталы', 'manage_options', $slug . '-portals', 'route');
  add_submenu_page($slug, 'Тарифы', 'Тарифы', 'manage_options', $slug . '-tariffs', 'route');
  add_submenu_page($slug, 'Устройства', 'Устройства', 'manage_options', $slug . '-devices', 'route');
}

function add_customer_account_tab()
{
  $slug = 'iptvsms-account';
  add_menu_page('Мой кабинет', 'Мой кабинет', 'read', $slug, 'route');
}

add_action('admin_menu', 'add_billing_settings_tab');
add_action('admin_menu', 'add_customer_account_tab');
