<?php

namespace LudoTest;

use Ludo\Support\ClassLoader;
use PHPUnit\Framework\TestCase;


class ClassLoaderTest extends TestCase
{
    public function testAddDirectory()
    {
        ClassLoader::addDirectories('src/Ludo/');
    }
}