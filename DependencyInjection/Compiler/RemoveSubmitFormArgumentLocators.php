<?php

namespace Bdf\Form\Bundle\DependencyInjection\Compiler;

use Bdf\Form\Bundle\Http\Submit\SubmitForm;
use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Remove controller argument service-locators generated for parameters marked with a {@see SubmitForm} attribute.
 *
 * When a controller argument is type-hinted with a form value (e.g. a DTO/struct handled by SubmitFormValueResolver),
 * Symfony's RegisterControllerArgumentLocatorsPass still registers a reference to that type into the controller
 * argument locator, so ServiceValueResolver may inject it from the container. That reference is never used at runtime
 * because SubmitFormValueResolver provides the value first, but it keeps the type alive as a container service.
 *
 * When the type is registered as an autowired but non-instantiable service (typical for a DTO with scalar promoted
 * properties), this has two consequences depending on the parameter nullability:
 *  - non-nullable: the reference uses RUNTIME_EXCEPTION_ON_INVALID_REFERENCE, so the autowiring error is deferred to
 *    runtime (and never triggered) -> the container compiles;
 *  - nullable: the reference uses IGNORE_ON_INVALID_REFERENCE, which DefinitionErrorExceptionPass treats as a
 *    compile-time error -> the container fails to build with "Cannot autowire service ...".
 *
 * This pass drops those references so the behaviour is consistent (and correct) regardless of nullability: the value
 * is always provided by SubmitFormValueResolver, never by the container.
 */
class RemoveSubmitFormArgumentLocators implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('argument_resolver.controller_locator') && !$container->hasAlias('argument_resolver.controller_locator')) {
            return;
        }

        $controllerLocator = $container->findDefinition('argument_resolver.controller_locator');
        $controllers = $controllerLocator->getArgument(0);

        foreach ($controllers as $controller => $argument) {
            $names = $this->submitFormArgumentNames($container, (string) $controller);

            if (!$names) {
                continue;
            }

            // $argument is a ServiceClosureArgument wrapping a Reference to the per-method locator
            $reference = $argument instanceof ServiceClosureArgument ? $argument->getValues()[0] : $argument;
            $argumentLocator = $container->getDefinition((string) $reference);

            // per-consumer locators are derived from a shared prototype through a "withContext" cloning factory
            if ($argumentLocator->getFactory()) {
                $argumentLocator = $container->getDefinition((string) $argumentLocator->getFactory()[0]);
            }

            $services = $argumentLocator->getArgument(0);
            $changed = false;

            foreach ($names as $name) {
                if (isset($services[$name])) {
                    unset($services[$name]);
                    $changed = true;
                }
            }

            if ($changed) {
                $argumentLocator->replaceArgument(0, $services);
            }
        }
    }

    /**
     * Get the parameter names of the given controller that are marked with a SubmitForm attribute.
     *
     * @param string $controller The controller identifier, formatted as "serviceId::method"
     *
     * @return list<string>
     */
    private function submitFormArgumentNames(ContainerBuilder $container, string $controller): array
    {
        if (!str_contains($controller, '::')) {
            return [];
        }

        [$serviceId, $method] = explode('::', $controller, 2);

        if (!$container->hasDefinition($serviceId) && !$container->hasAlias($serviceId)) {
            return [];
        }

        $class = $container->findDefinition($serviceId)->getClass();

        if (!$class || !method_exists($class, $method)) {
            return [];
        }

        try {
            $parameters = (new \ReflectionMethod($class, $method))->getParameters();
        } catch (\ReflectionException) {
            return [];
        }

        $names = [];

        foreach ($parameters as $parameter) {
            if ($parameter->getAttributes(SubmitForm::class, \ReflectionAttribute::IS_INSTANCEOF)) {
                $names[] = $parameter->name;
            }
        }

        return $names;
    }
}
