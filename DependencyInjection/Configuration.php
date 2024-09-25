<?php

namespace Bdf\Form\Bundle\DependencyInjection;

use Bdf\Form\Attribute\AttributeForm;
use Bdf\Form\Attribute\Processor\AttributesProcessorInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration for bdf form bundle.
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('form');
        $node = $treeBuilder->getRootNode();

        if (class_exists(AttributeForm::class)) {
            $node->children()->append($this->getAttributesNode())->end();
        }

        return $treeBuilder;
    }

    /**
     * Configuration for attributes processor.
     *
     * This configuration is enabled when "b2pweb/bdf-form-attribute" package is included
     *
     * @see AttributesProcessorInterface
     */
    private function getAttributesNode(): NodeDefinition
    {
        $root = (new TreeBuilder('attributes'))->getRootNode();

        $root
            ->addDefaultsIfNotSet()
            ->children()
            ->booleanNode('compile')->defaultTrue()->end()
            ->scalarNode('configuratorClassPrefix')->defaultValue('GeneratedConfigurator\\')
                ->validate()
                    ->ifTrue(function ($value) {
                        return null !== $value && '' !== $value && !preg_match('#^[a-z][a-z\\\\]*$#i', $value);
                    })
                    ->thenInvalid('Invalid class name prefix')
                ->end()->end()
            ->scalarNode('configuratorClassSuffix')->defaultValue('Configurator')
                ->validate()
                    ->ifTrue(function ($value) {
                        return null !== $value && '' !== $value && !preg_match('#^[a-z\\\\]*[a-z]$#i', $value);
                    })
                    ->thenInvalid('Invalid class name suffix')
                ->end()->end()
            ->scalarNode('configuratorBasePath')->defaultValue('%kernel.build_dir%/form')
        ;

        return $root;
    }
}
