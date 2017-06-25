<?php

namespace Librecores\ProjectRepoBundle\Util;

use Symfony\Component\Process\ProcessBuilder;

/**
 * Default implementation of ExecutorInterface
 *
 * Uses Symfony ProcessBuilder component to build the process.
 *
 */
class DefaultExecutor implements ExecutorInterface
{
    const CLOC_TIMEOUT = 300;

    /**
     * {@inheritdoc}
     */
    public function exec(
        string $command,
        array $args = [],
        array $options = []
    ) : string {
        $builder = new ProcessBuilder();
        $builder->setPrefix($command)
            ->setArguments($args)
            ->setTimeout(static::CLOC_TIMEOUT);
        if (array_key_exists('cwd', $options)) {
            $builder->setWorkingDirectory($options['cwd']);
        }

        if (array_key_exists('environment', $options)) {
            $builder->addEnvironmentVariables($options['environment']);
        }

        if (array_key_exists('inherit_env', $options)) {
            $builder->inheritEnvironmentVariables($options['inherit_env']);
        }

        $process = $builder->getProcess();
        $process->mustRun();

        return $process->getOutput();
    }
}