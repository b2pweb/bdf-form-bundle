<?php

namespace Bdf\Form\Bundle\Tests;

require_once __DIR__.'/TestKernel.php';

use Bdf\Form\Bundle\Tests\Forms\A;
use Bdf\Form\Aggregate\FormBuilder;
use Bdf\Form\Aggregate\FormBuilderInterface;
use Bdf\Form\Bundle\FormBundle;
use Bdf\Form\Bundle\Registry\SymfonyRegistry;
use Bdf\Form\Bundle\Tests\Forms\FooElement;
use Bdf\Form\Bundle\Tests\Forms\FooElementBuilder;
use Bdf\Form\Bundle\Tests\Forms\MyConstraintValidator;
use Bdf\Form\Bundle\Tests\Forms\MyCustomForm;
use Bdf\Form\Csrf\CsrfElement;
use Bdf\Form\Csrf\CsrfElementBuilder;
use Bdf\Form\Registry\RegistryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * BdfSerializerBundleTest
 */
class BdfFormBundleTest extends TestCase
{
    public function test_default_config()
    {
        $builder = $this->createMock(ContainerBuilder::class);

        $bundle = new FormBundle();

        $this->assertNull($bundle->build($builder));
    }

    /**
     *
     */
    public function test_instances()
    {
        $kernel = new \TestKernel('dev', true);
        $kernel->boot();

        $this->assertInstanceOf(SymfonyRegistry::class, $kernel->getContainer()->get(SymfonyRegistry::class));
        $this->assertInstanceOf(SymfonyRegistry::class, $kernel->getContainer()->get(RegistryInterface::class));
        $this->assertInstanceOf(FormBuilder::class, $kernel->getContainer()->get(FormBuilder::class));
        $this->assertInstanceOf(FormBuilder::class, $kernel->getContainer()->get(FormBuilderInterface::class));
        $this->assertNotSame($kernel->getContainer()->get(FormBuilder::class), $kernel->getContainer()->get(FormBuilder::class));
    }

    /**
     *
     */
    public function test_form_builder_should_use_validator_from_container()
    {
        $kernel = new \TestKernel('dev', true);
        $kernel->boot();

        /** @var FormBuilder $builder */
        $builder = $kernel->getContainer()->get(FormBuilder::class);
        $form = $builder->buildElement();

        $this->assertSame($kernel->getContainer()->get('validator'), $form->root()->getValidator());
    }

    /**
     *
     */
    public function test_custom_form()
    {
        $kernel = new \TestKernel('dev', true);
        $kernel->boot();

        $form = $kernel->getContainer()->get(RegistryInterface::class)->elementBuilder(MyCustomForm::class)->buildElement();

        $this->assertInstanceOf(MyCustomForm::class, $form);
        $this->assertEquals($form, $kernel->getContainer()->get(MyCustomForm::class));
        $this->assertNotSame($form, $kernel->getContainer()->get(MyCustomForm::class));

        $this->assertEquals('foo', $form->a->foo);
    }

    /**
     *
     */
    public function test_custom_form_should_instantiate_constraint_validator_from_container()
    {
        $kernel = new \TestKernel('dev', true);
        $kernel->boot();

        $form = $kernel->getContainer()->get(RegistryInterface::class)->elementBuilder(MyCustomForm::class)->buildElement();
        $form->submit(['foo' => 'bar', 'other' => 'baz']);

        $this->assertEquals(new A('foo'), MyConstraintValidator::$injectedParameter);
    }

    /**
     *
     */
    public function test_custom_form_should_use_current_element_builder_instance()
    {
        $kernel = new \TestKernel('dev', true);
        $kernel->boot();

        $builder = $kernel->getContainer()->get(RegistryInterface::class)->elementBuilder(MyCustomForm::class);
        $builder->string('bar');

        $form = $builder->buildElement();

        $this->assertInstanceOf(MyCustomForm::class, $form);

        $this->assertTrue(isset($form['foo']));
        $this->assertTrue(isset($form['bar']));
    }

    /**
     *
     */
    public function test_custom_element()
    {
        $kernel = new \TestKernel('dev', true);
        $kernel->boot();

        $builder = $kernel->getContainer()->get(RegistryInterface::class)->elementBuilder(FooElement::class);

        $this->assertInstanceOf(FooElementBuilder::class, $builder);
        $this->assertInstanceOf(FooElement::class, $builder->buildElement());

        $this->assertNotSame($kernel->getContainer()->get(RegistryInterface::class)->elementBuilder(FooElement::class), $kernel->getContainer()->get(RegistryInterface::class)->elementBuilder(FooElement::class));

        $this->assertSame($kernel->getContainer()->get(A::class), $builder->a);
    }

    /**
     *
     */
    public function test_csrf_element()
    {
        $kernel = new \TestKernel('dev', true);
        $kernel->boot();

        $builder = $kernel->getContainer()->get(RegistryInterface::class)->elementBuilder(CsrfElement::class);

        $this->assertInstanceOf(CsrfElementBuilder::class, $builder);

        /** @var CsrfElement $element */
        $element = $builder->buildElement();

        $this->assertSame($kernel->getContainer()->get('security.csrf.token_manager'), $element->getTokenManager());
    }
}
