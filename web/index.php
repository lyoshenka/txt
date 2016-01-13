<?php

ini_set('display_errors', 1);
error_reporting(-1);
set_error_handler(function ($errno, $errstr, $errfile, $errline)
{
  throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
});

# Enable PHP dev cli-server
if (php_sapi_name() === 'cli-server' && is_file(__DIR__.preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI'])))
{
  return false;
}

define('TXTROOT', dirname(__DIR__));

require_once TXTROOT.'/vendor/autoload.php';

require_once TXTROOT.'/src/redis.php';
require_once TXTROOT.'/src/datum.php';
require_once TXTROOT.'/src/template.php';
require_once TXTROOT.'/src/request.php';
require_once TXTROOT.'/src/response.php';

require_once TXTROOT.'/src/app.php';

try
{
  \txt\App::run();
}
catch(Exception $e)
{
  echo '<h1>ERROR</h1><pre>' . $e . '</pre>';
}