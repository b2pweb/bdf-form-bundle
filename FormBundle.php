<?php

namespace Bdf\Form\Bundle;

use Bdf\Form\Attribute\AttributeForm;
use Bdf\Form\Bundle\DependencyInjection\Compiler\CompileAttributeForms;
use Bdf\Form\Bundle\DependencyInjection\Compiler\RegisterCustomBuilders;
use Bdf\Form\Bundle\DependencyInjection\Compiler\RegisterCustomForms;
use Bdf\Form\Bundle\DependencyInjection\Compiler\UseCsrfTokenManager;
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
        $container->addCompilerPass(new RegisterCustomBuilders());
        $container->addCompilerPass(new RegisterCustomForms());
        $container->addCompilerPass(new UseCsrfTokenManager());

        if (class_exists(AttributeForm::class)) {
            $container->addCompilerPass(new CompileAttributeForms());
        }
    }
}
