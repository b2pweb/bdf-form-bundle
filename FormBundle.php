<?php

namespace Bdf\Form\Bundle;

use Bdf\Form\Bundle\Registry\SymfonyRegistry;
use Bdf\Form\Csrf\CsrfElementBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
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
                    $builder
                        ->setPublic(true)
                        ->setShared(false)
                    ;

                    $registry->addMethodCall('registerCustomElementBuilder', [$id, $builder->getClass()]);
                }

                // Register custom forms
                foreach ($container->findTaggedServiceIds('form.custom_form') as $id => $tags) {
                    $container->findDefinition($id)
                        ->setPublic(true)
                        ->setShared(false)
                    ;
                }

                if ($container->hasDefinition('security.csrf.token_manager')) {
                    $container->findDefinition(CsrfElementBuilder::class)
                        ->addMethodCall('tokenManager', [new Reference('security.csrf.token_manager')])
                    ;
                }
            }
        });
    }
}
