<?php

namespace RebelCode\Atlas\Test;

use RebelCode\Atlas\Order;
use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{
    public function testConstructorNoSort()
    {
        $order = new Order($field = 'foo');

        $this->assertEquals($field, $order->getField());
        $this->assertEquals(Order::ASC, $order->getSort());
    }

    public function testConstructorAsc()
    {
        $order = new Order($field = 'foo', Order::ASC);

        $this->assertEquals($field, $order->getField());
        $this->assertEquals(Order::ASC, $order->getSort());
    }

    public function testConstructorDesc()
    {
        $order = new Order($field = 'foo', Order::DESC);

        $this->assertEquals($field, $order->getField());
        $this->assertEquals(Order::DESC, $order->getSort());
    }
}
