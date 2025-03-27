<?php

namespace Bdf\Form\Bundle\Tests\Http\Form;

use Bdf\Form\Aggregate\FormBuilderInterface;
use Bdf\Form\Custom\CustomForm;

/**
 * @extends CustomForm<PersonDto>
 */
class PersonForm extends CustomForm
{
    protected function configure(FormBuilderInterface $builder): void
    {
        $builder->generates(PersonDto::class);

        $builder
            ->integer('id')
            ->getset()
            ->required()
        ;

        $builder
            ->string('firstName')
            ->trim()
            ->regex('/^[a-zA-Z-]{3,25}$/')
            ->required()
            ->getset()
        ;

        $builder
            ->string('lastName')
            ->trim()
            ->regex('/^[a-zA-Z-]{3,25}$/')
            ->required()
            ->getset()
        ;
    }
}
