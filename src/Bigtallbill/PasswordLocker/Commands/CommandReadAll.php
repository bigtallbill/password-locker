<?php
/**
 * Created by PhpStorm.
 * User: bigtallbill
 * Date: 2/22/15
 * Time: 12:40 AM
 */

namespace Bigtallbill\PasswordLocker\Commands;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CommandReadAll extends ACommandBase
{
    protected function configure()
    {
        $this->setName('read-all')->setDescription('read all passwords');
        parent::configure();
        $this->addOption(
            'b64d',
            null,
            InputOption::VALUE_NONE,
            'When supplied will decode the passwords to readable text'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->configureCryptMethod($input);
        $this->getPassword($output);
        $this->decryptFile($input, $output);

        $decrypted = $this->passwordLocker->getDecrypted();

        if ($input->getOption('b64d')) {
            $decryptedB64d = array();
            foreach ($decrypted as $key => $value) {
                $decryptedB64d[base64_decode($key)] = base64_decode($value);
            }
            $decrypted = $decryptedB64d;
        }

        $table = new Table($output);
        $table->setHeaders(array('Key', 'Password'));

        foreach ($decrypted as $key => $pass) {
            $table->addRow(array($key, $pass));
        }

        $table->render();
    }
}
