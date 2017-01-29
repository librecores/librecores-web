<?php
namespace Librecores\ProjectRepoBundle\Util;

use Symfony\Component\Process\Process;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * Convert text written in a markup language to safe HTML
 *
 * This class supports all markup also supported by GitHub, as it uses the same
 * processing tool "github-markup". See https://github.com/github/markup for
 * details. The output is sanitized by HTML Purifier to make it safe for
 * display as embedded content.
 *
 * The following processing pipeline is applied to all input:
 *
 * - Convert the input data to HTML using github-markup
 * - Sanitize HTML to strip all possibly malicious content using HTML Purifier.
 *
 * Note that the conversion process is not tuned for speed, but for safety and
 * a support of a wide range of input formats.
 */
class MarkupToHtmlConverter
{
    /**
     * Maximum time in seconds the github-markup tool may take to convert
     * the input document. After this time, the processing is aborted.
     */
    const GITHUB_MARKUP_PROCESS_TIMEOUT = 3; // seconds

    private $logger;
    private $htmlPurifier;

    public function __construct(LoggerInterface $logger,
                                \HTMLPurifier $htmlPurifier)
    {
        $this->logger = $logger;
        $this->htmlPurifier = $htmlPurifier;
    }

    /**
     * Convert content in a markup language to sanitized HTML
     *
     * @param string $markupInput
     * @return boolean|string
     */
    public function convert($markupInput)
    {
        $unsafeHtml = $this->markupToUnsafeHtml($markupInput);
        $sanitizedHtml = $this->sanitizeHtml($unsafeHtml);
        return $sanitizedHtml;
    }

    /**
     * Convert a file with markup to sanitized HTML
     *
     * @param string$markupInputFile
     * @return boolean|string
     */
    public function convertFile($markupInputFile)
    {
        $this->logger->debug("Converting $markupInputFile to HTML ...");

        $unsafeHtml = $this->markupFileToUnsafeHtml($markupInputFile);
        $sanitizedHtml = $this->sanitizeHtml($unsafeHtml);

        $this->logger->debug("Conversion done.");
        return $sanitizedHtml;
    }

    /**
     * Convert a string of markup text to HTML
     *
     * @param string $markupInput
     * @return string HTML
     * @throws \RuntimeException processing failed (see message for details)
     * @see markupFileToHtml()
     */
    protected function markupToUnsafeHtml($markupInput)
    {
        // write $markupInput into temporary file
        $tmpFilename = tempnam(sys_get_temp_dir(), 'lc-markup-conv-');
        if ($tmpFilename === false) {
            throw new \RuntimeException('Unable to create temporary file in '.sys_get_temp_dir());
        }
        try {
            $rv = file_put_contents($tmpFilename, $markupInput);
            if ($rv === false) {
                throw new \RuntimeException("Unable to write to file $tmpFilename");
            }

            $unsafeHtml = $this->markupFileToHtml($tmpFilename);
        } finally {
            unlink($tmpFilename);
        }

        return $unsafeHtml;
    }

    /**
     * Convert a file to HTML
     *
     * @param string $markupInputFile
     * @return string string of HTML data, UTF-8 encoded
     * @throws \RuntimeException processing failed (see message for details)
     */
    protected function markupFileToUnsafeHtml($markupInputFile)
    {
        $fileExtension = pathinfo($markupInputFile, PATHINFO_EXTENSION);
        if (in_array($fileExtension, [ '.txt', ''])) {
            return $this->markupFileToUnsafeHtmlPlaintext($markupInputFile);
        } else {
            return $this->markupFileToUnsafeHtmlWithGhMarkup($markupInputFile);
        }
    }

    /**
     * Convert plaintext content to HTML
     *
     * @param string $markupInputFile
     * @return string string of HTML data, UTF-8 encoded
     */
    private function markupFileToUnsafeHtmlPlaintext($markupInputFile)
    {
        $text = file_get_contents($markupInputFile);
        return '<pre>'.htmlspecialchars($text, ENT_QUOTES, 'UTF-8').'</pre>';
    }

    /**
     * Convert a file containing markup content to HTML using github-markup
     *
     * @param string $markupInputFile
     * @return string string of HTML data, UTF-8 encoded
     * @throws ProcessFailedException processing failed (see message for details)
     */
    private function markupFileToUnsafeHtmlWithGhMarkup($markupInputFile)
    {
        // convert into HTML using the github-markup tool
        $cmd = 'github-markup '.escapeshellarg($markupInputFile);
        $this->logger->debug("Running command: $cmd");
        $process = new Process($cmd);
        $process->setTimeout(self::GITHUB_MARKUP_PROCESS_TIMEOUT);
        $process->mustRun();
        $output = $process->getOutput();

        // The github-markdown tool sets a 0 (success) return code even if
        // it's unable to read the input file.
        if (preg_match('/^usage: .+\/github-markup FILE$/', $output)) {
            throw new ProcessFailedException($process);
        }

        return $output;
    }

    protected function sanitizeHtml($htmlInput)
    {
        return $this->htmlPurifier->purify($htmlInput);
    }
}
