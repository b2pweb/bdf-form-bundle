<?php

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\RouteCollectionBuilder;

class TestKernel extends \Symfony\Component\HttpKernel\Kernel
{
    use \Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;

    private $configs = [];

    public function __construct($configs = [])
    {
        parent::__construct('dev', true);

        $this->configs = $configs;
    }

    public function registerBundles(): iterable
    {
        return [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Bdf\Form\Bundle\FormBundle(),
        ];
    }

    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/conf.yaml');

        foreach ($this->configs as $config) {
            $loader->load(__DIR__. '/' . $config);
        }

        //$c->import(__DIR__.'/conf.yaml');
    }
}
