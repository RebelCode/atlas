<?php

namespace RebelCode\Atlas\Test;

use RebelCode\Atlas\Expression\ColumnTerm;
use RebelCode\Atlas\Order;
use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{
    public function testConstructorNoSort()
    {
        $order = new Order($column = 'foo');

        $this->assertEquals($column, $order->getColumn());
        $this->assertEquals(Order::ASC, $order->getSort());
    }

    public function testConstructorAsc()
    {
        $order = new Order($column = 'foo', Order::ASC);

        $this->assertEquals($column, $order->getColumn());
        $this->assertEquals(Order::ASC, $order->getSort());
    }

    public function testConstructorDesc()
    {
        $order = new Order($column = 'foo', Order::DESC);

        $this->assertEquals($column, $order->getColumn());
        $this->assertEquals(Order::DESC, $order->getSort());
    }

    public function testConstructorWithTerm()
    {
        $columnTerm = $this->createMock(ColumnTerm::class);
        $columnTerm->method('getName')->willReturn($columnName = 'foo');

        $order = new Order($columnTerm);

        $this->assertEquals($columnName, $order->getColumn());
    }

    public function provideDataForTestDir(): array
    {
        return [
            [new Order('foo', Order::DESC), Order::ASC, Order::ASC],
            [new Order('foo', Order::ASC), Order::DESC, Order::DESC],
            [new Order('foo', Order::ASC), 'invalid', Order::ASC],
            [new Order('foo', Order::DESC), 'invalid', Order::DESC],
        ];
    }

    /** @dataProvider provideDataForTestDir */
    public function testDir(Order $order, string $dir, string $expected): void
    {
        $newOrder = $order->dir($dir, $expected);

        $this->assertEquals($expected, $newOrder->getSort());
    }
}
