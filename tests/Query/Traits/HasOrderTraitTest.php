<?php

namespace RebelCode\Atlas\Test\Query\Traits;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Order;
use RebelCode\Atlas\Query\Traits\HasOrderTrait;
use RebelCode\Atlas\Test\Helpers\ReflectionHelper;

class HasOrderTraitTest extends TestCase
{
    use ReflectionHelper;

    public function testSetOrder()
    {
        $mock = $this->getMockForTrait(HasOrderTrait::class);
        $clone = $mock->orderBy(
            $order = [
                new Order('foo', Order::ASC),
                new Order('bar', Order::DESC),
            ]
        );

        $this->assertEquals([], $this->expose($mock)->order);
        $this->assertEquals($order, $this->expose($clone)->order);
    }

    public function testCompile()
    {
        $mock = $this->getMockForTrait(HasOrderTrait::class)->orderBy([
            new Order('foo', Order::ASC),
            new Order('bar', Order::DESC),
        ]);

        $this->assertEquals('ORDER BY `foo` ASC, `bar` DESC', $this->expose($mock)->compileOrder());
    }

    public function testCompileNoOrder()
    {
        $mock = $this->getMockForTrait(HasOrderTrait::class);

        $this->assertEquals('', $this->expose($mock)->compileOrder());
    }
}
