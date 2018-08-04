<?php
namespace Librecores\ProjectRepoBundle\Util;

use Symfony\Component\Process\Process;

/**
 * Utilities to access files
 */
class FileUtil
{
    /**
     * Find a file in a directory matching a basename and an extension
     *
     * Both basename and extension are ordered arrays, the search starts with
     * the first basename and all possible extensions, and continues then with
     * the next basename.
     *
     * If a case-insensitive find is requested the file names in the filesystem and the passed
     * strings are assumed to be UTF-8 encoded.
     *
     * @param string   $basedir
     * @param string[] $basenames
     * @param string[] $extensions
     * @param bool     $caseSensitive perform case-sensitive matches
     *
     * @return string|boolean the name of the file including the base directory
     *                        if a match was found, or false if no match was
     *                        found
     */
    public static function findFile($basedir, $basenames, $extensions, $caseSensitive=true)
    {
        $basedirFilenames = @scandir($basedir);
        if ($basedirFilenames === false) {
            return false;
        }

        foreach ($basenames as $basename) {
            foreach ($extensions as $ext) {
                $filename = $basename.$ext;

                foreach ($basedirFilenames as $basedirFilename) {
                    if (!$caseSensitive) {
                        $s1 = mb_strtolower($basedirFilename, 'UTF-8');
                        $s2 = mb_strtolower($filename, 'UTF-8');
                        if ($s1 != $s2) {
                            continue;
                        }
                    } else {
                        if ($basedirFilename != $filename) {
                            continue;
                        }
                    }
                }

                // found file; double-check if the file exists (and if it is a file)
                $fullFilename = $basedir.'/'.$basedirFilename;
                if (!is_file($fullFilename)) {
                    return false;
                }
                return $fullFilename;
            }
        }

        return false;
    }

    /**
     * Recursive delete of a directory (rm -r)
     *
     * @param string $dir string directory to delete recursively
     *
     * @return
     */
    public static function recursiveRmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir."/".$object)) {
                        self::recursiveRmdir($dir."/".$object);
                    } else {
                        unlink($dir."/".$object);
                    }
                }
            }
            rmdir($dir);
        }
    }

    /**
     * Create temporary directory with an unique name and return its path
     *
     * The directory must be manually deleted if not used any more.
     *
     * @return string path to the temporary directory
     *
     * @throws \RuntimeException
     *
     * @see recursiveRmdir()
     */
    public static function createTemporaryDirectory($prefix)
    {
        $cmd = 'mktemp -d --tmpdir '.$prefix.'XXXXXXXXXX';
        $process = new Process($cmd);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new \RuntimeException(
                sprintf(
                    'Unable to create temporary directory: %s',
                    $process->getErrorOutput()
                )
            );
        }

        return trim($process->getOutput());
    }
}
