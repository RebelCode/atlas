<?php

namespace RebelCode\Atlas\Test\Query;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Query;
use RebelCode\Atlas\DatabaseAdapter;
use RebelCode\Atlas\Exception\QueryCompileException;
use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Order;
use RebelCode\Atlas\Query\DeleteQuery;
use RebelCode\Atlas\Test\Helpers;
use Throwable;

class DeleteQueryTest extends TestCase
{
    public function testIsQuery()
    {
        $this->assertInstanceOf(Query::class, new DeleteQuery());
    }

    public function testCtor()
    {
        $adapter = $this->createMock(DatabaseAdapter::class);
        $from = 'foo';
        $where = $this->createMock(ExprInterface::class);
        $order = [Order::by('foo')];
        $limit = 1;

        $query = new DeleteQuery($adapter, $from, $where, $order, $limit);

        $this->assertEquals($from, Helpers::property($query, 'from'));
        $this->assertEquals($where, Helpers::property($query, 'where'));
        $this->assertEquals($order, Helpers::property($query, 'order'));
        $this->assertEquals($limit, Helpers::property($query, 'limit'));
    }

    public function testFrom()
    {
        $query = new DeleteQuery();
        $new = $query->from($from = 'foo');

        $this->assertNotSame($query, $new);
        $this->assertEquals($from, Helpers::property($new, 'from'));
    }

    public function testWhere()
    {
        $query = new DeleteQuery();
        $new = $query->where($where = $this->createMock(ExprInterface::class));

        $this->assertNotSame($query, $new);
        $this->assertSame($where, Helpers::property($new, 'where'));
    }

    public function testOrder()
    {
        $query = new DeleteQuery();
        $new = $query->orderBy($order = [Order::by('foo'), Order::by('bar')]);

        $this->assertNotSame($query, $new);
        $this->assertEquals($order, Helpers::property($new, 'order'));
    }

    public function testLimit()
    {
        $query = new DeleteQuery();
        $new = $query->limit($limit = 5);

        $this->assertNotSame($query, $new);
        $this->assertEquals($limit, Helpers::property($new, 'limit'));
    }

    public function testExec()
    {
        $adapter = $this->createMock(DatabaseAdapter::class);

        $query = new DeleteQuery($adapter, 'foo');

        $numRows = 123;
        $adapter->expects($this->once())->method('queryNumRows')->willReturn($numRows);

        $this->assertEquals($numRows, $query->exec());
    }

    public function testCompile()
    {
        $query = new DeleteQuery(null, 'test');

        $expected = 'DELETE FROM `test`';
        $actual = $query->compile();

        $this->assertEquals($expected, $actual);
    }

    public function testCompileWhere()
    {
        $where = $this->createMock(ExprInterface::class);
        $where->expects($this->once())->method('toString')->willReturn('TEST_EXPR');

        $query = new DeleteQuery(null, 'test', $where);

        $expected = 'DELETE FROM `test` WHERE TEST_EXPR';
        $actual = $query->compile();

        $this->assertEquals($expected, $actual);
    }

    public function testCompileOrderNoWhere()
    {
        $query = new DeleteQuery(null, 'test', null, [
            new Order('foo'),
            new Order('bar', Order::ASC),
            new Order('baz', Order::DESC),
        ]);

        $expected = 'DELETE FROM `test`';
        $actual = $query->compile();

        $this->assertEquals($expected, $actual);
    }

    public function testCompileOrderWithWhere()
    {
        $where = $this->createMock(ExprInterface::class);
        $where->expects($this->once())->method('toString')->willReturn('TEST_EXPR');

        $query = new DeleteQuery(null, 'test', $where, [
            new Order('foo'),
            new Order('bar', Order::ASC),
            new Order('baz', Order::DESC),
        ]);

        $expected = 'DELETE FROM `test` WHERE TEST_EXPR ORDER BY `foo` ASC, `bar` ASC, `baz` DESC';
        $actual = $query->compile();

        $this->assertEquals($expected, $actual);
    }

    public function testCompileLimit()
    {
        $query = new DeleteQuery(null, 'test', null, [], 10);

        $expected = 'DELETE FROM `test` LIMIT 10';
        $actual = $query->compile();

        $this->assertEquals($expected, $actual);
    }

    public function testCompileWhereOrderLimit()
    {
        $where = $this->createMock(ExprInterface::class);
        $where->expects($this->once())->method('toString')->willReturn('TEST_EXPR');

        $query = new DeleteQuery(null, 'test', $where, [
            new Order('foo', Order::ASC),
            new Order('bar', Order::DESC),
        ], 10);

        $expected = 'DELETE FROM `test` WHERE TEST_EXPR ORDER BY `foo` ASC, `bar` DESC LIMIT 10';
        $actual = $query->compile();

        $this->assertEquals($expected, $actual);
    }

    public function testCompileInvalidTable()
    {
        $query = new DeleteQuery(null, '');

        try {
            $query->compile();
            $this->fail();
        } catch (Throwable $e) {
            $this->assertInstanceOf(QueryCompileException::class, $e);
            $this->assertSame($query, $e->getQuery());
        }
    }
}
