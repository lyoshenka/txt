<?php

namespace txt;

class App
{
  public static function run()
  {
    $dotenv = new \Dotenv\Dotenv(TXTROOT);
    $dotenv->load();

    if (isset($_SERVER['HTTP_USER_AGENT']) && stripos($_SERVER['HTTP_USER_AGENT'], 'Slackbot-LinkExpanding') !== false)
    {
      Response::sendResponse(Response::HTTP_403, ['error' => "No slackbots allowed"]);
      exit();
    }


    if (!getenv('REDIS_URL'))
    {
      Response::sendResponse(Response::HTTP_500, ['error' => "REDIS_URL environment variable required"]);
      exit();
    }

    if (!Request::isGet() && !Request::isPost())
    {
      Response::sendResponse(Response::HTTP_405, ['error' => "Please use a GET or POST"]);
      exit();
    }

    if (getenv('AUTH') && (!isset($_POST['auth']) || !static::compareStrings(getenv('AUTH'), $_POST['auth'])))
    {
      Response::sendResponse(Response::HTTP_401, ['error' => "'auth' parameter is missing or invalid"]);
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
        Response::sendResponse(Response::HTTP_405, ['error' => "Cannot post to a hash"]);
        exit();
      }

      if (strlen($hash) > Redis::MAX_KEY_LENGTH || !preg_match('/^[A-Za-z0-9]+$/', $hash))
      {
        Response::sendResponse(Response::HTTP_404, ['error' => "Invalid hash"]);
        exit();
      }

      $data = $redis->hGetAll(Redis::PREFIX.$hash);
      if (!$data)
      {
        Response::sendResponse(Response::HTTP_404, ['error' => "Hash not found"]);
        exit();
      }

      $datum = Datum::createFromArray($data);
      if ($datum->once)
      {
        $redis->del(Redis::PREFIX.$hash);
      }

      // set proper cache header, esp for read-once
      // Response::setCacheForeverHeaders();
      Response::sendResponse('datum', ['datum' => $datum]);
      exit();
    }

    if (Request::isGet())
    {
      Response::sendResponse('home', ['domain' => 'http' . (Request::isSSL() ? 's' : ''). '://' . Request::getHost()]);
      exit;
    }
    else
    {
      $data = isset($_POST['data']) ? $_POST['data'] : file_get_contents("php://input");
      if (!$data)
      {
        Response::sendResponse(Response::HTTP_400, ['error' => 'No data submitted']);
        exit();
      }
      $datum = new Datum(trim($data), Datum::T_TEXT, Request::isFlagOn('once'));

      $key = substr(static::randId(), 0, Redis::MAX_KEY_LENGTH);
      $ttl = isset($_POST['ttl']) ? max(1, min((int)$_POST['ttl'], Redis::MAX_TTL)) : Redis::MAX_TTL;
      $redis->hMSet(Redis::PREFIX.$key, $datum->toArray());
      $redis->expire(Redis::PREFIX.$key, $ttl);

      $url = 'http' . (Request::isSSL() ? 's' : '') . '://' . Request::getHost().'/'.$key;
      Response::sendResponse(Response::HTTP_201, ['url' => $url, 'ttl' => $ttl, '_textKey' => 'url']);
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
