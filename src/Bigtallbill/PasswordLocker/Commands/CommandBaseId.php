<?php
/**
 * Created by PhpStorm.
 * User: bigtallbill
 * Date: 3/6/15
 * Time: 11:04 PM
 */

namespace Bigtallbill\PasswordLocker\Commands;


use Symfony\Component\Console\Input\InputArgument;

class CommandBaseId extends CommandBase
{
    protected function configure()
    {
        parent::configure();
        $this->addArgument('id', InputArgument::REQUIRED, 'name of password to work with');
    }
}
