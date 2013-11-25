<?php
/**
 * Created by PhpStorm.
 * User: bigtallbill
 * Date: 24/11/2013
 * Time: 19:29
 */

$buildRoot = "./build";
$applicationName = 'password-locker';

$phar = new Phar(
    $buildRoot . "/$applicationName.phar"
);

$phar->buildFromDirectory(dirname(__FILE__));


// create stub that allows direct commandline usage
$stub = <<<EOT
#!/usr/bin/env php
<?php

Phar::mapPhar('$applicationName.phar');

require 'phar://$applicationName.phar/script/main.php';

__HALT_COMPILER();
EOT;

$phar->setStub($stub);
