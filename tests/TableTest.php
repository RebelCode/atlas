<?php

namespace RebelCode\Atlas\Test;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\DatabaseAdapter;
use RebelCode\Atlas\Expression\BinaryExpr;
use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Expression\Term;
use RebelCode\Atlas\Group;
use RebelCode\Atlas\Order;
use RebelCode\Atlas\Query\CreateIndexQuery;
use RebelCode\Atlas\Query\CreateTableQuery;
use RebelCode\Atlas\Schema;
use RebelCode\Atlas\Table;

class TableTest extends TestCase
{
    public function testConstructor()
    {
        $adapter = $this->createMock(DatabaseAdapter::class);
        $schema = $this->createMock(Schema::class);

        $table = new Table($name = 'test', $schema, $adapter);

        $this->assertEquals($name, $table->getName());
        $this->assertSame($schema, $table->getSchema());
        $this->assertSame($adapter, $table->getDbAdapter());
        $this->assertNull($table->getWhere());
        $this->assertEquals([], $table->getOrder());
    }

    public function testWhere()
    {
        $table = new Table('test', $this->createMock(Schema::class));

        $clone = $table->where($where = $this->createMock(ExprInterface::class));

        $this->assertSame($where, $clone->getWhere());
        $this->assertNotSame($table, $clone, 'Clone is the same as original instance');
        $this->assertNull($table->getWhere(), 'Original instance should not be modified');
        $this->assertEquals($table->getName(), $clone->getName(), 'Clone should keep the original table name');
        $this->assertSame($table->getSchema(), $clone->getSchema(), 'Clone should keep the original table schema');
    }

    public function testWhereAnd()
    {
        $table = new Table('test', $this->createMock(Schema::class));

        $expr1 = $this->createMock(ExprInterface::class);
        $expr2 = $this->createMock(ExprInterface::class);
        $expected = $this->createMock(BinaryExpr::class);

        $expr1->expects($this->once())->method('and')->with($expr2)->willReturn($expected);

        $clone1 = $table->where($expr1);
        $clone2 = $clone1->where($expr2);

        $this->assertNull($table->getWhere(), 'Original table should not be modified');
        $this->assertSame($expr1, $clone1->getWhere(), 'First clone should not be modified');
        $this->assertEquals($expected, $clone2->getWhere(), 'Final clone does not have expected expression');
    }

    public function testWhereOr()
    {
        $table = new Table('test', $this->createMock(Schema::class));

        $expr1 = $this->createMock(ExprInterface::class);
        $expr2 = $this->createMock(ExprInterface::class);
        $expected = $this->createMock(BinaryExpr::class);

        $expr1->expects($this->once())->method('or')->with($expr2)->willReturn($expected);

        $clone1 = $table->where($expr1);
        $clone2 = $clone1->orWhere($expr2);

        $this->assertNull($table->getWhere(), 'Original table should not be modified');
        $this->assertSame($expr1, $clone1->getWhere(), 'First clone should not be modified');
        $this->assertEquals($expected, $clone2->getWhere(), 'Final clone does not have expected expression');
    }

    public function testColumn()
    {
        $table = new Table('test');

        $term = $table->column($value = 'foobar');

        $this->assertEquals($value, $term->getValue());
        $this->assertEquals(Term::COLUMN, $term->getType());
    }

    public function testColumnExistsWithSchema()
    {
        $schema = $this->createMock(Schema::class);
        $schema->method('getColumns')->willReturn([
            'foo' => new Schema\Column(''),
            'bar' => new Schema\Column(''),
        ]);

        $table = new Table('test', $schema);

        $term = $table->column($value = 'foo');

        $this->assertEquals($value, $term->getValue());
        $this->assertEquals(Term::COLUMN, $term->getType());
    }

    public function testColumnNotExistsWithSchema()
    {
        $schema = $this->createMock(Schema::class);
        $schema->method('getColumns')->willReturn([
            'foo' => new Schema\Column(''),
            'bar' => new Schema\Column(''),
        ]);

        $table = new Table('test', $schema);

        try {
            $table->column('oopsie');
            $this->fail('Expected exception to be thrown');
        } catch (\Throwable $e) {
            $this->assertInstanceOf(\DomainException::class, $e);
        }
    }

    public function testOrderBy()
    {
        $table = new Table('test');

        $clone = $table->orderBy(
            $order = [
                $this->createMock(Order::class),
                $this->createMock(Order::class),
            ]
        );

        $this->assertSame($order, $clone->getOrder());
    }

    public function testOrderByTwice()
    {
        $table = new Table('test');

        $clone = $table->orderBy(
            $order1 = [
                $this->createMock(Order::class),
                $this->createMock(Order::class),
            ]
        );

        $clone2 = $clone->orderBy(
            $order2 = [
                $this->createMock(Order::class),
                $this->createMock(Order::class),
            ]
        );

        $this->assertSame(array_merge($order1, $order2), $clone2->getOrder());
    }

    public function provideDataForCreate(): array
    {
        return [
            'no collate' => [false, null],
            'with collate' => [false, 'utf8'],
            'if not exists, no collate' => [true, null],
            'if not exists, with collate' => [true, 'utf8'],
        ];
    }

    /** @dataProvider provideDataForCreate */
    public function testCreate(bool $ifNotExists, ?string $collate)
    {
        $table = new Table($name = 'test', $schema = $this->createMock(Schema::class));
        $queries = $table->create($ifNotExists, $collate);

        $this->assertCount(1, $queries);
        $this->assertInstanceOf(CreateTableQuery::class, $query = $queries[0]);
        $this->assertEquals($name, Helpers::property($query, 'name'));
        $this->assertEquals($ifNotExists, Helpers::property($query, 'ifNotExists'));
        $this->assertEquals($collate, Helpers::property($query, 'collate'));
        $this->assertSame($schema, Helpers::property($query, 'schema'));
    }

    public function testCreateWithIndexes()
    {
        $schema = new Schema([], [], [], [
            'index1' => $index1 = new Schema\Index(false, []),
            'index2' => $index2 = new Schema\Index(false, []),
        ]);

        $table = new Table('test', $schema);
        $queries = $table->create();

        $this->assertCount(3, $queries);

        $this->assertInstanceOf(CreateIndexQuery::class, $queries[1]);
        $this->assertInstanceOf(CreateIndexQuery::class, $queries[2]);
        $this->assertEquals('test', Helpers::property($queries[1], 'table'));
        $this->assertEquals('test', Helpers::property($queries[2], 'table'));
        $this->assertEquals('index1', Helpers::property($queries[1], 'name'));
        $this->assertEquals('index2', Helpers::property($queries[2], 'name'));
        $this->assertEquals($index1, Helpers::property($queries[1], 'index'));
        $this->assertEquals($index2, Helpers::property($queries[2], 'index'));
    }

    public function provideDataForDrop(): array
    {
        return [
            'cascade' => [false, true],
            'no cascade' => [false, false],
            'if exists, cascade' => [true, true],
            'if exists, no cascade' => [true, false],
        ];
    }

    /** @dataProvider provideDataForDrop */
    public function testDrop(bool $ifExists, bool $cascade)
    {
        $table = new Table($name = 'test', $this->createMock(Schema::class));
        $query = $table->drop($ifExists, $cascade);

        $this->assertEquals($name, Helpers::property($query, 'table'));
        $this->assertEquals($ifExists, Helpers::property($query, 'ifExists'));
        $this->assertEquals($cascade, Helpers::property($query, 'cascade'));
    }

    public function testSelect()
    {
        $table = new Table($name = 'test');

        $columns = ['foo', 'bar'];
        $where = $this->createMock(ExprInterface::class);
        $group = [Group::by('foo')];
        $having = $this->createMock(ExprInterface::class);
        $order = [Order::by('foo')];
        $limit = 19;
        $offset = 4;

        $query = $table->select($columns, $where, $group, $having, $order, $limit, $offset);

        $this->assertEquals($name, Helpers::property($query, 'from'));
        $this->assertEquals($columns, Helpers::property($query, 'columns'));
        $this->assertEquals($where, Helpers::property($query, 'where'));
        $this->assertEquals($group, Helpers::property($query, 'group'));
        $this->assertEquals($having, Helpers::property($query, 'having'));
        $this->assertEquals($order, Helpers::property($query, 'order'));
        $this->assertEquals($limit, Helpers::property($query, 'limit'));
        $this->assertEquals($offset, Helpers::property($query, 'offset'));
    }

    public function testSelectWithWhereState()
    {
        $where = $this->createMock(ExprInterface::class);

        $table = new Table('test');
        $table = $table->where($where);

        $query = $table->select();

        $this->assertEquals($where, Helpers::property($query, 'where'));
    }

    public function testSelectWithWhereStateAndArg()
    {
        $whereState = $this->createMock(ExprInterface::class);
        $whereArg = $this->createMock(ExprInterface::class);
        $whereFinal = $this->createMock(BinaryExpr::class);

        $whereState->expects($this->once())->method('and')->with($whereArg)->willReturn($whereFinal);

        $table = new Table('test');
        $table = $table->where($whereState);

        $query = $table->select([], $whereArg);

        $this->assertEquals($whereFinal, Helpers::property($query, 'where'));
    }

    public function testSelectWithOrderState()
    {
        $order = [Order::by('foo'), Order::by('bar')];

        $table = new Table('test');
        $table = $table->orderBy($order);

        $query = $table->select();

        $this->assertEquals($order, Helpers::property($query, 'order'));
    }

    public function testSelectWithOrderStateAndArg()
    {
        $order1 = Order::by('foo');
        $order2 = Order::by('bar');
        $order3 = Order::by('baz');
        $order4 = Order::by('qux');

        $table = new Table('test');
        $table = $table->orderBy([$order1, $order2]);

        $query = $table->select([], null, [], null, [$order3, $order4]);

        $this->assertEquals([$order1, $order2, $order3, $order4], Helpers::property($query, 'order'));
    }

    public function testInsertColumns()
    {
        $values = [
            ['a' => 1, 'b' => 2, 'c' => 3],
            ['d' => 4, 'e' => 5, 'f' => 6],
        ];

        $assignList = [
            'a' => 'A',
            'b' => $this->createMock(ExprInterface::class),
            'c' => 88.8,
        ];

        $table = new Table('test');
        $query = $table->insert($values, $assignList);

        $this->assertEquals(['a', 'b', 'c'], Helpers::property($query, 'columns'));
        $this->assertEquals($values, Helpers::property($query, 'values'));
        $this->assertEquals($assignList, Helpers::property($query, 'assignList'));
    }
}
