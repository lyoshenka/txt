<?php

namespace txt;

class Redis
{
  const PREFIX ='txt:';
  const MAX_KEY_LENGTH = 10;

  public static function getRedis($dsn)
  {
    list($host, $port, $db, $user, $password, $options) = static::parseDsn($dsn);
    return new \Credis_Client($host, $port);
  }

  /**
   * Parse a DSN string, which can have one of the following formats:
   *
   * - host:port
   * - redis://user:pass@host:port/db?option1=val1&option2=val2
   * - tcp://user:pass@host:port/db?option1=val1&option2=val2
   *
   * Note: the 'user' part of the DSN is not used.
   *
   * @param string $dsn A DSN string
   *
   * @return array An array of DSN components, with 'null' values for any unknown components. e.g.
   *               [host, port, db, user, pass, options]
   */
  public static function parseDsn($dsn)
  {
    // Use a sensible default for an empty DNS string
    $parts = parse_url($dsn ?: 'redis://localhost');

    // Check the URI scheme
    $validSchemes = ['redis', 'tcp'];
    if (isset($parts['scheme']) && !in_array($parts['scheme'], $validSchemes))
    {
      throw new \RuntimeException("Invalid DSN. Supported schemes are " . implode(', ', $validSchemes));
    }

    // Allow simple 'hostname' format, which `parse_url` treats as a path, not host.
    if (!isset($parts['host']) && isset($parts['path']))
    {
      $parts['host'] = $parts['path'];
      unset($parts['path']);
    }

    // Extract the port number as an integer
    $port = isset($parts['port']) ? intval($parts['port']) : 6379;

    // Get the database from the 'path' part of the URI. Strip non-digit chars from path
    $database = isset($parts['path']) ? intval(preg_replace('/[^0-9]/', '', $parts['path'])) : null;

    // Extract any 'user' and 'pass' values
    $user = isset($parts['user']) ? $parts['user'] : null;
    $pass = isset($parts['pass']) ? $parts['pass'] : null;

    // Convert the query string into an associative array
    $options = [];
    if (isset($parts['query']))
    {
      parse_str($parts['query'], $options);
    }

    return [
      $parts['host'],
      $port,
      $database,
      $user,
      $pass,
      $options,
    ];
  }
}