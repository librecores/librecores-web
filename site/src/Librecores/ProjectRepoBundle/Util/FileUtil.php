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
     * If a case-insensitive find is requested the file names in the filesystem
     * and the passed strings are assumed to be UTF-8 encoded.
     *
     * The search is not recursive, only $basedir is searched.
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
    public static function findFile($basedir, $basenames, $extensions,
                                    $caseSensitive=true)
    {
        $searchFilenames = [];
        foreach ($basenames as $basename) {
            foreach ($extensions as $ext) {
                $searchFilenames[] = $basename.$ext;
            }
        }

        $basedirFilenames = [];
        $basedirFilenamesLc = [];
        $it = new \FilesystemIterator($basedir);
        foreach ($it as $fileinfo) {
            if (!$fileinfo->isFile()) {
                continue;
            }
            $basedirFilenames[] = $fileinfo->getFilename();
            $basedirFilenamesLc[] = mb_strtolower($fileinfo->getFilename(), 'UTF-8');
        }

        $foundFilename = null;
        foreach ($searchFilenames as $searchFilename) {
            $idx = false;
            if ($caseSensitive) {
                $idx = array_search($searchFilename, $basedirFilenames);
            } else {
                $searchFilenameLc = mb_strtolower($searchFilename, 'UTF-8');
                $idx = array_search($searchFilenameLc, $basedirFilenamesLc);
            }
            if ($idx !== false) {
                $foundFilename = $basedirFilenames[$idx];
                break;
            }
        }

        if (!$foundFilename) {
            return false;
        }

        // found file; double-check if the file exists (and if it is a file)
        $fullFilename = $basedir.'/'.$foundFilename;
        if (!is_file($fullFilename)) {
            return false;
        }
        return $fullFilename;
    }

    /**
     * Create temporary directory with an unique name and return its path
     *
     * The directory must be manually deleted if not used any more.
     *
     * @return string path to the temporary directory
     *
     * @throws \RuntimeException
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
