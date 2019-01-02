<?php

namespace Librecores\ProjectRepoBundle\Util;

use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Process\ProcessUtils;

/**
 * Creates a process object
 *
 * Inject this class as service instead of using Process directly if you want
 * to be able to mock a process in a unit test.
 *
 * @author Amitosh Swain Mahapatra <amitosh.swain@gmail.com>
 */
class ProcessCreator
{

    /**
     * Return a new Process object
     *
     * @param string[] $commandLine
     * @param string $cwd working directory
     *
     * @return Process
     */
    public function createProcess($commandLine, $cwd = null): Process
    {
        return new Process($commandLine, $cwd);
    }
}
