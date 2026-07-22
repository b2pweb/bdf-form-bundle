<?php

namespace Bdf\Form\Bundle\Tests\Forms;

use Bdf\Form\Aggregate\FormBuilderInterface;
use Bdf\Form\Custom\CustomForm;

/**
 * Custom form using a constraint ({@see NoForbiddenValue}) whose validator has a service dependency.
 */
class CustomFormWithDependentConstraint extends CustomForm
{
    protected function configure(FormBuilderInterface $builder): void
    {
        $builder->string('value')->satisfy(new NoForbiddenValue());
    }
}
