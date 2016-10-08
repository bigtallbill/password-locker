<?php
namespace Bigtallbill\PasswordLocker\Commands;

use Bigtallbill\PasswordLocker\PasswordLocker;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class CommandMerge extends ACommandBase
{
    protected function configure()
    {
        $this->setName('merge')
            ->setDescription('merges two password files together');
        parent::configure();
        $this->addArgument('second-file', InputArgument::REQUIRED, 'The second file to merge into the first');
        $this->addOption('second-pass', null, InputOption::VALUE_REQUIRED, 'The password for the second file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // decrypt first file
        $this->configureCryptMethod($input, $this->passwordLocker);
        $this->getPassword($output, $input, $this->passwordLocker);
        $this->decryptFile($input->getArgument('file'), $output, $this->passwordLocker, true);

        // decrypt second file
        $secondFile = new PasswordLocker();
        $this->configureCryptMethod($input, $secondFile);

        if ($input->getOption('second-pass')) {
            $secondFile->setPass($input->getOption('second-pass'));
        } else {
            $secondFile->setPass($this->askForPassword($output, $input));
        }

        $this->decryptFile($input->getArgument('second-file'), $output, $secondFile, true);
        $secondDecrypted = $secondFile->getDecrypted();

        // merge
        foreach ($secondDecrypted as $key => $value) {

            $key = base64_decode($key);
            $value = base64_decode($value);

            if ($this->passwordLocker->exists($key)) {
                $helper = $this->getHelper('question');
                $question = new ConfirmationQuestion($key . ' already exists, do you wish to overwrite?');
                if (!$helper->ask($input, $output, $question)) {
                    continue;
                }
            } elseif ($this->passwordLocker->read($key) === $value) {
                // contents and key are the same so just move along
                continue;
            }

            $this->passwordLocker->create($key, $value);
        }

        $this->encryptFile($input, $output);
    }
}
