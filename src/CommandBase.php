<?php

namespace AcquiaOrca\Robo\Plugin\Commands;

use Robo\Exception\TaskException;
use Robo\Tasks;
use Symfony\Component\Process\ExecutableFinder;

/**
 * Provides a base Robo command implementation.
 *
 * All Composer tasks are overridden here to specify the global Composer path so
 * as to avoid version issues since Composer itself is a required dependency.
 */
abstract class CommandBase extends Tasks
{

    /**
     * The relative path to the build directory.
     */
    const BUILD_DIR = '../build';

    /**
     * The options passed to the command.
     *
     * @var array
     */
    protected $commandOptions;

    /**
     * @return \Robo\ResultData
     */
    abstract public function execute();

    /**
     * {@inheritdoc}
     *
     * @throws \Robo\Exception\TaskException
     */
    protected function taskComposerConfig($pathToComposer = null)
    {
        return parent::taskComposerConfig($this->handleComposerPathArg($pathToComposer));
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Robo\Exception\TaskException
     */
    protected function taskComposerCreateProject($pathToComposer = null)
    {
        return parent::taskComposerCreateProject($this->handleComposerPathArg($pathToComposer));
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Robo\Exception\TaskException
     */
    protected function taskComposerDumpAutoload($pathToComposer = null)
    {
        return parent::taskComposerDumpAutoload($this->handleComposerPathArg($pathToComposer));
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Robo\Exception\TaskException
     */
    protected function taskComposerInit($pathToComposer = null)
    {
        return parent::taskComposerInit($this->handleComposerPathArg($pathToComposer));
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Robo\Exception\TaskException
     */
    protected function taskComposerInstall($pathToComposer = null)
    {
        return parent::taskComposerInstall($this->handleComposerPathArg($pathToComposer));
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Robo\Exception\TaskException
     */
    protected function taskComposerRemove($pathToComposer = null)
    {
        return parent::taskComposerRemove($this->handleComposerPathArg($pathToComposer));
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Robo\Exception\TaskException
     */
    protected function taskComposerRequire($pathToComposer = null)
    {
        return parent::taskComposerRequire($this->handleComposerPathArg($pathToComposer));
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Robo\Exception\TaskException
     */
    protected function taskComposerUpdate($pathToComposer = null)
    {
        return parent::taskComposerUpdate($this->handleComposerPathArg($pathToComposer));
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Robo\Exception\TaskException
     */
    protected function taskComposerValidate($pathToComposer = null)
    {
        return parent::taskComposerValidate($this->handleComposerPathArg($pathToComposer));
    }

    /**
     * Handles the Composer path argument, defaulting to global the install.
     *
     * @param string $path_to_composer
     *
     * @return string
     * @throws \Robo\Exception\TaskException
     */
    private function handleComposerPathArg($path_to_composer)
    {
        return $path_to_composer ?: $this->getPathToGlobalComposer();
    }

    /**
     * Gets the path to the global Composer installation.
     *
     * @return string
     * @throws \Robo\Exception\TaskException
     */
    private function getPathToGlobalComposer()
    {
        static $path;
        if (!$path) {
            $finder = new ExecutableFinder();
            $path = $finder->find('composer');
            if (!$path) {
                throw new TaskException(__CLASS__, 'Global Composer installation could be found.');
            }
        }
        return $path;
    }
}