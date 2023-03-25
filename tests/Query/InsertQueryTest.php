<?php

namespace RebelCode\Atlas\Test\Query;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\DatabaseAdapter;
use RebelCode\Atlas\Exception\QuerySqlException;
use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Query;
use RebelCode\Atlas\Query\InsertQuery;
use RebelCode\Atlas\Test\Helpers\ReflectionHelper;

class InsertQueryTest extends TestCase
{
    use ReflectionHelper;

    public function testIsQuery()
    {
        $this->assertInstanceOf(Query::class, new InsertQuery());
    }

    public function testCtor()
    {
        $adapter = $this->createMock(DatabaseAdapter::class);
        $into = 'foo';
        $columns = ['foo', 'bar'];
        $values = [['foo' => 'bar'], ['baz' => 'qux']];
        $onDuplicate = ['a' => 1, 'b' => 2];

        $query = new InsertQuery($adapter, $into, $columns, $values, $onDuplicate);

        $this->assertEquals($into, $this->expose($query)->into);
        $this->assertEquals($columns, $this->expose($query)->columns);
        $this->assertEquals($values, $this->expose($query)->values);
        $this->assertEquals($onDuplicate, $this->expose($query)->assign);
    }

    public function testInto()
    {
        $query = new InsertQuery();
        $new = $query->into($table = 'foo');

        $this->assertNotSame($query, $new);
        $this->assertEquals($table, $this->expose($new)->into);
    }

    public function testColumns()
    {
        $query = new InsertQuery();
        $new = $query->columns($columns = ['foo', 'bar']);

        $this->assertNotSame($query, $new);
        $this->assertEquals($columns, $this->expose($new)->columns);
    }

    public function testValues()
    {
        $query = new InsertQuery();
        $new = $query->values($values = [['foo' => 'bar'], ['baz' => 'qux']]);

        $this->assertNotSame($query, $new);
        $this->assertEquals($values, $this->expose($new)->values);
    }

    public function testOnDuplicate()
    {
        $query = new InsertQuery();
        $new = $query->onDuplicateKey($assign = ['a' => 1, 'b' => 2]);

        $this->assertNotSame($query, $new);
        $this->assertEquals($assign, $this->expose($new)->assign);
    }

    public function provideExecNumRowsAffected()
    {
        return [
            '0' => [0, null],
            '1' => [1, 123],
        ];
    }

    /** @dataProvider provideExecNumRowsAffected */
    public function testExec(int $numRows, ?int $insertId)
    {
        $adapter = $this->createMock(DatabaseAdapter::class);

        $query = new InsertQuery($adapter, 'foo', ['name', 'age'], [['Alice', 20]]);

        $adapter->expects($this->once())->method('queryNumRows')->willReturn($numRows);
        $adapter->method('getInsertId')->willReturn($insertId);

        $this->assertEquals($insertId, $query->exec());
    }

    public function testCompile()
    {
        $query = new InsertQuery(null, 'foo', ['a', 'b', 'c'], [[1, 2, 3]]);

        $expected = 'INSERT INTO `foo` (`a`, `b`, `c`) VALUES (1, 2, 3)';

        $this->assertEquals($expected, $query->toSql());
    }

    public function testCompileStrings()
    {
        $query = new InsertQuery(null, 'foo', ['a', 'b', 'c'], [['hey', 'there', 'buddy']]);

        $expected = "INSERT INTO `foo` (`a`, `b`, `c`) VALUES ('hey', 'there', 'buddy')";

        $this->assertEquals($expected, $query->toSql());
    }

    public function testCompileMultipleValues()
    {
        $query = new InsertQuery(null, 'foo', ['a', 'b', 'c'], [
            [1, 2, 3],
            ['hey', 'there', 'buddy'],
            [4, 5, 6],
        ]);

        $expected = "INSERT INTO `foo` (`a`, `b`, `c`) VALUES (1, 2, 3), ('hey', 'there', 'buddy'), (4, 5, 6)";

        $this->assertEquals($expected, $query->toSql());
    }

    public function testCompileInvalidTableName()
    {
        $query = new InsertQuery(null, '', ['a', 'b', 'c'], [[1, 2, 3]]);

        try {
            $query->toSql();
            $this->fail('Expected an exception to be thrown');
        } catch (QuerySqlException $e) {
            $this->assertSame($query, $e->getQuery(), "The exception's query is invalid");
        }
    }

    public function testCompileOnDuplicateKey()
    {
        $bExpr = $this->createMock(ExprInterface::class);
        $bExpr->expects($this->once())->method('toSql')->willReturn('BBB');

        $insert = new InsertQuery(null, 'foo', ['a', 'b', 'c'], [[1, 2, 3]], [
            'a' => 'A',
            'b' => $bExpr,
        ]);

        $actual = $insert->toSql();
        $expected = "INSERT INTO `foo` (`a`, `b`, `c`) VALUES (1, 2, 3) ON DUPLICATE KEY UPDATE `a` = 'A', `b` = BBB";

        $this->assertEquals($expected, $actual);
    }
}
