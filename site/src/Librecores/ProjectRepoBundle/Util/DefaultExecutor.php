<?php

namespace Librecores\ProjectRepoBundle\Util;

use Psr\Log\LoggerInterface;

use Symfony\Component\Process\ProcessBuilder;

/**
 * Default implementation of ExecutorInterface
 *
 * Uses Symfony ProcessBuilder component to build the process.
 *
 * @author Amitosh Swain Mahapatra <amitosh.swain@gmail.com>
 */
class DefaultExecutor implements ExecutorInterface
{
    /**
     * Default timeout for commands
     *
     * @var int
     */
    const DEFAULT_TIMEOUT = 300;

    /**
     * logger for this service
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * DefaultExecutor constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
       $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function exec(string $cmd, array $args = [], array $options = [], &$exitCode = null, &$errorOutput = null) : string
    {

        $timeout = array_key_exists('timeout', $options) ? $options['timeout'] : static::DEFAULT_TIMEOUT;
        $errors = array_key_exists('errors', $options) ? $options['errors'] : false;

        $builder = new ProcessBuilder();
        $builder->setPrefix($cmd)
            ->setArguments($args)
            ->setTimeout($timeout);

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

        $this->logger->debug('Starting process ' . $process->getCommandLine());

        if ($errors) {
            $process->run();
            $exitCode = $process->getExitCode();
            $errorOutput = $process->getErrorOutput();
        } else {
            $process->mustRun();
        }

        $this->logger->debug("Process $cmd exited succesfuly");

        return $process->getOutput();
    }
}