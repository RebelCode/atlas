<?php

namespace RebelCode\Atlas\Test;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Expression\BinaryExpr;
use RebelCode\Atlas\Expression\ColumnTerm;
use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Expression\Term;
use RebelCode\Atlas\Expression\UnaryExpr;
use RebelCode\Atlas\Order;
use RebelCode\Atlas\Table;
use RebelCode\Atlas\TableRef;
use function RebelCode\Atlas\asc;
use function RebelCode\Atlas\col;
use function RebelCode\Atlas\desc;
use function RebelCode\Atlas\expr;
use function RebelCode\Atlas\neg;
use function RebelCode\Atlas\not;

class FunctionsTest extends TestCase
{
    public function provideDataForColTest(): array
    {
        return [
            '(col_name)' => [new ColumnTerm(null, 'name'), 'name', null],
            '(col_term)' => [$t = new ColumnTerm(null, 'name'), $t, null],
            '(table_name, col_name)' => [new ColumnTerm('users', 'name'), 'users', 'name'],
            '(table, col_name)' => [new ColumnTerm('users', 'name'), new Table('users'), 'name'],
            '(aliased_table, col_name)' => [new ColumnTerm('u', 'name'), (new Table('users'))->as('u'), 'name'],
            '(table_ref, col_name)' => [new ColumnTerm('users', 'name'), new TableRef('users'), 'name'],
            '(aliased_table_ref, col_name)' => [new ColumnTerm('u', 'name'), new TableRef('users', 'u'), 'name'],
        ];
    }

    /** @dataProvider provideDataForColTest */
    public function testCol(ColumnTerm $expected, $arg1, $arg2)
    {
        $this->assertEquals($expected, col($arg1, $arg2));
    }

    public function testAsc()
    {
        $this->assertEquals(asc('name'), new Order('name', Order::ASC));
    }

    public function testDesc()
    {
        $this->assertEquals(desc('name'), new Order('name', Order::DESC));
    }

    public function provideValuesForExprTest(): array
    {
        return [
            'null' => [null, null],
            'string' => ['test', 'test'],
            'int 0' => [0, 0],
            'int' => [1234, 1234],
            'negative int' => [-45092, -45092],
            'float' => [1234.56789, 1234.56789],
            'negative float' => [-450.92, -450.92],
            'true' => [true, true],
            'false' => [false, false],
            'list' => [[1, 2], [new Term(Term::NUMBER, 1), new Term(Term::NUMBER, 2)]],
        ];
    }

    /** @dataProvider provideValuesForExprTest */
    public function testExpr($value, $expected)
    {
        $actual = expr($value);

        $this->assertInstanceOf(ExprInterface::class, $actual);
        $this->assertEquals($expected, $actual->getValue());
    }

    public function testExprTwoArgs()
    {
        $expr = expr(1, '+');

        $this->assertInstanceOf(BinaryExpr::class, $expr);
        $this->assertEquals(1, $expr->getLeft()->getValue());
        $this->assertEquals('+', $expr->getOperator());
        $this->assertNull($expr->getRight()->getValue());
    }

    public function testExprThreeArgs()
    {
        $expr = expr(1, '+', 2);

        $this->assertInstanceOf(BinaryExpr::class, $expr);
        $this->assertEquals(1, $expr->getLeft()->getValue());
        $this->assertEquals('+', $expr->getOperator());
        $this->assertEquals(2, $expr->getRight()->getValue());
    }

    public function testNot()
    {
        $expected = $this->createMock(UnaryExpr::class);

        $arg = $this->createMock(ExprInterface::class);
        $arg->expects($this->once())->method('not')->willReturn($expected);
        $actual = not($arg);

        $this->assertSame($expected, $actual);
    }

    public function testNeg()
    {
        $expected = $this->createMock(UnaryExpr::class);

        $arg = $this->createMock(ExprInterface::class);
        $arg->expects($this->once())->method('neg')->willReturn($expected);
        $actual = neg($arg);

        $this->assertSame($expected, $actual);
    }
}
