<?php

namespace Bdf\Form\Bundle\Tests;

require_once __DIR__.'/TestKernel.php';

use Bdf\Form\Aggregate\FormBuilder;
use Bdf\Form\Aggregate\FormInterface;
use Bdf\Form\Attribute\AttributeForm;
use Bdf\Form\Attribute\Processor\CompileAttributesProcessor;
use Bdf\Form\Attribute\Processor\ReflectionProcessor;
use Bdf\Form\Bundle\Tests\Forms\A;
use Bdf\Form\Bundle\Tests\Forms\NoForbiddenValueValidator;
use Bdf\Form\Bundle\Tests\FormsAttributes\StructDto;
use Bdf\Form\Bundle\Tests\FormsAttributes\StructWithDependentConstraint;
use Bdf\Form\Bundle\Tests\FormsAttributes\WithAnonymousFormClass;
use Bdf\Form\Bundle\Tests\FormsAttributes\WithAttributes;
use Bdf\Form\Registry\RegistryInterface;
use Bdf\Form\Struct\StructForm;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * BdfSerializerBundleTest.
 */
class FormBundleWithAttributeTest extends TestCase
{
    protected function setUp(): void
    {
        if (!\class_exists(AttributeForm::class)) {
            $this->markTestSkipped();
        }

        parent::setUp();
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove(__DIR__.'/../var');
    }

    public function testShouldCompileConfiguratorsOnContainerBuild()
    {
        $kernel = new \TestKernel(['conf_php8.yaml']);
        $kernel->boot();

        $this->assertFileExists($kernel->getBuildDir().'/form/GeneratedConfigurator/Bdf/Form/Bundle/Tests/FormsAttributes/WithAttributesConfigurator.php');
    }

    /**
     * @return void
     */
    public function testShouldUseCompileAttributesProcessor()
    {
        $kernel = new \TestKernel(['conf_php8.yaml']);
        $kernel->boot();

        $form = $kernel->getContainer()->get(WithAttributes::class);
        $prop = new \ReflectionProperty(AttributeForm::class, 'processor');
        PHP_VERSION_ID >= 80500 || $prop->setAccessible(true);

        $processor = $prop->getValue($form);
        $this->assertInstanceOf(CompileAttributesProcessor::class, $processor);

        $this->assertInstanceOf('GeneratedConfigurator\Bdf\Form\Bundle\Tests\FormsAttributes\WithAttributesConfigurator', $processor->configureBuilder($form, new FormBuilder()));
    }

    /**
     * @return void
     */
    public function testFunctional()
    {
        $kernel = new \TestKernel(['conf_php8.yaml']);
        $kernel->boot();

        /** @var WithAttributes $form */
        $form = $kernel->getContainer()->get(WithAttributes::class);

        $form->submit(['foo' => 'azerty', 'bar' => '-5']);

        $this->assertFalse($form->valid());
        $this->assertEquals(['bar' => 'This value should be positive.'], $form->error()->toArray());

        $form->submit(['foo' => 'azerty', 'bar' => '5']);
        $this->assertTrue($form->valid());
        $this->assertSame(['foo' => 'azerty', 'bar' => 5], $form->value());
    }

    /**
     * @return void
     */
    public function testFunctionalStruct()
    {
        if (!\class_exists(StructForm::class)) {
            $this->markTestSkipped('Struct not supported');
        }

        $kernel = new \TestKernel(['conf_php8.yaml']);
        $kernel->boot();

        /** @var FormInterface<StructDto> $form */
        $form = $kernel->getContainer()->get(RegistryInterface::class)
            ->elementBuilder(StructForm::class)
            ->class(StructDto::class)
            ->buildElement();

        $form->submit([
            'id' => -2,
            'name' => 'azerty',
        ]);

        $this->assertFalse($form->valid());
        $this->assertEquals(['id' => 'This value should be positive.'], $form->error()->toArray());

        $form->submit([
            'id' => 1,
            'name' => 'azerty',
        ]);
        $this->assertTrue($form->valid());
        $this->assertEquals(new StructDto(1, 'azerty'), $form->value());
    }

    /**
     * @return void
     */
    public function testFunctionalStructWithConstraintUsingValidatorWithDependencies()
    {
        if (!\class_exists(StructForm::class)) {
            $this->markTestSkipped('Struct not supported');
        }

        $kernel = new \TestKernel(['conf_php8.yaml']);
        $kernel->boot();

        NoForbiddenValueValidator::$dependency = null;

        /** @var FormInterface<StructWithDependentConstraint> $form */
        $form = $kernel->getContainer()->get(RegistryInterface::class)
            ->elementBuilder(StructForm::class)
            ->class(StructWithDependentConstraint::class)
            ->buildElement();

        // 'foo' is the value carried by the injected A service, so it must be rejected by the custom constraint
        $form->submit(['value' => 'foo']);

        $this->assertFalse($form->valid());
        $this->assertEquals(['value' => 'This value is forbidden.'], $form->error()->toArray());
        // the validator has been instantiated from the container, with its dependency injected
        $this->assertEquals(new A('foo'), NoForbiddenValueValidator::$dependency);

        $form->submit(['value' => 'bar']);

        $this->assertTrue($form->valid());
        $this->assertEquals(new StructWithDependentConstraint('bar'), $form->value());
    }

    public function testDisableCompilation()
    {
        $kernel = new \TestKernel(['conf_php8.yaml', 'conf_disable_compilation.yaml']);
        $kernel->boot();

        $this->assertFileDoesNotExist($kernel->getBuildDir().'/form/GeneratedConfigurator/Bdf/Form/Bundle/Tests/FormsAttributes/WithAttributesConfigurator.php');

        $form = $kernel->getContainer()->get(WithAttributes::class);
        $prop = new \ReflectionProperty(AttributeForm::class, 'processor');
        PHP_VERSION_ID >= 80500 || $prop->setAccessible(true);

        $processor = $prop->getValue($form);
        $this->assertInstanceOf(ReflectionProcessor::class, $processor);
    }

    public function testWithCustomResolverConfig()
    {
        $kernel = new \TestKernel(['conf_php8.yaml', 'conf_custom_resolver.yaml']);
        $kernel->boot();

        $this->assertFileExists($kernel->getBuildDir().'/generated/Foo/Bdf/Form/Bundle/Tests/FormsAttributes/WithAttributesBar.php');

        $form = $kernel->getContainer()->get(WithAttributes::class);
        $prop = new \ReflectionProperty(AttributeForm::class, 'processor');
        PHP_VERSION_ID >= 80500 || $prop->setAccessible(true);

        $processor = $prop->getValue($form);
        $this->assertInstanceOf(CompileAttributesProcessor::class, $processor);

        $this->assertInstanceOf('Foo\Bdf\Form\Bundle\Tests\FormsAttributes\WithAttributesBar', $processor->configureBuilder($form, new FormBuilder()));
    }

    /**
     * @testWith ["conf_invalid_configurator_prefix.yaml", "Invalid class name prefix"]
     *           ["conf_invalid_configurator_suffix.yaml", "Invalid class name suffix"]
     */
    public function testWithInvalidConf(string $conf, string $error)
    {
        $this->expectExceptionMessage($error);

        $kernel = new \TestKernel(['conf_php8.yaml', $conf]);
        $kernel->boot();
    }

    /**
     * @return void
     */
    public function testWithAnonymousFormClass()
    {
        $kernel = new \TestKernel(['conf_php8.yaml']);
        $kernel->boot();

        $o = $kernel->getContainer()->get(WithAnonymousFormClass::class);

        $this->assertSame(['foo' => 'BAR'], $o->process(['foo' => 'bar']));

        $prop = new \ReflectionProperty(AttributeForm::class, 'processor');
        PHP_VERSION_ID >= 80500 || $prop->setAccessible(true);

        $processor = $prop->getValue($o->form);
        $this->assertInstanceOf(CompileAttributesProcessor::class, $processor);
    }
}
