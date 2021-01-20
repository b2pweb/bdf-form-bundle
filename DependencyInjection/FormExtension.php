<?php

namespace Bdf\Form\Bundle\DependencyInjection;

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
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('form.yaml');

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
    }
}
