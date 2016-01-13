<?php

namespace txt;

class Request
{
  public static function getMethod()
  {
    return isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : null;
  }

  public static function isGet()
  {
    return strtolower(static::getMethod()) === 'get';
  }

  public static function isPost()
  {
    return strtolower(static::getMethod()) === 'post';
  }

  public static function isSSL()
  {
    return
      (isset($_SERVER['HTTPS']) && ('on' == strtolower($_SERVER['HTTPS']) || 1 == $_SERVER['HTTPS']))
      ||
      (isset($_SERVER['HTTP_SSL_HTTPS']) && ('on' == strtolower($_SERVER['HTTP_SSL_HTTPS']) || 1 == $_SERVER['HTTP_SSL_HTTPS']))
      ||
      (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && 'https' == strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']))
    ;
  }

  public static function getHost()
  {
    $host = null;

    if (isset($_SERVER['HTTP_X_FORWARDED_HOST']))
    {
      $elements = explode(',', $_SERVER['HTTP_X_FORWARDED_HOST']);
      $host = trim($elements[count($elements) - 1]);
    }
    else
    {
      $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
    }

    return rtrim($host, '.'); // apparently trailing period is legal: http://www.dns-sd.org/TrailingDotsInDomainNames.html
  }

  public static function getScriptName()
  {
    return isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : (isset($_SERVER['ORIG_SCRIPT_NAME']) ? $_SERVER['ORIG_SCRIPT_NAME'] : '');
  }

  public static function parseUrl()
  {
    $parsed = parse_url(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '');
    if ($parsed === null)
    {
      throw new \RuntimeException('cannot parse URL');
    }
    return $parsed;
  }

  public static function getPath()
  {
    return isset($_SERVER["REQUEST_URI"]) ? strtok($_SERVER["REQUEST_URI"],'?') : null;
  }

  public static function getQuery()
  {
    $parsed = static::parseUrl();
    if (!isset($parsed['query']))
    {
      return null;
    }

    $query = [];
    parse_str($parsed['query'], $query);
    return $query;
  }

  public static function isFlagOn($name)
  {
    return (array_key_exists($name, $_GET) && ($_GET[$name] === '' || $_GET[$name])) ||
           (static::isPost() && isset($_POST[$name]) && $_POST[$name]);
  }

  public static function guessResponseType()
  {
    if (static::isFlagOn('raw'))
    {
      return Response::TEXT;
    }

    if (static::isFlagOn('json'))
    {
      return Response::JSON;
    }

    if (isset($_SERVER['HTTP_ACCEPT']))
    {
      $knownTypes = array_flip(Response::getContentTypes());
      foreach(explode(';', strtolower($_SERVER['HTTP_ACCEPT'])) as $types)
      {
        foreach (explode(',', $types) as $type)
        {
          if (isset($knownTypes[$type]))
          {
            return $knownTypes[$type];
          }
        }
      }
    }

    return Response::TEXT;
  }
}