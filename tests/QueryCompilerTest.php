<?php

namespace RebelCode\Atlas\Test;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Order;
use RebelCode\Atlas\Query;
use RebelCode\Atlas\QueryCompiler;
use stdClass;

class QueryCompilerTest extends TestCase
{
    public function provideEmptyValues(): array
    {
        return [
            'null' => [null],
            'zero' => [0],
            'string' => [''],
            'false' => [false],
            'array' => [[]],
        ];
    }

    public function testCompileFromString()
    {
        $result = QueryCompiler::compileFrom('test');

        $this->assertEquals('FROM `test`', $result);
    }

    public function testCompileFromStringAlias()
    {
        $result = QueryCompiler::compileFrom('test', 'alias');

        $this->assertEquals('FROM `test` AS `alias`', $result);
    }

    public function testCompileFromSubQuery()
    {
        $sub = $this->createMock(Query::class);
        $sub->expects($this->once())->method('compile')->willReturn('foobar');

        $result = QueryCompiler::compileFrom($sub, null, true);

        $this->assertEquals('FROM (foobar)', $result);
    }

    public function testCompileFromExprAlias()
    {
        $sub = $this->createMock(Query::class);
        $sub->expects($this->once())->method('compile')->willReturn('foobar');

        $result = QueryCompiler::compileFrom($sub, 'sub', true);

        $this->assertEquals('FROM (foobar) AS `sub`', $result);
    }

    public function provideInvalidDataForCompileFrom(): array
    {
        return [
            'int' => [213],
            'float' => [432.49],
            'true' => [true],
            'false' => [false],
            'array' => [[5, 1, 9]],
            'object' => [new stdClass()],
        ];
    }

    /** @dataProvider provideInvalidDataForCompileFrom */
    public function testCompileFromInvalid()
    {
        $this->expectException(InvalidArgumentException::class);

        QueryCompiler::compileFrom(new stdClass(), null, true);
    }

    public function testCompileFromInvalidSubQuery()
    {
        $this->expectException(InvalidArgumentException::class);

        QueryCompiler::compileFrom(new stdClass(), null, true);
    }

    public function testCompileFromSubQueriesDisabled()
    {
        $this->expectException(InvalidArgumentException::class);

        QueryCompiler::compileFrom($this->createMock(Query::class));
    }

    public function testCompileColumnList()
    {
        $result = QueryCompiler::compileColumnList(['foo', 'bar', 'baz']);

        $this->assertEquals('`foo`, `bar`, `baz`', $result);
    }

    public function testCompileColumnListSelectWildcard()
    {
        $result = QueryCompiler::compileColumnList(['*'], true);

        $this->assertEquals('*', $result);
    }

    public function testCompileColumnListSelectExpr()
    {
        $expr = $this->createMock(ExprInterface::class);
        $expr->expects($this->once())->method('toString')->willReturn('foobar');

        $result = QueryCompiler::compileColumnList([$expr], true);

        $this->assertEquals('foobar', $result);
    }

    public function testCompileColumnListSelectAlias()
    {
        $result = QueryCompiler::compileColumnList(['first', 'two' => 'second', 'third'], true);

        $this->assertEquals('`first`, `second` AS `two`, `third`', $result);
    }

    public function testCompileColumnListSelectMixed()
    {
        $expr = $this->createMock(ExprInterface::class);
        $expr->expects($this->once())->method('toString')->willReturn('foobar');

        $result = QueryCompiler::compileColumnList([
            'test',
            $expr,
            '*',
            'alias' => 'col',
        ], true);

        $this->assertEquals('`test`, foobar, *, `col` AS `alias`', $result);
    }

    public function provideValidLimitsOffsets(): array
    {
        return [
            'integer' => [9],
            'numeric string' => ['9'],
            'float' => [9.52],
        ];
    }

    public function provideInvalidLimitsOffsets(): array
    {
        return [
            'null' => [null],
            'true' => [true],
            'false' => [false],
            'array' => [[1, 2, 3]],
            'object' => [new stdClass()],
        ];
    }

    /** @dataProvider provideValidLimitsOffsets */
    public function testCompileLimitInt($limit)
    {
        $result = QueryCompiler::compileLimit($limit);
        $this->assertEquals('LIMIT 9', $result);
    }

    /** @dataProvider provideInvalidLimitsOffsets */
    public function testCompileLimitEmpty($limit)
    {
        $result = QueryCompiler::compileLimit($limit);
        $this->assertEquals('', $result);
    }

    /** @dataProvider provideValidLimitsOffsets */
    public function testCompileOffsetInt($limit)
    {
        $result = QueryCompiler::compileOffset($limit);
        $this->assertEquals('OFFSET 9', $result);
    }

    /** @dataProvider provideInvalidLimitsOffsets */
    public function testCompileOffsetEmpty($limit)
    {
        $result = QueryCompiler::compileOffset($limit);
        $this->assertEquals('', $result);
    }

    public function testCompileOrder()
    {
        $result = QueryCompiler::compileOrder([
            new Order('test'),
            new Order('foo', Order::ASC),
            new Order('bar', Order::DESC),
        ]);

        $this->assertEquals('ORDER BY `test` ASC, `foo` ASC, `bar` DESC', $result);
    }

    public function provideInvalidOrderValue(): array
    {
        return [
            'int' => [23],
            'float' => [23.04],
            'string' => ['test'],
            'true' => [true],
            'false' => [false],
            'array' => [[1, 2, 3]],
            'object' => [new stdClass()],
        ];
    }

    /** @dataProvider provideInvalidOrderValue */
    public function testCompileOrderInvalidValue($value)
    {
        $this->expectException(InvalidArgumentException::class);

        QueryCompiler::compileOrder([
            new Order('test'),
            $value,
            new Order('bar', Order::DESC),
        ]);
    }

    public function testCompileWhere()
    {
        $expr = $this->createMock(ExprInterface::class);
        $expr->expects($this->once())->method('toString')->willReturn('foobar');

        $result = QueryCompiler::compileWhere($expr);

        $this->assertEquals('WHERE foobar', $result);
    }

    public function testCompileWhereNull()
    {
        $result = QueryCompiler::compileWhere(null);

        $this->assertEquals('', $result);
    }

    /** @dataProvider provideInvalidOrderValue */
    public function testCompileWhereInvalid($value)
    {
        $this->expectException(InvalidArgumentException::class);
        QueryCompiler::compileWhere($value);
    }

    public function testCompileAssignmentList()
    {
        $expr = $this->createMock(ExprInterface::class);
        $expr->expects($this->once())->method('toString')->willReturn('foobar');

        $actual = QueryCompiler::compileAssignmentList('test', [
            'foo' => $expr,
            'bar' => 'BAR',
            'baz' => 23,
        ]);

        $expected = "test `foo` = foobar, `bar` = 'BAR', `baz` = 23";

        $this->assertEquals($expected, $actual);
    }

    public function testCompileAssignmentListEmpty()
    {
        $actual = QueryCompiler::compileAssignmentList('test', []);

        $this->assertEmpty($actual);
    }

    function provideInvalidAssignmentList(): array
    {
        return [
            'int' => [23],
            'float' => [23.04],
            'string' => ['test'],
            'true' => [true],
            'false' => [false],
            'object' => [new stdClass()],
        ];
    }

    /** @dataProvider provideInvalidAssignmentList */
    public function testCompileAssignmentListInvalid($value)
    {
        $this->expectException(InvalidArgumentException::class);

        QueryCompiler::compileAssignmentList('test', $value);
    }
}
