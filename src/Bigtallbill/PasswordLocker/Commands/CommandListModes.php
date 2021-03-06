<?php
/**
 * Created by PhpStorm.
 * User: bigtallbill
 * Date: 2/22/15
 * Time: 12:40 AM
 */

namespace Bigtallbill\PasswordLocker\Commands;


use Bigtallbill\PasswordLocker\PasswordLocker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CommandListModes extends Command
{
    protected function configure()
    {
        parent::configure();
        $this->setName('list-modes')->setDescription('lists all available algorithms');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(implode(PHP_EOL, PasswordLocker::getModes()));
    }
}
