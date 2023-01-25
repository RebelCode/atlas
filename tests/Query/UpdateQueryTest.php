<?php

namespace RebelCode\Atlas\Test\Query;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Query;
use RebelCode\Atlas\DatabaseAdapter;
use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Order;
use RebelCode\Atlas\Query\UpdateQuery;
use RebelCode\Atlas\Test\Helpers;

class UpdateQueryTest extends TestCase
{
    public function testIsQuery()
    {
        $this->assertInstanceOf(Query::class, new UpdateQuery());
    }

    public function testCtor()
    {
        $adapter = $this->createMock(DatabaseAdapter::class);
        $table = 'foo';
        $set = ['name' => 'Alice'];
        $where = $this->createMock(ExprInterface::class);
        $order = [Order::by('foo')];
        $limit = 123;

        $query = new UpdateQuery($adapter, $table, $set, $where, $order, $limit);

        $this->assertSame($table, Helpers::property($query, 'table'));
        $this->assertSame($set, Helpers::property($query, 'set'));
        $this->assertSame($where, Helpers::property($query, 'where'));
        $this->assertSame($order, Helpers::property($query, 'order'));
        $this->assertSame($limit, Helpers::property($query, 'limit'));
    }

    public function testTable()
    {
        $query = new UpdateQuery();
        $new = $query->table($table = 'foo');

        $this->assertNotSame($query, $new);
        $this->assertSame($table, Helpers::property($new, 'table'));
    }

    public function testSet()
    {
        $query = new UpdateQuery();
        $new = $query->set($set = ['name' => 'Alice']);

        $this->assertNotSame($query, $new);
        $this->assertSame($set, Helpers::property($new, 'set'));
    }

    public function testWhere()
    {
        $query = new UpdateQuery();
        $new = $query->where($where = $this->createMock(ExprInterface::class));

        $this->assertNotSame($query, $new);
        $this->assertSame($where, Helpers::property($new, 'where'));
    }

    public function testOrder()
    {
        $query = new UpdateQuery();
        $new = $query->orderBy($order = [Order::by('foo')]);

        $this->assertNotSame($query, $new);
        $this->assertSame($order, Helpers::property($new, 'order'));
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
