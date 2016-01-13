<?php

if (!\txt\Response::isSuccess($_status))
{
  echo $_status . ': ';
  $_textKey = isset($_textKey) ? $_textKey : 'error';
}
echo (isset($_textKey) ? $_vars[$_textKey] : \txt\Response::getStatusMessage($_status)) . "\n";