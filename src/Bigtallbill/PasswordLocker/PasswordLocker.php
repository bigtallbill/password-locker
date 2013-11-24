<?php
namespace Bigtallbill\PasswordLocker;

/**
 * Class PasswordLocker
 *
 * Manages a password archive locked with the specified algorithms
 *
 * @package Bigtallbill\PasswordLocker
 */
class PasswordLocker
{
    public static $DEFAULT_PASSWORD_TEMPLATE = '{C:20}{S:1}{N:12}{S:1}{C:20}{N:5}';

    protected $algorithm;
    protected $mode;
    protected $pass;
    protected $hashMethod = 'sha256';
    protected $randomNumberSource = MCRYPT_DEV_URANDOM;

    /**
     * @var IPasswordFactory
     */
    protected $passwordFactory = '\Bigtallbill\PasswordLocker\PasswordFactory';

    protected $data;

    /**
     * @var array The decrypted password locker
     */
    protected $decrypted = array();


    /**
     * @var string The current path to the open locker file
     */
    protected $openPath;

    /**
     * Performs a compatibility check
     */
    public function __construct()
    {
        static::compatibilityCheck();
    }

    /**
     * Loads and decrypts a password archive using the configured
     * algorithms
     *
     * @param string $path
     *
     * @return bool true on success, false on failure
     */
    public function loadArchive($path = '')
    {
        $encrypted = static::loadFile($path);

        $data = base64_decode($encrypted);
        $iv = substr($data, 0, mcrypt_get_iv_size($this->getAlgorithm(), $this->getMode()));

        $decrypted = rtrim(
            mcrypt_decrypt(
                $this->getAlgorithm(),
                hash($this->getHashMethod(), $this->getPass(), true),
                substr($data, mcrypt_get_iv_size($this->getAlgorithm(), $this->getMode())),
                $this->getMode(),
                $iv
            ),
            "\0"
        );

        @$decrypted = unserialize(base64_decode($decrypted)) or $decrypted = false;

        if (empty($decrypted)) {
            return false;
        }

        $this->decrypted = $decrypted;

        return true;
    }

    /**
     * encrypts and saves a password archive file using the
     * configured algorithms
     *
     * @param string $path A valid file path. If it exists it will be overwritten
     *
     * @return bool true on success, false on failure
     */
    public function saveArchive($path = '')
    {
        // cannot encrypt an empty array
        // because of issues with unserialize()
        if (count($this->decrypted) == 0) {
            return false;
        }

        // create initialisation vector
        $iv = mcrypt_create_iv(
            mcrypt_get_iv_size(
                $this->getAlgorithm(),
                $this->getMode()
            ),
            $this->getRandomNumberSource()
        );

        // encrypt the data
        @$encrypted = base64_encode(
            $iv .
            mcrypt_encrypt(
                $this->getAlgorithm(),
                hash($this->getHashMethod(), $this->getPass(), true),
                base64_encode(serialize($this->decrypted)),
                $this->getMode(),
                $iv
            )
        ) or $encrypted = false;

        if (empty($encrypted)) {
            return false;
        }

        static::saveFile($path, $encrypted);

        return true;
    }


    //--------------------------------------
    // PASSWORD METHODS
    //--------------------------------------

    /**
     * creates a new entry for id in the password archive
     *
     * $id and $raw are first base64encoded before being saved
     *
     * @note this does NOT apply the changes to
     *       the archive. You MUST call saveArchive()
     *       for changes to be saved
     *
     * @param string $id  The id to store the password. You use this to reference
     *                    your saved password
     * @param string $raw The raw password to store. If a blank string is provided
     *                    a new random password is generated with the configured
     *                    IPasswordFactory and the template static::$DEFAULT_PASSWORD_TEMPLATE
     */
    public function create($id, $raw = '')
    {
        // if no password was provided then attempt
        // to create one with the configured factory
        if (empty($raw)) {
            $factoryClass = $this->getPasswordFactory();

            $raw = call_user_func($factoryClass . '::make', static::$DEFAULT_PASSWORD_TEMPLATE);
        }

        // encode the id into base64
        $id = base64_encode($id);

        // store the password with both the $id and $raw values
        // base64 encoded
        $this->decrypted[$id] = base64_encode($raw);
    }

    /**
     * Decrypts and reads the password associated with id
     *
     * @param string $id
     *
     * @return bool|string False on error, the decrypted password on success
     */
    public function read($id)
    {
        // encode the id which would have been
        // encoded on create
        $id = base64_encode($id);

        if (!isset($this->decrypted[$id])) {
            return false;
        }

        // return raw password
        return base64_decode($this->decrypted[$id]);
    }

    /**
     * Alias of create
     *
     * @see create
     */
    public function update($id, $raw = '')
    {
        $this->create($id, $raw);
    }

    /**
     * Deletes id (key) from the decrypted array
     *
     * @note this does NOT apply the changes to
     *       the archive. You MUST call saveArchive()
     *       for changes to be saved
     *
     * @param string $id
     *
     * @return bool
     */
    public function delete($id)
    {
        // encode the id which would have been
        // encoded on create
        $id = base64_encode($id);

        if (!isset($this->decrypted[$id])) {
            return false;
        }

        unset($this->decrypted[$id]);

        return true;
    }

    /**
     * Checks that the provided id (key) exists in
     * the decrypted array
     *
     * @param string $id
     *
     * @return bool
     */
    public function exists($id)
    {
        // encode the id which would have been
        // encoded on create
        $id = base64_encode($id);

        if (isset($this->decrypted[$id])) {
            return true;
        }

        return false;
    }


    //--------------------------------------
    // GETS / SETS
    //--------------------------------------

    /**
     * @param string $algorithm
     */
    public function setAlgorithm($algorithm)
    {
        static::assertInArray($algorithm, static::getAlgorithms());
        $this->algorithm = $algorithm;
    }

    /**
     * @return string
     */
    public function getAlgorithm()
    {
        return $this->algorithm;
    }

    /**
     * @param string $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $hashMethod
     */
    public function setHashMethod($hashMethod)
    {
        static::assertInArray($hashMethod, static::getHashAlgorithms());
        $this->hashMethod = $hashMethod;
    }

    /**
     * @return string
     */
    public function getHashMethod()
    {
        return $this->hashMethod;
    }

    /**
     * @param string $mode
     */
    public function setMode($mode)
    {
        static::assertInArray($mode, static::getModes());
        $this->mode = $mode;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param string $pass
     */
    public function setPass($pass)
    {
        $this->pass = $pass;
    }

    /**
     * @return string
     */
    public function getPass()
    {
        return $this->pass;
    }

    /**
     * @param \Bigtallbill\PasswordLocker\IPasswordFactory $passwordFactory
     */
    public function setPasswordFactory(IPasswordFactory $passwordFactory)
    {
        $this->passwordFactory = $passwordFactory;
    }

    /**
     * @return \Bigtallbill\PasswordLocker\IPasswordFactory
     */
    public function getPasswordFactory()
    {
        return $this->passwordFactory;
    }

    /**
     * @param int $randomNumberSource
     */
    public function setRandomNumberSource($randomNumberSource)
    {
        $this->randomNumberSource = $randomNumberSource;
    }

    /**
     * @return int
     */
    public function getRandomNumberSource()
    {
        return $this->randomNumberSource;
    }

    /**
     * @return array
     */
    public function getDecrypted()
    {
        return $this->decrypted;
    }


    //--------------------------------------
    // UTILITY METHODS
    //--------------------------------------

    /**
     * @return array Available encryption modes
     */
    public static function getModes()
    {
        return mcrypt_list_modes();
    }

    /**
     * @return array Available encryption algorithms
     */
    public static function getAlgorithms()
    {
        return mcrypt_list_algorithms();
    }

    /**
     * @return array Available hash algorithms
     */
    public static function getHashAlgorithms()
    {
        return hash_algos();
    }

    /**
     * @param $needle
     * @param $haystack
     *
     * @throws PasswordLockerException
     */
    public static function assertInArray($needle, $haystack)
    {
        if (!in_array($needle, $haystack)) {
            throw new PasswordLockerException("'$needle' was not found in : " . implode(',', $haystack));
        }
    }

    public static function compatibilityCheck()
    {
        if (!extension_loaded('mcrypt')) {
            throw new PasswordLockerException('"mcrypt" must be loaded for this class to function');
        }
    }

    /**
     * Loads a file
     *
     * @param string $path
     *
     * @return string The loaded file data
     */
    public static function loadFile($path = '')
    {
        return file_get_contents($path);
    }

    /**
     * Saves a file
     *
     * @note This function will overwrite the target file
     *       without warning
     *
     * @param string $path
     * @param string $data The data to write
     *
     * @return int
     */
    public static function saveFile($path = '', $data = '')
    {
        return file_put_contents($path, $data);
    }
}
