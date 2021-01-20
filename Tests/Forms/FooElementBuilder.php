<?php


namespace Bdf\Form\Bundle\Tests\Forms;

use Bdf\Form\AbstractElementBuilder;
use Bdf\Form\ElementInterface;
use Bdf\Form\Registry\RegistryInterface;
use Bdf\Form\Transformer\TransformerInterface;
use Bdf\Form\Validator\ValueValidatorInterface;

class FooElementBuilder extends AbstractElementBuilder
{
    public $a;

    public function __construct(A $a, RegistryInterface $registry = null)
    {
        parent::__construct($registry);

        $this->a = $a;
    }

    /**
     * @inheritDoc
     */
    protected function createElement(ValueValidatorInterface $validator, TransformerInterface $transformer): ElementInterface
    {
        return new FooElement($validator, $transformer);
    }
}
