<?php

namespace RebelCode\Atlas\Test\Query;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\DatabaseAdapter;
use RebelCode\Atlas\Exception\QuerySqlException;
use RebelCode\Atlas\Query;
use RebelCode\Atlas\Query\DropTableQuery;
use RebelCode\Atlas\Test\Helpers\ReflectionHelper;

class DropTableQueryTest extends TestCase
{
    use ReflectionHelper;

    public function testIsQuery()
    {
        $this->assertInstanceOf(Query::class, new DropTableQuery(null, 'foo'));
    }

    public function testCtor()
    {
        $adapter = $this->createMock(DatabaseAdapter::class);
        $table = 'foo';
        $ifExists = true;
        $cascade = true;
        $query = new DropTableQuery($adapter, $table, $ifExists, $cascade);

        $this->assertSame($adapter, $this->expose($query)->adapter);
        $this->assertSame($table, $this->expose($query)->table);
        $this->assertSame($ifExists, $this->expose($query)->ifExists);
        $this->assertSame($cascade, $this->expose($query)->cascade);
    }

    public function testExec()
    {
        $adapter = $this->createMock(DatabaseAdapter::class);
        $query = new DropTableQuery($adapter, 'foo');

        $adapter->expects($this->once())->method('query')->willReturn(true);

        $this->assertTrue($query->exec());
    }

    public function testCompile()
    {
        $query = new DropTableQuery(null, 'test');

        $expected = 'DROP TABLE `test`';

        $this->assertEquals($expected, $query->toSql());
    }

    public function testCompileIfExists()
    {
        $query = new DropTableQuery(null, 'test', true);

        $expected = 'DROP TABLE IF EXISTS `test`';

        $this->assertEquals($expected, $query->toSql());
    }

    public function testCompileCascade()
    {
        $query = new DropTableQuery(null, 'test', false, true);

        $expected = 'DROP TABLE `test` CASCADE';

        $this->assertEquals($expected, $query->toSql());
    }

    public function testCompileIfExistsCascade()
    {
        $query = new DropTableQuery(null, 'test', true, true);

        $expected = 'DROP TABLE IF EXISTS `test` CASCADE';

        $this->assertEquals($expected, $query->toSql());
    }

    public function testCompileInvalidTableName()
    {
        $query = new DropTableQuery(null, '');

        try {
            $query->toSql();
            $this->fail('Expected an exception to be thrown');
        } catch (QuerySqlException $e) {
            $this->assertSame($query, $e->getQuery(), 'Exception query is invalid');
        }
    }
}
