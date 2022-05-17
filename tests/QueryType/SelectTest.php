<?php /** @noinspection SqlDialectInspection */

namespace RebelCode\Atlas\Test\QueryType;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Exception\QueryCompileException;
use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Group;
use RebelCode\Atlas\Order;
use RebelCode\Atlas\Query;
use RebelCode\Atlas\QueryType\Select;
use stdClass;
use Throwable;

class SelectTest extends TestCase
{
    public function testCompileSelectAll()
    {
        $select = new Select();
        $query = new Query($select, [
            Select::FROM => 'test',
        ]);

        $expected = 'SELECT * FROM `test`';
        $actual = $select->compile($query);

        $this->assertEquals($expected, $actual);
    }

    public function testCompileSelectColumns()
    {
        $select = new Select();
        $query = new Query($select, [
            Select::FROM => 'test',
            Select::COLUMNS => ['foo', 'bar'],
        ]);

        $expected = 'SELECT `foo`, `bar` FROM `test`';
        $actual = $select->compile($query);

        $this->assertEquals($expected, $actual);
    }

    public function testCompileSelectWithAlias()
    {
        $select = new Select();
        $query = new Query($select, [
            Select::FROM => 'test',
            Select::COLUMNS => ['bar' => 'foo', 'baz', 'quuz' => 'qux'],
        ]);

        $expected = 'SELECT `foo` AS `bar`, `baz`, `qux` AS `quuz` FROM `test`';
        $actual = $select->compile($query);

        $this->assertEquals($expected, $actual);
    }

    public function testCompileSelectExpr()
    {
        $expr = $this->createMock(ExprInterface::class);
        $expr->expects($this->once())->method('toString')->willReturn('foobar');

        $select = new Select();
        $query = new Query($select, [
            Select::FROM => 'test',
            Select::COLUMNS => [
                $expr,
            ],
        ]);

        $expected = 'SELECT foobar FROM `test`';
        $actual = $select->compile($query);

        $this->assertEquals($expected, $actual);
    }

    public function testCompileSelectExprWithAlias()
    {
        $expr = $this->createMock(ExprInterface::class);
        $expr->expects($this->once())->method('toString')->willReturn('foobar');

        $select = new Select();
        $query = new Query($select, [
            Select::FROM => 'test',
            Select::COLUMNS => [
                'baz' => $expr,
            ],
        ]);

        $expected = 'SELECT foobar AS `baz` FROM `test`';
        $actual = $select->compile($query);

        $this->assertEquals($expected, $actual);
    }

    public function testCompileSelectWithSubQuery()
    {
        $select = new Select();

        $innerQuery = new Query($select, [
            Select::COLUMNS => ['foo', 'bar'],
            Select::FROM => 'test',
            Select::WHERE => $this->createConfiguredMock(ExprInterface::class, ['toString' => 'somewhere()']),
        ]);

        $outerQuery = new Query($select, [
            Select::FROM => $innerQuery,
        ]);

        $expected = 'SELECT * FROM (SELECT `foo`, `bar` FROM `test` WHERE somewhere())';

        $this->assertEquals($expected, $select->compile($outerQuery));
    }

    public function testCompileSelectWhere()
    {
        $expr = $this->createMock(ExprInterface::class);
        $expr->expects($this->once())->method('toString')->willReturn('foobar');

        $select = new Select();
        $query = new Query($select, [
            Select::FROM => 'test',
            Select::WHERE => $expr,
        ]);

        $expected = 'SELECT * FROM `test` WHERE foobar';
        $actual = $select->compile($query);

        $this->assertEquals($expected, $actual);
    }

    public function testCompileSelectGroupBy()
    {
        $select = new Select();
        $query = new Query($select, [
            Select::FROM => 'test',
            Select::GROUP => [
                new Group('foo'),
                new Group('bar', Group::ASC),
                new Group('baz', Group::DESC),
            ],
        ]);

        $expected = 'SELECT * FROM `test` GROUP BY `foo` ASC, `bar` ASC, `baz` DESC';
        $actual = $select->compile($query);

        $this->assertEquals($expected, $actual);
    }

    public function testCompileSelectHaving()
    {
        $expr = $this->createMock(ExprInterface::class);
        $expr->expects($this->once())->method('toString')->willReturn('foobar');

        $select = new Select();
        $query = new Query($select, [
            Select::FROM => 'test',
            Select::HAVING => $expr,
        ]);

        $expected = 'SELECT * FROM `test` HAVING foobar';
        $actual = $select->compile($query);

        $this->assertEquals($expected, $actual);
    }

    public function testCompileSelectOrder()
    {
        $select = new Select();
        $query = new Query($select, [
            Select::FROM => 'test',
            Select::ORDER => [
                new Order('foo'),
                new Order('bar', Order::ASC),
                new Order('baz', Order::DESC),
            ],
        ]);

        $expected = 'SELECT * FROM `test` ORDER BY `foo` ASC, `bar` ASC, `baz` DESC';
        $actual = $select->compile($query);

        $this->assertEquals($expected, $actual);
    }

    public function testCompileSelectLimit()
    {
        $select = new Select();
        $query = new Query($select, [
            Select::FROM => 'test',
            Select::LIMIT => 10,
        ]);

        $expected = 'SELECT * FROM `test` LIMIT 10';
        $actual = $select->compile($query);

        $this->assertEquals($expected, $actual);
    }

    public function testCompileSelectOffset()
    {
        $select = new Select();
        $query = new Query($select, [
            Select::FROM => 'test',
            Select::OFFSET => 5,
        ]);

        $expected = 'SELECT * FROM `test` OFFSET 5';
        $actual = $select->compile($query);

        $this->assertEquals($expected, $actual);
    }

    public function testCompileSelectEverything()
    {
        $expr = $this->createMock(ExprInterface::class);
        $expr->expects($this->once())->method('toString')->willReturn('TEST_EXPR');

        $select = new Select();
        $query = new Query($select, [
            Select::FROM => 'test',
            Select::COLUMNS => ['foo', 'baz' => 'bar'],
            Select::WHERE => $expr,
            Select::LIMIT => 10,
            Select::OFFSET => 5,
            Select::ORDER => [
                new Order('foo', Order::DESC),
            ],
        ]);

        $expected = 'SELECT `foo`, `bar` AS `baz` FROM `test` WHERE TEST_EXPR ORDER BY `foo` DESC LIMIT 10 OFFSET 5';
        $actual = $select->compile($query);

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
        $select = new Select();
        $query = new Query($select, [
            Select::FROM => $table,
        ]);

        try {
            $select->compile($query);
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
        $select = new Select();
        $query = new Query($select, [
            Select::FROM => 'test',
            Select::WHERE => $expr,
        ]);

        try {
            $select->compile($query);
            $this->fail();
        } catch (Throwable $e) {
            $this->assertInstanceOf(QueryCompileException::class, $e);
            $this->assertSame($query, $e->getQuery());
        }
    }

    public function provideInvalidColumns(): array
    {
        return [
            'string' => ['test'],
            'integer' => [4],
            'true' => [true],
            'object' => [new stdClass()],
        ];
    }

    /** @dataProvider provideInvalidColumns */
    public function testCompileInvalidColumns($columns)
    {
        $select = new Select();
        $query = new Query($select, [
            Select::FROM => 'test',
            Select::COLUMNS => $columns,
        ]);

        try {
            $select->compile($query);
            $this->fail();
        } catch (Throwable $e) {
            $this->assertInstanceOf(QueryCompileException::class, $e);
            $this->assertSame($query, $e->getQuery());
        }
    }
}
