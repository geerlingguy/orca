<?php

namespace Acquia\Orca\Utility;

use Noodlehaus\Config;

/**
 * Loads a configuration file.
 *
 * The sole purpose of this class is to make \Noodlehaus\Config an injectable
 * dependency.
 */
class ConfigLoader {

  /**
   * Loads configuration.
   *
   * @param string|array $values
   *   A filename or an array of filenames of configuration files.
   *
   * @return \Noodlehaus\Config
   */
  public function load($values): Config {
    return new Config($values);
  }

}
