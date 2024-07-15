<?php

namespace RebelCode\Atlas\Test\Query;

use PHPUnit\Framework\MockObject\MockClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\DatabaseAdapter;
use RebelCode\Atlas\DataSource;
use RebelCode\Atlas\Expression\ColumnTerm;
use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Expression\VarExpr;
use RebelCode\Atlas\Group;
use RebelCode\Atlas\Join;
use RebelCode\Atlas\Order;
use RebelCode\Atlas\Query;
use RebelCode\Atlas\Query\SelectQuery;
use RebelCode\Atlas\Test\Helpers\ReflectionHelper;

use function RebelCode\Atlas\andAll;
use function RebelCode\Atlas\arg;
use function RebelCode\Atlas\col;
use function RebelCode\Atlas\table;

class SelectQueryTest extends TestCase
{
    use ReflectionHelper;

    protected function createDataSource(?string $name = null): DataSource
    {
        $source = $this->createMock(DataSource::class);

        if ($name !== null) {
            $source->method('compileSource')->willReturn($name);
        }

        return $source;
    }

    public function testIsQuery()
    {
        $this->assertInstanceOf(Query::class, new SelectQuery());
    }

    public function testCtor()
    {
        $adapter = $this->createMock(DatabaseAdapter::class);
        $from = $this->createDataSource();
        $columns = ['a', 'b'];
        $where = $this->createMock(ExprInterface::class);
        $orderBy = [Order::by('foo')];
        $limit = 1;
        $offset = 5;

        $query = new SelectQuery($adapter, $from, $columns, $where, $orderBy, $limit, $offset);

        $this->assertSame($from, $this->expose($query)->source);
        $this->assertSame($columns, $this->expose($query)->columns);
        $this->assertSame($where, $this->expose($query)->where);
        $this->assertSame($orderBy, $this->expose($query)->order);
        $this->assertSame($limit, $this->expose($query)->limit);
        $this->assertSame($offset, $this->expose($query)->offset);
    }

    public function testFrom()
    {
        $query = new SelectQuery();

        $source = $this->createDataSource();
        $new = $query->from($source);

        $this->assertNotSame($query, $new);
        $this->assertSame($source, $this->expose($new)->source);
    }

    public function testCalcFoundRows()
    {
        $query = new SelectQuery();

        $new1 = $query->calcFoundRows();

        $this->assertNotSame($query, $new1);
        $this->assertTrue($this->expose($new1)->calcFoundRows);

        $new2 = $query->calcFoundRows(false);

        $this->assertNotSame($query, $new2);
        $this->assertNotSame($new1, $new2);
        $this->assertFalse($this->expose($new2)->calcFoundRows);
    }

    public function testColumns()
    {
        $query = new SelectQuery();
        $new = $query->columns(['a', 'b']);

        $this->assertNotSame($query, $new);
        $this->assertSame(['a', 'b'], $this->expose($new)->columns);
    }

    public function testWhere()
    {
        $query = new SelectQuery();
        $new = $query->where($where = $this->createMock(ExprInterface::class));

        $this->assertNotSame($query, $new);
        $this->assertSame($where, $this->expose($new)->where);
    }

    public function testGroupBy()
    {
        $query = new SelectQuery();
        $new = $query->groupBy($group = [Group::by('foo')]);

        $this->assertNotSame($query, $new);
        $this->assertSame($group, $this->expose($new)->groups);
    }

    public function testHaving()
    {
        $query = new SelectQuery();
        $new = $query->having($having = $this->createMock(ExprInterface::class));

        $this->assertNotSame($query, $new);
        $this->assertSame($having, $this->expose($new)->having);
    }

    public function testOrderBy()
    {
        $query = new SelectQuery();
        $new = $query->orderBy($order = [Order::by('foo')]);

        $this->assertNotSame($query, $new);
        $this->assertSame($order, $this->expose($new)->order);
    }

    public function testLimit()
    {
        $query = new SelectQuery();
        $new = $query->limit(1);

        $this->assertNotSame($query, $new);
        $this->assertSame(1, $this->expose($new)->limit);
    }

    public function testOffset()
    {
        $query = new SelectQuery();
        $new = $query->offset(5);

        $this->assertNotSame($query, $new);
        $this->assertSame(5, $this->expose($new)->offset);
    }

    public function testPage()
    {
        $query = new SelectQuery();
        $new = $query->page(5, 15);

        $this->assertNotSame($query, $new);
        $this->assertSame(15, $this->expose($new)->limit);
        $this->assertSame(60, $this->expose($new)->offset);
    }

    public function testPageNullLimit()
    {
        $query = new SelectQuery();
        $new = $query->page(5, null);

        $this->assertNotSame($query, $new);
        $this->assertNull($this->expose($new)->limit);
        $this->assertNull($this->expose($new)->offset);
    }

    public function testExec()
    {
        $adapter = $this->createMock(DatabaseAdapter::class);
        $source = $this->createDataSource();

        $query = new SelectQuery($adapter, $source);

        $expected = [
            ['id' => 1, 'name' => 'Alice'],
            ['id' => 2, 'name' => 'Bob'],
            ['id' => 3, 'name' => 'Charlie'],
        ];

        $adapter->expects($this->once())->method('queryResults')->willReturn($expected);

        $this->assertEquals($expected, $query->exec());
    }

    public function testCompileNoTable()
    {
        $expr = $this->createMock(ExprInterface::class);
        $expr->expects($this->once())->method('toSql')->willReturn('1 + 1');
        $query = new SelectQuery(null, null, [$expr]);

        $expected = 'SELECT 1 + 1';
        $actual = $query->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function testCompileSelectAll()
    {
        $source = $this->createDataSource('table');
        $query = new SelectQuery(null, $source);

        $expected = 'SELECT * FROM table';
        $actual = $query->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function testCompileSelectColumns()
    {
        $source = $this->createDataSource('table');
        $query = new SelectQuery(null, $source, ['foo', 'bar']);

        $expected = 'SELECT `foo`, `bar` FROM table';
        $actual = $query->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function testCompileSelectWithAlias()
    {
        $source = $this->createDataSource('table');
        $query = new SelectQuery(null, $source, ['bar' => 'foo', 'baz', 'quuz' => 'qux']);

        $expected = 'SELECT `foo` AS `bar`, `baz`, `qux` AS `quuz` FROM table';
        $actual = $query->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function testCompileSelectColumnsWithDistinct()
    {
        $source = $this->createDataSource('table');
        $query = new SelectQuery(null, $source, [
            (new ColumnTerm(null, 'foo'))->distinct()->fn('COUNT'),
            'bar',
        ]);

        $expected = 'SELECT COUNT(DISTINCT `foo`), `bar` FROM table';
        $actual = $query->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function testCompileSelectExpr()
    {
        $source = $this->createDataSource('table');

        $expr = $this->createMock(ExprInterface::class);
        $expr->expects($this->once())->method('toSql')->willReturn('foobar');

        $query = new SelectQuery(null, $source, [$expr]);

        $expected = 'SELECT foobar FROM table';
        $actual = $query->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function testCompileSelectExprWithAlias()
    {
        $source = $this->createDataSource('table');

        $expr = $this->createMock(ExprInterface::class);
        $expr->expects($this->once())->method('toSql')->willReturn('foobar');

        $query = new SelectQuery(null, $source, ['baz' => $expr]);

        $expected = 'SELECT foobar AS `baz` FROM table';
        $actual = $query->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function testCompileSelectWithSubQuery()
    {
        $source = $this->createDataSource('table');

        $innerWhere = $this->createMock(ExprInterface::class);
        $innerWhere->expects($this->once())->method('toSql')->willReturn('someWhere()');
        $innerQuery = new SelectQuery(null, $source, ['foo', 'bar'], $innerWhere);

        $query = new SelectQuery(null, $innerQuery);

        $expected = 'SELECT * FROM (SELECT `foo`, `bar` FROM table WHERE someWhere())';

        $this->assertEquals($expected, $query->toSql());
    }

    public function testCompileSelectWhere()
    {
        $source = $this->createDataSource('table');

        $expr = $this->createMock(ExprInterface::class);
        $expr->expects($this->once())->method('toSql')->willReturn('foobar');

        $query = new SelectQuery(null, $source, [], $expr);

        $expected = 'SELECT * FROM table WHERE foobar';
        $actual = $query->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function testCompileSelectGroupBy()
    {
        $source = $this->createDataSource('table');

        $query = (new SelectQuery(null, $source))->groupBy([
            new Group('foo'),
            new Group('bar', Group::ASC),
            new Group('baz', Group::DESC),
        ]);

        $expected = 'SELECT * FROM table GROUP BY `foo` ASC, `bar` ASC, `baz` DESC';
        $actual = $query->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function testCompileSelectHaving()
    {
        $source = $this->createDataSource('table');

        $expr = $this->createMock(ExprInterface::class);
        $expr->expects($this->once())->method('toSql')->willReturn('foobar');

        $query = (new SelectQuery(null, $source))->having($expr);

        $expected = 'SELECT * FROM table HAVING foobar';
        $actual = $query->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function testCompileSelectOrder()
    {
        $source = $this->createDataSource('table');
        $query = new SelectQuery(null, $source, [], null, [
            new Order('foo'),
            new Order('bar', Order::ASC),
            new Order('baz', Order::DESC),
        ]);

        $expected = 'SELECT * FROM table ORDER BY `foo` ASC, `bar` ASC, `baz` DESC';
        $actual = $query->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function testCompileSelectLimit()
    {
        $source = $this->createDataSource('table');
        $query = new SelectQuery(null, $source, [], null, [], 10);

        $expected = 'SELECT * FROM table LIMIT 10';
        $actual = $query->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function testCompileSelectLimitAndOffset()
    {
        $source = $this->createDataSource('table');
        $query = new SelectQuery(null, $source, [], null, [], 10, 5);

        $expected = 'SELECT * FROM table LIMIT 10 OFFSET 5';
        $actual = $query->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function testCompileSelectOffsetNoLimit()
    {
        $source = $this->createDataSource('table');
        $query = new SelectQuery(null, $source, [], null, [], null, 5);

        $expected = 'SELECT * FROM table';
        $actual = $query->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function testCompileJoins()
    {
        $users = $this->createDataSource('users');
        $posts = $this->createDataSource('posts');
        $comments = $this->createDataSource('comments');

        $expr = $this->createMock(ExprInterface::class);
        $expr->expects($this->once())->method('toSql')->willReturn('foobar');

        $query = new SelectQuery(null, $users);
        $query = $query->join([
            new Join(Join::INNER, $posts, $expr),
            new Join(Join::CROSS, $comments),
        ]);

        $expected = 'SELECT * FROM users INNER JOIN posts ON foobar CROSS JOIN comments';
        $actual = $query->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function testCompileSelectEverything()
    {
        $source = $this->createDataSource('table');

        $where = $this->createMock(ExprInterface::class);
        $where->expects($this->once())->method('toSql')->willReturn('TEST_WHERE');

        $having = $this->createMock(ExprInterface::class);
        $having->expects($this->once())->method('toSql')->willReturn('TEST_HAVING');

        $joinOn = $this->createMock(ExprInterface::class);
        $joinOn->expects($this->once())->method('toSql')->willReturn('TEST_JOIN');

        $query = new SelectQuery(
            null,
            $source,
            ['foo', 'baz' => 'bar'],
            $where,
            [Order::by('foo')->desc()],
            10,
            5
        );
        $query = $query
            ->groupBy([Group::by('foo')->asc()])
            ->having($having)
            ->join([
                new Join(Join::INNER, $source, $joinOn),
            ]);

        $expected = str_replace("\n", " ", <<<SQL
        SELECT `foo`, `bar` AS `baz`
        FROM table
        INNER JOIN table ON TEST_JOIN
        WHERE TEST_WHERE
        GROUP BY `foo` ASC
        HAVING TEST_HAVING
        ORDER BY `foo` DESC
        LIMIT 10
        OFFSET 5
        SQL);

        $actual = $query->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function testExists()
    {
        $query = new SelectQuery();
        $query = $query->from(table('test'))->where(col('role')->eq('admin'));
        $exists = $query->exists();

        $this->assertEquals("EXISTS(SELECT * FROM `test` WHERE (`role` = 'admin'))", $exists->toSql());
    }

    public function testNotExists()
    {
        $query = new SelectQuery();
        $query = $query->from(table('test'))->where(col('role')->eq('admin'));
        $exists = $query->notExists();

        $this->assertEquals("NOT EXISTS(SELECT * FROM `test` WHERE (`role` = 'admin'))", $exists->toSql());
    }

    public function testWithVars()
    {
        $args = ['foo' => 'VALUE1', 'bar' => 'VALUE2'];

        $adapter = $this->createMock(DatabaseAdapter::class);
        $adapter->expects($this->exactly(2))
            ->method('getPlaceholder')
            ->withConsecutive(['bar', 'VALUE2'], ['foo', 'VALUE1'])
            ->willReturnOnConsecutiveCalls('<bar>', '<foo>');

        $adapter->expects($this->once())
            ->method('queryResults')
            ->with(
                'SELECT * FROM `test` WHERE ((`name` = <bar>) AND (`age` = <foo>))',
                ['VALUE2', 'VALUE1']
            );

        $query = (new SelectQuery($adapter))
            ->from(table('test'))
            ->where(andAll([
                col('name')->eq(arg('bar')),
                col('age')->eq(arg('foo')),
            ]));

        $query->exec($args);
    }
}
