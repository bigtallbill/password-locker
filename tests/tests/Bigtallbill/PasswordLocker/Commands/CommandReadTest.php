<?php
/**
 * Created by PhpStorm.
 * User: bigtallbill
 * Date: 3/7/15
 * Time: 12:53 AM
 */

namespace Bigtallbill\PasswordLocker\Commands;


use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CommandReadTest extends \PHPUnit_Framework_TestCase {
    /** @var Application */
    protected $app;
    protected $testFilePath;

    protected function setUp()
    {
        parent::setUp();

        $this->app = new Application();
        $this->app->add(new CommandRead());
        $this->testFilePath = TEST_ASSETS . '/testpasswords';
    }

    public function testExecute()
    {
        $command = $this->app->find('read');
        $commandTester = new CommandTester($command);

        $dialog = $command->getHelper('question');
        $dialog->setInputStream($this->getInputStream("john\n"));

        $commandTester->execute(
            array(
                'command' => $command->getName(),
                'file' => $this->testFilePath,
                'id' => 'john',
                '--show-pass' => null
            )
        );

        $this->assertRegExp("/john/", $commandTester->getDisplay());
        $this->assertRegExp("/iamjohn/", $commandTester->getDisplay());
    }

    protected function getInputStream($input)
    {
        $stream = fopen('php://memory', 'r+', false);
        fputs($stream, $input);
        rewind($stream);

        return $stream;
    }
}
