<?php

namespace RebelCode\Atlas\Test;

use LogicException;
use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Expression\BinaryExpr;
use RebelCode\Atlas\Expression\ColumnTerm;
use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Expression\Term;
use RebelCode\Atlas\Expression\UnaryExpr;
use RebelCode\Atlas\Order;
use RebelCode\Atlas\Query\SelectQuery;
use RebelCode\Atlas\Table;
use RebelCode\Atlas\TableRef;

use function RebelCode\Atlas\all;
use function RebelCode\Atlas\andAll;
use function RebelCode\Atlas\asc;
use function RebelCode\Atlas\col;
use function RebelCode\Atlas\desc;
use function RebelCode\Atlas\distinct;
use function RebelCode\Atlas\expr;
use function RebelCode\Atlas\neg;
use function RebelCode\Atlas\not;
use function RebelCode\Atlas\orAll;

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

    public function provideDataForAscDescTests(): array
    {
        return [
            'column name' => ['name', 'name'],
            'column term' => ['name', new ColumnTerm(null, 'name')],
        ];
    }

    /** @dataProvider provideDataForAscDescTests */
    public function testAsc(string $expected, $arg)
    {
        $this->assertEquals(asc($expected), new Order($arg, Order::ASC));
    }

    /** @dataProvider provideDataForAscDescTests */
    public function testDesc(string $expected, $arg)
    {
        $this->assertEquals(desc($expected), new Order($arg, Order::DESC));
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

    public function testDistinct()
    {
        $col1 = $this->createMock(ColumnTerm::class);
        $col2 = $this->createMock(ColumnTerm::class);
        $col1->expects($this->once())->method('distinct')->willReturn($col2);

        $this->assertSame($col2, distinct($col1));
    }

    public function provideDataForAllTest(): array
    {
        return [
            'name' => ['users', new ColumnTerm('users', '*')],
            'table' => [new Table('users'), new ColumnTerm('users', '*')],
            'aliased_table' => [(new Table('users'))->as('u'), new ColumnTerm('u', '*')],
            'table_ref' => [new TableRef('users'), new ColumnTerm('users', '*')],
            'aliased_table_ref' => [new TableRef('users', 'u'), new ColumnTerm('u', '*')],
            'select_query' => [(new SelectQuery())->as('s'), new ColumnTerm('s', '*')],
        ];
    }

    /** @dataProvider provideDataForAllTest */
    public function testAll($arg, $expected)
    {
        $this->assertEquals($expected, all($arg));
    }

    public function testAllSelectNoAlias()
    {
        $this->expectException(LogicException::class);

        all(new SelectQuery());
    }

    public function provideDataForTestOrAll(): array
    {
        $foo = Term::create('foo');
        $bar = Term::create('bar');
        $baz = Term::create('baz');

        return [
            'empty' => [[], null],
            'one' => [[$foo], $foo],
            'two' => [[$foo, $bar], $foo->or($bar)],
            'two' => [[$foo, $bar, $baz], $foo->or($bar)->or($baz)],
        ];
    }

    /** @dataProvider provideDataForTestOrAll */
    public function testOrAll($in, $expected)
    {
        $result = orAll($in);

        $this->assertEquals($expected, $result);
    }

    public function provideDataForTestAndAll(): array
    {
        $foo = Term::create('foo');
        $bar = Term::create('bar');
        $baz = Term::create('baz');

        return [
            'empty' => [[], null],
            'one' => [[$foo], $foo],
            'two' => [[$foo, $bar], $foo->and($bar)],
            'two' => [[$foo, $bar, $baz], $foo->and($bar)->and($baz)],
        ];
    }

    /** @dataProvider provideDataForTestAndAll */
    public function testAndAll($in, $expected)
    {
        $result = andAll($in);

        $this->assertEquals($expected, $result);
    }
}
