<?php

namespace Acquia\Orca\Tests\Command\Tests;

use Acquia\Orca\Utility\Clock;
use Acquia\Orca\Command\StatusCodes;
use Acquia\Orca\Command\Tests\RunCommand;
use Acquia\Orca\Server\ChromeDriverServer;
use Acquia\Orca\Fixture\Fixture;
use Acquia\Orca\Task\BehatTask;
use Acquia\Orca\Task\PhpUnitTask;
use Acquia\Orca\Task\TaskRunner;
use Acquia\Orca\Tests\Command\CommandTestBase;
use Acquia\Orca\Server\WebServer;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @property \Prophecy\Prophecy\ObjectProphecy|\Acquia\Orca\Task\BehatTask $behat
 * @property \Prophecy\Prophecy\ObjectProphecy|\Acquia\Orca\Server\ChromeDriverServer $chromedriver
 * @property \Prophecy\Prophecy\ObjectProphecy|\Acquia\Orca\Utility\Clock $clock
 * @property \Prophecy\Prophecy\ObjectProphecy|\Acquia\Orca\Fixture\Fixture $fixture
 * @property \Prophecy\Prophecy\ObjectProphecy|\Acquia\Orca\Task\PhpUnitTask $phpunit
 * @property \Prophecy\Prophecy\ObjectProphecy|\Acquia\Orca\Task\TaskRunner $taskRunner
 * @property \Prophecy\Prophecy\ObjectProphecy|\Acquia\Orca\Server\WebServer $webServer
 */
class RunCommandTest extends CommandTestBase {

  private const TESTS_DIR = '/var/www/orca-build/docroot/modules/contrib/acquia';

  protected function setUp() {
    $this->behat = $this->prophesize(BehatTask::class);
    $this->chromedriver = $this->prophesize(ChromeDriverServer::class);
    $this->clock = $this->prophesize(Clock::class);
    $this->fixture = $this->prophesize(Fixture::class);
    $this->fixture->exists()
      ->willReturn(FALSE);
    $this->fixture->getPath()
      ->willReturn(self::FIXTURE_ROOT);
    $this->fixture->getTestsPath()
      ->willReturn(self::TESTS_DIR);
    $this->phpunit = $this->prophesize(PhpUnitTask::class);
    $this->taskRunner = $this->prophesize(TaskRunner::class);
    $this->webServer = $this->prophesize(WebServer::class);
  }

  /**
   * @dataProvider providerCommand
   */
  public function testCommand($fixture_exists, $run_called, $status_code, $display) {
    $this->fixture
      ->exists()
      ->shouldBeCalledTimes(1)
      ->willReturn($fixture_exists);
    $this->taskRunner
      ->addTask($this->phpunit->reveal())
      ->shouldBeCalledTimes(1)
      ->willReturn($this->taskRunner);
    $this->taskRunner
      ->addTask($this->behat->reveal())
      ->shouldBeCalledTimes(1)
      ->willReturn($this->taskRunner);
    $this->webServer
      ->start()
      ->shouldBeCalledTimes($run_called);
    $this->chromedriver
      ->start()
      ->shouldBeCalledTimes($run_called);
    $this->taskRunner
      ->setPath(self::TESTS_DIR)
      ->shouldBeCalledTimes($run_called)
      ->willReturn($this->taskRunner);
    $this->taskRunner
      ->run()
      ->shouldBeCalledTimes($run_called)
      ->willReturn($status_code);
    $this->webServer
      ->stop()
      ->shouldBeCalledTimes($run_called);
    $this->chromedriver
      ->stop()
      ->shouldBeCalledTimes($run_called);
    $tester = $this->createCommandTester();

    $this->executeCommand($tester, RunCommand::getDefaultName(), []);

    $this->assertEquals($display, $tester->getDisplay(), 'Displayed correct output.');
    $this->assertEquals($status_code, $tester->getStatusCode(), 'Returned correct status code.');
  }

  public function providerCommand() {
    return [
      [TRUE, 1, StatusCodes::OK, ''],
      [TRUE, 1, StatusCodes::ERROR, ''],
      [FALSE, 0, StatusCodes::ERROR, sprintf("Error: No fixture exists at %s.\nHint: Use the \"fixture:init\" command to create one.\n", self::FIXTURE_ROOT)],
    ];
  }

  private function createCommandTester(): CommandTester {
    $application = new Application();
    /** @var \Acquia\Orca\Task\BehatTask $behat */
    $behat = $this->behat->reveal();
    /** @var \Acquia\Orca\Server\ChromeDriverServer $chromedriver */
    $chromedriver = $this->chromedriver->reveal();
    /** @var \Acquia\Orca\Utility\Clock $clock */
    $clock = $this->clock->reveal();
    /** @var \Acquia\Orca\Fixture\Fixture $fixture */
    $fixture = $this->fixture->reveal();
    /** @var \Acquia\Orca\Task\PhpUnitTask $phpunit */
    $phpunit = $this->phpunit->reveal();
    /** @var \Acquia\Orca\Task\TaskRunner $task_runner */
    $task_runner = $this->taskRunner->reveal();
    /** @var \Acquia\Orca\Server\WebServer $web_server */
    $web_server = $this->webServer->reveal();
    $application->add(new RunCommand($behat, $chromedriver, $clock, $fixture, $phpunit, $task_runner, $web_server));
    /** @var \Acquia\Orca\Command\Tests\RunCommand $command */
    $command = $application->find(RunCommand::getDefaultName());
    $this->assertInstanceOf(RunCommand::class, $command, 'Successfully instantiated class.');
    return new CommandTester($command);
  }

}
