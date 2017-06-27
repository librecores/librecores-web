<?php

namespace Tests\Librecores\Util;

use Librecores\ProjectRepoBundle\Util\DefaultExecutor;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * Tests for DefaultExecutor
 * @author Amitosh Swain Mahapatra <amitosh.swain@gmail.com>
 *
 * @see DefaultExecutor
 */
class DefaultExecutorTest extends TestCase
{
    public function testExecSuccess()
    {
        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $executor = new DefaultExecutor($logger);
        
        $output = $executor->exec('echo', ['hello']);

        $this->assertEquals("hello\n", $output);
    }

    public function testExecNoExecVulnerebility()
    {
        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $executor = new DefaultExecutor($logger);
        $output = $executor->exec('echo', ['&& false']);

        $this->assertEquals("&& false\n", $output);

        $this->assertEquals("$ HOME\n", $executor->exec('echo', ['$ HOME']));
    }

    public function testExecSuccessNoOutput()
    {
        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $executor = new DefaultExecutor($logger);

        $output = $executor->exec('true');

        $this->assertEquals('', $output);
    }

    public function testExecSuccessWithOptions()
    {
        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $executor = new DefaultExecutor($logger);

        $output = $executor->exec(
            'cat',
            ['file.txt'],
            [
                'cwd' => join(
                    DIRECTORY_SEPARATOR,
                    ['tests', 'Librecores', 'ProjectRepoBundle', 'Resources']
                ),
            ]
        );

        $this->assertEquals("hello world", $output);
    }

    public function testExecFail()
    {
        $this->expectException(ProcessFailedException::class);

        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $executor = new DefaultExecutor($logger);

        $executor->exec('false');
    }
}
