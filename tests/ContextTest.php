<?php
namespace LudoTest;

use Ludo\Support\Facades\Context;
use PHPUnit\Framework\TestCase;

class ContextTest extends TestCase
{
    public function testSet()
    {
        $id = 'foo';
        $this->assertNull(Context::get($id));

        $value = 'bar';
        Context::set($id, $value);
        $this->assertEquals($value, Context::get($id));
    }
}