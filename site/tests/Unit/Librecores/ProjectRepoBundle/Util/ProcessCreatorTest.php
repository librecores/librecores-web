<?php

namespace Tests\Librecores\Util;

use Librecores\ProjectRepoBundle\Util\ExecutorInterface;
use Librecores\ProjectRepoBundle\Util\ProcessCreator;
use PHPUnit\Framework\TestCase;

/**
 * Tests for DefaultExecutor
 * @author Amitosh Swain Mahapatra <amitosh.swain@gmail.com>
 *
 * @see ProcessCreator
 */
class ProcessCreatorTest extends TestCase
{
    public function testExecSuccess()
    {
        $processCreator = new ProcessCreator();
        $process = $processCreator->createProcess(['echo', 'hello']);
        $process->mustRun();
        $this->assertEquals("hello\n", $process->getOutput());
    }

    public function testExecNoExecVulnerability()
    {
        $processCreator = new ProcessCreator();
        $process = $processCreator->createProcess(['echo', '&& false $ HOME']);
        $process->mustRun();
        $this->assertEquals("&& false $ HOME\n", $process->getOutput());
    }
}
