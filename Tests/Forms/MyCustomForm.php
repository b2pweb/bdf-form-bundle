<?php

namespace Bdf\Form\Bundle\Tests\Forms;

use Bdf\Form\Aggregate\FormBuilderInterface;
use Bdf\Form\Custom\CustomForm;

/**
 * Class MyCustomForm
 */
class MyCustomForm extends CustomForm
{
    public $a;

    public function __construct(A $a, ?FormBuilderInterface $builder = null)
    {
        parent::__construct($builder);

        $this->a = $a;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(FormBuilderInterface $builder): void
    {
        $builder->string('foo');
    }
}
