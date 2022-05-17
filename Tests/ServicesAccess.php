<?php

namespace Bdf\Form\Bundle\Tests;

use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ServicesAccess
{
    /**
     * @var CsrfTokenManager
     */
    public $tokenManager;

    /**
     * @var ValidatorInterface
     */
    public $validator;

    /**
     * @param CsrfTokenManager $tokenManager
     * @param ValidatorInterface $validator
     */
    public function __construct(CsrfTokenManager $tokenManager, ValidatorInterface $validator)
    {
        $this->tokenManager = $tokenManager;
        $this->validator = $validator;
    }
}
