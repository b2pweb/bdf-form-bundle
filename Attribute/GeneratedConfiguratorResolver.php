<?php

namespace Bdf\Form\Bundle\Attribute;

use Bdf\Form\Attribute\AttributeForm;
use Bdf\Form\Attribute\Processor\GenerateConfiguratorStrategy;

/**
 * Resolver for generated configurator
 *
 * @see GenerateConfiguratorStrategy
 */
class GeneratedConfiguratorResolver
{
    /**
     * @var string
     */
    private $prefix;

    /**
     * @var string
     */
    private $suffix;

    /**
     * @var string
     */
    private $basePath;

    public function __construct(string $prefix, string $suffix, string $basePath)
    {
        $this->prefix = $prefix;
        $this->suffix = $suffix;
        $this->basePath = $basePath;
    }

    /**
     * Resolve the class name from the form instance
     * Prefix and suffix will be added on the form class name
     * In case of anonymous class, all forbidden chars will be removed to generate a correct class name
     *
     * @param AttributeForm $form
     *
     * @return string
     */
    public function resolveClassName(AttributeForm $form): string
    {
        $formClass = get_class($form);
        $parts = preg_split('#[^a-z\\\\]#i', $formClass);

        if (count($parts) > 1) {
            $formClass = implode('', array_map('ucfirst', $parts));
        }

        return $this->prefix . $formClass . $this->suffix;
    }

    /**
     * Resolve the filename from the configurator class name
     * Simply replace namespace separator \ to directory separator / of the class name,
     * prefix by the configured path and add ".php" extension
     *
     * @param string $className
     *
     * @return string
     */
    public function resolveFilename(string $className): string
    {
        return $this->basePath . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';
    }
}
