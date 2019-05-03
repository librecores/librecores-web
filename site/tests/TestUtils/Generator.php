<?php

namespace TestUtils;

class Generator
{
    /**
     * @param int $minLength
     * @param int $maxLength
     *
     * @return string
     *
     * @throws \Exception
     */
    public static function randomString(int $minLength, int $maxLength)
    {

        $length = random_int($minLength, $maxLength);

        $characters = '0123456789'.
            'abcdefghijklmnopqrstuvwxyz'.
            'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $string = $characters[random_int(10, strlen($characters) - 1)];

        for ($i = 1; $i < $length; $i++) {
            $string .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $string;
    }
}
