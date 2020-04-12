<?php

use Models\DeviceType;

$redirect = "<script type='text/javascript'>window.location=document.location.href;</script>";
if(!current_user_can('manage_options'))
{
  exit('У вас недостаточно прав для совершения данного действия');
}
global $wpdb;
$device_types_table = $wpdb->prefix . 'device_types';
$portals_table = $wpdb->prefix . 'portals';
$device_types_in_portals_table = $wpdb->prefix . 'device_types_in_portals';

if(isset($_POST['action']))
{
  switch ($_POST['action'])
  {
    case 'insert-update':
      if(empty($_POST['device_type_id']))
      {
        $newDeviceType = DeviceType::create()
                          ->setName($_POST['device_type_name'])
                          ->setAuthType($_POST['auth_type']);
        $deviceTypeId = DeviceTypes::add($newDeviceType);
        DeviceTypesInPortals::add($deviceTypeId, $_POST['portal_id']);
      }
      else
      {
        $updatedDeviceType = DeviceType::create()
                              ->setId($_POST['device_type_id'])
                              ->setName($_POST['device_type_name'])
                              ->setAuthType($_POST['auth_type']);
        DeviceTypes::update($updatedDeviceType);
        DeviceTypesInPortals::update($_POST['device_type_id'], $_POST['portal_id']);
      }
      break;
    case 'delete':
      DeviceTypes::deleteById($_POST['dev_type_id']);
      DeviceTypesInPortals::deleteByDeviceTypeId($_POST['dev_type_id']);
      break;
    default:
      echo $redirect;
      break;
  }
  echo $redirect;
}

$device_types = DeviceTypes::selectAll();
$print_data = [];
foreach ($device_types as $device_type)
{
  $portal_data = $wpdb->get_row(
    " SELECT $portals_table.id, $portals_table.name
      FROM $portals_table
      JOIN $device_types_in_portals_table
      ON $device_types_in_portals_table.portal_id = $portals_table.id
      WHERE $device_types_in_portals_table.device_type_id = " . intval($device_type['id']),
    ARRAY_A);

  $print_data[] = [
    'device_type_id' => $device_type['id'],
    'device_type_name' => $device_type['name'],
    'device_auth_type' => $device_type['auth_type'],
    'portal_id' => $portal_data['id'],
    'portal_name' => $portal_data['name']
  ];
}

$portals = $wpdb->get_results("SELECT id, name FROM $portals_table", ARRAY_A);

$authTypes = [
  'mac' => 'MAC-адрес',
  'login-password' => 'Логин и пароль'
];

?>

<!DOCTYPE html>
<html>
  <head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <meta charset="utf-8">
    <title>Device Types</title>
  </head>
  <body>
    <div>
      <br/>
    </div>
    <div class="w-80 container-fluid">
        <button
          type="button"
          class="btn btn-link"
          data-toggle="modal"
          data-target="#devicetypemodal"
          data-portals="<?=base64_encode(json_encode($portals))?>"
          data-types="<?=base64_encode(json_encode($authTypes))?>"
        >
          Добавить тип устройства
        </button>
        <br>
        <?php if(!empty($print_data)): ?>
          <table class="table table-bordered">
            <thead>
              <tr>
                <th scope="col">Тип устройства</th>
                <th scope="col">Портал</th>
                <th scope="col">Удалить</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($print_data as $p): ?>
                <?php
                  $data = '';
                  foreach($p as $key =>$value)
                  {
                    $data .= "data-$key=\"$value\" ";
                  }
                  $data .= ' data-portals=' . base64_encode(json_encode($portals));
                  $data .= ' data-types=' . base64_encode(json_encode($authTypes));
                ?>
                <tr>
                  <td><button type="button" class="btn btn-link" data-toggle="modal" data-target="#devicetypemodal" <?= $data?>><?= $p['device_type_name']?></button></td>
                  <td><?= $p['portal_name']?></td>
                  <td><button type="button" class="btn btn-link" data-toggle="modal" data-target="#deletedevicetypemodal" data-dev_type_id=<?= $p['device_type_id']?> data-dev_type_name=<?= $p['device_type_name']?> >удалить</button></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
    </div>

    <div class="modal fade" id="devicetypemodal" tabindex="-1" role="dialog" aria-labelledby="devicetypemodal">
      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-body">

          <form class="form-horizontal" id="device_type_modal" method="POST">

            <div class="form-group row">
              <label for="device_type_name" class="col-md-4 col-form-label">Тип устройства </label>
              <div class="col-md-8">
                <input id="device_type_name" class="form-control-plaintext col-md-8" name="device_type_name" type="text">
              </div>
            </div>

            <div class="form-group row">
              <label for="auth_type" class="col-md-4 col-form-label">Тип авторизации </label>
              <div class="col-md-8">
                <?php foreach($authTypes as $key => $value): ?>
                    <div id="auth_type" class="col-form-label">

                    </div>
                <?php endforeach; ?>
              </div>
            </div>

                <div class="form-group row">
                  <label for="device_type_in_portal" class="col-md-4 col-form-label">Портал </label>
                  <div class="col-md-8">
                    <select id="portal_id" class="col-md-8" name="portal_id">

                    </select>
                  </div>
                </div>

              <input type="hidden" name="device_type_id" value="">
              <input type="hidden" name="action" value="insert-update">
                  <button type="button" class="btn btn-link" data-dismiss="modal" aria-label="Close">Отменить</button>
                  <button id="send" type="submit" class="btn btn-link">Сохранить</button>
          </form>
          </div>
        </div>
      </div>
    </div>


    <div class="modal fade" id="deletedevicetypemodal" tabindex="-1" role="dialog" aria-labelledby="deletedevicetypemodal">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-body">

          <form class="form-horizontal" id="delete_device_type_modal" method="POST">
              <div id="confirmation"></div>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="dev_type_id">
                  <button type="button" class="btn btn-link" data-dismiss="modal" aria-label="Close">Отменить</button>
                  <button id="send" type="submit" class="btn btn-link">Удалить</button>
          </form>
          </div>
        </div>
      </div>
    </div>





    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/jquery-validation@1.17.0/dist/jquery.validate.min.js"></script>
    <script type="text/javascript">
       $(document).ready(function () {
           $("#device_type_modal").validate({
              rules: {
                device_type_name: {
                  required: true,
                },

                auth_type: {
                  required: true
                },
                portal_id: {
                  required: true,
                }
              }
            });

            $('#devicetypemodal').on('show.bs.modal', function (event) {
               var button = $(event.relatedTarget);
               var modal = $(this);
               var portals = JSON.parse(atob(button.data('portals')));
               var authTypes = JSON.parse(atob(button.data('types')));
               var device_type_name = button.data('device_type_name');
               var device_type_id = button.data('device_type_id');
               var portal_id = button.data('portal_id');
               var device_auth_type = button.data('device_auth_type');

               $(event.currentTarget).find('input[name="device_type_name"]').val(device_type_name);
               $(event.currentTarget).find('input[name="device_type_id"]').val(device_type_id);

               var options = '';
               for (var i = 0; i < portals.length; i++)
               {
                 var selected = '';
                 if(portals[i].id == portal_id)
                 {
                   selected = 'selected';
                 }
                 options += '<option '+selected+' value="'+portals[i].id+'">'+portals[i].name+'</option>';
               }

               var select = document.getElementById('portal_id');
               select.innerHTML = options;

               var radios = '';
               var authTypesNames = Object.keys(authTypes);

               for (var i = 0; i < authTypesNames.length; i++)
               {
                 var checked = '';
                 if(authTypesNames[i] == device_auth_type)
                 {
                   checked = 'checked';
                 }
                 radios += '<input '+checked+' type="radio" name="auth_type" value="'+authTypesNames[i]+'">'+authTypes[authTypesNames[i]] + '<br />';
               }

               var radioInput = document.getElementById('auth_type');
               radioInput.innerHTML = radios;
             });

             $('#deletedevicetypemodal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var modal = $(this);
                var dev_type_name = button.data('dev_type_name');
                var dev_type_id = button.data('dev_type_id');
                $(event.currentTarget).find('input[name="dev_type_id"]').val(dev_type_id);
                var text = document.getElementById('confirmation');
                text.innerHTML = 'Вы уверены, что хотите удалить тип устройств <b>' + dev_type_name + '</b>? <br/> Все данные будут удалены без возможности восстановления';
                $(event.currentTarget).find('input[name="dev_type_id"]').val(dev_type_id);
              });

      });
    </script>
  </body>
</html>
