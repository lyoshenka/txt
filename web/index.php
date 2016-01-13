<?php

ini_set('display_errors', 1);
error_reporting(-1);
set_error_handler(function ($errno, $errstr, $errfile, $errline)
{
  throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
});

define('TXTROOT', dirname(__DIR__));

require_once TXTROOT.'/vendor/autoload.php';
require_once TXTROOT.'/src/request.php';
require_once TXTROOT.'/src/redis.php';
require_once TXTROOT.'/src/datum.php';
require_once TXTROOT.'/src/app.php';