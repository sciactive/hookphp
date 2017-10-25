<?php

error_reporting(E_ALL);

require __DIR__.'/../vendor/autoload.php';

spl_autoload_register(function ($class) {
  $prefix = 'SciActive\\';
  $base_dir = __DIR__ . '/../src/';
  $len = strlen($prefix);
  if (strncmp($prefix, $class, $len) !== 0) {
    return;
  }
  $relative_class = substr($class, $len);
  $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
  if (file_exists($file)) {
    require $file;
  }
});

require_once __DIR__.'/TestModel.php';
