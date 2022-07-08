<?php

namespace Bdf\Form\Bundle\Tests\Forms;

use Bdf\Form\Leaf\LeafElement;

/**
 * Class FooElement.
 */
class FooElement extends LeafElement
{
    /**
     * {@inheritDoc}
     */
    protected function toPhp($httpValue)
    {
        return $httpValue;
    }

    /**
     * {@inheritDoc}
     */
    protected function toHttp($phpValue)
    {
        return $phpValue;
    }
}
