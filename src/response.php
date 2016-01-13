<?php

namespace txt;

class Response
{
  const TEXT = 'text';
  const JSON = 'json';
  const HTML = 'html';

  protected static $contentTypes = [
    self::TEXT => 'text/plain',
    self::JSON => 'application/json',
    self::HTML => 'text/html'
  ];

  const HTTP_200 = 200;
  const HTTP_201 = 201;
  const HTTP_400 = 400;
  const HTTP_401 = 401;
  const HTTP_404 = 404;
  const HTTP_405 = 405;
  const HTTP_500 = 500;

  protected static $httpStatusMessages = [
    self::HTTP_200 => 'OK',
    self::HTTP_201 => 'Created',
    self::HTTP_400 => 'Bad Request',
    self::HTTP_401 => 'Unauthorized',
    self::HTTP_404 => 'Not Found',
    self::HTTP_405 => 'Method Not Allowed',
    self::HTTP_500 => 'Internal Server Error',
  ];

  protected static $sent = false;
  protected static $headers = [];
  protected static $content = '';

  public static function getContentTypes()
  {
    return static::$contentTypes;
  }

  public static function getStatusMessage($status)
  {
    if (!isset(static::$httpStatusMessages[$status]))
    {
      throw new \OutOfRangeException('Invalid status: ' . $status);
    }
    return static::$httpStatusMessages[$status];
  }

  public static function isSuccess($status)
  {
    return (int)$status < static::HTTP_400;
  }

  public static function setHeader($name, $value)
  {
    static::$headers[$name] = $value;
  }

  public static function setStatus($status)
  {
    static::$headers['Status'] = $status . ' ' . static::$httpStatusMessages[$status];
  }

  public static function getStatus()
  {
    return isset(static::$headers['Status']) ? static::$headers['Status'] : '';
  }

  public static function setContent($content)
  {
    static::$content = $content;
  }

  public static function setContentType($type)
  {
    if (!isset(static::$contentTypes[$type]))
    {
      throw new \OutOfRangeException('Invalid content type: ' . $type);
    }

    static::setHeader('Content-Type', static::$contentTypes[$type] . '; charset=utf-8');
  }

  public static function send($status = null, $content = null)
  {
    if (static::$sent)
    {
      throw new \LogicException('Response already sent');
    }

    static::$sent = true;

    if ($status !== null)
    {
      static::setStatus($status);
    }
    if ($content !== null)
    {
      static::setContent($content);
    }

    header('HTTP/1.1 ' . static::getStatus());
    foreach(static::$headers as $name => $value)
    {
      header($name . ': ' . $value);
    }
    echo static::$content;
  }

  public static function setCacheForeverHeaders()
  {
    static::setHeader('Cache-Control', 'max-age=31556926');
  }

  public static function sendResponse($nameOrStatus, $data = [])
  {
    $type = Request::guessResponseType();
    $template = Template::find($nameOrStatus, $type);

    $status = is_numeric($nameOrStatus) ? $nameOrStatus : static::HTTP_200;
    static::setStatus($status);
    static::setContentType($type);

    if ($type == static::HTML)
    {
      Template::setDecorator('baseHtml');
    }

    $data['_status'] = $status;
    static::setContent(Template::renderPage($template, $data));
    static::send();
  }
}