<?php /** @noinspection SqlDialectInspection */

namespace RebelCode\Atlas\Test\QueryType;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Exception\QueryCompileException;
use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Order;
use RebelCode\Atlas\Query;
use RebelCode\Atlas\QueryType\Delete;
use RebelCode\Atlas\QueryType\Select;
use stdClass;
use Throwable;

class DeleteTest extends TestCase
{
    public function testCompile()
    {
        $delete = new Delete();
        $query = new Query($delete, [
            Delete::FROM => 'test',
        ]);

        $expected = 'DELETE FROM `test`';
        $actual = $delete->compile($query);

        $this->assertEquals($expected, $actual);
    }

    public function testCompileWhere()
    {
        $where = $this->createMock(ExprInterface::class);
        $where->expects($this->once())->method('toString')->willReturn('TEST_EXPR');

        $delete = new Delete();
        $query = new Query($delete, [
            Delete::FROM => 'test',
            Delete::WHERE => $where,
        ]);

        $expected = 'DELETE FROM `test` WHERE TEST_EXPR';
        $actual = $delete->compile($query);

        $this->assertEquals($expected, $actual);
    }

    public function testCompileOrderNoWhere()
    {
        $delete = new Delete();
        $query = new Query($delete, [
            Delete::FROM => 'test',
            Delete::ORDER => [
                new Order('foo'),
                new Order('bar', Order::ASC),
                new Order('baz', Order::DESC),
            ],
        ]);

        $expected = 'DELETE FROM `test`';
        $actual = $delete->compile($query);

        $this->assertEquals($expected, $actual);
    }

    public function testCompileOrderWithWhere()
    {
        $where = $this->createMock(ExprInterface::class);
        $where->expects($this->once())->method('toString')->willReturn('TEST_EXPR');

        $delete = new Delete();
        $query = new Query($delete, [
            Delete::FROM => 'test',
            Delete::WHERE => $where,
            Delete::ORDER => [
                new Order('foo'),
                new Order('bar', Order::ASC),
                new Order('baz', Order::DESC),
            ],
        ]);

        $expected = 'DELETE FROM `test` WHERE TEST_EXPR ORDER BY `foo` ASC, `bar` ASC, `baz` DESC';
        $actual = $delete->compile($query);

        $this->assertEquals($expected, $actual);
    }

    public function testCompileLimit()
    {
        $delete = new Delete();
        $query = new Query($delete, [
            Delete::FROM => 'test',
            Delete::LIMIT => 10,
        ]);

        $expected = 'DELETE FROM `test` LIMIT 10';
        $actual = $delete->compile($query);

        $this->assertEquals($expected, $actual);
    }

    public function testCompileWhereOrderLimit()
    {
        $where = $this->createMock(ExprInterface::class);
        $where->expects($this->once())->method('toString')->willReturn('TEST_EXPR');

        $delete = new Delete();
        $query = new Query($delete, [
            Delete::FROM => 'test',
            Delete::LIMIT => 10,
            Delete::WHERE => $where,
            Delete::ORDER => [
                new Order('foo', Order::ASC),
                new Order('bar', Order::DESC),
            ],
        ]);

        $expected = 'DELETE FROM `test` WHERE TEST_EXPR ORDER BY `foo` ASC, `bar` DESC LIMIT 10';
        $actual = $delete->compile($query);

        $this->assertEquals($expected, $actual);
    }

    public function provideInvalidTableNames(): array
    {
        return [
            'null' => [null],
            'empty string' => [''],
            'whitespace' => ['    '],
            'integer' => [4],
            'true' => [true],
            'false' => [false],
            'array' => [[1, 2, 3]],
            'object' => [new stdClass()],
        ];
    }

    /** @dataProvider provideInvalidTableNames */
    public function testCompileInvalidTable($table)
    {
        $delete = new Delete();
        $query = new Query($delete, [
            Delete::FROM => $table,
        ]);

        try {
            $delete->compile($query);
            $this->fail();
        } catch (Throwable $e) {
            $this->assertInstanceOf(QueryCompileException::class, $e);
            $this->assertSame($query, $e->getQuery());
        }
    }

    public function provideInvalidExpressions(): array
    {
        return [
            'string' => ['test'],
            'integer' => [4],
            'true' => [true],
            'array' => [[1, 2, 3]],
            'object' => [new stdClass()],
        ];
    }

    /** @dataProvider provideInvalidExpressions */
    public function testCompileInvalidWhere($expr)
    {
        $delete = new Delete();
        $query = new Query($delete, [
            Delete::FROM => 'test',
            Delete::WHERE => $expr,
        ]);

        try {
            $delete->compile($query);
            $this->fail();
        } catch (Throwable $e) {
            $this->assertInstanceOf(QueryCompileException::class, $e);
            $this->assertSame($query, $e->getQuery());
        }
    }
}
