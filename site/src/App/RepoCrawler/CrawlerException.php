<?php

namespace App\RepoCrawler;

use Exception;
use Librecores\ProjectRepoBundle\Entity\SourceRepo;
use Throwable;

class CrawlerException extends Exception
{
    protected $repository;

    /**
     * CrawlerException constructor.
     *
     * @param SourceRepo     $repository repository on which the error occurred
     * @param string         $message    the error message
     * @param int            $code       error code, if available
     * @param Throwable|null $previous   the error/exception that
     *                                   resulted this exception
     */
    public function __construct(
        SourceRepo $repository,
        $message = "",
        $code = 0,
        Throwable $previous = null
    ) {
        $this->repository = $repository;
        parent::__construct(
            'Error during crawling repository '.$repository->getUrl()
            .' '.$message,
            $code,
            $previous
        );
    }

    /**
     * Get the repository associated with this exception
     *
     * @return SourceRepo
     */
    public function getRepository(): SourceRepo
    {
        return $this->repository;
    }
}
