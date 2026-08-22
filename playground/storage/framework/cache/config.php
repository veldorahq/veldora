<?php return array (
  'app' => 
  array (
    'name' => 'Veldora Playground',
    'env' => 'local',
    'debug' => true,
    'url' => 'http://localhost:8000',
    'key' => 'base64:veldoraPlaygroundSecretKey2026Secure=',
    'locale' => 'en',
    'timezone' => 'UTC',
  ),
  'auth' => 
  array (
    'default' => 'web',
    'guards' => 
    array (
      'web' => 
      array (
        'driver' => 'session',
        'provider' => 'users',
      ),
    ),
    'providers' => 
    array (
      'users' => 
      array (
        'driver' => 'model',
        'model' => 'App\\Models\\User',
      ),
    ),
    'passwords' => 
    array (
      'expire' => 60,
      'throttle' => 60,
    ),
  ),
  'database' => 
  array (
    'default' => 'sqlite',
    'connections' => 
    array (
      'sqlite' => 
      array (
        'driver' => 'sqlite',
        'database' => 'database/database.sqlite',
      ),
      'mysql' => 
      array (
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'port' => 3306,
        'database' => 'database/database.sqlite',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
      ),
    ),
    'migrations' => 'migrations',
  ),
  'session' => 
  array (
    'driver' => 'file',
    'lifetime' => 120,
    'expire_on_close' => false,
    'cookie' => 'veldora_session',
    'path' => '/',
    'domain' => NULL,
    'secure' => false,
    'http_only' => true,
    'same_site' => 'lax',
  ),
);
