<?php

namespace Bdf\Form\Bundle\Tests\FormsAttributes;

use Bdf\Form\Attribute\AttributeForm;
use Bdf\Form\Attribute\Child\GetSet;
use Bdf\Form\Attribute\Element\Raw;
use Bdf\Form\Leaf\IntegerElement;
use Bdf\Form\Leaf\StringElement;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class WithAttributes extends AttributeForm
{
    #[NotBlank, GetSet]
    public StringElement $foo;

    #[NotBlank, Positive, Raw, GetSet]
    public IntegerElement $bar;
}
