<?php

namespace RebelCode\Atlas\Test\Expression;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Expression\BaseExpr;
use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Expression\UnaryExpr;

class UnaryExprTest extends TestCase
{
    public function testInstance()
    {
        $operator = UnaryExpr::NEG;
        $operand = $this->createMock(ExprInterface::class);
        $instance = new UnaryExpr($operator, $operand);

        $this->assertInstanceOf(BaseExpr::class, $instance);
        $this->assertInstanceOf(ExprInterface::class, $instance);
    }

    public function testConstructor()
    {
        $operator = UnaryExpr::NEG;
        $operand = $this->createMock(ExprInterface::class);

        $expr = new UnaryExpr($operator, $operand);

        $this->assertSame($operand, $expr->getOperand());
        $this->assertEquals($operator, $expr->getOperator());
    }

    public function testToString()
    {
        $operand = $this->createMock(ExprInterface::class);
        $operand->expects($this->once())->method('toString')->willReturn('foo');

        $operator = UnaryExpr::NEG;
        $expr = new UnaryExpr($operator, $operand);

        $this->assertEquals('-(foo)', $expr->toString());
    }

    public function testToStringFunctionName()
    {
        $operand = $this->createMock(ExprInterface::class);
        $operand->expects($this->once())->method('toString')->willReturn('foo');

        $expr = new UnaryExpr('SUM', $operand);

        $this->assertEquals('SUM(foo)', $expr->toString());
    }
}
