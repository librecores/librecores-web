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
     * @param string $basedir
     * @param string[] $basenames
     * @return string|boolean the name of the file including the base directory
     *                        if a match was found, or false if no match was
     *                        found
     */
    public static function findFile($basedir, $basenames, $extensions)
    {
        foreach ($basenames as $basename) {
            foreach ($extensions as $ext) {
                $filename = $basedir.'/'.$basename.$ext;
                if (is_file($filename)) {
                    return $filename;
                }
            }
        }

        return false;
    }

    /**
     * Recursive delete of a directory (rm -r)
     *
     * @param $dir string directory to delete recursively
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
     * @throws \RuntimeException
     * @see recursiveRmdir()
     */
    public static function createTemporaryDirectory($prefix)
    {
        $cmd = 'mktemp -d --tmpdir '.$prefix.'XXXXXXXXXX';
        $process = new Process($cmd);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new \RuntimeException('Unable to create temporary '.
                'directory: '.$process->getErrorOutput());
        }
        return trim($process->getOutput());
    }
}
