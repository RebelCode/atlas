<?php

namespace RebelCode\Atlas\Test\Query;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Query;
use RebelCode\Atlas\DatabaseAdapter;
use RebelCode\Atlas\Exception\QueryCompileException;
use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Expression\Term;
use RebelCode\Atlas\Group;
use RebelCode\Atlas\Order;
use RebelCode\Atlas\Query\SelectQuery;
use RebelCode\Atlas\Test\Helpers;
use Throwable;

class SelectQueryTest extends TestCase
{
    public function testIsQuery()
    {
        $this->assertInstanceOf(Query::class, new SelectQuery());
    }

    public function testCtor()
    {
        $adapter = $this->createMock(DatabaseAdapter::class);
        $from = 'foo';
        $columns = ['a', 'b'];
        $where = $this->createMock(ExprInterface::class);
        $groupBy = [Group::by('foo')];
        $having = $this->createMock(ExprInterface::class);
        $orderBy = [Order::by('foo')];
        $limit = 1;
        $offset = 5;

        $query = new SelectQuery($adapter, $from, $columns, $where, $groupBy, $having, $orderBy, $limit, $offset);

        $this->assertSame($from, Helpers::property($query, 'from'));
        $this->assertSame($columns, Helpers::property($query, 'columns'));
        $this->assertSame($where, Helpers::property($query, 'where'));
        $this->assertSame($groupBy, Helpers::property($query, 'group'));
        $this->assertSame($having, Helpers::property($query, 'having'));
        $this->assertSame($orderBy, Helpers::property($query, 'order'));
        $this->assertSame($limit, Helpers::property($query, 'limit'));
        $this->assertSame($offset, Helpers::property($query, 'offset'));
    }

    public function testFrom()
    {
        $query = new SelectQuery();
        $new = $query->from('foo');

        $this->assertNotSame($query, $new);
        $this->assertSame('foo', Helpers::property($new, 'from'));
    }

    public function testColumns()
    {
        $query = new SelectQuery();
        $new = $query->columns(['a', 'b']);

        $this->assertNotSame($query, $new);
        $this->assertSame(['a', 'b'], Helpers::property($new, 'columns'));
    }

    public function testWhere()
    {
        $query = new SelectQuery();
        $new = $query->where($where = $this->createMock(ExprInterface::class));

        $this->assertNotSame($query, $new);
        $this->assertSame($where, Helpers::property($new, 'where'));
    }

    public function testGroupBy()
    {
        $query = new SelectQuery();
        $new = $query->groupBy($group = [Group::by('foo')]);

        $this->assertNotSame($query, $new);
        $this->assertSame($group, Helpers::property($new, 'group'));
    }

    public function testHaving()
    {
        $query = new SelectQuery();
        $new = $query->having($having = $this->createMock(ExprInterface::class));

        $this->assertNotSame($query, $new);
        $this->assertSame($having, Helpers::property($new, 'having'));
    }

    public function testOrderBy()
    {
        $query = new SelectQuery();
        $new = $query->orderBy($order = [Order::by('foo')]);

        $this->assertNotSame($query, $new);
        $this->assertSame($order, Helpers::property($new, 'order'));
    }

    public function testLimit()
    {
        $query = new SelectQuery();
        $new = $query->limit(1);

        $this->assertNotSame($query, $new);
        $this->assertSame(1, Helpers::property($new, 'limit'));
    }

    public function testOffset()
    {
        $query = new SelectQuery();
        $new = $query->offset(5);

        $this->assertNotSame($query, $new);
        $this->assertSame(5, Helpers::property($new, 'offset'));
    }

    public function testExec()
    {
        $adapter = $this->createMock(DatabaseAdapter::class);

        $query = new SelectQuery($adapter, 'foo');

        $expected = [
            ['id' => 1, 'name' => 'Alice'],
            ['id' => 2, 'name' => 'Bob'],
            ['id' => 3, 'name' => 'Charlie'],
        ];

        $adapter->expects($this->once())->method('queryResults')->willReturn($expected);

        $this->assertEquals($expected, $query->exec());
    }

    public function testCompileSelectAll()
    {
        $query = new SelectQuery(null, 'test');

        $expected = 'SELECT * FROM `test`';
        $actual = $query->compile();

        $this->assertEquals($expected, $actual);
    }

    public function testCompileSelectColumns()
    {
        $query = new SelectQuery(null, 'test', ['foo', 'bar']);

        $expected = 'SELECT `foo`, `bar` FROM `test`';
        $actual = $query->compile();

        $this->assertEquals($expected, $actual);
    }

    public function testCompileSelectWithAlias()
    {
        $query = new SelectQuery(null, 'test', ['bar' => 'foo', 'baz', 'quuz' => 'qux']);

        $expected = 'SELECT `foo` AS `bar`, `baz`, `qux` AS `quuz` FROM `test`';
        $actual = $query->compile();

        $this->assertEquals($expected, $actual);
    }

    public function testCompileSelectColumnsWithDistinct()
    {
        $query = new SelectQuery(null, 'test', [
            Term::column('foo')->distinct()->fn('COUNT'),
            'bar',
        ]);

        $expected = 'SELECT COUNT(DISTINCT `foo`), `bar` FROM `test`';
        $actual = $query->compile();

        $this->assertEquals($expected, $actual);
    }

    public function testCompileSelectExpr()
    {
        $expr = $this->createMock(ExprInterface::class);
        $expr->expects($this->once())->method('toString')->willReturn('foobar');

        $query = new SelectQuery(null, 'test', [$expr]);

        $expected = 'SELECT foobar FROM `test`';
        $actual = $query->compile();

        $this->assertEquals($expected, $actual);
    }

    public function testCompileSelectExprWithAlias()
    {
        $expr = $this->createMock(ExprInterface::class);
        $expr->expects($this->once())->method('toString')->willReturn('foobar');

        $query = new SelectQuery(null, 'test', ['baz' => $expr]);

        $expected = 'SELECT foobar AS `baz` FROM `test`';
        $actual = $query->compile();

        $this->assertEquals($expected, $actual);
    }

    public function testCompileSelectWithSubQuery()
    {
        $innerWhere = $this->createConfiguredMock(ExprInterface::class, ['toString' => 'somewhere()']);
        $innerQuery = new SelectQuery(null, 'test', ['foo', 'bar'], $innerWhere);

        $query = new SelectQuery(null, $innerQuery);

        $expected = 'SELECT * FROM (SELECT `foo`, `bar` FROM `test` WHERE somewhere())';

        $this->assertEquals($expected, $query->compile());
    }

    public function testCompileSelectWhere()
    {
        $expr = $this->createMock(ExprInterface::class);
        $expr->expects($this->once())->method('toString')->willReturn('foobar');

        $query = new SelectQuery(null, 'test', [], $expr);

        $expected = 'SELECT * FROM `test` WHERE foobar';
        $actual = $query->compile();

        $this->assertEquals($expected, $actual);
    }

    public function testCompileSelectGroupBy()
    {
        $query = new SelectQuery(null, 'test', [], null, [
            new Group('foo'),
            new Group('bar', Group::ASC),
            new Group('baz', Group::DESC),
        ]);

        $expected = 'SELECT * FROM `test` GROUP BY `foo` ASC, `bar` ASC, `baz` DESC';
        $actual = $query->compile();

        $this->assertEquals($expected, $actual);
    }

    public function testCompileSelectHaving()
    {
        $expr = $this->createMock(ExprInterface::class);
        $expr->expects($this->once())->method('toString')->willReturn('foobar');

        $query = new SelectQuery(null, 'test', [], null, [], $expr);

        $expected = 'SELECT * FROM `test` HAVING foobar';
        $actual = $query->compile();

        $this->assertEquals($expected, $actual);
    }

    public function testCompileSelectOrder()
    {
        $query = new SelectQuery(null, 'test', [], null, [], null, [
            new Order('foo'),
            new Order('bar', Order::ASC),
            new Order('baz', Order::DESC),
        ]);

        $expected = 'SELECT * FROM `test` ORDER BY `foo` ASC, `bar` ASC, `baz` DESC';
        $actual = $query->compile();

        $this->assertEquals($expected, $actual);
    }

    public function testCompileSelectLimit()
    {
        $query = new SelectQuery(null, 'test', [], null, [], null, [], 10);

        $expected = 'SELECT * FROM `test` LIMIT 10';
        $actual = $query->compile();

        $this->assertEquals($expected, $actual);
    }

    public function testCompileSelectOffset()
    {
        $query = new SelectQuery(null, 'test', [], null, [], null, [], null, 5);

        $expected = 'SELECT * FROM `test` OFFSET 5';
        $actual = $query->compile();

        $this->assertEquals($expected, $actual);
    }

    public function testCompileSelectEverything()
    {
        $where = $this->createMock(ExprInterface::class);
        $where->expects($this->once())->method('toString')->willReturn('TEST_WHERE');

        $having = $this->createMock(ExprInterface::class);
        $having->expects($this->once())->method('toString')->willReturn('TEST_HAVING');

        $query = new SelectQuery(
            null,
            'test',
            ['foo', 'baz' => 'bar'],
            $where,
            [Group::by('foo')->asc()],
            $having,
            [Order::by('foo')->desc()],
            10,
            5
        );

        $expected = 'SELECT `foo`, `bar` AS `baz` FROM `test` WHERE TEST_WHERE GROUP BY `foo` ASC HAVING TEST_HAVING ORDER BY `foo` DESC LIMIT 10 OFFSET 5';
        $actual = $query->compile();

        $this->assertEquals($expected, $actual);
    }

    public function testCompileInvalidTable()
    {
        $query = new SelectQuery(null, '');

        try {
            $query->compile();
            $this->fail();
        } catch (Throwable $e) {
            $this->assertInstanceOf(QueryCompileException::class, $e);
            $this->assertSame($query, $e->getQuery());
        }
    }
}
