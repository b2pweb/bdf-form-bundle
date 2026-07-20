<?php

namespace Bdf\Form\Bundle\Tests\Http;

use Bdf;
use Bdf\Form\Aggregate\FormInterface;
use Bdf\Form\Bundle\Http\InvalidFormException;
use Bdf\Form\Bundle\Http\PayloadSource;
use Bdf\Form\Bundle\Http\Submit\SubmitForm;
use Bdf\Form\Bundle\Tests\Http\Form\PersonDto;
use Bdf\Form\Bundle\Tests\Http\Form\PersonForm;
use Bdf\Form\Bundle\Tests\Http\Form\PersonStruct;
use Symfony;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class TestKernel extends Symfony\Component\HttpKernel\Kernel
{
    use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;

    public function __construct()
    {
        parent::__construct('dev', true);
    }

    public function registerBundles(): iterable
    {
        return [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Bdf\Form\Bundle\FormBundle(),
        ];
    }

    public function error(\Throwable $exception): Response
    {
        if ($exception instanceof InvalidFormException) {
            return new JsonResponse(
                [
                    'message' => $exception->getMessage(),
                    'fields' => $exception->error->toArray(),
                ],
                $exception->getStatusCode(),
                ['Content-Type' => 'application/json']
            );
        }

        if ($exception instanceof HttpException) {
            return new Response(
                $exception->getMessage(),
                $exception->getStatusCode(),
                $exception->getHeaders()
            );
        }

        return new Response($exception->getMessage(), 500, ['Content-Type' => 'text/plain']);
    }

    protected function configureRoutes(RoutingConfigurator $routes)
    {
        $routes->import(__FILE__, 'attribute');
    }

    protected function configureContainer(ContainerConfigurator $container, LoaderInterface $loader, ContainerBuilder $builder)
    {
        $loader->load(__DIR__.'/conf.yaml');
    }

    #[Route('/value', methods: ['POST'])]
    public function value(#[SubmitForm(form: PersonForm::class)] PersonDto $dto): JsonResponse
    {
        return new JsonResponse($dto);
    }

    #[Route('/struct', methods: ['POST'])]
    public function struct(#[SubmitForm] PersonStruct $struct): JsonResponse
    {
        return new JsonResponse($struct);
    }

    #[Route('/struct2', methods: ['POST'])]
    public function struct2(#[SubmitForm(validate: false)] PersonStruct $struct, FormInterface $form): JsonResponse
    {
        return new JsonResponse([
            'value' => $form->valid() ? $struct : $form->httpValue(),
            'errors' => $form->error()->toArray(),
        ]);
    }

    #[Route('/form', methods: ['POST'])]
    public function form(#[SubmitForm(validate: false)] PersonForm $form): JsonResponse
    {
        return new JsonResponse([
            'value' => $form->valid() ? $form->value() : $form->httpValue(),
            'errors' => $form->error()->toArray(),
        ]);
    }

    #[Route('/person/{id}', methods: ['PUT'])]
    public function person(#[SubmitForm(source: [PayloadSource::Attributes, PayloadSource::Body], form: PersonForm::class)] PersonDto $dto): JsonResponse
    {
        return new JsonResponse($dto);
    }
}
