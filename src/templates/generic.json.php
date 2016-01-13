<?php

$json = $_vars;
foreach(array_keys($json) as $key)
{
  if ($key == '_status')
  {
    $json['status'] = $json['_status'];
  }
  if ($key[0] == '_')
  {
    unset($json[$key]);
  }
}
echo json_encode($json, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);