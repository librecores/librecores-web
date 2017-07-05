<?php

namespace Tests\Librecores\Util;

use Librecores\ProjectRepoBundle\Util\DefaultExecutor;
use Librecores\ProjectRepoBundle\Util\ExecutorInterface;
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
        $output = $this->createExecutor()->exec('echo', ['hello']);

        $this->assertEquals("hello\n", $output);
    }

    public function testExecNoExecVulnerebility()
    {
        $executor = $this->createExecutor();
        $output = $executor->exec('echo', ['&& false']);

        $this->assertEquals("&& false\n", $output);

        $this->assertEquals("$ HOME\n", $executor->exec('echo', ['$ HOME']));
    }

    public function testExecSuccessNoOutput()
    {
        $output = $this->createExecutor()->exec('true');

        $this->assertEquals('', $output);
    }

    public function testExecSuccessWithOptions()
    {
        $output = $this->createExecutor()->exec(
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
        $this->createExecutor()->exec('false');
    }

    public function testExecWithErrorsOption()
    {
        $output = $this->createExecutor()->exec(join(
            DIRECTORY_SEPARATOR,
            ['tests', 'Librecores', 'ProjectRepoBundle', 'Resources', 'failing-script.sh']),[],['errors' => true],$exitCode, $error);

        $this->assertEquals($output, "Output\n");
        $this->assertEquals($exitCode,1);
        $this->assertEquals($error,"Failed\n");
    }

    private function createExecutor() : ExecutorInterface
    {
        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);
        return new DefaultExecutor($logger);
    }
}
