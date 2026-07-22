<?php

use Bdf\Form\Bundle\Tests\Forms\MyCustomForm;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class TestKernel extends Symfony\Component\HttpKernel\Kernel
{
    use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;

    private $configs = [];

    public function __construct($configs = [])
    {
        parent::__construct('dev', true);

        $this->configs = $configs;
    }

    public function registerBundles(): iterable
    {
        return [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Bdf\Form\Bundle\FormBundle(),
        ];
    }

    protected function configureRoutes(RoutingConfigurator $routes)
    {
        $routes->add('form_only', '/form-only')
            ->controller('kernel::formOnly')
            ->methods(['POST'])
        ;
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/conf.yaml');

        foreach ($this->configs as $config) {
            $loader->load(__DIR__.'/'.$config);
        }
    }

    public function formOnly(MyCustomForm $form, Request $request): JsonResponse
    {
        $form->submit($request->request->all());

        return new JsonResponse([
            'value' => $form->value(),
            'errors' => $form->error()->toArray(),
        ]);
    }
}
