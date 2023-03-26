<?php

namespace RebelCode\Atlas\Test\Expression;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Expression\FnExpr;
use RebelCode\Atlas\Test\Helpers\ReflectionHelper;

class FnExprTest extends TestCase
{
    use ReflectionHelper;

    public function testImplementsExprInterface()
    {
        $this->assertInstanceOf(ExprInterface::class, new FnExpr('', []));
    }

    public function testCtor()
    {
        $args = [
            $this->createMock(ExprInterface::class),
            $this->createMock(ExprInterface::class),
        ];

        $expr = new FnExpr('foo', $args);

        $this->assertEquals('foo', $this->expose($expr)->name);
        $this->assertSame($args, $this->expose($expr)->args);
    }

    public function testCtorNoArgs()
    {
        $expr = new FnExpr('foo');

        $this->assertEquals('foo', $this->expose($expr)->name);
        $this->assertEquals([], $this->expose($expr)->args);
    }

    public function testToSql()
    {
        $args = [
            $arg1 = $this->createMock(ExprInterface::class),
            $arg2 = $this->createMock(ExprInterface::class),
        ];

        $arg1->expects($this->once())->method('toSql')->willReturn('bar');
        $arg2->expects($this->once())->method('toSql')->willReturn('baz');

        $expr = new FnExpr('foo', $args);

        $this->assertEquals('foo(bar, baz)', $expr->toSql());
    }

    public function testToSqlNoArgs()
    {
        $expr = new FnExpr('foo');

        $this->assertEquals('foo()', $expr->toSql());
    }
}
