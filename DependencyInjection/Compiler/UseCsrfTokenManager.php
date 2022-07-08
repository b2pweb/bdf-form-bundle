<?php

namespace Bdf\Form\Bundle\DependencyInjection\Compiler;

use Bdf\Form\Csrf\CsrfElementBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Use the symfony's csrf token manager is available on container.
 */
class UseCsrfTokenManager implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('security.csrf.token_manager')) {
            $container->findDefinition(CsrfElementBuilder::class)
                ->addMethodCall('tokenManager', [new Reference('security.csrf.token_manager')])
            ;
        }
    }
}
