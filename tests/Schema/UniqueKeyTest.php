<?php

namespace RebelCode\Atlas\Test\Schema;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Schema\Key;
use RebelCode\Atlas\Schema\UniqueKey;
use RebelCode\Atlas\Test\Helpers\ReflectionHelper;

class UniqueKeyTest extends TestCase
{
    use ReflectionHelper;

    public function testExtendsKey()
    {
        $this->assertInstanceOf(Key::class, new UniqueKey([]));
    }

    public function testCtor()
    {
        $cols = ['foo', 'bar'];
        $pk = new UniqueKey($cols);

        $this->assertEquals($cols, $this->expose($pk)->columns);
    }

    public function testToSql()
    {
        $pk = new UniqueKey(['foo', 'bar']);

        $expected = "CONSTRAINT `foo_bar` UNIQUE (`foo`, `bar`)";
        $actual = $pk->toSql('foo_bar');

        $this->assertEquals($expected, $actual);
    }
}
