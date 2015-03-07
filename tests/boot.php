<?php
/**
 * Created by PhpStorm.
 * User: bigtallbill
 * Date: 23/11/2013
 * Time: 20:57
 */

require dirname(dirname(__FILE__)) . '/vendor/autoload.php';

use MarketMeSuite\Phranken\Spl\SplClassLoader;

// register local autoloader
$loader = new SplClassLoader('Bigtallbill', dirname(dirname(__FILE__)) . '/src');
$loader->register();

define('TEST_ASSETS', __DIR__ . '/assets');
