<?php

namespace App\Util;

use App\Util\FileUtil;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use org\bovigo\vfs\vfsStream;

/**
 * Tests for FileUtil
 *
 * @see FileUtil
 */
class FileUtilTest extends TestCase
{
    public function testFindFile()
    {
        // This test uses a virtual filesystem with the following layout
        $directory = [
            'README.pod' => 'README.pod',
            'lower.md' => 'lower.md',
            'README.md' => 'README.md',
            'README.unknown' => 'README.unknown',
            'README' => 'README',
            'READYOU' => 'READYOU',
            'READYOU.md' => 'READYOU.md',
            'READYOU.pod' => 'READYOU.pod',
            'READYOU.unknown' => 'READYOU.unknown',
            'subdir' => [ 'file1' => 'file1', 'file2' => 'file2'],
            'subdir.md' => 'subdir.md',
            'ZZZ' => 'ZZZ',
        ];
        $fs = vfsStream::setup('basedir', null, $directory);

        $foundFile = FileUtil::findFile(
            $fs->url(),
            [ 'doesntexist' ],
            [ '.md', '.pod', '' ],
            true
        );
        $this->assertFalse($foundFile);

        $foundFile = FileUtil::findFile(
            $fs->url(),
            [ 'README', 'READYOU' ],
            [ '.md', '.pod', '' ],
            true
        );
        $this->assertEquals(vfsStream::url('basedir/README.md'), $foundFile);

        $foundFile = FileUtil::findFile(
            $fs->url(),
            [ 'READYOU', 'README' ],
            [ '.md', '.pod', '' ],
            true
        );
        $this->assertEquals(vfsStream::url('basedir/READYOU.md'), $foundFile);

        $foundFile = FileUtil::findFile(
            $fs->url(),
            [ 'README', 'READYOU' ],
            [ '.pod', '.md', '' ],
            true
        );
        $this->assertEquals(vfsStream::url('basedir/README.pod'), $foundFile);

        $foundFile = FileUtil::findFile(
            $fs->url(),
            [ 'README', 'READYOU' ],
            [ '.asdf', '.pod', '.md', '' ],
            true
        );
        $this->assertEquals(vfsStream::url('basedir/README.pod'), $foundFile);

        $foundFile = FileUtil::findFile(
            $fs->url(),
            [ 'LOWER', 'READYOU' ],
            [ '.md', '.pod', '.md', '' ],
            true
        );
        $this->assertEquals(vfsStream::url('basedir/READYOU.md'), $foundFile);

        $foundFile = FileUtil::findFile(
            $fs->url(),
            [ 'LOWER', 'READYOU' ],
            [ '.md', '.pod', '' ],
            false
        );
        $this->assertEquals(vfsStream::url('basedir/lower.md'), $foundFile);

        $foundFile = FileUtil::findFile(
            $fs->url(),
            [ 'subdir' ],
            [ '', '.md' ],
            true
        );
        $this->assertEquals(vfsStream::url('basedir/subdir.md'), $foundFile);

        $foundFile = FileUtil::findFile(
            $fs->url(),
            [ 'subdir' ],
            [ '' ],
            true
        );
        $this->assertFalse($foundFile);
    }
}
