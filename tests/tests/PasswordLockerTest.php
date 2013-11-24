<?php
namespace Bigtallbill\PasswordLocker;

/**
 * Class PasswordLockerTest
 * @package Bigtallbill\PasswordLocker
 */
class PasswordLockerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PasswordLocker
     */
    protected $object;

    protected $testFilePath;

    public function setup()
    {
        $this->object = new PasswordLocker();

        // setup class
        $this->object->setAlgorithm(MCRYPT_RIJNDAEL_256);
        $this->object->setMode(MCRYPT_MODE_CBC);
        $this->object->setHashMethod('sha256');

        // set a temporary file location to read/write the password archive
        $this->testFilePath = sys_get_temp_dir() . '/phpunit_pwdfile';
    }

    public function tearDown()
    {
        if (file_exists($this->testFilePath)) {
            unlink($this->testFilePath);
        }
    }

    //--------------------------------------
    // CORE METHODS
    //--------------------------------------


    /**
     * @covers \Bigtallbill\PasswordLocker\PasswordLocker::__construct
     */
    public function testConstruct()
    {
        new PasswordLocker();
    }

    /**
     * @covers \Bigtallbill\PasswordLocker\PasswordLocker::create
     * @covers \Bigtallbill\PasswordLocker\PasswordLocker::read
     */
    public function testCreateRead()
    {
        $expected = 'my_pass';
        $this->object->create('amazon', $expected);
        $actual = $this->object->read('amazon');

        $this->assertSame($expected, $actual);


        // another password id

        $expected = 'my_dropbox_pass';
        $this->object->create('dropbox', $expected);
        $actual = $this->object->read('dropbox');

        $this->assertSame($expected, $actual);
    }

    /**
     * @covers \Bigtallbill\PasswordLocker\PasswordLocker::read
     */
    public function testRead()
    {
        $expected = false;
        $actual = $this->object->read('non_exists');
        $this->assertSame(
            $expected,
            $actual,
            'read should return false if the id does not exist'
        );
    }



    /**
     * @covers \Bigtallbill\PasswordLocker\PasswordLocker::create
     * @covers \Bigtallbill\PasswordLocker\PasswordLocker::update
     */
    public function testCreateUpdate()
    {
        $expected = 'my_pass';
        $this->object->create('amazon', '12345');
        $this->object->update('amazon', $expected);
        $actual = $this->object->read('amazon');

        $this->assertSame($expected, $actual);
    }

    /**
     * @covers \Bigtallbill\PasswordLocker\PasswordLocker::create
     * @covers \Bigtallbill\PasswordLocker\PasswordLocker::delete
     */
    public function testCreateDelete()
    {
        $expected = false;

        $this->object->create('amazon', 'my_pass');
        $this->object->delete('amazon');
        $actual = $this->object->read('amazon');

        $this->assertSame($expected, $actual);
    }

    /**
     * @covers \Bigtallbill\PasswordLocker\PasswordLocker::delete
     */
    public function testDelete()
    {
        $expected = false;
        $actual = $this->object->delete('non_exists');
        $this->assertSame(
            $expected,
            $actual,
            'delete should return false if the id does not exist'
        );
    }

    /**
     * @covers \Bigtallbill\PasswordLocker\PasswordLocker::create
     */
    public function testCreate()
    {
        $expected = 'string';

        $this->object->create('amazon');
        $actual = $this->object->read('amazon');

        $this->assertInternalType($expected, $actual);
    }

    /**
     * @covers \Bigtallbill\PasswordLocker\PasswordLocker::exists
     */
    public function testExists()
    {
        // test exists true
        $expected = true;
        $this->object->create('amazon');
        $actual = $this->object->exists('amazon');

        $this->assertSame($expected, $actual);

        // test exists false
        $expected = false;
        $actual = $this->object->exists('does_not_exist');

        $this->assertSame($expected, $actual);
    }


    //--------------------------------------
    // File Handling
    //--------------------------------------

    /**
     * @covers \Bigtallbill\PasswordLocker\PasswordLocker::saveFile
     * @covers \Bigtallbill\PasswordLocker\PasswordLocker::loadFile
     */
    public function testLoadFileSaveFile()
    {
        $fileContents = 'my file data';

        // test exists true
        $expected = true;
        PasswordLocker::saveFile($this->testFilePath, $fileContents);

        $actual = file_exists($this->testFilePath);
        $this->assertSame($expected, $actual);

        // test exists true
        $expected = $fileContents;
        $actual = PasswordLocker::loadFile($this->testFilePath);

        $this->assertSame($expected, $actual);
    }

    /**
     * @covers \Bigtallbill\PasswordLocker\PasswordLocker::saveArchive
     */
    public function testSaveArchive()
    {
        // test exists true
        $expected = true;
        $this->object->create('amazon');
        $this->object->saveArchive($this->testFilePath);

        $actual = file_exists($this->testFilePath);

        $this->assertSame($expected, $actual);
    }

    /**
     * @covers \Bigtallbill\PasswordLocker\PasswordLocker::saveArchive
     */
    public function testSaveArchive2()
    {
        // test empty archive
        $expected = false;
        $this->object->saveArchive($this->testFilePath);

        $actual = file_exists($this->testFilePath);

        $this->assertSame(
            $expected,
            $actual,
            'file should not be created when there is an empty archive'
        );
    }

    /**
     * @covers \Bigtallbill\PasswordLocker\PasswordLocker::loadArchive
     */
    public function testLoadArchive()
    {
        $this->object->setPass('super_secure_password');

        // test exists true
        $expected = true;
        $this->object->create('amazon');
        $this->object->saveArchive($this->testFilePath);

        $actual = file_exists($this->testFilePath);

        $this->assertSame($expected, $actual);


        // now load the file back
        $expected = true;
        $actual = $this->object->loadArchive($this->testFilePath);

        $this->assertSame($expected, $actual);


        // try to load with wrong password
        $expected = false;
        $this->object->setPass('the wrong password');
        $actual = $this->object->loadArchive($this->testFilePath);

        $this->assertSame($expected, $actual);
    }




    //--------------------------------------
    // GETS / SETS
    //--------------------------------------

    /**
     * @covers \Bigtallbill\PasswordLocker\PasswordLocker::setAlgorithm
     * @covers \Bigtallbill\PasswordLocker\PasswordLocker::getAlgorithm
     */
    public function testSetAlgorithm()
    {
        $this->object->setAlgorithm(MCRYPT_RIJNDAEL_256);
        $actual = $this->object->getAlgorithm();

        $this->assertSame(MCRYPT_RIJNDAEL_256, $actual);
    }

    /**
     * @covers \Bigtallbill\PasswordLocker\PasswordLocker::setAlgorithm
     *
     * @expectedException \Bigtallbill\PasswordLocker\PasswordLockerException
     */
    public function testSetAlgorithm2()
    {
        $nonExistingAlgorithm = 'bills_awesome_algo';

        $this->object->setAlgorithm($nonExistingAlgorithm);
    }

    /**
     * @covers \Bigtallbill\PasswordLocker\PasswordLocker::setHashMethod
     * @covers \Bigtallbill\PasswordLocker\PasswordLocker::getHashMethod
     */
    public function testSetHashMethod()
    {
        $expected = 'sha256';
        $this->object->setHashMethod($expected);
        $actual = $this->object->getHashMethod();

        $this->assertSame($expected, $actual);
    }

    /**
     * @covers \Bigtallbill\PasswordLocker\PasswordLocker::setHashMethod
     *
     * @expectedException \Bigtallbill\PasswordLocker\PasswordLockerException
     */
    public function testSetHashMethod2()
    {
        $nonExistingAlgorithm = 'bills_awesome_algo';

        $this->object->setHashMethod($nonExistingAlgorithm);
    }

    /**
     * @covers \Bigtallbill\PasswordLocker\PasswordLocker::setMode
     * @covers \Bigtallbill\PasswordLocker\PasswordLocker::getMode
     */
    public function testSetMode()
    {
        $expected = MCRYPT_MODE_CBC;
        $this->object->setMode($expected);
        $actual = $this->object->getMode();

        $this->assertSame($expected, $actual);
    }

    /**
     * @covers \Bigtallbill\PasswordLocker\PasswordLocker::setMode
     *
     * @expectedException \Bigtallbill\PasswordLocker\PasswordLockerException
     */
    public function testSetMode2()
    {
        $nonExistingMode = 'bills_awesome_algo';

        $this->object->setMode($nonExistingMode);
    }

    /**
     * @covers \Bigtallbill\PasswordLocker\PasswordLocker::setData
     * @covers \Bigtallbill\PasswordLocker\PasswordLocker::getData
     *
     * @covers \Bigtallbill\PasswordLocker\PasswordLocker::setPass
     * @covers \Bigtallbill\PasswordLocker\PasswordLocker::getPass
     *
     * @covers \Bigtallbill\PasswordLocker\PasswordLocker::setRandomNumberSource
     * @covers \Bigtallbill\PasswordLocker\PasswordLocker::getRandomNumberSource
     *
     * @covers \Bigtallbill\PasswordLocker\PasswordLocker::getDecrypted
     *
     * @covers \Bigtallbill\PasswordLocker\PasswordLocker::setPasswordFactory
     * @covers \Bigtallbill\PasswordLocker\PasswordLocker::getPasswordFactory
     */
    public function testMiscSettersGetters()
    {
        // setData getData
        $expected = 'some data';
        $this->object->setData($expected);
        $actual = $this->object->getData();

        $this->assertSame($expected, $actual);


        // setPass getPass
        $expected = 'my password';
        $this->object->setPass($expected);
        $actual = $this->object->getPass();

        $this->assertSame($expected, $actual);


        // getData setData
        $expected = 1;
        $this->object->setRandomNumberSource($expected);
        $actual = $this->object->getRandomNumberSource();

        $this->assertSame($expected, $actual);


        // getDecrypted
        $expected = 'array';
        $actual = $this->object->getDecrypted();

        $this->assertInternalType($expected, $actual);


        // setPasswordFactory setData
        $expected = '\Bigtallbill\PasswordLocker\IPasswordFactory';
        $mock = $this->getMockForAbstractClass($expected);

        $this->object->setPasswordFactory($mock);
        $actual = $this->object->getPasswordFactory();

        $this->assertInstanceOf($expected, $actual);
    }

    //--------------------------------------
    // UTILITY METHODS
    //--------------------------------------

    /**
     * @covers \Bigtallbill\PasswordLocker\PasswordLocker::getModes
     * @covers \Bigtallbill\PasswordLocker\PasswordLocker::getAlgorithms
     * @covers \Bigtallbill\PasswordLocker\PasswordLocker::getHashAlgorithms
     */
    public function testUtilityMethods()
    {
        // getModes
        $expected = 'array';
        $actual = $this->object->getModes();

        $this->assertInternalType($expected, $actual);


        // getModes
        $expected = 'array';
        $actual = $this->object->getAlgorithms();

        $this->assertInternalType($expected, $actual);


        // getModes
        $expected = 'array';
        $actual = $this->object->getHashAlgorithms();

        $this->assertInternalType($expected, $actual);
    }

    /**
     * @covers \Bigtallbill\PasswordLocker\PasswordLocker::assertInArray
     */
    public function testAssertInArray()
    {
        PasswordLocker::assertInArray('i exist', array('i exist'));
    }

    /**
     * @covers \Bigtallbill\PasswordLocker\PasswordLocker::assertInArray
     *
     * @expectedException \Bigtallbill\PasswordLocker\PasswordLockerException
     */
    public function testAssertInArray2()
    {
        PasswordLocker::assertInArray('i dont exist', array());
    }

    /**
     * @covers \Bigtallbill\PasswordLocker\PasswordLocker::compatibilityCheck
     */
    public function testCompatibilityCheck()
    {
        PasswordLocker::compatibilityCheck();
    }
}
