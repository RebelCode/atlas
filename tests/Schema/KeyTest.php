<?php

namespace RebelCode\Atlas\Test\Schema;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Schema\ForeignKey;
use RebelCode\Atlas\Schema\Key;
use RebelCode\Atlas\Schema\PrimaryKey;
use RebelCode\Atlas\Schema\UniqueKey;
use RebelCode\Atlas\Test\Helpers\ReflectionHelper;

class KeyTest extends TestCase
{
    use ReflectionHelper;

    public function testUnique()
    {
        $key = Key::unique(['foo', 'bar']);

        $this->assertInstanceOf(UniqueKey::class, $key);
        $this->assertEquals(['foo', 'bar'], $this->expose($key)->columns);
    }

    public function testPrimary()
    {
        $key = Key::primary(['foo', 'bar']);

        $this->assertInstanceOf(PrimaryKey::class, $key);
        $this->assertEquals(['foo', 'bar'], $this->expose($key)->columns);
    }

    public function testForeign()
    {
        $key = Key::foreign('test', $mappings = ['foo' => 'bar', 'baz' => 'qux']);

        $this->assertInstanceOf(ForeignKey::class, $key);
        $this->assertEquals('test', $this->expose($key)->table);
        $this->assertEquals($mappings, $this->expose($key)->mappings);
    }
}
