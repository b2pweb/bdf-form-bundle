<?php

namespace Bdf\Form\Bundle\DependencyInjection;

use Bdf\Form\Attribute\AttributeForm;
use Bdf\Form\Attribute\Processor\AttributesProcessorInterface;
use Bdf\Form\Attribute\Processor\CompileAttributesProcessor;
use Bdf\Form\Attribute\Processor\ReflectionProcessor;
use Bdf\Form\Bundle\Attribute\GeneratedConfiguratorResolver;
use Bdf\Form\Bundle\Registry\SymfonyRegistry;
use Bdf\Form\Custom\CustomForm;
use Bdf\Form\ElementBuilderInterface;
use Bdf\Form\Struct\StructAttributesProcessorFactory;
use Bdf\Form\Struct\StructForm;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class FormExtension extends Extension
{
    use PriorityTaggedServiceTrait;

    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('form.yaml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        if (class_exists(CompileAttributesProcessor::class)) {
            $loader->load('attribute.yaml');
        }

        if (PHP_VERSION_ID >= 80100) {
            $loader->load('http.yaml');
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

        if (class_exists(StructForm::class)) {
            $this->configureStructForm($container, $config['attributes']);
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

    private function configureStructForm(ContainerBuilder $container, array $config): void
    {
        $container->register(StructAttributesProcessorFactory::class);

        if ($config['compile']) {
            $container->register(SymfonyRegistry::STRUCT_FORM_PROCESSOR_SERVICE_ID, AttributesProcessorInterface::class)
                ->setFactory([new Reference(StructAttributesProcessorFactory::class), 'generated'])
                ->setArguments([
                    (new Definition(\Closure::class))
                        ->setFactory([\Closure::class, 'fromCallable'])
                        ->setArgument(0, [new Reference(GeneratedConfiguratorResolver::class), 'resolveClassName']),
                    (new Definition(\Closure::class))
                        ->setFactory([\Closure::class, 'fromCallable'])
                        ->setArgument(0, [new Reference(GeneratedConfiguratorResolver::class), 'resolveFilename']),
                ])
                ->setPublic(true)
            ;
        } else {
            $container->register(SymfonyRegistry::STRUCT_FORM_PROCESSOR_SERVICE_ID, AttributesProcessorInterface::class)
                ->setFactory([new Reference(StructAttributesProcessorFactory::class), 'runtime'])
                ->setPublic(true)
            ;
        }
    }
}
