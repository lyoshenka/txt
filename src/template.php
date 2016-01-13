<?php

namespace txt;

class Template
{
  public static function renderPage($_name, $_vars = [])
  {
    return static::render('base', [
      'content' => static::render($_name, $_vars)
    ]);
  }

  public static function render($_name, $_vars = [])
  {
    if (!file_exists(TXTROOT . '/src/templates/' . $_name . '.php'))
    {
      throw new \LogicException('Template "' . $_name . '" not found');
    }

    extract($_vars);

    ob_start();
    require TXTROOT . '/src/templates/' . $_name . '.php';
    return ob_get_clean();
  }
}