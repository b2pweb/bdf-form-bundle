<?php

namespace Bdf\Form\Bundle\Tests\FormsAttributes;

use Bdf\Form\Bundle\Tests\Forms\NoForbiddenValue;

/**
 * DTO used as a StructForm, carrying a custom constraint ({@see NoForbiddenValue}) as attribute.
 * That constraint's validator has a service dependency, so it must be resolved from the container.
 */
class StructWithDependentConstraint
{
    public function __construct(
        #[NoForbiddenValue]
        public string $value,
    ) {
    }
}
