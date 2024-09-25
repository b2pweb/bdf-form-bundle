<?php

namespace Bdf\Form\Bundle\Tests\FormsAttributes;

use Bdf\Form\Attribute\AttributeForm;
use Bdf\Form\Attribute\Element\CallbackTransformer;
use Bdf\Form\Attribute\Processor\AttributesProcessorInterface;
use Bdf\Form\Leaf\StringElement;
use Bdf\Form\PropertyAccess\Setter;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class WithAnonymousFormClass
{
    public $form;

    public function __construct(
        private AttributesProcessorInterface $processor,
    ) {
    }

    public function process(array $value): array
    {
        $this->form = new class(null, $this->processor) extends AttributeForm {
            #[NotBlank, Length(min: 3), CallbackTransformer(fromHttp: 'toUpper'), Setter]
            public StringElement $foo;

            public function toUpper(string $value): string
            {
                return strtoupper($value);
            }
        };

        if (!$this->form->submit($value)->valid()) {
            throw new \InvalidArgumentException((string) $this->form->error());
        }

        return $this->form->value();
    }
}
