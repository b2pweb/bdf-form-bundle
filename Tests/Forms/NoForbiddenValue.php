<?php

namespace Bdf\Form\Bundle\Tests\Forms;

use Symfony\Component\Validator\Constraint;

/**
 * Custom constraint whose validator ({@see NoForbiddenValueValidator}) requires a service dependency.
 *
 * Declared as a PHP attribute so it can be used on a DTO property (StructForm).
 */
#[\Attribute]
class NoForbiddenValue extends Constraint
{
    public $message = 'This value is forbidden.';
}
