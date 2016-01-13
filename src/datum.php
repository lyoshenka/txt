<?php

namespace txt;

class Datum
{
  const T_TEXT = 'text';

  /** @var string */
  public $content;

  /** @var string */
  public $type;

  /** @var bool */
  public $once;

  public static function getValidTypes()
  {
    return [static::T_TEXT];
  }

  public function __construct($content = '', $type = self::T_TEXT, $once = false)
  {
    if (!in_array($type, static::getValidTypes()))
    {
      throw new \RuntimeException('Invalid type');
    }

    $this->content = $content;
    $this->type = $type;
    $this->once = (bool) $once;
  }

  public function toArray()
  {
    $r = new \ReflectionClass($this);
    $arr = [];
    foreach($r->getProperties(ReflectionProperty::IS_PUBLIC) as $prop)
    {
      $arr[$prop->getName()] = $this->{$prop->getName()};
    }
    return $arr;
  }

  public static function createFromArray($arr)
  {
    $obj = new static();
    $r = new \ReflectionClass($obj);
    foreach($r->getProperties(ReflectionProperty::IS_PUBLIC) as $prop)
    {
      if (isset($arr[$prop->getName()]))
      {
        $obj->{$prop->getName()} = $arr[$prop->getName()];
      }
    }
    return $obj;
  }
}