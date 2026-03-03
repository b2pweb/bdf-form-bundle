<?php

namespace Bdf\Form\Bundle\Tests\Http\Submit;

use Bdf\Form\Bundle\Http\InvalidFormException;
use Bdf\Form\Bundle\Http\PayloadSource;
use Bdf\Form\Bundle\Http\Submit\SubmitForm;
use Bdf\Form\Bundle\Http\Submit\SubmitFormValueResolver;
use Bdf\Form\Bundle\Tests\Http\Form\PersonDto;
use Bdf\Form\Bundle\Tests\Http\Form\PersonForm;
use Bdf\Form\Registry\Registry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class SubmitFormValueResolverTest extends TestCase
{
    private SubmitFormValueResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new SubmitFormValueResolver(new Registry());
    }

    public function testResolveNoAttributeSubmitForm()
    {
        $metadata = new ArgumentMetadata('foo', null, false, false, null, false, [new \stdClass()]);
        $this->assertSame([], $this->resolver->resolve(new Request(), $metadata));
    }

    public function testResolveFormTypeFromArgumentType()
    {
        $metadata = new ArgumentMetadata('foo', PersonForm::class, false, false, null, false, [new SubmitForm()]);
        $actual = $this->resolver->resolve(new Request(), $metadata)[0];

        $expected = new SubmitForm(form: PersonForm::class);
        $expected->metadata = $metadata;
        $expected->value = false;
        $this->assertEquals($expected, $actual);
        $this->assertFalse($actual->value);
    }

    public function testResolveWithValueArgument()
    {
        $metadata = new ArgumentMetadata('foo', PersonDto::class, false, false, null, false, [new SubmitForm(form: PersonForm::class)]);
        $actual = $this->resolver->resolve(new Request(), $metadata)[0];

        $expected = new SubmitForm(form: PersonForm::class);
        $expected->metadata = $metadata;
        $expected->value = true;
        $this->assertEquals($expected, $actual);
        $this->assertTrue($actual->value);
    }

    public function testResolveWithValueArgumentMissingForm()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The form class must be defined when the value is requested');

        $metadata = new ArgumentMetadata('foo', PersonDto::class, false, false, null, false, [new SubmitForm()]);
        $this->resolver->resolve(new Request(), $metadata)[0];
    }

    public function testResolveCannotResolveForm()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The form class must be defined as controller parameter type, or in the SubmitForm attribute');

        $metadata = new ArgumentMetadata('foo', null, false, false, null, false, [new SubmitForm()]);
        $this->resolver->resolve(new Request(), $metadata)[0];
    }

    public function testResolveExplicitlyDefinedParameters()
    {
        $metadata = new ArgumentMetadata('foo', null, false, false, null, false, [new SubmitForm(
            form: PersonForm::class,
            value: true,
            validateMessage: 'My error',
        )]);
        $actual = $this->resolver->resolve(new Request(), $metadata)[0];

        $expected = new SubmitForm(
            form: PersonForm::class,
            value: true,
            validateMessage: 'My error',
        );
        $expected->metadata = $metadata;
        $expected->value = true;
        $this->assertEquals($expected, $actual);
        $this->assertTrue($actual->value);
    }

    public function testOnKernelControllerArgumentsSuccess()
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $controller = fn () => null;
        $request = new Request(['id' => 1, 'firstName' => 'John', 'lastName' => 'Doe']);
        $argument = new SubmitForm(form: PersonForm::class);
        $argument->metadata = new ArgumentMetadata('foo', PersonForm::class, false, false, null, false, [new SubmitForm()]);
        $event = new ControllerArgumentsEvent($kernel, $controller, [$argument], $request, HttpKernelInterface::MAIN_REQUEST);
        $resolver = new SubmitFormValueResolver(new Registry());

        $resolver->onKernelControllerArguments($event);

        $this->assertInstanceOf(PersonForm::class, $event->getArguments()[0]);
        $this->assertSame(1, $event->getArguments()[0]['id']->element()->value());
        $this->assertSame('John', $event->getArguments()[0]['firstName']->element()->value());
        $this->assertSame('Doe', $event->getArguments()[0]['lastName']->element()->value());
    }

    public function testOnKernelControllerArgumentsValidationError()
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $controller = fn () => null;
        $request = new Request(['firstName' => 'John', 'lastName' => 'Doe']);
        $argument = new SubmitForm(form: PersonForm::class);
        $argument->metadata = new ArgumentMetadata('foo', PersonForm::class, false, false, null, false, [new SubmitForm()]);
        $event = new ControllerArgumentsEvent($kernel, $controller, [$argument], $request, HttpKernelInterface::MAIN_REQUEST);
        $resolver = new SubmitFormValueResolver(new Registry());

        try {
            $resolver->onKernelControllerArguments($event);
            $this->fail('Expected exception not thrown');
        } catch (InvalidFormException $e) {
            $this->assertSame(['id' => 'This value should not be blank.'], $e->error->toArray());
        }
    }

    public function testOnKernelControllerArgumentsNotValidate()
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $controller = fn () => null;
        $request = new Request(['firstName' => 'John', 'lastName' => 'Doe']);
        $argument = new SubmitForm(form: PersonForm::class, validate: false);
        $argument->metadata = new ArgumentMetadata('foo', PersonForm::class, false, false, null, false, [new SubmitForm()]);
        $event = new ControllerArgumentsEvent($kernel, $controller, [$argument], $request, HttpKernelInterface::MAIN_REQUEST);
        $resolver = new SubmitFormValueResolver(new Registry());
        $resolver->onKernelControllerArguments($event);

        $this->assertInstanceOf(PersonForm::class, $event->getArguments()[0]);
        $this->assertNull($event->getArguments()[0]['id']->element()->value());
        $this->assertSame('John', $event->getArguments()[0]['firstName']->element()->value());
        $this->assertSame('Doe', $event->getArguments()[0]['lastName']->element()->value());
        $this->assertSame(['id' => 'This value should not be blank.'], $event->getArguments()[0]->error()->toArray());
    }

    public function testOnKernelControllerArgumentsValue()
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $controller = fn () => null;
        $request = new Request(['id' => 1, 'firstName' => 'John', 'lastName' => 'Doe']);
        $argument = new SubmitForm(form: PersonForm::class, value: true);
        $argument->metadata = new ArgumentMetadata('foo', PersonDto::class, false, false, null, false, [new SubmitForm()]);
        $event = new ControllerArgumentsEvent($kernel, $controller, [$argument], $request, HttpKernelInterface::MAIN_REQUEST);
        $resolver = new SubmitFormValueResolver(new Registry());

        $resolver->onKernelControllerArguments($event);

        $this->assertInstanceOf(PersonDto::class, $event->getArguments()[0]);
        $this->assertSame(1, $event->getArguments()[0]->id);
        $this->assertSame('John', $event->getArguments()[0]->firstName);
        $this->assertSame('Doe', $event->getArguments()[0]->lastName);
    }

    public function testOnKernelControllerArgumentsSuccessMultipleSource()
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $controller = fn () => null;
        $request = new Request(
            request: ['id' => 1, 'firstName' => 'John', 'lastName' => 'Doe'],
            attributes: ['id' => 42],
        );
        $argument = new SubmitForm(source: [PayloadSource::Attributes, PayloadSource::Body], form: PersonForm::class);
        $argument->metadata = new ArgumentMetadata('foo', PersonForm::class, false, false, null, false, [new SubmitForm()]);
        $event = new ControllerArgumentsEvent($kernel, $controller, [$argument], $request, HttpKernelInterface::MAIN_REQUEST);
        $resolver = new SubmitFormValueResolver(new Registry());

        $resolver->onKernelControllerArguments($event);

        $this->assertInstanceOf(PersonForm::class, $event->getArguments()[0]);
        $this->assertSame(42, $event->getArguments()[0]['id']->element()->value());
        $this->assertSame('John', $event->getArguments()[0]['firstName']->element()->value());
        $this->assertSame('Doe', $event->getArguments()[0]['lastName']->element()->value());
    }

    public function testOnKernelControllerArgumentsSuccessJsonBody()
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $controller = fn () => null;
        $request = new Request(
            server: ['REQUEST_METHOD' => 'POST'],
            content: \json_encode(['id' => 42, 'firstName' => 'John', 'lastName' => 'Doe']),
        );
        $argument = new SubmitForm(form: PersonForm::class);
        $argument->metadata = new ArgumentMetadata('foo', PersonForm::class, false, false, null, false, [new SubmitForm()]);
        $event = new ControllerArgumentsEvent($kernel, $controller, [$argument], $request, HttpKernelInterface::MAIN_REQUEST);
        $resolver = new SubmitFormValueResolver(new Registry());

        $resolver->onKernelControllerArguments($event);

        $this->assertInstanceOf(PersonForm::class, $event->getArguments()[0]);
        $this->assertSame(42, $event->getArguments()[0]['id']->element()->value());
        $this->assertSame('John', $event->getArguments()[0]['firstName']->element()->value());
        $this->assertSame('Doe', $event->getArguments()[0]['lastName']->element()->value());
    }
}
