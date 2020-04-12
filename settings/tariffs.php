<?php


$redirect = "<script type='text/javascript'>window.location=document.location.href;</script>";
if(!current_user_can('manage_options'))
{
  exit('У вас недостаточно прав для совершения данного действия');
}

require_once __DIR__ . '/../autoload.php';

$options = [
  'test_devices_limit' => [
    'value' => get_option('test_devices_limit'),
    'title' => 'Лимит тестовых устройств на один аккаунт'
  ],
  'days_test' => [
    'value' => get_option('days_test'),
    'title' => 'Количество дней тестового периода'
  ],
];

if(isset($_POST['test_devices_limit'], $_POST['days_test']))
{
  update_option('test_devices_limit', intval($_POST['test_devices_limit']));
  update_option('days_test', intval($_POST['days_test']));

  echo $redirect;
}

?>

<!DOCTYPE html>
<html>
  <head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <meta charset="utf-8">
    <title>Tariffs</title>
  </head>
  <body>
    <div>
      <br/>
    </div>
    <div class="w-80 container-fluid">
        <form id="settings_tariffs" class="form-horizontal" method="post">
          <?php foreach ($options as $key =>$data): ?>
            <div class="item form-group row">
              <label class="col-form-label col-md-5 col-sm-5 col-xs-5" for="<?=$key ?>"><?=$data['title'] ?> </label>
              <div class="col-md-5">
                <input id="<?=$key ?>" class="form-control-plaintext col-md-5 col-xs-5" name="<?=$key ?>" value="<?=$data['value']?>" type="text">
              </div>
            </div>
          <?php endforeach; ?>

          <div class="form-group">
              <div class="col-md-6 col-md-offset-3">
                <button id="send" type="submit" class="btn btn-success">Сохранить</button>
              </div>
            </div>
        </form>
    </div>



    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/jquery-validation@1.17.0/dist/jquery.validate.min.js"></script>
    <script type="text/javascript">
       $(document).ready(function () {
           $("#settings_tariffs").validate({
              rules: {
                test_devices_limit: {
                  required: true,
                  digits: true
                },
                days_test: {
                  required: true,
                  digits: true
                }
              }
            });
      });
    </script>
  </body>
</html>
