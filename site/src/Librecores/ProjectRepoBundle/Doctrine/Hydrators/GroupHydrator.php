<?php

namespace Librecores\ProjectRepoBundle\Doctrine\Hydrators;

use Doctrine\ORM\Internal\Hydration\AbstractHydrator;
use PDO;

/**
 * Hydrate a row set from the database into an associative array
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
     * @return mixed
     */
    private function group(array $rows)
    {
        // must be a 2d array with more than 2 columns
        if (empty($rows) || !is_array($rows[0]) || count($rows[0]) < 2) {
            return $rows;
        }

        $result = [];
        foreach ($rows as $row) {
            $value             = array_slice($row, 1);
            $result[$row[0]][] = count($value) > 1 ? $value : $value[0];
        }

        foreach ($result as $key => $item) {
            $result[$key] = $this->group($item);
        }

        return $result;
    }
}
