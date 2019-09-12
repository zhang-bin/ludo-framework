<?php
namespace LudoTest;

use Ludo\Support\Facades\Crypt;
use PHPUnit\Framework\TestCase;

class CryptTest extends TestCase
{
    public function testEncrypt()
    {
        $data = 'foo';
        $payload = Crypt::encrypt($data);
        $this->assertEquals($data, Crypt::decrypt($payload));
    }
}