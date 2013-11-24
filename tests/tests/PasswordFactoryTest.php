<?php
namespace Bigtallbill\PasswordLocker;

/**
 * Class PasswordFactoryTest
 * @package Bigtallbill\PasswordLocker
 */
class PasswordFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PasswordFactory
     */
    protected $object;

    public function setup()
    {
        $this->object = new PasswordFactory();
    }

    /**
     * @covers \Bigtallbill\PasswordLocker\PasswordFactory::make
     */
    public function testMake()
    {
        // test basic template
        $template = '{C:5}{S:1}{C:4}{N:1}';
        $pattern = "/[a-zA-Z]{5}.{1}[a-zA-Z]{4}[0-9]{1}/";

        $password = PasswordFactory::make($template);

        $expected = 1;
        $actual = preg_match($pattern, $password);

        $this->assertSame($expected, $actual);

        // test number string
        $template = '{NC:50}';
        $pattern = "/[a-zA-Z0-9]{50}/";

        $password = PasswordFactory::make($template);

        $expected = 1;
        $actual = preg_match($pattern, $password);

        $this->assertSame($expected, $actual);
    }

    /**
     * @covers \Bigtallbill\PasswordLocker\PasswordFactory::getRandomCharacters
     */
    public function testGetRandomCharacters()
    {
        $chars = PasswordFactory::getRandomCharacters(50, 'ABC');
        $pattern = "/[A-C]{50}/";

        $expected = 1;
        $actual = preg_match($pattern, $chars);

        $this->assertSame($expected, $actual);
    }

    /**
     * @covers \Bigtallbill\PasswordLocker\PasswordFactory::getRandomCharacterInString
     */
    public function testGetRandomCharacterInString()
    {
        $chars = PasswordFactory::getRandomCharacterInString('ABC');
        $pattern = "/[A-C]{1}/";

        $expected = 1;
        $actual = preg_match($pattern, $chars);

        $this->assertSame($expected, $actual);
    }
}
