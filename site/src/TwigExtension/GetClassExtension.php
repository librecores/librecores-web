<?php


namespace App\TwigExtension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class GetClassExtension extends AbstractExtension
{

    function getFunctions()
    {

        $filter = new TwigFunction('get_class', function ($object) {
            return get_class($object);
        });

        return [$filter];
    }
}
