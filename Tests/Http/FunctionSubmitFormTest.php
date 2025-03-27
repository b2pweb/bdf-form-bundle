<?php

namespace Bdf\Form\Bundle\Tests\Http;

require_once __DIR__.'/TestKernel.php';

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\HttpKernelBrowser;

class FunctionSubmitFormTest extends TestCase
{
    private TestKernel $kernel;
    private HttpKernelBrowser $client;

    protected function setUp(): void
    {
        $this->kernel = new TestKernel();
        $this->client = new HttpKernelBrowser($this->kernel);
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove(__DIR__.'/../../var');
    }

    public function testValue()
    {
        $this->client->request('POST', '/value', [
            'id' => 1,
            'firstName' => 'John',
            'lastName' => 'Doe',
        ]);

        $content = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame([
            'id' => 1,
            'firstName' => 'John',
            'lastName' => 'Doe',
        ], $content);
    }

    public function testValueError()
    {
        $this->client->request('POST', '/value', [
            'firstName' => '@@@@@@',
            'lastName' => 'Doe',
        ]);

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
        $this->assertSame([
            'message' => 'The JSON contains invalid data.',
            'fields' => [
                'id' => 'This value should not be blank.',
                'firstName' => 'This value is not valid.',
            ],
        ], \json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testInjectFormValid()
    {
        $this->client->request('POST', '/form', [
            'id' => 1,
            'firstName' => 'John',
            'lastName' => 'Doe',
        ]);

        $content = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame([
            'value' => [
                'id' => 1,
                'firstName' => 'John',
                'lastName' => 'Doe',
            ],
            'errors' => [],
        ], $content);
    }

    public function testInjectFormError()
    {
        $this->client->request('POST', '/form', [
            'firstName' => '#####',
            'lastName' => 'Doe',
        ]);

        $content = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame([
            'value' => [
                'id' => null,
                'firstName' => '#####',
                'lastName' => 'Doe',
            ],
            'errors' => [
                'id' => 'This value should not be blank.',
                'firstName' => 'This value is not valid.',
            ],
        ], $content);
    }

    public function testMultiplePayloadSource()
    {
        $this->client->request('PUT', '/person/42', [
            'firstName' => 'John',
            'lastName' => 'Doe',
        ]);

        $content = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame([
            'id' => 42,
            'firstName' => 'John',
            'lastName' => 'Doe',
        ], $content);
    }

    public function testMultiplePayloadSourcePriority()
    {
        $this->client->request('PUT', '/person/42', [
            'id' => 1,
            'firstName' => 'John',
            'lastName' => 'Doe',
        ]);

        $content = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame([
            'id' => 42,
            'firstName' => 'John',
            'lastName' => 'Doe',
        ], $content);
    }
}
