<?php


namespace Librecores\ProjectRepoBundle\Entity;

/**
 * Statistics about a language in a repository
 *
 * @author Amitosh Swain Mahapatra <amitosh.swain@gmail.com>
 */
class LanguageStat
{
    /**
     * @var string
     */
    private $language;

    /**
     * @var int
     */
    private $fileCount;

    /**
     * @var int
     */
    private $linesOfCode;

    /**
     * @var int
     */
    private $commentLineCount;

    /**
     * @var int
     */
    private $blankLineCount;

    /**
     * @param string $language
     * @return LanguageStat
     */
    public function setLanguage(string $language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * @param int $fileCount
     * @return LanguageStat
     */
    public function setFileCount(int $fileCount)
    {
        $this->fileCount = $fileCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getFileCount(): int
    {
        return $this->fileCount;
    }

    /**
     * @param int $linesOfCode
     * @return LanguageStat
     */
    public function setLinesOfCode(int $linesOfCode)
    {
        $this->linesOfCode = $linesOfCode;

        return $this;
    }

    /**
     * @return int
     */
    public function getLinesOfCode(): int
    {
        return $this->linesOfCode;
    }

    /**
     * @param int $commentLineCount
     * @return LanguageStat
     */
    public function setCommentLineCount(int $commentLineCount)
    {
        $this->commentLineCount = $commentLineCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getCommentLineCount(): int
    {
        return $this->commentLineCount;
    }

    /**
     * @param int $blankLineCount
     * @return LanguageStat
     */
    public function setBlankLineCount(int $blankLineCount)
    {
        $this->blankLineCount = $blankLineCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getBlankLineCount(): int
    {
        return $this->blankLineCount;
    }
}