<?php

namespace Librecores\ProjectRepoBundle\Util;


/**
 * Utility statistics functions
 */
class StatsUtil
{
    /**
     * Calculate average of an array
     *
     * @param $values
     * @return float average of the array
     */
    public static function average($values)
    {
        if (empty($values)) {
            return 0;
        }

        return array_sum($values) / count($values);
    }

    /**
     * Normalize values of an array within [-1,1]
     *
     * @param $values
     * @return array values normalized within [-1,1]
     */
    public static function normalize($values)
    {
        $values = array_map('abs', $values);
        $maxValue = max($values);

        if (0 === $maxValue) {
            return $values;
        }

        return array_map(function ($v) use ($maxValue) {
            return $v / $maxValue;
        }, $values);
    }

    /**
     * Calculate average rate of change
     *
     * @param $values
     * @return float average rate of change of values in the array
     */
    public static function averageRateOfChange($values)
    {
        if (empty($values)) {
            return -1;
        }

        if (count($values) === 1) {
            return 1;
        }

        $trend = 0;

        for ($i = 1; $i < count($values); $i++) {
            if ($values[$i - 1] != 0) {
                $trend += ($values[$i] - $values[$i - 1]) / $values[$i - 1];
            } else {
                $trend += $values[$i] == 0 ? 0 : 1;
            }
        }

        $trend /= count($values) - 1;

        return $trend;
    }
}
