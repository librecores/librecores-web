<?php

namespace Tests\Librecores\Util;

use Librecores\ProjectRepoBundle\Util\DefaultExecutor;
use PHPUnit\Framework\TestCase;
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
        $executor = new DefaultExecutor();
        $output = $executor->exec('echo', ['hello']);

        $this->assertEquals("hello\n", $output);
    }

    public function testExecNoExecVulnerebility()
    {
        $executor = new DefaultExecutor();
        $output = $executor->exec('echo', ['&& false']);

        $this->assertEquals("&& false\n", $output);

        $this->assertEquals("$ HOME\n", $executor->exec('echo', ['$ HOME']));
    }

    public function testExecSuccessNoOutput()
    {
        $executor = new DefaultExecutor();
        $output = $executor->exec('true');

        $this->assertEquals('', $output);
    }

    public function testExecSuccessWithOptions()
    {
        $executor = new DefaultExecutor();
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

        $executor = new DefaultExecutor();
        $executor->exec('false');
    }
}
