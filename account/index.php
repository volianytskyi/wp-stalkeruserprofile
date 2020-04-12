<?php

$redirect = "<script type='text/javascript'>window.location=document.location.href;</script>";
if(!current_user_can('read'))
{
  exit('У вас недостаточно прав для совершения данного действия');
}

require_once __DIR__ . '/../autoload.php';

use Http\HttpClient as Http;
use StalkerPortal\ApiV1\Resources\Users;
use StalkerPortal\ApiV1\Resources\Tariffs;
use StalkerPortal\ApiV1\Exceptions\StalkerPortalException as StalkerException;

try {

  function findTariffByExternalId(array $tariffs, $external_id)
  {
    foreach ($tariffs as $t)
    {
      if($t['external_id'] == $external_id)
      {
        return $t;
      }
    }
    return null;
  }

  $user_id = get_current_user_id();
  global $wpdb;
  $portals_table = $wpdb->prefix . 'portals';
  $devices_table = $wpdb->prefix . 'devices';
  $device_types_table = $wpdb->prefix . 'device_types';
  $device_types_in_portals_table = $wpdb->prefix . 'device_types_in_portals';
  $devices_in_portals_table = $wpdb->prefix . 'devices_in_portals';

  if(!isset($_POST['action']))
  {
    $devices = Devices::getUserDevicesWithType($user_id);

  //   foreach($devices as &$device)
  //   {
  //     $credits = Portals::findById($device['portal_id']);
  //     $http_client = new Http($credits['api_url'], $credits['api_login'], $credits['api_pass']);
  //     $resource = new Users($http_client);
  //
  //     $portal_user_data = '';
  //     (!empty($device['mac'])) ? $portal_user_data = $resource->select($device['mac']) : $portal_user_data = $resource->select($device['login']);
  //
  //     $tariffs_resource = new Tariffs($http_client);
  //     $all_tariffs = $tariffs_resource->select();
  //     $tariff_data = findTariffByExternalId($all_tariffs, $portal_user_data['tariff_plan']);
  //     $tariff = $tariffs_resource->select($tariff_data['id']);
  //
  //     $device['name'] = $portal_user_data['full_name'];
  //     $device['ip'] = $portal_user_data['ip'];
  //     $device['tariff_plan'] = $tariff['name'];
  //     $device['expire_date'] = $portal_user_data['end_date'];
  //     $device['status'] = $portal_user_data['status'];
  //   }
  }




  $device_types = DeviceTypes::selectAll();
  if(isset($_POST['action']))
  {
    switch ($_POST['action'])
    {
      case 'update_device_details':
        $deviceId = intval($_POST['id']);
        $portalId = DevicesInPortals::findPortalIdByDeviceId($deviceId);
        
        break;

      case 'test':
        $devices_count = Devices::getTestDevicesCountByUserId($user_id);
        if($devices_count >= get_option("test_devices_limit"))
        {
          exit('К сожалению, вы превысили допустимый лимит тестовых аккаунтов для одного абонента');
        }

        $device_type_id = intval($_POST['test_device_type']);
        $portal = Portals::findByDeviceTypeId($device_type_id);
        $users = new Users(new Http($portal['api_url'], $portal['api_login'], $portal['api_pass']));
        $i = 1;
        do {
          $login = explode('@', get_userdata($user_id)->user_email)[0] . '_' . $i++;
          echo $login . PHP_EOL;
        } while (!$users->isLoginUnique($login) && !Devices::isLoginUnique($login));

        $testUser = StalkerPortalUser::create()
                                        ->setLogin($login)
                                        ->setPassword(mt_rand(100000, 999999))
                                        ->setExpireDate(date('Y-m-d', strtotime("+".get_option('days_test')." days")))
                                        ->setFullName(get_option('days_test') . ' days test')
                                        ->setStatus(true);
        $add = $users->add($testUser);
        if($add)
        {
            Devices::addNewDevice($testUser, $user_id, $device_type_id, $portal['id']);
        }
        break;

      case 'register':

        function generateSecureCode($mac)
        {
          if(empty($mac))
          {
            return null;
          }

          $salt = 'R8Cm53D28D59vVPsQ78yX9K5jpZpB_2qp2hdp9RJ' . strtoupper($mac);
          $hash_parts = str_split(hash('sha256', $salt), 4);
          return strtoupper(implode('-', [$hash_parts[1], $hash_parts[3], $hash_parts[5], $hash_parts[2]]));
        }

        $mac = filter_var(strtoupper($_POST['mag_mac']), FILTER_VALIDATE_MAC);
        $secure_code = $_POST['mac_secure_code'];

        if($mac === false || $secure_code != generateSecureCode($mac))
        {
          exit('Incorrect MAC adress or secure code mismatch');
        }

        $portals = Portals::selectAll();
        $existing_devices = [];
        foreach ($portals as $portal)
        {
          $users = new Users(new Http($portal['api_url'], $portal['api_login'], $portal['api_pass']));
          try
          {
            $device = $users->select($mac);
          }
          catch (StalkerException $e)
          {
            if($e == 'Account not found')
            {
              continue;
            }
          }
          $get_type_sql = "SELECT device_type_id
                            FROM $device_types_in_portals_table
                            WHERE portal_id = " . $portal['id'];
          $type_id = $wpdb->get_col($get_type_sql)[0];

          $existing_devices[] = [
            'portal' => $portal['id'],
            'device' => [
              'user_id' => $user_id,
              'login' => $device['login'],
              'pass' => $device['password'],
              'name' => $device['full_name'],
              'status' => $device['status'],
              'ip' => $device['ip'],
              'mac' => $device['stb_mac'],
              'expire_date' => $device['end_date'],
              'device_type_id' => $type_id
            ]
          ];

        }

        if(empty($existing_devices))
        {
          exit("Приставка $mac не найдена");
        }

        foreach ($existing_devices as $dev)
        {
          $wpdb->insert($devices_table, $dev['device']);
          $wpdb->insert($devices_in_portals_table, ['device_id' => $wpdb->insert_id, 'portal_id' => $dev['portal']]);
        }
        break;

      default:
        echo $redirect;
        break;
    }
    echo $redirect;
  }
}
catch(StalkerException $se){
  exit($se->getMessage());
}
catch (\Exception $e) {
  error_log($e->getMessage());
  exit('Произошла ошибка выполнения скрипта');
}



$devModalFields = [
  'name' => 'Имя',
  'ip' => 'IP',
  'mac' => 'MAC-адрес',
  'login' => 'Логин',
  'pass' => 'Пароль',
  'tariff_plan' => 'Тарифный план',
  'expire_date' => 'Срок действия подписки',
  'status' => 'Статус',

];

?>

<!DOCTYPE html>
<html>
  <head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <meta charset="utf-8">
    <title>My Account</title>
  </head>
  <body>
    <div>
      <br/>
    </div>
    <div class="w-80 container-fluid">
      <div class="row">
        <div class="col-md-6">
           <button type="button" class="btn btn-link pull-left" data-toggle="modal" data-target="#testaccountlmodal">Тестовый аккаунт</button>
        </div>

        <div class="col-md-6 text-right">
        	<button type="button" class="btn btn-link pull-left" data-toggle="modal" data-target="#regmagmodal">Зарегистрировать STB MAG</button>
        </div>
      </div>

        <br>
        <?php if(!empty($devices)): ?>
          <table class="table table-bordered">
            <thead>
              <tr>
                <th scope="col">Тип устройства</th>
                <th scope="col">Имя</th>
                <th scope="col">MAC</th>
                <th scope="col">Login</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($devices as $d): ?>
                <tr data-toggle="modal" data-target="#device_info_modal" data-id=<?=$d['id']?>>
                  <th cope="row"><?= $d['device_type']?></th>
                  <th cope="row"><?= $d['name']?></th>
                  <th cope="row"><?= $d['mac']?></th>
                  <th cope="row"><?= $d['login']?></th>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
    </div>

    <div class="modal fade" ondisplay="getDeviceData(event)" id="device_info_modal" tabindex="-1" role="dialog" aria-labelledby="device_info_modal">
      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-body">

          <form class="form-horizontal" id="dev_info_modal" method="POST">

            <div class="form-group row">
              <label for="type" class="col-md-4 col-form-label">Тип устройства </label>
              <div class="col-md-8">
                Селект
              </div>
            </div>

              <?php foreach ($devModalFields as $key => $title): ?>
                <div class="form-group row">
                  <label for="<?=$key?>" class="col-md-4 col-form-label"><?=$title?> </label>
                  <div class="col-md-8">
                    <p id="<?=$key?>" class="form-control-plaintext col-md-8" name="<?=$key?>" type="text">sdvfsdfv</p>
                  </div>
                </div>
              <?php endforeach; ?>

              <input type="hidden" name="id" value="">
              <input type="hidden" name="action" value="insert-update">
                  <button type="button" class="btn btn-link" data-dismiss="modal" aria-label="Close">Отменить</button>
                  <button id="send" type="submit" class="btn btn-link">Сохранить</button>
          </form>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="testaccountlmodal" tabindex="-1" role="dialog" aria-labelledby="testaccountlmodal">
      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-body" id="content">
            <b>Выберите тип устройства для получения авторизационных данных:</b>
            <form class="form-horizontal" id="test_account" method="POST">
                <?php foreach ($device_types as $device_type): ?>
                  <div class="form-group row">
                    <div class="col-md-12 col-form-label">
                      <input type="radio" name="test_device_type" value="<?= $device_type['id']?>"><?= $device_type['name']?>
                    </div>
                  </div>
                <?php endforeach; ?>

                <input type="hidden" name="action" value="test">
                    <button type="button" class="btn btn-link" data-dismiss="modal" aria-label="Close">Отменить</button>
                    <button id="send" type="submit" class="btn btn-link">Получить</button>
            </form>
          </div>
        </div>
      </div>
    </div>


    <div class="modal fade" id="regmagmodal" tabindex="-1" role="dialog" aria-labelledby="regmagmodal">
      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-body">

          <form class="form-horizontal" id="regmag_modal" method="POST">

            <div class="form-group row">
              <label for="mag_mac" class="col-md-4 col-form-label">MAC-адрес Infomir MAG </label>
              <div class="col-md-8">
                <input id="mag_mac" class="form-control-plaintext col-md-8" name="mag_mac" type="text">
              </div>
            </div>

            <div class="form-group row">
              <label for="mac_secure_code" class="col-md-4 col-form-label">Секретный код </label>
              <div class="col-md-8">
                <input id="mac_secure_code" class="form-control-plaintext col-md-8" name="mac_secure_code" type="text">
              </div>
            </div>

              <input type="hidden" name="action" value="register">
                  <button type="button" class="btn btn-link" data-dismiss="modal" aria-label="Close">Отменить</button>
                  <button id="send" type="submit" class="btn btn-link">Подтвердить</button>
          </form>
          </div>
        </div>
      </div>
    </div>





    <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/jquery-validation@1.17.0/dist/jquery.validate.min.js"></script>
    <script type="text/javascript">
       $(document).ready(function () {

         $('#device_info_modal').on('show.bs.modal', function (event) {
           var deviceId = $(event.relatedTarget).data('id');
           var details;
           $.ajax({
             async: false,
             type: "POST",
             //url: "../wp-content/plugins/wp-iptvsms/actions/get_device_details.php",
             data: "id="+deviceId+"&action=update_device_details",
             success: function(src){
               details = JSON.parse(src);
             }
           });


          //   var button = $(event.relatedTarget);
          //   var modal = $(this);
          //   var id = button.data('id');
          //   var name = button.data('name');
          //   var api_url = button.data('api_url');
          //   var api_login = button.data('api_login');
          //   var api_pass = button.data('api_pass');
          //   var checked;
          //   (button.data('is_base')) ? checked = 'checked' : checked = '';
          //   console.log(checked);
          //   var checkbox = document.getElementById('is_base');
          //   checkbox.innerHTML = '<input '+checked+' class="form-check-input col-md-8" name="is_base" type="checkbox">';
          //
          //   $(event.currentTarget).find('input[name="id"]').val(id);
          //   $(event.currentTarget).find('input[name="name"]').val(name);
          //   $(event.currentTarget).find('input[name="api_url"]').val(api_url);
          //   $(event.currentTarget).find('input[name="api_login"]').val(api_login);
          //   $(event.currentTarget).find('input[name="api_pass"]').val(api_pass);
        });

           $("#test_account").validate({
              rules: {
                test_device_type: {
                  required: true,
                }
              }
            });



             $('#regmagmodal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var modal = $(this);
                var mag_mac = button.data('mag_mac'); //console.log(id);
                var mac_secure_code = button.data('mac_secure_code');

                $(event.currentTarget).find('input[name="mag_mac"]').val(mag_mac);
                $(event.currentTarget).find('input[name="mac_secure_code"]').val(mac_secure_code);
              });

              // $("#regmag_modal").validate({
              //   rules: {
              //     mag_mac: {
              //       required: true,
              //       mac_secure_code: true,
              //       remote: {
              //         url: " echo __DIR__ . '/../validators/mac_secure_code.php' ?>",
              //         type: "post",
              //         data: {
              //           mac_secure_code: function() {
              //             return $("#mac_secure_code").val();
              //           }
              //         }
              //       }
              //     }
              //   }
              // });

      });
    </script>
  </body>
</html>
