<?php
/**
 * Created by PhpStorm.
 * User: bigtallbill
 * Date: 2/22/15
 * Time: 1:45 PM
 */

namespace Bigtallbill\PasswordLocker\Commands;


use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class CommandDelete extends ACommandBaseId
{
    protected function configure()
    {
        $this->setName('delete')
            ->setDescription('Delete a password');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->configureCryptMethod($input);
        $this->getPassword($output);
        $this->decryptFile($input, $output);

        // if id exists, ask to overwrite
        $id = $input->getArgument('id');
        if ($this->passwordLocker->exists($id)) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion("are you sure you wish to delete key '$id'? ");
            if (!$helper->ask($input, $output, $question)) {
                return;
            }
        }

        $this->passwordLocker->delete($id);
        $this->encryptFile($input, $output);
    }
}
