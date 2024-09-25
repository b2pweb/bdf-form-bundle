<?php

namespace Bdf\Form\Bundle\Tests\Forms;

use Bdf\Form\Leaf\LeafElement;

/**
 * Class FooElement.
 */
class FooElement extends LeafElement
{
    protected function toPhp($httpValue)
    {
        return $httpValue;
    }

    protected function toHttp($phpValue)
    {
        return $phpValue;
    }
}
