<?php

namespace Bdf\Form\Bundle\DependencyInjection;

use Bdf\Form\Attribute\AttributeForm;
use Bdf\Form\Attribute\Processor\AttributesProcessorInterface;
use Bdf\Form\Attribute\Processor\CompileAttributesProcessor;
use Bdf\Form\Attribute\Processor\ReflectionProcessor;
use Bdf\Form\Bundle\Attribute\GeneratedConfiguratorResolver;
use Bdf\Form\Custom\CustomForm;
use Bdf\Form\ElementBuilderInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class FormExtension extends Extension
{
    use PriorityTaggedServiceTrait;

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('form.yaml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        if (class_exists(CompileAttributesProcessor::class)) {
            $loader->load('attribute.yaml');
        }

        $container
            ->registerForAutoconfiguration(ElementBuilderInterface::class)
            ->addTag('form.custom_builder')
            ->setPublic(true)
            ->setShared(false)
        ;

        $container
            ->registerForAutoconfiguration(CustomForm::class)
            ->addTag('form.custom_form')
            ->setPublic(true)
            ->setShared(false)
        ;

        if (class_exists(AttributeForm::class)) {
            $this->configureAttributes($container, $config['attributes']);
        }
    }

    /**
     * Configure attribute forms.
     */
    private function configureAttributes(ContainerBuilder $container, array $config): void
    {
        $container->findDefinition(GeneratedConfiguratorResolver::class)
            ->setArguments([
                $config['configuratorClassPrefix'],
                $config['configuratorClassSuffix'],
                $config['configuratorBasePath'],
            ])
        ;

        if ($config['compile']) {
            $container
                ->registerForAutoconfiguration(AttributeForm::class)
                ->addTag('form.attribute_form')
            ;

            $container->setAlias(AttributesProcessorInterface::class, CompileAttributesProcessor::class);
        } else {
            $container->setAlias(AttributesProcessorInterface::class, ReflectionProcessor::class);
        }
    }
}
