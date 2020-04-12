<?php

$redirect = "<script type='text/javascript'>window.location=document.location.href;</script>";
if(!current_user_can('manage_options'))
{
  exit('У вас недостаточно прав для совершения данного действия');
}
global $iptvsms_plugin_path;
global $wpdb;
$portals_table = $wpdb->prefix . 'portals';
$portals = $wpdb->get_results("SELECT id, name, api_url, api_login, api_pass FROM $portals_table", ARRAY_A);

if(isset($_POST['action']))
{
  switch ($_POST['action'])
  {
    case 'insert-update':
      $id = $_POST['id'];
      $data = [
        'name' => $_POST['name'],
        'api_url' => $_POST['api_url'],
        'api_login' => $_POST['api_login'],
        'api_pass' => $_POST['api_pass'],
      ];
      if(empty($id))
      {
        $wpdb->insert($portals_table, $data);
      }
      else
      {
        $wpdb->update($portals_table, $data, ['id' => $id], ['%s', '%s', '%s', '%s'], ['%d']);
      }
      break;

    case 'delete':
      $id = $_POST['portal_id'];
      $wpdb->delete($portals_table, ['id' => $id], ['%d']);
      break;

    default:
      echo $redirect;
      break;
  }
  echo $redirect;
}

?>

<!DOCTYPE html>
<html>
  <head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <meta charset="utf-8">
    <title>Portals</title>
  </head>
  <body>
    <div>
      <br/>
    </div>
    <div class="w-80 container-fluid">
        <button type="button" class="btn btn-link" data-toggle="modal" data-target="#portalmodal">Добавить портал</button>
        <br>
        <?php if(!empty($portals)): ?>
          <table class="table table-bordered">
            <thead>
              <tr>
                <th scope="col">Id</th>
                <th scope="col">Портал</th>
                <th scope="col">API URL</th>
                <th scope="col">API Login</th>
                <th scope="col">API Password</th>
                <th scope="col">Удалить</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($portals as $p): ?>
                <?php
                  $data = '';
                  foreach($p as $key =>$value)
                  {
                    $data .= "data-$key=\"$value\" ";
                  }
                ?>
                <tr>
                  <th cope="row"><?= $p['id']?></th>
                  <td><button type="button" class="btn btn-link" data-toggle="modal" data-target="#portalmodal" <?= $data?>><?= $p['name']?></button></td>
                  <td><?= $p['api_url']?></td>
                  <td><?= $p['api_login']?></td>
                  <td><?= $p['api_pass']?></td>
                  <td>
                    <button type="button" class="btn btn-link" data-toggle="modal" data-target="#deleteportalmodal" data-portal_id=<?= $p['id']?> data-portal_name=<?= $p['name']?> >удалить</button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
    </div>

    <div class="modal fade" id="portalmodal" tabindex="-1" role="dialog" aria-labelledby="portalmodal">
      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-body">

          <form class="form-horizontal" id="portal_modal" method="POST">
              <?php foreach (['name' => 'Название', 'api_url' => 'API URL', 'api_login' => 'API Login', 'api_pass' => 'API Password'] as $key => $title): ?>
                <div class="form-group row">
                  <label for="<?=$key?>" class="col-md-4 col-form-label"><?=$title?> </label>
                  <div class="col-md-8">
                    <input id="<?=$key?>" class="form-control-plaintext col-md-8" name="<?=$key?>" type="text">
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


    <div class="modal fade" id="deleteportalmodal" tabindex="-1" role="dialog" aria-labelledby="deleteportalmodal">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-body">

          <form class="form-horizontal" id="delete_portal_modal" method="POST">
              <div id="confirmation"></div>
              <input type="hidden" name="portal_id" value="">
              <input type="hidden" name="action" value="delete">
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
           $("#portal_modal").validate({
              rules: {
                name: {
                  required: true,
                },

                api_url: {
                  required: true,
                  url: true
                },
                api_login: {
                  required: true,
                },
                api_pass: {
                  required: true
                }
              },
              messages: {
                name: {
                  required: "необходимо ввести название портала"
                },
                api_url: {
                  required: "необходимо ввести URL API портала",
                  url: "введите валидный URL"
                },
                api_login: {
                  required: "необходимо ввести API login",
                },
                api_pass: {
                  required: "необходимо ввести API password",
                }
              }
            });

            $('#portalmodal').on('show.bs.modal', function (event) {
               var button = $(event.relatedTarget);
               var modal = $(this);
               var id = button.data('id');
               var name = button.data('name');
               var api_url = button.data('api_url');
               var api_login = button.data('api_login');
               var api_pass = button.data('api_pass');

               $(event.currentTarget).find('input[name="id"]').val(id);
               $(event.currentTarget).find('input[name="name"]').val(name);
               $(event.currentTarget).find('input[name="api_url"]').val(api_url);
               $(event.currentTarget).find('input[name="api_login"]').val(api_login);
               $(event.currentTarget).find('input[name="api_pass"]').val(api_pass);
             });

             $('#deleteportalmodal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var modal = $(this);
                var id = button.data('portal_id');
                var name = button.data('portal_name');
                var text = document.getElementById('confirmation');
                text.innerHTML = 'Вы уверены, что хотите удалить портал <b>' + name + '</b>? <br/> Все данные будут удалены без возможности восстановления';
                $(event.currentTarget).find('input[name="portal_id"]').val(id);
              });

      });
    </script>
  </body>
</html>
