<?php

namespace Librecores\ProjectRepoBundle\Doctrine\Hydrators;

use Doctrine\ORM\Internal\Hydration\AbstractHydrator;
use PDO;

/**
 * Hydrates database results into a single column
 *
 * Uses the PDO::FETCH_COLUMN mode to fetch results. By default it
 * fetches the 1st column, set hint 'column' in the query to fetch
 * a different column.
 *
 * @author Amitosh Swain Mahapatra <amitosh.swain@gmail.com>
 *
 * @see PDO::FETCH_GROUP
 */
class ColumnHydrator extends AbstractHydrator
{

    /**
     * {@inheritdoc}
     */
    protected function hydrateAllData()
    {
        $column = 0;
        if(array_key_exists('column', $this->_hints)) {
            $column = $this->_hints['column'];
        }

        if (!is_integer($column)) {
            throw new \InvalidArgumentException("column must be an integer");
        }

        return $this->_stmt->fetchAll(PDO::FETCH_COLUMN, $column);
    }
}