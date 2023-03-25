<?php

namespace RebelCode\Atlas\Test\Expression;

use RebelCode\Atlas\Expression\BetweenExpr;
use PHPUnit\Framework\TestCase;

class BetweenExprTest extends TestCase
{
    public function testCtor()
    {
        $left = $this->createMock('RebelCode\Atlas\Expression\ExprInterface');
        $low = $this->createMock('RebelCode\Atlas\Expression\ExprInterface');
        $high = $this->createMock('RebelCode\Atlas\Expression\ExprInterface');
        $expr = new BetweenExpr($left, $low, $high, false);

        $this->assertSame($left, $expr->getLeft());
        $this->assertSame($low, $expr->getLow());
        $this->assertSame($high, $expr->getHigh());
        $this->assertFalse($expr->isNegated());
    }

    public function testCtorNegated()
    {
        $left = $this->createMock('RebelCode\Atlas\Expression\ExprInterface');
        $low = $this->createMock('RebelCode\Atlas\Expression\ExprInterface');
        $high = $this->createMock('RebelCode\Atlas\Expression\ExprInterface');
        $expr = new BetweenExpr($left, $low, $high, true);

        $this->assertSame($left, $expr->getLeft());
        $this->assertSame($low, $expr->getLow());
        $this->assertSame($high, $expr->getHigh());
        $this->assertTrue($expr->isNegated());
    }

    public function testToString()
    {
        $left = $this->createMock('RebelCode\Atlas\Expression\ExprInterface');
        $low = $this->createMock('RebelCode\Atlas\Expression\ExprInterface');
        $high = $this->createMock('RebelCode\Atlas\Expression\ExprInterface');
        $expr = new BetweenExpr($left, $low, $high);

        $left->expects($this->once())->method('toSql')->willReturn('left');
        $low->expects($this->once())->method('toSql')->willReturn('low');
        $high->expects($this->once())->method('toSql')->willReturn('high');

        $this->assertSame('left BETWEEN(low AND high)', $expr->toSql());
    }

    public function testToStringNegated()
    {
        $left = $this->createMock('RebelCode\Atlas\Expression\ExprInterface');
        $low = $this->createMock('RebelCode\Atlas\Expression\ExprInterface');
        $high = $this->createMock('RebelCode\Atlas\Expression\ExprInterface');
        $expr = new BetweenExpr($left, $low, $high, true);

        $left->expects($this->once())->method('toSql')->willReturn('left');
        $low->expects($this->once())->method('toSql')->willReturn('low');
        $high->expects($this->once())->method('toSql')->willReturn('high');

        $this->assertSame('left NOT BETWEEN(low AND high)', $expr->toSql());
    }
}
