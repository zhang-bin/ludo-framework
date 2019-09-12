<?php
namespace LudoTest;

use Ludo\Support\Facades\Context;
use Ludo\Support\Facades\Log;
use PHPUnit\Framework\TestCase;
use Exception;

class LogTest extends TestCase
{
    public function testWrite()
    {
        Context::set('current-controller', 'test');
        Context::set('current-action', 'log');

        try {
            $message = 'debug';
            Log::debug($message);

            $message = 'info';
            Log::info($message);
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail();
        }
    }
}