<?php

namespace txt;

$dotenv = new \Dotenv\Dotenv(TXTROOT);
$dotenv->load();
$dotenv->required(['REDIS_URL'])->notEmpty();


if (!Request::isGet() && !Request::isPost())
{
  header('HTTP/1.1 405 Method Not Allowed');
  header('Status: 405 Method Not Allowed');
  header('Content-Type: application/json');
  echo json_encode(['code' => 405, 'error' => "Please use a GET or POST"]);
  exit();
}

// header('Content-Type: application/json; charset='.ProjectConfiguration::CHARSET);

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
    header('HTTP/1.1 405 Method Not Allowed');
    header('Status: 405 Method Not Allowed');
    header('Content-Type: application/json');
    echo json_encode(['code' => 405, 'error' => "Cannot post to a hash"]);
    exit();
  }

  if (strlen($hash) > Redis::MAX_KEY_LENGTH || !preg_match('/^[A-Za-z0-9]+$/', $hash))
  {
    header('HTTP/1.1 404 Not Found');
    header('Status: 404 Not Found');
    header('Content-Type: application/json');
    echo json_encode(['code' => 404, 'error' => "Invalid hash"]);
    exit();
  }

  $data = $redis->hGetAll(Redis::PREFIX.$hash);
  if (!$data)
  {
    header('HTTP/1.1 404 Not Found');
    header('Status: 404 Not Found');
    header('Content-Type: application/json');
    echo json_encode(['code' => 404, 'error' => "Hash not found"]);
    exit();
  }

  $datum = Datum::createFromArray($data);
  if ($datum->once)
  {
    $redis->del(Redis::PREFIX.$hash);
  }

  header('HTTP/1.1 200 OK');
  header('Status: 200 OK');
  header('Content-Type: text/plain');
  echo $datum->content;
  exit();
}

if (Request::isGet())
{
  echo file_get_contents(TXTROOT.'/web/home.html');
  exit;
}
else
{
  $datum = new Datum(trim(file_get_contents("php://input")), Datum::T_TEXT, isset($_GET['once']) && $_GET['once']);

  $key = substr(randId(), 0, Redis::MAX_KEY_LENGTH);
  $redis->hMSet(Redis::PREFIX.$key, $datum->toArray());
  header('HTTP/1.1 201 Created');
  header('Status: 201 Created');
  header('Content-Type: application/json');
  echo json_encode(['code' => 201, 'url' => Request::getHost().'/'.$key]);
  exit();
}


function randId($length = 6)
{
  $string = '';
  $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

  for ($i = 0; $i < $length; $i++)
  {
    $string .= $chars[rand(0, strlen($chars)-1)];
  }

  return $string;
}