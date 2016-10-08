<?php
/**
 * Created by PhpStorm.
 * User: bigtallbill
 * Date: 3/6/15
 * Time: 11:51 PM
 */

namespace Bigtallbill\PasswordLocker\Commands;


use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CommandCreateTest extends \PHPUnit_Framework_TestCase
{
    /** @var Application */
    protected $app;
    protected $testFilePath;

    protected function setUp()
    {
        parent::setUp();

        $this->app = new Application();
        $this->app->add(new CommandCreate());
        $this->app->add(new CommandRead());
        $this->testFilePath = sys_get_temp_dir() . '/phpunit_pwdfile';
    }

    protected function tearDown()
    {
        parent::tearDown();
        unlink($this->testFilePath);
    }


    public function testExecute()
    {
        $command = $this->app->find('create');
        $commandTester = new CommandTester($command);

        $dialog = $command->getHelper('question');
        $dialog->setInputStream($this->getInputStream("Test\n"));

        $commandTester->execute(
            array(
                'command' => $command->getName(),
                'file' => $this->testFilePath,
                'id' => 'testkey',
                'raw' => 'foo',
                '--show-pass' => null
            )
        );

        $this->assertRegExp("/password: $/", $commandTester->getDisplay(), 'only the password prompt should be shown');
        $this->assertFileExists($this->testFilePath, 'the file should have been created');
    }

    public function testCreate()
    {
        $command = $this->app->find('create');
        $commandTester = new CommandTester($command);

        $dialog = $command->getHelper('question');
        $dialog->setInputStream($this->getInputStream("Test\n"));

        $commandTester->execute(
            array(
                'command' => $command->getName(),
                'file' => $this->testFilePath,
                'id' => 'testkey',
                'raw' => 'foo',
                '--show-pass' => null
            ),
            [
                'pass' => 'password',
                'verbosity' => 5
            ]
        );

        $this->assertFileExists($this->testFilePath, 'the file should have been created');
    }

    protected function getInputStream($input)
    {
        $stream = fopen('php://memory', 'r+', false);
        fputs($stream, $input);
        rewind($stream);

        return $stream;
    }
}
