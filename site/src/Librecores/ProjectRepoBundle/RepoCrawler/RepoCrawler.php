<?php
namespace Librecores\ProjectRepoBundle\RepoCrawler;

use Psr\Log\LoggerInterface;
use Librecores\ProjectRepoBundle\Entity\SourceRepo;
use Librecores\ProjectRepoBundle\Util\MarkupToHtmlConverter;


/**
 * Repository crawler base class
 *
 * Get contents from a source code repository.
 */
abstract class RepoCrawler
{
    /**
     * @var SourceRepo
     */
    protected $repo;

    /**
     * @var MarkupToHtmlConverter
     */
    protected $markupConverter;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var OutputParserInterface
     */
    protected $outputParser;

    /**
     * RepoCrawler constructor.
     * @param SourceRepo $repo
     * @param MarkupToHtmlConverter $markupConverter
     * @param LoggerInterface $logger
     * @param OutputParserInterface $outputParser
     */
    public function __construct(SourceRepo $repo,
        MarkupToHtmlConverter $markupConverter, LoggerInterface $logger, OutputParserInterface $outputParser)
    {
        $this->repo = $repo;
        $this->markupConverter = $markupConverter;
        $this->logger = $logger;
        $this->outputParser = $outputParser;

        if (!$this->isValidRepoType()) {
            throw new \RuntimeException("Repository type is not supported by this crawler.");
        }
    }

    /**
     * Is the source repository in $repo processable by this crawler?
     *
     * @return boolean
     */
    abstract protected function isValidRepoType(): bool;

    /**
     * Get the license text of the repository as safe HTML
     *
     * Usually this license text is taken from the LICENSE or COPYING files.
     *
     * "Safe" HTML is stripped from all possibly malicious content, such as
     * script tags, etc.
     *
     * @return string|null the license text, or null if none was found
     */
    abstract public function getLicenseTextSafeHtml(): ?string;

    /**
     * Get the description of the repository as safe HTML
     *
     * Usually this is the content of the README file.
     *
     * "Safe" HTML is stripped from all possibly malicious content, such as
     * script tags, etc.
     *
     * @return string|null the repository description, or null if none was found
     */
    abstract public function getDescriptionSafeHtml(): ?string;

    /**
     * Update the project associated with the crawled repository with
     * information extracted from the repo
     *
     * @return bool operation successful?
     */
    public function updateProject()
    {
        $project = $this->repo->getProject();
        if ($project === null) {
            $this->logger->debug('No project associated with source '.
                'repository '.$this->repo->getId());
            return false;
        }

        if ($project->getDescriptionTextAutoUpdate()) {
            $project->setDescriptionText($this->getDescriptionSafeHtml());
        }
        if ($project->getLicenseTextAutoUpdate()) {
            $project->setLicenseText($this->getLicenseTextSafeHtml());
        }
        return true;
    }

    /**
     * Update the source repository entity with information obtained through
     * the crawler
     */
    public function updateSourceRepo()
    {
        // the default implementation is empty
    }

    /**
     * Get all commits in the repository since a specified commit ID or all if
     * not specified
     *
     * Implementations supporting extraction of commits are required to return an
     * array of `SourceCommit` objects or an empty array if otherwise. The default
     * behavior is to return an empty array.
     *
     * @param string|null $sinceId ID of commit after which the commits are to be
     *                              returned
     * @return array all commits in the repository
     */
    public function getCommits(string $sinceId = null) : array {
        return [];
    }
}
