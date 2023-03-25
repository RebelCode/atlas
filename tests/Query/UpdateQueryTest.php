<?php

namespace RebelCode\Atlas\Test\Query;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\DatabaseAdapter;
use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Order;
use RebelCode\Atlas\Query;
use RebelCode\Atlas\Query\UpdateQuery;
use RebelCode\Atlas\Test\Helpers\ReflectionHelper;

class UpdateQueryTest extends TestCase
{
    use ReflectionHelper;

    public function testIsQuery()
    {
        $this->assertInstanceOf(Query::class, new UpdateQuery());
    }

    public function testCtor()
    {
        $adapter = $this->createMock(DatabaseAdapter::class);
        $table = 'foo';
        $assign = ['name' => 'Alice'];
        $where = $this->createMock(ExprInterface::class);
        $order = [Order::by('foo')];
        $limit = 123;

        $query = new UpdateQuery($adapter, $table, $assign, $where, $order, $limit);
        $query = $this->expose($query);

        $this->assertSame($table, $query->table);
        $this->assertSame($assign, $query->assign);
        $this->assertSame($where, $query->where);
        $this->assertSame($order, $query->order);
        $this->assertSame($limit, $query->limit);
    }

    public function testTable()
    {
        $query = new UpdateQuery();
        $new = $query->table($table = 'foo');

        $this->assertNotSame($query, $new);
        $this->assertSame($table, $this->expose($new)->table);
    }

    public function testSet()
    {
        $query = new UpdateQuery();
        $new = $query->set($assign = ['name' => 'Alice']);

        $this->assertNotSame($query, $new);
        $this->assertSame($assign, $this->expose($new)->assign);
    }

    public function testWhere()
    {
        $query = new UpdateQuery();
        $new = $query->where($where = $this->createMock(ExprInterface::class));

        $this->assertNotSame($query, $new);
        $this->assertSame($where, $this->expose($new)->where);
    }

    public function testOrder()
    {
        $query = new UpdateQuery();
        $new = $query->orderBy($order = [Order::by('foo')]);

        $this->assertNotSame($query, $new);
        $this->assertSame($order, $this->expose($new)->order);
    }

    public function testExec()
    {
        $adapter = $this->createMock(DatabaseAdapter::class);
        $query = new UpdateQuery($adapter, 'foo', ['name' => 'Alice']);

        $numRows = 123;
        $adapter->expects($this->once())->method('queryNumRows')->willReturn($numRows);

        $this->assertEquals($numRows, $query->exec());
    }
}
