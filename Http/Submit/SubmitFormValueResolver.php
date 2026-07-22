<?php

namespace Bdf\Form\Bundle\Http\Submit;

use Bdf\Form\Aggregate\FormInterface;
use Bdf\Form\Bundle\Http\InvalidFormException;
use Bdf\Form\Bundle\Http\PayloadSource;
use Bdf\Form\ElementInterface;
use Bdf\Form\Registry\RegistryInterface;
use Bdf\Form\Struct\StructForm;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

final class SubmitFormValueResolver implements ValueResolverInterface, EventSubscriberInterface
{
    /**
     * Placeholder value for FormInterface parameter without associated SubmitForm attribute.
     * The submitted form will be passed to arguments which is marked with this placeholder.
     *
     * @var object|null
     */
    private static $formArgumentPlaceholder;

    public function __construct(
        private readonly RegistryInterface $registry,
        private readonly ?TranslatorInterface $translator = null,
    ) {
        self::$formArgumentPlaceholder ??= new \stdClass();
    }

    #[\Override]
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $attribute = $argument->getAttributesOfType(SubmitForm::class)[0] ?? null;

        if (null === $attribute) {
            if (\is_a($argument->getType(), FormInterface::class, true)) {
                return [self::$formArgumentPlaceholder];
            }

            return [];
        }

        $type = $argument->getType();

        $attribute->value ??= $type && !\is_subclass_of($type, ElementInterface::class);

        if (null === $attribute->form) {
            if (true === $attribute->value && !\class_exists(StructForm::class)) {
                throw new \LogicException('The form class must be defined when the value is requested');
            }

            if (null === $type) {
                throw new \LogicException('The form class must be defined as controller parameter type, or in the SubmitForm attribute');
            }

            $attribute->form = $type;
        }

        $attribute->metadata = $argument;

        return [$attribute];
    }

    public function onKernelControllerArguments(ControllerArgumentsEvent $event): void
    {
        $arguments = $event->getArguments();
        $hasChanged = false;
        $form = null;

        foreach ($arguments as $i => $argument) {
            if (!$argument instanceof SubmitForm) {
                continue;
            }

            $payload = $this->extractPayload($event->getRequest(), $argument->source);
            $form = \is_subclass_of($argument->form, ElementInterface::class)
                ? $this->registry->elementBuilder($argument->form)->buildElement()
                : $this->registry->elementBuilder(StructForm::class)->class($argument->form)->buildElement()
            ;
            $form->submit($payload);
            $valid = $form->valid();

            if ($argument->validate && !$valid) {
                throw new InvalidFormException($form->error(), $this->translator ? $this->translator->trans($argument->validateMessage) : $argument->validateMessage);
            }

            if ($argument->value) {
                try {
                    $arguments[$i] = $valid ? $form->value() : null;
                } catch (\Throwable) {
                    $arguments[$i] = null;
                }
            } else {
                $arguments[$i] = $form;
            }

            $hasChanged = true;
        }

        if (null !== $form) {
            foreach ($arguments as $i => $argument) {
                if ($argument === self::$formArgumentPlaceholder) {
                    $arguments[$i] = $form;
                }
            }
        }

        if ($hasChanged) {
            $event->setArguments($arguments);
        }
    }

    private function extractPayload(Request $request, PayloadSource|array $sources): array
    {
        if (!\is_array($sources)) {
            return $sources->extract($request);
        }

        $payload = [];

        foreach ($sources as $source) {
            $payload += $source->extract($request);
        }

        return $payload;
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER_ARGUMENTS => 'onKernelControllerArguments',
        ];
    }
}
