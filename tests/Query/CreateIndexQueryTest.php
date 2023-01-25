<?php

namespace RebelCode\Atlas\Test\Query;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Query;
use RebelCode\Atlas\DatabaseAdapter;
use RebelCode\Atlas\Exception\QueryCompileException;
use RebelCode\Atlas\Order;
use RebelCode\Atlas\Query\CreateIndexQuery;
use RebelCode\Atlas\Schema\Index;
use RebelCode\Atlas\Test\Helpers;
use Throwable;

class CreateIndexQueryTest extends TestCase
{
    public function testIsQuery()
    {
        $this->assertInstanceOf(Query::class, new CreateIndexQuery(null, 'foo', 'bar', new Index(false, [])));
    }

    public function testCtor()
    {
        $adapter = $this->createMock(DatabaseAdapter::class);
        $table = 'table';
        $name = 'index';
        $index = new Index(false, ['foo', 'bar']);

        $query = new CreateIndexQuery($adapter, $table, $name, $index);

        $this->assertSame($adapter, Helpers::property($query, 'adapter'));
        $this->assertSame($table, Helpers::property($query, 'table'));
        $this->assertSame($name, Helpers::property($query, 'name'));
        $this->assertSame($index, Helpers::property($query, 'index'));
    }

    public function testExec()
    {
        $adapter = $this->createMock(DatabaseAdapter::class);
        $table = 'table';
        $name = 'index';
        $index = new Index(false, ['foo', 'bar']);

        $query = new CreateIndexQuery($adapter, $table, $name, $index);

        $adapter->expects($this->once())->method('query')->willReturn(true);

        $this->assertTrue($query->exec());
    }

    public function testCompile()
    {
        $query = new CreateIndexQuery(
            null, 'test', 'my_index', new Index(false, [
                'foo' => Order::ASC,
                'bar' => Order::DESC,
            ])
        );

        $expected = 'CREATE INDEX `my_index` ON `test` (`foo` ASC, `bar` DESC)';

        $this->assertEquals($expected, $query->compile());
    }

    public function testCompileNoTable()
    {
        $query = new CreateIndexQuery(
            null, '', 'my_index', new Index(false, [
                'foo' => Order::ASC,
            ])
        );

        try {
            $query->compile();
            $this->fail('Expected exception to be thrown');
        } catch (Throwable $e) {
            $this->assertInstanceOf(QueryCompileException::class, $e);
            $this->assertSame($query, $e->getQuery());
        }
    }

    public function testCompileNoName()
    {
        $query = new CreateIndexQuery(
            null, 'test', '', new Index(false, [
                'foo' => Order::ASC,
            ])
        );

        try {
            $query->compile();
            $this->fail('Expected exception to be thrown');
        } catch (Throwable $e) {
            $this->assertInstanceOf(QueryCompileException::class, $e);
            $this->assertSame($query, $e->getQuery());
        }
    }

    public function testCompileIndexNoColumns()
    {
        $query = new CreateIndexQuery(null, 'test', 'my_index', new Index(false, []));

        try {
            $query->compile();
            $this->fail('Expected exception to be thrown');
        } catch (Throwable $e) {
            $this->assertInstanceOf(QueryCompileException::class, $e);
            $this->assertSame($query, $e->getQuery());
        }
    }
}
