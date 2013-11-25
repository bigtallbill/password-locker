<?php
/**
 * Created by PhpStorm.
 * User: bigtallbill
 * Date: 23/11/2013
 * Time: 20:57
 */

require dirname(dirname(__FILE__)) . '/vendor/autoload.php';

use MarketMeSuite\Phranken\Commandline\SimpleLog;
use MarketMeSuite\Phranken\Commandline\CommandPrompt;
use MarketMeSuite\Phranken\Spl\SplClassLoader;
use MarketMeSuite\Phranken\Commandline\ArgUtils;
use Bigtallbill\PasswordLocker\PasswordLocker;

// register local autoloader
$loader = new SplClassLoader('Bigtallbill', dirname(dirname(__FILE__)) . '/src');
$loader->register();

$sl = new SimpleLog();


//--------------------------------------
// run compatibility check
//--------------------------------------

// check stty exists on system
// stty used for silent password typing
$whichStty = shell_exec('which stty');

if (empty($whichStty)) {
    $sl->log('"stty" was not found on system');
}


//--------------------------------------
// Check arguments and display
// help options
//--------------------------------------

// create a new PasswordLocker instance
$pl = new PasswordLocker();

if (count($argv) <= 1) {
    showHelp();
}

// get command argument
$command = $argv[1];

// look for utility commands
switch ($command) {
    case 'list-algos':
        $list = PasswordLocker::getAlgorithms();
        $sl->log(implode(PHP_EOL, $list));
        exit();
        break;
    case 'list-modes':
        $list = PasswordLocker::getModes();
        $sl->log(implode(PHP_EOL, $list));
        exit();
        break;
    case 'list-hash-algos':
        $list = PasswordLocker::getHashAlgorithms();
        $sl->log(implode(PHP_EOL, $list));
        exit();
        break;
    case 'help':
    case '--help':
    case '-help':
        showHelp();
        break;
}

// get required args
$algo     = ArgUtils::getArgPair($argv, '-a', MCRYPT_RIJNDAEL_256, true);
$mode     = ArgUtils::getArgPair($argv, '-m', MCRYPT_MODE_CBC, true);
$hashAlgo = ArgUtils::getArgPair($argv, '-h', 'sha256', true);
$pass     = ArgUtils::getArgPair($argv, '-p', null, true);
$file     = ArgUtils::getArgPair($argv, '-f');


// validate file
if (empty($file)) {
    $sl->log('you must provide a file path with -f');
    exit();
} elseif (empty($file['-f'])) {
    $sl->log('you must provide value next to -f');
    exit();
}

// validate password
if (empty($pass)) {
    $sl->log('you must provide a pass key with -p');
    exit();
} elseif (empty($pass['-p'])) {
    echo 'please enter your master password: ';
    $pass['-p'] = preg_replace('/\r?\n$/', '', `stty -echo; head -n1 ; stty echo`);
    echo PHP_EOL;
}

// merge all of the arguments together for easy reference
$arguments = array_merge($algo, $mode, $pass, $file, $hashAlgo);


//--------------------------------------
// MAIN COMMANDS
//--------------------------------------

// setup the password locker
$pl->setAlgorithm($arguments['-a']);
$pl->setMode($arguments['-m']);
$pl->setPass($arguments['-p']);
$pl->setHashMethod($arguments['-h']);

switch ($command) {
    case 'create':

        // get and validate id
        $id = ArgUtils::getArgPair($argv, '--id');

        if (empty($id)) {
            $sl->log('you must provide a password id with "--id"');
            exit();
        } elseif (empty($id['--id'])) {
            $sl->log('you must provide value next to --id');
            exit();
        }

        // get and validate raw password
        $raw = ArgUtils::getArgPair($argv, '--raw');

        if (empty($raw)) {
            $sl->log('you must provide a password to store with "--raw"');
            exit();
        } elseif (empty($raw['--raw'])) {
            $sl->log('generating a random password for "' . $id['--id'] . '"');
            $raw['--raw'] = '';
        }

        if (file_exists($arguments['-f'])) {
            if ($pl->loadArchive($arguments['-f']) === false) {
                $sl->log(
                    'target file exists and could not be decrypted, check your password and try again or change file'
                );
                exit();
            }
        }

        if ($pl->exists($id['--id'])) {

            $cmdPrompt = new CommandPrompt();
            $choice = $cmdPrompt->prompt($id['--id'] . ' exists. do you wish to override it? ');

            if ($choice) {
                $sl->log('ok overwritting');
            } else {
                // just exit
                exit();
            }
        }

        $pl->create($id['--id'], $raw['--raw']);

        if ($pl->saveArchive($arguments['-f']) === false) {
            $sl->log('encryption failed, check your configuration and try again');
            exit();
        }

        $sl->log($id['--id'] . ':' . $pl->read($id['--id']));

        break;
    case 'read':

        if (!file_exists($arguments['-f'])) {
            $sl->log('the file "' . $arguments['-f'] . '" does not exist');
            exit();
        }

        $id = ArgUtils::getArgPair($argv, '--id');

        // validate id
        if (empty($id)) {
            $sl->log('you must provide a password id with "--id"');
            exit();
        } elseif (empty($id['--id'])) {
            $sl->log('you must provide value next to --id');
            exit();
        }

        if ($pl->loadArchive($arguments['-f']) === false) {
            $sl->log('decryption failed, check your password and try again');
            exit();
        }

        $sl->log($id['--id'] . ' : ' . $pl->read($id['--id']));

        break;
    case 'read-all':
        if (!file_exists($arguments['-f'])) {
            $sl->log('the file "' . $arguments['-f'] . '" does not exist');
            exit();
        }

        $id = ArgUtils::getArgPair($argv, '--id');

        if ($pl->loadArchive($arguments['-f']) === false) {
            $sl->log('decryption failed, check your password and try again');
            exit();
        }

        // get password list
        $decrypted = $pl->getDecrypted();

        // get and validate raw password
        $b64d = in_array('--b64d', $argv);

        if ($b64d === true) {
            $decryptedB64d = array();
            foreach ($decrypted as $key => $value) {
                $decryptedB64d[base64_decode($key)] = base64_decode($value);
            }
            $decrypted = $decryptedB64d;
        }

        // align the colons
        $max = 0;
        foreach (array_keys($decrypted) as $key) {
            $max = max($max, mb_strlen($key));
        }

        foreach ($decrypted as $key => $pass) {
            $key = str_pad($key, $max, ' ');
            $sl->log($key . ' : ' . $pass);
        }

        break;
    case 'list-keys':
        if (!file_exists($arguments['-f'])) {
            $sl->log('the file "' . $arguments['-f'] . '" does not exist');
            exit();
        }

        $id = ArgUtils::getArgPair($argv, '--id');

        if ($pl->loadArchive($arguments['-f']) === false) {
            $sl->log('decryption failed, check your password and try again');
            exit();
        }

        // get password list
        $decrypted = $pl->getDecrypted();

        // get and validate raw password
        $b64d = in_array('--b64d', $argv);

        if ($b64d === true) {
            $decryptedB64d = array();
            foreach ($decrypted as $key => $value) {
                $decryptedB64d[base64_decode($key)] = $value;
            }
            $decrypted = $decryptedB64d;
        }

        foreach ($decrypted as $key => $pass) {
            $sl->log($key);
        }

        break;
    case 'delete':
        if (!file_exists($arguments['-f'])) {
            $sl->log('the file "' . $arguments['-f'] . '" does not exist');
            exit();
        }

        $id = ArgUtils::getArgPair($argv, '--id');

        // validate id
        if (empty($id)) {
            $sl->log('you must provide a password id with "--id"');
            exit();
        } elseif (empty($id['--id'])) {
            $sl->log('you must provide value next to --id');
            exit();
        }

        if ($pl->loadArchive($arguments['-f']) === false) {
            $sl->log('decryption failed, check your password and try again');
            exit();
        }

        $cmdPrompt = new CommandPrompt();
        $choice = $cmdPrompt->prompt('are you sure you wish to delete key "' . $id['--id'] . '"? ');

        if ($choice) {
            $sl->log('ok deleting');
        } else {
            // just exit
            exit();
        }

        $pl->delete($id['--id']);

        if ($pl->saveArchive($arguments['-f']) === false) {
            $sl->log('encryption failed, check your configuration and try again');
            exit();
        }

        $sl->log($id['--id'] . ' deleted');
        break;
    default:
        showHelp();
        break;
}

/**
 * displays the help text
 */
function showHelp()
{
    global $sl;

    $sl->log(file_get_contents(dirname(__FILE__) . '/help'));

    exit();
}
