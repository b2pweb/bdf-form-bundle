<?php

namespace Bdf\Form\Bundle\Http;

use Bdf\Form\Error\FormError;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class InvalidFormException extends BadRequestHttpException
{
    public function __construct(
        public readonly FormError $error,
        string $message = '',
    ) {
        parent::__construct($message ?: $this->error->global() ?? 'Invalid form data');
    }
}
