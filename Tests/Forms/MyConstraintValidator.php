<?php

namespace Bdf\Form\Bundle\Tests\Forms;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class MyConstraintValidator extends ConstraintValidator
{
    /**
     * @var A
     */
    private $a;

    public static $injectedParameter;

    /**
     * @param A $a
     */
    public function __construct(A $a)
    {
        $this->a = $a;
    }

    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint)
    {
        self::$injectedParameter = $this->a;
    }
}
