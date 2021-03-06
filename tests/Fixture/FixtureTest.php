<?php

namespace Acquia\Orca\Tests\Fixture;

use Acquia\Orca\Fixture\Fixture;
use Acquia\Orca\Fixture\Project;
use Acquia\Orca\Fixture\ProjectManager;
use Acquia\Orca\Utility\ConfigLoader;
use Noodlehaus\Config;
use Noodlehaus\Parser\Json;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @covers \Acquia\Orca\Fixture\Fixture
 *
 * @property \Prophecy\Prophecy\ObjectProphecy|\Acquia\Orca\Utility\ConfigLoader $configLoader
 * @property \Prophecy\Prophecy\ObjectProphecy|\Symfony\Component\Filesystem\Filesystem $filesystem
 * @property \Prophecy\Prophecy\ObjectProphecy|\Acquia\Orca\Fixture\ProjectManager $projectManager
 * @property string $rootPath
 */
class FixtureTest extends TestCase {

  protected function setUp() {
    $this->configLoader = $this->prophesize(ConfigLoader::class);
    $this->filesystem = $this->prophesize(Filesystem::class);
    $this->projectManager = $this->prophesize(ProjectManager::class);
    $this->rootPath = '/var/www/orca-build';
  }

  public function testConstruction() {
    $fixture = $this->createFixture();

    $this->assertTrue($fixture instanceof Fixture, 'Instantiated class.');
  }

  /**
   * @dataProvider providerExists
   */
  public function testExists($root_path, $exists) {
    $this->rootPath = $root_path;
    $this->filesystem
      ->exists($root_path)
      ->willReturn($exists);
    $fixture = $this->createFixture();

    $return = $fixture->exists();

    $this->filesystem
      ->exists($root_path)
      ->shouldHaveBeenCalledTimes(1);
    $this->assertEquals($exists, $return, 'Returned correct value.');
  }

  public function providerExists() {
    return [
      ['/fixture-exists', TRUE],
      ['/no-fixture-there', FALSE],
    ];
  }

  /**
   * @dataProvider providerGetPath
   */
  public function testGetPath($root_path) {
    $this->rootPath = $root_path;
    $fixture = $this->createFixture();
    $sub_path = '/some/sub-path';

    $this->assertEquals($root_path, $fixture->getPath(), 'Resolved root path.');
    $this->assertEquals("{$root_path}/{$sub_path}", $fixture->getPath($sub_path), 'Resolved root path with sub-path.');
  }

  public function providerGetPath() {
    return [
      ['/var/www/orca-build'],
      ['/tmp/test'],
    ];
  }

  /**
   * @dataProvider providerGetTestsPath
   */
  public function testGetTestsPath($data, $expected) {
    $config = new Config(json_encode($data), new Json(), TRUE);
    $this->configLoader
      ->load('/var/www/orca-build/composer.json')
      ->willReturn($config);
    $project = $this->prophesize(Project::class);
    $project->getInstallPathRelative()
      ->willReturn('docroot/modules/contrib/acquia/example');
    $project = $project->reveal();
    $this->projectManager
      ->get('drupal/example')
      ->willReturn($project);
    $fixture = $this->createFixture();

    $actual = $fixture->getTestsPath();

    $this->assertEquals($expected, $actual, '');
  }

  public function providerGetTestsPath() {
    return [
      [
        [],
        '/var/www/orca-build/docroot/modules/contrib/acquia',
      ],
      [
        [
          'extra' => [
            'orca' => [
              'sut' => 'drupal/example',
            ],
          ],
        ],
        '/var/www/orca-build/docroot/modules/contrib/acquia',
      ],
      [
        [
          'extra' => [
            'orca' => [
              'sut' => 'drupal/example',
              'sut-only' => TRUE,
            ],
          ],
        ],
        '/var/www/orca-build/docroot/modules/contrib/acquia/example',
      ],
    ];
  }

  protected function createFixture(): Fixture {
    /** @var \Acquia\Orca\Utility\ConfigLoader $config_loader */
    $config_loader = $this->configLoader->reveal();
    /** @var \Symfony\Component\Filesystem\Filesystem $filesystem */
    $filesystem = $this->filesystem->reveal();
    /** @var \Acquia\Orca\Fixture\ProjectManager $project_manager */
    $project_manager = $this->projectManager->reveal();
    return new Fixture($config_loader, $filesystem, $this->rootPath, $project_manager);
  }

}
