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

class CommandCreate extends ACommandBaseId
{
    protected function configure()
    {
        $this->setName('create')
            ->setDescription('create or overwrite a single password');
        parent::configure();
        $this->addArgument('raw', InputArgument::OPTIONAL, 'The raw password, or leave black to generate');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->configureCryptMethod($input, $this->passwordLocker);
        $this->getPassword($output, $input, $this->passwordLocker);
        $this->decryptFile($input->getArgument('file'), $output, $this->passwordLocker, true);

        // if id exists, ask to overwrite
        $id = $input->getArgument('id');
        if ($this->passwordLocker->exists($id)) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion($id . ' already exists, do you wish to overwrite?');
            if (!$helper->ask($input, $output, $question)) {
                return;
            }
        }

        $raw = $input->getArgument('raw');

        if (is_null($raw)) {
            $raw = '';
        }

        $this->passwordLocker->create($id, $raw);

        $this->encryptFile($input, $output);
    }
}
