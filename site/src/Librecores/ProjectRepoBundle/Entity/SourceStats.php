<?php

namespace Librecores\ProjectRepoBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Statistics about the source code of a repository
 *
 * @ORM\Embeddable()
 */
class SourceStats
{
    /**
     * Represents a development by a small team with intermediate experience
     * working on a project with less stringent requirements. Usually for small
     * software projects.
     */
    const DEVELOPMENT_TYPE_ORGANIC = 1;

    /**
     * Represents development by a sizable number of developers with mixed
     * experience on various parts of the system. Requirements may be range from
     * well-defined to rigid.
     */
    const DEVELOPMENT_TYPE_SEMI_DETACHED = 2;

    /**
     * Represents development on projects with stringent requirements such as
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
     * Total number of files in repository
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
     * @var LanguageStat[]
     *
     * @ORM\Column(type="array")
     */
    private $languageStats;

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
     * @return SourceStats
     */
    public function setAvailable(bool $available)
    {
        $this->available = $available;
        return $this;
    }

    /**
     * @param int $totalFiles
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
    public function getTotalFiles(): int
    {
        return $this->totalFiles;
    }

    /**
     * @param int $totalLinesOfCode
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
    public function getTotalLinesOfCode(): int
    {
        return $this->totalLinesOfCode;
    }

    /**
     * @param int $totalLinesOfComments
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
    public function getTotalLinesOfComments(): int
    {
        return $this->totalLinesOfComments;
    }

    /**
     * @param int $totalBlankLines
     * @return SourceStats
     */
    public function setTotalBlankLines(int $totalBlankLines)
    {
        $this->totalBlankLines = $totalBlankLines;

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
     * @param $language
     * @return SourceStats
     */
    public function addLanguageStat(LanguageStat $language)
    {
        $this->languageStats[] = $language;

        return $this;
    }

    /**
     * @param $language
     * @return bool
     */
    public function removeLanguageStat(LanguageStat $language)
    {
        if(($key = array_search($language, $this->languageStats)) !== false) {
            unset($this->languageStats[$key]);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return LanguageStat[]
     */
    public function getLanguageStats(): array
    {
        return $this->languageStats;
    }

    /**
     * Get the total number of lines in the codebase
     * @return int
     */
    public function getTotalLines(): int
    {
        return $this->totalLinesOfCode + $this->totalBlankLines + $this->totalLinesOfComments;
    }

    /**
     * Get comment to code ratio
     * @return float
     */
    public function getCommentToCodeRatio(): float
    {
        return $this->totalLinesOfCode / $this->totalLinesOfComments;
    }
    /**
     * The most used language in this repository
     * @return string|false
     */
    public function getMajorLanguage()
    {
        dump($this->languageStats);

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

            return $langStats[0]->getLanguage();
        } else {
            return false;
        }
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
     * @return float effort in man-months
     */
    public function getCocomoEffort(int $type = self::DEVELOPMENT_TYPE_EMBEDDED
    ): float {
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
                throw new \InvalidArgumentException('Invalid development type');
        }

        return $a * pow($kLoc, $b);
    }


    /**
     * Get estimated duration in man-months
     *
     * @param int $type Development type of the repository
     * @return float effort in man-months
     */
    public function getCocomoDuration(
        int $type = self::DEVELOPMENT_TYPE_EMBEDDED
    ): float {
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
                throw new \InvalidArgumentException('Invalid development type');
        }

        return 2.5 * pow($kLoc, $d);
    }

    // TODO: Cost estimation by man-hours * 160 * hourly_wages

    /**
     * Set languageStats
     *
     * @param array $languageStats
     *
     * @return SourceStats
     */
    public function setLanguageStats($languageStats)
    {
        $this->languageStats = $languageStats;

        return $this;
    }
}
