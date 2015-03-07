<?php
/**
 * Created by PhpStorm.
 * User: bigtallbill
 * Date: 23/11/2013
 * Time: 20:57
 */

require dirname(dirname(__FILE__)) . '/vendor/autoload.php';

use MarketMeSuite\Phranken\Commandline\SimpleLog;
use MarketMeSuite\Phranken\Commandline\CommandPrompt;
use MarketMeSuite\Phranken\Spl\SplClassLoader;
use MarketMeSuite\Phranken\Commandline\ArgUtils;
use Bigtallbill\PasswordLocker\PasswordLocker;

// register local autoloader
$loader = new SplClassLoader('Bigtallbill', dirname(dirname(__FILE__)) . '/src');
$loader->register();


// symphony app

$app = new \Symfony\Component\Console\Application('password-locker', '1.0.0');
$app->add(new \Bigtallbill\PasswordLocker\Commands\CommandListAlgos());
$app->add(new \Bigtallbill\PasswordLocker\Commands\CommandListHashAlgos());
$app->add(new \Bigtallbill\PasswordLocker\Commands\CommandListModes());
$app->add(new \Bigtallbill\PasswordLocker\Commands\CommandRead());
$app->add(new \Bigtallbill\PasswordLocker\Commands\CommandReadAll());
$app->add(new \Bigtallbill\PasswordLocker\Commands\CommandCreate());
$app->add(new \Bigtallbill\PasswordLocker\Commands\CommandDelete());
$app->run();
