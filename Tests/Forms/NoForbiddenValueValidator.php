<?php

namespace Bdf\Form\Bundle\Tests\Forms;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator for {@see NoForbiddenValue} which depends on the {@see A} service (dependency injection).
 *
 * The forbidden value is read from the injected service, so the validator can only work when it has been
 * instantiated from the container.
 */
class NoForbiddenValueValidator extends ConstraintValidator
{
    /**
     * @var A
     */
    private $a;

    /**
     * The dependency injected on the last validate() call. Used by tests to assert DI actually happened.
     *
     * @var A|null
     */
    public static $dependency;

    public function __construct(A $a)
    {
        $this->a = $a;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof NoForbiddenValue) {
            return;
        }

        self::$dependency = $this->a;

        if ($value === $this->a->foo) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
