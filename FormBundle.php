<?php

namespace Bdf\Form\Bundle;

use Bdf\Form\Bundle\Registry\SymfonyRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Bundle for register the BDF Form library into the Symfony container
 */
class FormBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new class implements CompilerPassInterface {
            public function process(ContainerBuilder $container)
            {
                $registry = $container->findDefinition(SymfonyRegistry::class);

                // Register custom builders
                foreach ($container->findTaggedServiceIds('form.custom_builder') as $id => $tags) {
                    $builder = $container->findDefinition($id);

                    $registry->addMethodCall('registerCustomElementBuilder', [$id, $builder->getClass()]);
                }
            }
        });
    }
}
