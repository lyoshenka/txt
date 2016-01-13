<?php

namespace txt;

class App
{
  public static function run()
  {

    $dotenv = new \Dotenv\Dotenv(TXTROOT);
    $dotenv->load();
    $dotenv->required(['REDIS_URL'])->notEmpty();

    $apiResponseType = array_key_exists('json', $_GET) ? Response::JSON : Response::TEXT;

    if (!Request::isGet() && !Request::isPost())
    {
      Response::sendVaried($apiResponseType, '405 Method Not Allowed', ['code' => 404, 'error' => "Please use a GET or POST"]);
      exit();
    }

    if (getenv('AUTH_TOKEN') && (!isset($_GET['auth_token']) || !static::compareStrings(getenv('AUTH_TOKEN'), $_GET['auth_token'])))
    {
      Response::sendVaried($apiResponseType, '401 Unauthorized', ['code' => 401, 'error' => "'auth_token' required"]);
      exit();
    }

    //    header('Access-Control-Allow-Origin: ' . $_SERVER['ORIGIN']);
    //    header('Access-Control-Allow-Credentials: true');
    //    Access-Control-Allow-Methods: GET, POST
    // x-frame-options


    $redis = Redis::getRedis(getenv('REDIS_URL'));


    $hash = ltrim(Request::getPath(), '/');

    if ($hash)
    {
      if (Request::isPost())
      {
        Response::sendVaried($apiResponseType, '405 Method Not Allowed', ['code' => 404, 'error' => "Cannot post to a hash"]);
        exit();
      }

      if (strlen($hash) > Redis::MAX_KEY_LENGTH || !preg_match('/^[A-Za-z0-9]+$/', $hash))
      {
        Response::sendVaried($apiResponseType, '404 Not Found', ['code' => 404, 'error' => "Invalid hash"]);
        exit();
      }

      $data = $redis->hGetAll(Redis::PREFIX.$hash);
      if (!$data)
      {
        Response::sendVaried($apiResponseType, '404 Not Found', ['code' => 404, 'error' => "Hash not found"]);
        exit();
      }

      $datum = Datum::createFromArray($data);
      if ($datum->once)
      {
        $redis->del(Redis::PREFIX.$hash);
      }

      if (array_key_exists('raw', $_GET))
      {
        Response::setCacheForeverHeaders();
        Response::sendText('200 OK', $datum->content);
        exit();
      }

      Response::sendTemplate('200 OK', 'item', ['content' => $datum->content]);
      exit();
    }

    if (Request::isGet())
    {
      Response::sendTemplate('200 OK', 'home', ['domain' => Request::getHost(), 'ssl' => Request::isSSL()]);
      exit;
    }
    else
    {
      $data = isset($_POST['data']) ? $_POST['data'] : file_get_contents("php://input");
      $datum = new Datum(trim($data), Datum::T_TEXT, array_key_exists('once', $_GET));

      $key = substr(static::randId(), 0, Redis::MAX_KEY_LENGTH);
      $ttl = isset($_GET['ttl']) ? min((int)$_GET['ttl'], Redis::MAX_TTL) : Redis::MAX_TTL;
      $redis->hMSet(Redis::PREFIX.$key, $datum->toArray());
      $redis->expire(Redis::PREFIX.$key, $ttl);

      Response::sendVaried($apiResponseType, '201 Created', ['code' => 201, 'url' => Request::getHost().'/'.$key, 'ttl' => $ttl], 'url');
      exit();
    }
  }

  protected static function randId($length = 6)
  {
    $string = '';
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

    for ($i = 0; $i < $length; $i++)
    {
      $string .= $chars[rand(0, strlen($chars)-1)];
    }

    return $string;
  }

  public static function compareStrings($expected, $actual)
  {
    $expected = (string) $expected;
    $actual = (string) $actual;
    $lenExpected = strlen($expected);
    $lenActual = strlen($actual);
    $len = min($lenExpected, $lenActual);

    $result = 0;
    for ($i = 0; $i < $len; $i++)
    {
      $result |= ord($expected[$i]) ^ ord($actual[$i]);
    }
    $result |= $lenExpected ^ $lenActual;

    return ($result === 0);
  }
}