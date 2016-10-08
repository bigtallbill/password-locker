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


// symphony app

$app = new \Symfony\Component\Console\Application('password-locker', '1.0.0');
$app->add(new \Bigtallbill\PasswordLocker\Commands\CommandListAlgos());
$app->add(new \Bigtallbill\PasswordLocker\Commands\CommandListHashAlgos());
$app->add(new \Bigtallbill\PasswordLocker\Commands\CommandListModes());
$app->add(new \Bigtallbill\PasswordLocker\Commands\CommandRead());
$app->add(new \Bigtallbill\PasswordLocker\Commands\CommandReadAll());
$app->add(new \Bigtallbill\PasswordLocker\Commands\CommandCreate());
$app->add(new \Bigtallbill\PasswordLocker\Commands\CommandDelete());
$app->add(new \Bigtallbill\PasswordLocker\Commands\CommandMerge());
$app->run();
