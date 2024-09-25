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

    public function __construct(A $a)
    {
        $this->a = $a;
    }

    public function validate($value, Constraint $constraint)
    {
        self::$injectedParameter = $this->a;
    }
}
