<?php

namespace Bdf\Form\Bundle\Tests\FormsAttributes;

use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Regex;

class StructDto
{
    public function __construct(
        #[Positive]
        public int $id,

        #[Regex(pattern: '/^[a-z-]{2,32}$/i')]
        public string $name,
    ) {
    }
}
