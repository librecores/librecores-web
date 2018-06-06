<?php

namespace Librecores\ProjectRepoBundle\Util;

use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Process\ProcessUtils;

/**
 * Creates a process object
 *
 * @author Amitosh Swain Mahapatra <amitosh.swain@gmail.com>
 */
class ProcessCreator
{

    /**
     * Create a new process
     *
     * @param string   $cmd
     * @param string[] $args
     *
     * @return Process
     */
    public function createProcess(string $cmd, array $args): Process
    {
        $commandLine = ProcessUtils::escapeArgument($cmd)
            .' '
            .implode(' ', array_map([ProcessUtils::class, 'escapeArgument'], $args));
        $process = new Process($commandLine);

        return $process;
    }
}
