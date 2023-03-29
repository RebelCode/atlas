<?php

namespace RebelCode\Atlas\Test;

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
}
