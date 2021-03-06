<?php

namespace Acquia\Orca\Fixture;

/**
 * Provides access to a project's details.
 */
class Project {

  /**
   * The raw project data supplied to the constructor.
   *
   * @var array
   */
  private $data;

  /**
   * The path the project installs at relative to the fixture root.
   *
   * @var string
   */
  private $installPath;

  /**
   * The package name.
   *
   * E.g., "drupal/example".
   *
   * @var string
   */
  private $packageName;

  /**
   * The project name.
   *
   * E.g., "example".
   *
   * @var string
   */
  private $projectName;

  /**
   * The URL for the Composer path repository.
   *
   * E.g., "../example" or "/var/www/example/modules/submodule".
   *
   * @var string
   */
  private $repositoryUrl;

  /**
   * The type.
   *
   * E.g., "drupal-module".
   *
   * @var string
   */
  private $type = 'drupal-module';

  /**
   * The version constraint.
   *
   * E.g., "*" or "~1.0".
   *
   * @var string
   */
  private $version = '*';

  /**
   * Constructs an instance.
   *
   * @param array $data
   *   An array of project data.
   */
  public function __construct(array $data) {
    $this->data = $data;
    $this->initializePackageName();
    $this->initializeProjectName();
    $this->initializeRepositoryUrl();
    $this->initializeInstallPath();
    $this->initializeType();
    $this->initializeVersion();
  }

  /**
   * Gets the path the project installs at relative to the fixture root.
   *
   * @return string
   */
  public function getInstallPathRelative(): string {
    if (!empty($this->installPath)) {
      return $this->installPath;
    }

    switch ($this->getType()) {
      case 'drupal-drush':
        return "drush/Commands/{$this->getProjectName()}";

      case 'drupal-library':
      case 'bower-asset':
      case 'npm-asset':
        return "docroot/libraries/{$this->getProjectName()}";

      case 'drupal-module':
        return sprintf("%s/{$this->getProjectName()}", Fixture::ACQUIA_MODULE_PATH);

      case 'drupal-profile':
        return "docroot/profiles/contrib/acquia/{$this->getProjectName()}";

      case 'drupal-theme':
        return "docroot/themes/contrib/acquia/{$this->getProjectName()}";

      default:
        return "vendor/{$this->getPackageName()}";
    }
  }

  /**
   * Sets the path the project installs at relative to the fixture root.
   *
   * @param string $install_path
   *   The install path relative to the fixture root.
   *
   * @return self
   */
  public function setInstallPathRelative(string $install_path): Project {
    $this->installPath = $install_path;
    return $this;
  }

  /**
   * Gets the URL for the Composer path repository.
   *
   * E.g., "../example" or "/var/www/example/modules/submodule".
   */
  public function getRepositoryUrl(): string {
    return $this->repositoryUrl;
  }

  /**
   * Sets the URL for the Composer path repository.
   *
   * @param string $url
   *   An absolute path or a path relative to the fixture root, e.g.,
   *   "../example" or "/var/www/example/modules/submodule".
   *
   * @return self
   */
  public function setRepositoryUrl(string $url): Project {
    $this->repositoryUrl = $url;
    return $this;
  }

  /**
   * Gets the type.
   *
   * E.g., "drupal-module".
   */
  public function getType(): string {
    return $this->type;
  }

  /**
   * Sets the type.
   *
   * @param string $type
   *   The type, e.g., "drupal-module".
   *
   * @return self
   */
  public function setType(string $type): Project {
    $this->type = $type;
    return $this;
  }

  /**
   * Gets the package name.
   *
   * E.g., "drupal/example".
   *
   * @return string
   */
  public function getPackageName(): string {
    return $this->packageName;
  }

  /**
   * Sets the package name.
   *
   * @param string $name
   *   The name, e.g., "drupal/example".
   *
   * @return self
   */
  public function setPackageName(string $name): Project {
    $this->packageName = $name;
    return $this;
  }

  /**
   * Gets the package string.
   *
   * Gets the package string as passed to `composer require`, e.g.,
   * "drupal/example:~1.0".
   *
   * @return string
   */
  public function getPackageString(): string {
    return "{$this->getPackageName()}:{$this->getVersion()}";
  }

  /**
   * Gets the project name.
   *
   * E.g., "example".
   *
   * @return string
   */
  public function getProjectName(): string {
    return $this->projectName;
  }

  /**
   * Sets the project name.
   *
   * @param string $name
   *   The project name, e.g., "example".
   *
   * @return self
   */
  public function setProjectName(string $name): Project {
    $this->projectName = $name;
    return $this;
  }

  /**
   * Gets the version constraint.
   *
   * E.g., "*" or "~1.0".
   *
   * @return string
   */
  public function getVersion(): string {
    return $this->version;
  }

  /**
   * Sets the version constraint.
   *
   * @param string $version
   *   The version constraint, e.g., "*" or "~1.0".
   *
   * @return self
   */
  public function setVersion(string $version): Project {
    $this->version = $version;
    return $this;
  }

  /**
   * Initializes the package name.
   */
  private function initializePackageName(): void {
    if (!array_key_exists('name', $this->data)) {
      throw new \InvalidArgumentException('Missing required property: "name"');
    }
    elseif (empty($this->data['name']) || !is_string($this->data['name']) || strpos($this->data['name'], '/') === FALSE) {
      throw new \InvalidArgumentException(sprintf('Invalid value for "name" property: %s', var_export($this->data['name'], TRUE)));
    }

    $this->setPackageName($this->data['name']);
  }

  /**
   * Initializes the project name.
   */
  private function initializeProjectName(): void {
    $name_parts = explode('/', $this->getPackageName());
    $name = $name_parts[count($name_parts) - 1];
    $this->setProjectName($name);
  }

  /**
   * Initializes the directory base name.
   *
   * I.e., the directory name (not path) of the project as determined by its
   * Git repository name.
   */
  private function initializeRepositoryUrl(): void {
    $this->setRepositoryUrl("../{$this->getProjectName()}");

    if (!empty($this->data['url'])) {
      $this->setRepositoryUrl($this->data['url']);
    }
  }

  /**
   * Initializes the install path.
   */
  private function initializeInstallPath(): void {
    if (!empty($this->data['install_path'])) {
      $this->setInstallPathRelative($this->data['install_path']);
    }
  }

  /**
   * Initializes the type.
   */
  private function initializeType(): void {
    if (!empty($this->data['type'])) {
      $this->setType($this->data['type']);
    }
  }

  /**
   * Initializes the version.
   */
  private function initializeVersion(): void {
    if (!empty($this->data['version'])) {
      $this->setVersion($this->data['version']);
    }
  }

}
