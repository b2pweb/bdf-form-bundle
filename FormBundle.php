<?php

namespace Bdf\Form\Bundle;

use Bdf\Form\Attribute\AttributeForm;
use Bdf\Form\Bundle\DependencyInjection\Compiler\CompileAttributeForms;
use Bdf\Form\Bundle\DependencyInjection\Compiler\RegisterCustomBuilders;
use Bdf\Form\Bundle\DependencyInjection\Compiler\RegisterCustomForms;
use Bdf\Form\Bundle\DependencyInjection\Compiler\RemoveSubmitFormArgumentLocators;
use Bdf\Form\Bundle\DependencyInjection\Compiler\UseCsrfTokenManager;
use Bdf\Form\Bundle\Http\Submit\SubmitForm;
use Bdf\Form\Struct\StructForm;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use function class_exists;

/**
 * Bundle for register the BDF Form library into the Symfony container.
 */
class FormBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new RegisterCustomBuilders());
        $container->addCompilerPass(new RegisterCustomForms());
        $container->addCompilerPass(new UseCsrfTokenManager());

        // Fix "Cannot autowire service" when a DTO argument (using StructForm) is present on a controller.
        // Must run after Symfony's RegisterControllerArgumentLocatorsPass (beforeOptimization, priority 0),
        // hence the negative priority, so the controller argument locators are already built.
        if (class_exists(SubmitForm::class)) {
            $container->addCompilerPass(new RemoveSubmitFormArgumentLocators(), PassConfig::TYPE_BEFORE_OPTIMIZATION, -100);
        }

        if (class_exists(AttributeForm::class)) {
            $container->addCompilerPass(new CompileAttributeForms());
        }
    }
}
