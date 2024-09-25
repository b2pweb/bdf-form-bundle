<?php

namespace Bdf\Form\Bundle\Registry;

use Bdf\Form\Aggregate\Form;
use Bdf\Form\Button\ButtonBuilderInterface;
use Bdf\Form\Child\ChildBuilderInterface;
use Bdf\Form\Custom\CustomForm;
use Bdf\Form\Custom\CustomFormBuilder;
use Bdf\Form\ElementBuilderInterface;
use Bdf\Form\ElementInterface;
use Bdf\Form\Filter\FilterInterface;
use Bdf\Form\Registry\Registry;
use Bdf\Form\Registry\RegistryInterface;
use Bdf\Form\Transformer\TransformerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;

/**
 * Decorate Registry to handle container for custom types.
 */
class SymfonyRegistry implements RegistryInterface
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ElementBuilderInterface[][]
     */
    private $builders = [];

    /**
     * SymfonyRegistry constructor.
     */
    public function __construct(Registry $registry, ContainerInterface $container)
    {
        $this->registry = $registry;
        $this->container = $container;

        $registry->register(CustomForm::class, [$this, 'customFormBuilder']);
    }

    public function filter($filter): FilterInterface
    {
        return $this->registry->filter($filter);
    }

    public function constraint($constraint): Constraint
    {
        return $this->registry->constraint($constraint);
    }

    public function transformer($transformer): TransformerInterface
    {
        return $this->registry->transformer($transformer);
    }

    public function childBuilder(string $element, string $name): ChildBuilderInterface
    {
        return $this->registry->childBuilder($element, $name);
    }

    public function elementBuilder(string $element): ElementBuilderInterface
    {
        if (!empty($this->builders[$element])) {
            return array_pop($this->builders[$element]);
        }

        return $this->registry->elementBuilder($element);
    }

    public function buttonBuilder(string $name): ButtonBuilderInterface
    {
        return $this->registry->buttonBuilder($name);
    }

    public function pushElementBuilder(string $element, ElementBuilderInterface $builder): void
    {
        $this->builders[$element][] = $builder;
    }

    /**
     * Create the form builder instance.
     *
     * @internal
     */
    public function customFormBuilder(RegistryInterface $registry, string $formClass): CustomFormBuilder
    {
        return new CustomFormBuilder(
            function ($builder) use ($formClass) {
                $this->pushElementBuilder(Form::class, $builder);

                return $this->container->get($formClass);
            },
            $registry->elementBuilder(Form::class)
        );
    }

    /**
     * Register the element builder to the related element if possible.
     *
     * @param string $id           The service ID on the container
     * @param string $builderClass The builder class name
     */
    public function registerCustomElementBuilder(string $id, string $builderClass): void
    {
        $elementClass = substr($builderClass, 0, -strlen('Builder'));

        if (!is_subclass_of($elementClass, ElementInterface::class)) {
            return;
        }

        $this->registry->register($elementClass, function () use ($id) {
            return $this->container->get($id);
        });
    }
}
