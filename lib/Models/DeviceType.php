<?php

namespace Models;

class DeviceType implements IDeviceType
{

  public static function create()
  {
    return new DeviceType();
  }
  
  private $id;
  private $name;
  private $authType;

  public function getId()
  {
    return $this->id;
  }

  public function getName()
  {
    return $this->name;
  }

  public function getAuthType()
  {
    return $this->authType;
  }

  public function setId($id)
  {
    $this->id = intval($id);
    return $this;
  }

  public function setName($name)
  {
    $this->name = filter_var($name, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    return $this;
  }

  public function setAuthType($type)
  {
    $allowedTypes = [
      'mac',
      'login-password'
    ];

    if(in_array($type, $allowedTypes))
    {
      $this->authType = $type;
    }

    return $this;
  }
}
