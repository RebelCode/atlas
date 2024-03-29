<?php

namespace RebelCode\Atlas\Test\Expression;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Expression\BaseExpr;
use RebelCode\Atlas\Expression\BinaryExpr;
use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Expression\Term;

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

    public function provideToStringData() : array
    {
        return [
            'and' => [BinaryExpr::AND],
            'or' => [BinaryExpr::OR],
            'xor' => [BinaryExpr::XOR],
            'equals' => [BinaryExpr::EQ],
            'notEquals' => [BinaryExpr::NEQ],
            'gt' => [BinaryExpr::GT],
            'lt' => [BinaryExpr::LT],
            'gte' => [BinaryExpr::GTE],
            'lte' => [BinaryExpr::LTE],
            'is' => [BinaryExpr::IS],
            'isNot' => [BinaryExpr::IS_NOT],
            'in' => [BinaryExpr::IN],
            'notIn' => [BinaryExpr::NOT_IN],
            'like' => [BinaryExpr::LIKE],
            'notLike' => [BinaryExpr::NOT_LIKE],
            'regexp' => [BinaryExpr::REGEXP],
            'notRegexp' => [BinaryExpr::NOT_REGEXP],
            'plus' => [BinaryExpr::PLUS],
            'minus' => [BinaryExpr::MINUS],
            'mult' => [BinaryExpr::MULT],
            'div' => [BinaryExpr::DIV],
            'intDiv' => [BinaryExpr::INT_DIV],
            'mod' => [BinaryExpr::MOD],
            'leftShift' => [BinaryExpr::L_SHIFT],
            'rightShift' => [BinaryExpr::R_SHIFT],
            'bitwiseAnd' => [BinaryExpr::B_AND],
            'bitwiseOr' => [BinaryExpr::B_OR],
            'bitwiseXor' => [BinaryExpr::B_XOR],
        ];
    }

    /** @dataProvider provideToStringData */
    public function testToString($operator)
    {
        $left = $this->createMock(ExprInterface::class);
        $right = $this->createMock(ExprInterface::class);

        $left->expects($this->once())->method('toSql')->willReturn('foo');
        $right->expects($this->once())->method('toSql')->willReturn('bar');

        $expr = new BinaryExpr($left, $operator, $right);

        $this->assertEquals("(foo $operator bar)", $expr->toSql());
    }
}
