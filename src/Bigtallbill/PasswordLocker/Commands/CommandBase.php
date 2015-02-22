<?php
/**
 * Created by PhpStorm.
 * User: bigtallbill
 * Date: 2/22/15
 * Time: 1:12 PM
 */

namespace Bigtallbill\PasswordLocker\Commands;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CommandBase extends Command
{
    protected function configure()
    {
        $this->addArgument('file', InputArgument::REQUIRED)
            ->addArgument('id', InputArgument::REQUIRED)
            ->addOption('--algorithm', '-a', InputOption::VALUE_OPTIONAL, '', MCRYPT_RIJNDAEL_256)
            ->addOption('--mode', '-m', InputOption::VALUE_OPTIONAL, '', MCRYPT_MODE_CBC)
            ->addOption('--hash', null, InputOption::VALUE_OPTIONAL, '', 'sha256');
    }
}