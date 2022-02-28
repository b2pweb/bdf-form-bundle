<?php

namespace Bdf\Form\Bundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Register custom forms into container, using tag "form.custom_form"
 */
class RegisterCustomForms implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($container->findTaggedServiceIds('form.custom_form') as $id => $tags) {
            $container->findDefinition($id)
                ->setPublic(true)
                ->setShared(false)
            ;
        }
    }
}
