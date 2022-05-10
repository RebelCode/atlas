<?php

namespace RebelCode\Atlas\Test\Schema;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Order;
use RebelCode\Atlas\Schema\Index;

class IndexTest extends TestCase
{
    public function testConstructorUnique()
    {
        $index = new Index(true, []);

        $this->assertTrue($index->isUnique());
    }

    public function testConstructorNotUnique()
    {
        $index = new Index(false, []);

        $this->assertFalse($index->isUnique());
    }

    public function testConstructorColumns()
    {
        $index = new Index(false, $columns = [
            'foo' => null,
            'bar' => Order::ASC,
            'baz' => Order::DESC,
        ]);

        $this->assertEquals($columns, $index->getColumns());
    }
}
