<?php

namespace Bdf\Form\Bundle\Tests\Http;

use Bdf\Form\Bundle\Http\PayloadSource;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class PayloadSourceTest extends TestCase
{
    private Request $request;

    protected function setUp(): void
    {
        $this->request = new Request(
            [
                'foo' => 123,
                'bar' => 'azerty',
            ],
            [
                'baz' => 456,
                'qux' => 'ytreza',
            ],
            [
                'quux' => 789,
                'corge' => 'qwerty',
            ],
            server: ['REQUEST_METHOD' => 'POST']
        );
    }

    public function testAuto()
    {
        $this->assertSame([
            'baz' => 456,
            'qux' => 'ytreza',
        ], PayloadSource::Auto->extract($this->request));

        $this->request->setMethod('GET');
        $this->assertSame([
            'foo' => 123,
            'bar' => 'azerty',
        ], PayloadSource::Auto->extract($this->request));
    }

    public function testQueryString()
    {
        $this->assertSame([
            'foo' => 123,
            'bar' => 'azerty',
        ], PayloadSource::QueryString->extract($this->request));
    }

    public function testBody()
    {
        $this->assertSame([
            'baz' => 456,
            'qux' => 'ytreza',
        ], PayloadSource::Body->extract($this->request));
    }

    public function testAttribute()
    {
        $this->assertSame([
            'quux' => 789,
            'corge' => 'qwerty',
        ], PayloadSource::Attributes->extract($this->request));
    }
}
