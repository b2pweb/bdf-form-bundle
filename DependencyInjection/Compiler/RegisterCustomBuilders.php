<?php

namespace Bdf\Form\Bundle\DependencyInjection\Compiler;

use Bdf\Form\Bundle\Registry\SymfonyRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Register user defined element builders, using tag "form.custom_builder"
 */
class RegisterCustomBuilders implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $registry = $container->findDefinition(SymfonyRegistry::class);

        foreach ($container->findTaggedServiceIds('form.custom_builder') as $id => $tags) {
            $builder = $container->findDefinition($id);
            $builder
                ->setPublic(true)
                ->setShared(false)
            ;

            $registry->addMethodCall('registerCustomElementBuilder', [$id, $builder->getClass()]);
        }
    }
}
