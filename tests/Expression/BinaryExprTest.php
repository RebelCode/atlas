<?php

namespace RebelCode\Atlas\Test\Expression;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Expression\BaseExpr;
use RebelCode\Atlas\Expression\BinaryExpr;
use RebelCode\Atlas\Expression\ExprInterface;

class BinaryExprTest extends TestCase
{
    public function testInstance()
    {
        $left = $this->createMock(ExprInterface::class);
        $right = $this->createMock(ExprInterface::class);
        $operator = BinaryExpr::R_SHIFT;
        $instance = new BinaryExpr($left, $operator, $right);

        $this->assertInstanceOf(BaseExpr::class, $instance);
        $this->assertInstanceOf(ExprInterface::class, $instance);
    }

    public function testConstructor()
    {
        $left = $this->createMock(ExprInterface::class);
        $right = $this->createMock(ExprInterface::class);
        $operator = BinaryExpr::R_SHIFT;

        $expr = new BinaryExpr($left, $operator, $right);

        $this->assertSame($left, $expr->getLeft());
        $this->assertSame($right, $expr->getRight());
        $this->assertEquals($operator, $expr->getOperator());
    }

    public function testToString()
    {
        $left = $this->createMock(ExprInterface::class);
        $right = $this->createMock(ExprInterface::class);
        $operator = BinaryExpr::PLUS;

        $left->expects($this->once())->method('toString')->willReturn('foo');
        $right->expects($this->once())->method('toString')->willReturn('bar');

        $expr = new BinaryExpr($left, $operator, $right);

        $this->assertEquals('(foo + bar)', $expr->toString());
    }
}
