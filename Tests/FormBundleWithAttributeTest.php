<?php

namespace Bdf\Form\Bundle\Tests;

require_once __DIR__.'/TestKernel.php';

use Bdf\Form\Aggregate\FormBuilder;
use Bdf\Form\Attribute\AttributeForm;
use Bdf\Form\Attribute\Processor\CompileAttributesProcessor;
use Bdf\Form\Attribute\Processor\ReflectionProcessor;
use Bdf\Form\Bundle\Tests\FormsAttributes\WithAnonymousFormClass;
use Bdf\Form\Bundle\Tests\FormsAttributes\WithAttributes;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * BdfSerializerBundleTest.
 */
class FormBundleWithAttributeTest extends TestCase
{
    protected function setUp(): void
    {
        if (!class_exists(AttributeForm::class)) {
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
        $prop->setAccessible(true);

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

    public function testDisableCompilation()
    {
        $kernel = new \TestKernel(['conf_php8.yaml', 'conf_disable_compilation.yaml']);
        $kernel->boot();

        $this->assertFileDoesNotExist($kernel->getBuildDir().'/form/GeneratedConfigurator/Bdf/Form/Bundle/Tests/FormsAttributes/WithAttributesConfigurator.php');

        $form = $kernel->getContainer()->get(WithAttributes::class);
        $prop = new \ReflectionProperty(AttributeForm::class, 'processor');
        $prop->setAccessible(true);

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
        $prop->setAccessible(true);

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
        $prop->setAccessible(true);

        $processor = $prop->getValue($o->form);
        $this->assertInstanceOf(CompileAttributesProcessor::class, $processor);
    }
}
