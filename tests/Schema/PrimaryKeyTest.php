<?php

namespace RebelCode\Atlas\Test\Schema;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Schema\Key;
use RebelCode\Atlas\Schema\PrimaryKey;

class PrimaryKeyTest extends TestCase
{
    public function testExtendsKey() {
        $pk = new PrimaryKey([]);

        $this->assertInstanceOf(Key::class, $pk);
    }

    public function testConstructorColumns()
    {
        $pk = new PrimaryKey($columns = ['foo', 'bar']);

        $this->assertEquals($columns, $pk->getColumns());
    }

    public function testConstructorIsPrimary()
    {
        $pk = new PrimaryKey(['test']);

        $this->assertTrue($pk->isPrimary());
    }
}
