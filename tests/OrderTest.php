<?php

namespace RebelCode\Atlas\Test;

use RebelCode\Atlas\Expression\ColumnTerm;
use RebelCode\Atlas\Order;
use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{
    public static function provideDataForCtorTest()
    {
        return [
            'no sort' => [
                new Order('foo'),
                new ColumnTerm(null, 'foo'),
                Order::ASC
            ],
            'asc' => [
                new Order('foo', Order::ASC), 
                new ColumnTerm(null, 'foo'), 
                Order::ASC
            ],
            'desc' => [
                new Order('foo', Order::DESC), 
                new ColumnTerm(null, 'foo'), 
                Order::DESC
            ],
            'column term' => [
                new Order($ct = new ColumnTerm(null, 'foo')), 
                $ct, 
                Order::ASC
            ],
        ];
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
