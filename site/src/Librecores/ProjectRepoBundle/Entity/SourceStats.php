<?php

namespace Librecores\ProjectRepoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

/**
 * Statistics about the source code of a repository
 *
 * @author Amitosh Swain Mahapatra <amitosh.swain@gmail.com>
 *
 * @ORM\Embeddable()
 */
class SourceStats
{
    /**
     * Development by a small team with intermediate experience
     * working on a project with less stringent requirements. Usually for small
     * software projects.
     */
    const DEVELOPMENT_TYPE_ORGANIC = 1;

    /**
     * Development by a sizable number of developers with mixed
     * experience on various parts of the system. Requirements may be range from
     * well-defined to rigid.
     */
    const DEVELOPMENT_TYPE_SEMI_DETACHED = 2;

    /**
     * Development on projects with stringent requirements such as
     * embedded projects.
     */
    const DEVELOPMENT_TYPE_EMBEDDED = 3;

    /**
     * Is source stats available
     *
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $available = false;

    /**
     * Total files in repository
     *
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $totalFiles = 0;

    /**
     * Total lines of code in the repository
     *
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $totalLinesOfCode = 0;

    /**
     * Total comment lines in the repository
     *
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $totalLinesOfComments = 0;

    /**
     * Total blank lines in the repository
     *
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $totalBlankLines = 0;

    /**
     * Stats about all other languages used by this repository
     *
     * @var LanguageStat[]
     *
     * @ORM\Column(type="array")
     */
    private $languageStats;

    /**
     * SourceStats constructor.
     */
    public function __construct()
    {
        $this->languageStats = [];
    }

    /**
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->available;
    }

    /**
     * @param bool $available
     *
     * @return SourceStats
     */
    public function setAvailable(bool $available)
    {
        $this->available = $available;

        return $this;
    }

    /**
     * @return int
     */
    public function getTotalFiles(): int
    {
        return $this->totalFiles;
    }

    /**
     * @param int $totalFiles
     *
     * @return SourceStats
     */
    public function setTotalFiles(int $totalFiles)
    {
        $this->totalFiles = $totalFiles;

        return $this;
    }

    /**
     * @return int
     */
    public function getTotalLinesOfCode(): int
    {
        return $this->totalLinesOfCode;
    }

    /**
     * @param int $totalLinesOfCode
     *
     * @return SourceStats
     */
    public function setTotalLinesOfCode(int $totalLinesOfCode)
    {
        $this->totalLinesOfCode = $totalLinesOfCode;

        return $this;
    }

    /**
     * @return int
     */
    public function getTotalLinesOfComments(): int
    {
        return $this->totalLinesOfComments;
    }

    /**
     * @param int $totalLinesOfComments
     *
     * @return SourceStats
     */
    public function setTotalLinesOfComments(int $totalLinesOfComments)
    {
        $this->totalLinesOfComments = $totalLinesOfComments;

        return $this;
    }

    /**
     * @return int
     */
    public function getTotalBlankLines(): int
    {
        return $this->totalBlankLines;
    }

    /**
     * @param int $totalBlankLines
     *
     * @return SourceStats
     */
    public function setTotalBlankLines(int $totalBlankLines)
    {
        $this->totalBlankLines = $totalBlankLines;

        return $this;
    }

    /**
     * @param LanguageStat $language
     *
     * @return SourceStats
     */
    public function addLanguageStat(LanguageStat $language)
    {
        $this->languageStats[] = $language;

        return $this;
    }

    /**
     * @param LanguageStat $language
     *
     * @return bool
     */
    public function removeLanguageStat(LanguageStat $language)
    {
        if (($key = array_search($language, $this->languageStats)) !== false) {
            unset($this->languageStats[$key]);

            return true;
        }

        return false;
    }

    /**
     * @return LanguageStat[]
     */
    public function getLanguageStats(): array
    {
        return $this->languageStats;
    }

    /**
     * Set languageStats
     *
     * @param LanguageStat[] $languageStats
     *
     * @return SourceStats
     */
    public function setLanguageStats($languageStats)
    {
        $this->languageStats = $languageStats;

        return $this;
    }

    /**
     * Get the total number of lines in the codebase
     *
     * @return int
     */
    public function getTotalLines(): int
    {
        return $this->totalLinesOfCode + $this->totalBlankLines + $this->totalLinesOfComments;
    }

    /**
     * Get comment to code ratio
     *
     * @return float
     */
    public function getCommentToCodeRatio(): float
    {
        if (0 === $this->totalLinesOfComments) {
            $ratio = 0;
        } else {
            $ratio = $this->totalLinesOfCode / $this->totalLinesOfComments;
        }

        return $ratio;
    }

    /**
     * Get the most used language in this repository
     *
     * @return string|null the primary language of this repository or false if
     *                      such information does not exist
     */
    public function getMostUsedLanguage(): ?string
    {
        $language = null;

        // only sort if language entries are present
        if (0 !== count($this->languageStats)) {
            $langStats = $this->languageStats;

            usort(
                $langStats,
                function (LanguageStat $a, LanguageStat $b) {
                    // we compare using file count, not loc
                    $aCount = $a->getFileCount();
                    $bCount = $b->getFileCount();

                    if ($aCount === $bCount) {
                        return 0;
                    }

                    return ($aCount > $bCount) ? -1 : 1;
                }
            );

            $language = $langStats[0]->getLanguage();
        }

        return $language;
    }

    // Estimation of project effort in man-months and development time in

    // months according to Constructive Cost Model (COCOMO)
    // https://en.wikipedia.org/wiki/COCOMO
    // Basic COCOMO is used to calculate the effort and development values.

    // References:

    // Roger S. Pressman, 1997. Software Engineering - A Practitioner's Approach, Fourth Edition.

    /**
     * Get estimated effort in man-months
     *
     * @param int $type Development type of the repository
     *
     * @return float effort in man-months
     */
    public function getCocomoEffort(int $type = self::DEVELOPMENT_TYPE_EMBEDDED): float
    {
        $kLoc = $this->totalLinesOfCode / 1000;

        switch ($type) {
            case self::DEVELOPMENT_TYPE_ORGANIC:
                $a = 2.4;
                $b = 1.05;
                break;
            case self::DEVELOPMENT_TYPE_SEMI_DETACHED:
                $a = 3.0;
                $b = 1.12;
                break;
            case self::DEVELOPMENT_TYPE_EMBEDDED:
                $a = 3.6;
                $b = 1.2;
                break;
            default:
                throw new InvalidArgumentException('Invalid development type');
        }

        return $a * pow($kLoc, $b);
    }


    /**
     * Get estimated duration in man-months
     *
     * @param int $type Development type of the repository
     *
     * @return float effort in man-months
     */
    public function getCocomoDuration(int $type = self::DEVELOPMENT_TYPE_EMBEDDED): float
    {
        $kLoc = $this->totalLinesOfCode / 1000;

        switch ($type) {
            case self::DEVELOPMENT_TYPE_ORGANIC:
                $d = 0.35;
                break;
            case self::DEVELOPMENT_TYPE_SEMI_DETACHED:
                $d = 0.38;
                break;
            case self::DEVELOPMENT_TYPE_EMBEDDED:
                $d = 0.32;
                break;
            default:
                throw new InvalidArgumentException('Invalid development type');
        }

        return 2.5 * pow($kLoc, $d);
    }

    /**
     * Get estimated number of developers
     *
     * @param int $type Development type of the repository
     *
     * @return int estimated number of developers
     */
    public function getCocomoTeamSize(int $type = self::DEVELOPMENT_TYPE_EMBEDDED): int
    {
        return ceil($this->getCocomoEffort($type) / $this->getCocomoDuration($type));
    }

    // TODO: Cost estimation by man-hours * median monthly wages of developers
}
