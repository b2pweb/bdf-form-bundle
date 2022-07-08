<?php

namespace Bdf\Form\Bundle\DependencyInjection\Compiler;

use Bdf\Form\Attribute\Processor\CompileAttributesProcessor;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Precompile AttributeForms
 * This compiled is enabled only if "b2pweb/bdf-form-attribute" is included.
 */
class CompileAttributeForms implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        /** @var CompileAttributesProcessor $compiler */
        $compiler = $container->get(CompileAttributesProcessor::class);

        foreach ($container->findTaggedServiceIds('form.attribute_form') as $id => $tags) {
            $formClass = $container->findDefinition($id)->getClass();
            $reflection = new ReflectionClass($formClass);

            $compiler->generate(
                $reflection->getConstructor()->getNumberOfRequiredParameters() > 0
                    ? $reflection->newInstanceWithoutConstructor()
                    : $reflection->newInstance()
            );
        }
    }
}
