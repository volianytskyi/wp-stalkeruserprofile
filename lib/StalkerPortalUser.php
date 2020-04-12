<?php

use StalkerPortal\ApiV1\Interfaces\Account;

class StalkerPortalUser implements Account
{
  private $mac = '';
  private $login = '';
  private $pass = '';
  private $accountNumber = '';
  private $status = '';
  private $tariff = '';
  private $comment = '';
  private $expireDate = '';
  private $accountBalance = '';
  private $name = '';

  static public function create()
  {
    return new StalkerPortalUser();
  }

  public function setMac($mac)
  {
    $this->mac = $mac;
    return $this;
  }

  public function setLogin($login)
  {
    $this->login = $login;
    return $this;
  }

  public function setPassword($pass)
  {
    $this->pass = $pass;
    return $this;
  }

  public function setAccountNumber($accountNumber)
  {
    $this->accountNumber = $accountNumber;
    return $this;
  }

  public function setStatus($status)
  {
    $this->status = $status;
    return $this;
  }

  public function setTariffPlanExternalId($tariff)
  {
    $this->tariffs = $tariff;
    return $this;
  }

  public function setComment($comment)
  {
    $this->comment = $comment;
    return $this;
  }

  public function setExpireDate($date)
  {
    $this->expireDate = $date;
    return $this;
  }

  public function setAccountBalance($balance)
  {
    $this->accountBalance = $balance;
    return $this;
  }

  public function setFullName($name)
  {
    $this->name = $name;
    return $this;
  }

  public function getMac()
  {
    return $this->mac;
  }
  public function getLogin()
  {
    return $this->login;
  }
  public function getPassword()
  {
    return $this->pass;
  }
  public function getAccountNumber()
  {
    return $this->accountNumber;
  }
  public function getStatus()
  {
    return $this->status;
  }
  public function getTariffPlanExternalId()
  {
    return $this->tarif;
  }
  public function getComment()
  {
    return $this->comment;
  }
  public function getExpireDate()
  {
    return $this->expireDate;
  }
  public function getAccountBalance()
  {
    return $this->accountBalance;
  }
  public function getFullName()
  {
    return $this->name;
  }
}


 ?>
