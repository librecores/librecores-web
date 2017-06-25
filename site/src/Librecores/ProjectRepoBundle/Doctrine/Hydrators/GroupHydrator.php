<?php

namespace Librecores\ProjectRepoBundle\Doctrine\Hydrators;

use Doctrine\ORM\Internal\Hydration\AbstractHydrator;
use PDO;

/**
 * Hydrates database results into groups
 *
 * Uses the PDO::FETCH_GROUP mode to fetch results.
 * Example: [ [2017,1,12],[2017,2,14],...[2017,12,10] ... ] will be converted to
 *          [ "2017" => [ "1" => [12], "2" => [14], ... "12" => [10] ] ... ].
 *
 * @see PDO::FETCH_GROUP
 */
class GroupHydrator extends AbstractHydrator
{
    /**
     * {@inheritdoc}
     */
    protected function hydrateAllData()
    {
        $rows = $this->_stmt->fetchAll(PDO::FETCH_NUM);
        return $this->group($rows);
    }

    /**
     * Recursively group a 2D array to form a nested associative array
     *
     * @param $rows
     * @return array|mixed
     */
    private function group($rows)
    {
        if (!is_array($rows)) {
            return $rows;
        } elseif (count($rows) === 1) {
            return $rows[0];
        }

        $result = [];
        foreach ($rows as $row) {
            $result[$row[0]][] = array_slice($row, 1);
        }

        foreach ($result as $key => $item) {
            $result[$key] = $this->group($item);
        }

        return $result;
    }
}