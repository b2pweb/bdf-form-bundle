<?php

namespace Bdf\Form\Bundle\Tests\Http\Form;

use Bdf\Form\Filter\TrimFilter;
use Symfony\Component\Validator\Constraints\Regex;

class PersonStruct
{
    public function __construct(
        public int $id,

        #[TrimFilter, Regex('/^[a-zA-Z-]{3,25}$/')]
        public string $firstName,

        #[TrimFilter, Regex('/^[a-zA-Z-]{3,25}$/')]
        public string $lastName,
    ) {
    }
}
