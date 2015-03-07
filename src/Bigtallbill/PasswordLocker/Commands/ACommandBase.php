<?php
/**
 * Created by PhpStorm.
 * User: bigtallbill
 * Date: 2/22/15
 * Time: 1:12 PM
 */

namespace Bigtallbill\PasswordLocker\Commands;


use Bigtallbill\PasswordLocker\PasswordLocker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ACommandBase extends Command
{
    /** @var PasswordLocker */
    protected $passwordLocker;

    protected function configure()
    {
        $this->passwordLocker = new PasswordLocker();

        $this->addArgument('file', InputArgument::REQUIRED, 'path to password file')
            ->addOption('--algorithm', '-a', InputOption::VALUE_OPTIONAL, '', MCRYPT_RIJNDAEL_256)
            ->addOption('--mode', '-m', InputOption::VALUE_OPTIONAL, '', MCRYPT_MODE_CBC)
            ->addOption('--hash', null, InputOption::VALUE_OPTIONAL, '', 'sha256');
    }

    /**
     * @param InputInterface $input
     */
    public function configureCryptMethod(InputInterface $input)
    {
        $hashMethod = 'sha256';
        $algorithm = MCRYPT_RIJNDAEL_256;
        $mode = MCRYPT_MODE_CBC;

        if ($input->hasOption('--algorithm')) {
            $algorithm = $input->getOption('--algorithm');
        }

        if ($input->hasOption('--mode')) {
            $mode = $input->getOption('--mode');
        }

        if ($input->hasOption('--hash')) {
            $hashMethod = $input->getOption('--hash');
        }

        $this->passwordLocker->setAlgorithm($algorithm);
        $this->passwordLocker->setHashMethod($hashMethod);
        $this->passwordLocker->setMode($mode);
    }

    /**
     * @param OutputInterface $output
     */
    protected function getPassword(OutputInterface $output)
    {
        $dialog = $this->getHelperSet()->get('dialog');
        $this->passwordLocker->setPass($dialog->askHiddenResponse($output, 'please enter your master password: '));
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param bool $createMode
     */
    protected function decryptFile(InputInterface $input, OutputInterface $output, $createMode = false)
    {
        $filePath = $input->getArgument('file');
        $fileExists = file_exists($filePath);

        if (!$fileExists && $createMode === false) {
            $output->writeln('the file "' . $filePath . '" does not exist');
            exit();
        }

        if ($fileExists) {
            $archiveLoaded = $this->passwordLocker->loadArchive($filePath);
        } else {
            $archiveLoaded = false;
        }

        if ($createMode === true && $fileExists && !$archiveLoaded) {
            $output->writeln('target file exists and could not be decrypted, check your password and try again or change file');
            exit();
        }

        if ($archiveLoaded === false && $createMode === false) {
            $output->writeln('decryption failed, check your password and try again');
            exit();
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function encryptFile(InputInterface $input, OutputInterface $output)
    {
        $this->passwordLocker->saveArchive($input->getArgument('file'));
    }
}