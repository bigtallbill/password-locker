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
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

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
            ->addOption('--hash', null, InputOption::VALUE_OPTIONAL, '', 'sha256')
            ->addOption('pass', null, InputOption::VALUE_REQUIRED, 'set password when in non interactive mode')
            ->addOption('show-pass', null, InputOption::VALUE_NONE);
    }

    /**
     * @param InputInterface $input
     * @param PasswordLocker $passwordLocker
     */
    public function configureCryptMethod(InputInterface $input, PasswordLocker $passwordLocker)
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

        $passwordLocker->setAlgorithm($algorithm);
        $passwordLocker->setHashMethod($hashMethod);
        $passwordLocker->setMode($mode);
    }

    /**
     * @param OutputInterface $output
     * @param InputInterface $input
     * @param PasswordLocker $passwordLocker
     */
    protected function getPassword(OutputInterface $output, InputInterface $input, PasswordLocker $passwordLocker)
    {
        if ($input->getOption('pass')) {
            $password = $input->getOption('pass');
        } else {
            $password = $this->askForPassword($output, $input);
        }

        $passwordLocker->setPass($password);
    }

    /**
     * @param string $path
     * @param OutputInterface $output
     * @param PasswordLocker $passwordLocker
     * @param bool $createMode
     */
    protected function decryptFile($path, OutputInterface $output, PasswordLocker $passwordLocker, $createMode = false)
    {
        $fileExists = file_exists($path);

        if (!$fileExists && $createMode === false) {
            $output->writeln('the file "' . $path . '" does not exist');
            exit();
        }

        if ($fileExists) {
            $archiveLoaded = $passwordLocker->loadArchive($path);
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

    /**
     * @param OutputInterface $output
     * @param InputInterface $input
     * @return string
     */
    protected function askForPassword(OutputInterface $output, InputInterface $input)
    {
        /** @var QuestionHelper $dialog */
        $dialog = $this->getHelper('question');
        $question = new Question('please enter your master password: ');
        $question->setHidden(true);

        if ($input->hasOption('show-pass')) {
            if ($input->getOption('show-pass')) {
                $question->setHidden(false);
            }
        }

        $password = $dialog->ask($input, $output, $question);
        return $password;
    }
}
