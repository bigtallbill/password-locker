<?php
/**
 * Created by PhpStorm.
 * User: bigtallbill
 * Date: 23/11/2013
 * Time: 20:57
 */

require dirname(dirname(__FILE__)) . '/vendor/autoload.php';

// register local autoloader
$loader = new \Composer\Autoload\ClassLoader();
$loader->add('Bigtallbill', dirname(dirname(__FILE__)) . '/src');
$loader->register();

define('TEST_ASSETS', __DIR__ . '/assets');
