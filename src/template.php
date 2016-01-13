<?php

namespace txt;

class Template
{
  const GENERIC = 'generic';

  protected static $decorator = null;

  public static function setDecorator($decorator)
  {
    static::$decorator = $decorator;
  }

  public static function renderPage($_name, $_vars = [])
  {
    if (static::$decorator)
    {
      return static::render(static::$decorator, [
        'content' => static::render($_name, $_vars)
      ]);
    }
    else
    {
      return static::render($_name, $_vars);
    }
  }

  public static function render($_name, $_vars = [])
  {
    if (!static::exists($_name))
    {
      throw new \LogicException('Template "' . $_name . '" not found');
    }

    extract($_vars);
    ob_start();
    require static::src($_name);
    return ob_get_clean();
  }

  public static function find($name, $type)
  {
    foreach([$name.'.'.$type, $name, static::GENERIC.'.'.$type] as $guess)
    {
      if (static::exists($guess))
      {
        return $guess;
      }
    }

    throw new \LogicException('No ' . $type . ' template for ' . $name);
  }

  public static function exists($name)
  {
    return file_exists(static::src($name));
  }

  public static function src($name)
  {
    return TXTROOT . '/src/templates/' . $name . '.php';
  }
}