<?php
/**
 * Created by PhpStorm.
 * User: bigtallbill
 * Date: 2/22/15
 * Time: 12:40 AM
 */

namespace Bigtallbill\PasswordLocker\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CommandRead extends CommandBaseId
{
    protected function configure()
    {
        $this->setName('read')
            ->setDescription('read a single password');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->configureCryptMethod($input);
        $this->getPassword($output);
        $this->decryptFile($input, $output);

        $id = $input->getArgument('id');
        $output->writeln($id . ' : ' . $this->passwordLocker->read($id));
    }
}
