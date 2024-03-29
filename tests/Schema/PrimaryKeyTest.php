<?php

namespace RebelCode\Atlas\Test\Schema;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Schema\Key;
use RebelCode\Atlas\Schema\PrimaryKey;
use RebelCode\Atlas\Test\Helpers\ReflectionHelper;

class PrimaryKeyTest extends TestCase
{
    use ReflectionHelper;

    public function testExtendsKey()
    {
        $this->assertInstanceOf(Key::class, new PrimaryKey([]));
    }

    public function testCtor()
    {
        $cols = ['foo', 'bar'];
        $pk = new PrimaryKey($cols);

        $this->assertEquals($cols, $this->expose($pk)->columns);
    }

    public function testToSql()
    {
        $pk = new PrimaryKey(['foo', 'bar']);

        $expected = "CONSTRAINT `foo_bar` PRIMARY KEY (`foo`, `bar`)";
        $actual = $pk->toSql('foo_bar');

        $this->assertEquals($expected, $actual);
    }
}
