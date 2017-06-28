<?php

namespace Librecores\ProjectRepoBundle\Util;

/**
 * Interface ExecutorInterface
 *
 * @author Amitosh Swain Mahapatra <amitosh.swain@gmail.com>
 */
interface ExecutorInterface
{
    /**
     * Run a command and get its output.
     *
     * This method shall throw an exception if the command cannot be executed.
     *
     * @param string $cmd
     * @param string[] $args
     * @param sring[] $opts
     * @return string
     */
    function exec(string $cmd, array $args = [], array $opts = [] ) : string;
}