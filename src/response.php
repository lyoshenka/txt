<?php

namespace txt;

class Response
{
  const TEXT = 'text';
  const JSON = 'json';

  protected static $sent = false;
  protected static $headers = [];
  protected static $content = '';

  public static function setHeader($name, $value)
  {
    static::$headers[$name] = $value;
  }

  public static function setStatus($status)
  {
    static::$headers['Status'] = $status;
  }

  public static function getStatus()
  {
    return isset(static::$headers['Status']) ? static::$headers['Status'] : '';
  }

  public static function setContent($content)
  {
    static::$content = $content;
  }

  public static function setIsText()
  {
    static::setHeader('Content-Type', 'text/plain; charset=utf-8');
  }

  public static function setIsHtml()
  {
    static::setHeader('Content-Type', 'text/html; charset=utf-8');
  }

  public static function setIsJson()
  {
    static::setHeader('Content-Type', 'application/json; charset=utf-8');
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

  public static function sendJson($status, $content)
  {
    static::setStatus($status);
    static::setIsJson();
    static::setContent(json_encode($content, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK));
    static::send();
  }

  public static function sendTemplate($status, $templateName, $templateVars = [])
  {
    static::setStatus($status);
    static::setIsHtml();
    static::setContent(Template::renderPage($templateName, $templateVars));
    static::send();
  }

  public static function sendText($status, $text)
  {
    static::setStatus($status);
    static::setIsText();
    static::setContent($text);
    static::send();
  }

  public static function sendVaried($type, $status, $json, $textKey = 'error')
  {
    switch($type)
    {
      case static::JSON:
        static::sendJson($status, $json);
        break;
      case static::TEXT:
        $prefix = $json['code'] >= 400 ? ($json['code'] . ': ') : '';
        static::sendText($status, $prefix . $json[$textKey] . "\n");
        break;
      default:
        throw new \DomainException('Invalid response type: ' . $type);
    }
  }
}