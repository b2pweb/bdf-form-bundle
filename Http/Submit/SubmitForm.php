<?php

namespace Bdf\Form\Bundle\Http\Submit;

use Bdf\Form\Aggregate\FormInterface;
use Bdf\Form\Bundle\Http\PayloadSource;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Mark the controller parameter as a form to be submitted.
 *
 * Usage:
 * ```php
 * class MyController
 * {
 *     public function simpleForm(#[SubmitForm] MyForm $form): Response
 *     {
 *         // Form is submitted using values depending on the HTTP method, and validated
 *         // The form class is resolved from the parameter type. You can manually set it using the `form` parameter.
 *         // If the form is invalid, an InvalidFormException will be thrown
 *
 *         assert($form->valid()); // Always true
 *         // ...
 *     }
 *
 *     public function manualValidation(#[SubmitForm(validate: false)] MyForm $form): Response
 *     {
 *         // Work like the previous example, but the form is not validated
 *         // So you have to call $form->validate() manually
 *
 *         if (!$form->valid()) {
 *              // Handle the error
 *         }
 *
 *         // ...
 *     }
 *
 *     public function useValue(#[SubmitForm(form: MyForm::class)] MyValue $value): Response
 *     {
 *         // The form is submitted, validated, and it's value is generated using the FormInterface::value() method
 *     }
 *
 *     public function withCustomSources(#[SubmitForm(source: [PayloadSource::Attributes, PayloadSource::Body])] MyForm $form): Response
 *     {
 *         // You can define the source of the payload using the `source` parameter, instead of relying on the HTTP method
 *         // Multiple sources can be defined, and all values will be merged. The first source takes the priority over following ones,
 *         // So field that are defined in multiple sources will not be overridden.
 *     }
 * }
 * ```
 */
#[\Attribute(\Attribute::TARGET_PARAMETER)]
final class SubmitForm extends ValueResolver
{
    public ArgumentMetadata $metadata;

    public function __construct(
        /**
         * The request payload source.
         *
         * By default, it will be determined based on the HTTP method.
         * If an array is given, all sources will be merged, with the first one taking precedence.
         *
         * @var PayloadSource|PayloadSource[]
         */
        public PayloadSource|array $source = PayloadSource::Auto,

        /**
         * The form class to use.
         * If null, it will be determined based on the argument type.
         *
         * In case of struct form, the DTO/Struct class name will be used instead of the form class.
         *
         * @var class-string|null
         */
        public ?string $form = null,

        /**
         * If true, the form will be validated, and {@see InvalidFormException} will be thrown if the form is invalid.
         */
        public bool $validate = true,

        /**
         * The error message to return if the form is invalid.
         */
        public string $validateMessage = 'The JSON contains invalid data.',

        /**
         * Get the value instead of the form instance.
         *
         * If null, this flag will be resolved based on the parameter type (i.e. if the controller parameter type is `FormInterface`, it will be set to false).
         *
         * Note: if this flag is set to true, the form class must be defined on {@see SubmitForm::$form} parameter.
         *
         * @see FormInterface::value() will be called to get the value.
         */
        public ?bool $value = null,
    ) {
        parent::__construct(SubmitFormValueResolver::class);
    }
}
