<?php

if(!current_user_can('manage_options'))
{
  exit('У вас недостаточно прав для совершения данного действия');
}

?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Настройки биллинга</title>
  </head>
  <body>
    <h2>Настройки биллинговой системы</h2>
    <ul>
      <li><a href="<?= $_SERVER['PHP_SELF']?>?page=iptvsms-settings-portals">Настройки доступа к порталам</a></li>
      <li><a href="<?= $_SERVER['PHP_SELF']?>?page=iptvsms-settings-tariffs">Настройки тарифных планов</a></li>
      <li><a href="<?= $_SERVER['PHP_SELF']?>?page=iptvsms-settings-devices">Виды клиентских устройств</a></li>
    </ul>
  </body>
</html>
