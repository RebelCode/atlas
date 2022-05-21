<?php

namespace RebelCode\Atlas\Test;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Config;
use RebelCode\Atlas\Expression\BinaryExpr;
use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Expression\Term;
use RebelCode\Atlas\Order;
use RebelCode\Atlas\QueryType;
use RebelCode\Atlas\QueryType\CreateIndex;
use RebelCode\Atlas\QueryType\CreateTable;
use RebelCode\Atlas\QueryType\DropTable;
use RebelCode\Atlas\QueryTypeInterface;
use RebelCode\Atlas\Schema;
use RebelCode\Atlas\Table;

class TableTest extends TestCase
{
    public function testConstructor()
    {
        $config = $this->createMock(Config::class);
        $table = new Table($config, $name = 'test', $schema = $this->createMock(Schema::class));

        $this->assertEquals($name, $table->getName());
        $this->assertSame($schema, $table->getSchema());
        $this->assertNull($table->getWhere());
    }

    public function testWhere()
    {
        $config = $this->createMock(Config::class);
        $table = new Table($config, 'test', $this->createMock(Schema::class));

        $clone = $table->where($where = $this->createMock(ExprInterface::class));

        $this->assertSame($where, $clone->getWhere());
        $this->assertNotSame($table, $clone, 'Clone is the same as original instance');
        $this->assertNull($table->getWhere(), 'Original instance should not be modified');
        $this->assertEquals($table->getName(), $clone->getName(), 'Clone should keep the original table name');
        $this->assertSame($table->getSchema(), $clone->getSchema(), 'Clone should keep the original table schema');
    }

    public function testWhereAnd()
    {
        $config = $this->createMock(Config::class);
        $table = new Table($config, 'test', $this->createMock(Schema::class));

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
        $config = $this->createMock(Config::class);
        $table = new Table($config, 'test', $this->createMock(Schema::class));

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
        $config = $this->createMock(Config::class);
        $table = new Table($config, 'test');

        $term = $table->column($value = 'foobar');

        $this->assertEquals($value, $term->getValue());
        $this->assertEquals(Term::COLUMN, $term->getType());
    }

    public function testColumnExistsWithSchema()
    {
        $config = $this->createMock(Config::class);
        $schema = $this->createMock(Schema::class);
        $schema->method('getColumns')->willReturn([
            'foo' => new Schema\Column(''),
            'bar' => new Schema\Column(''),
        ]);

        $table = new Table($config, 'test', $schema);

        $term = $table->column($value = 'foo');

        $this->assertEquals($value, $term->getValue());
        $this->assertEquals(Term::COLUMN, $term->getType());
    }

    public function testColumnNotExistsWithSchema()
    {
        $config = $this->createMock(Config::class);
        $schema = $this->createMock(Schema::class);
        $schema->method('getColumns')->willReturn([
            'foo' => new Schema\Column(''),
            'bar' => new Schema\Column(''),
        ]);

        $table = new Table($config, 'test', $schema);

        try {
            $table->column('oopsie');
            $this->fail('Expected exception to be thrown');
        } catch (\Throwable $e) {
            $this->assertInstanceOf(\DomainException::class, $e);
        }
    }

    public function testOrderBy()
    {
        $config = $this->createMock(Config::class);
        $table = new Table($config, 'test');

        $clone = $table->orderBy($order = [
            $this->createMock(Order::class),
            $this->createMock(Order::class),
        ]);

        $this->assertSame($order, $clone->getOrder());
    }

    public function testOrderByTwice()
    {
        $config = $this->createMock(Config::class);
        $table = new Table($config, 'test');

        $clone = $table->orderBy($order1 = [
            $this->createMock(Order::class),
            $this->createMock(Order::class),
        ]);

        $clone2 = $clone->orderBy($order2 = [
            $this->createMock(Order::class),
            $this->createMock(Order::class),
        ]);

        $this->assertSame(array_merge($order1, $order2), $clone2->getOrder());
    }

    public function testCreate()
    {
        $config = $this->createMock(Config::class);
        $config->expects($this->once())
               ->method('getQueryType')
               ->with(QueryType::CREATE_TABLE)
               ->willReturn(new CreateTable());

        $table = new Table($config, $name = 'test', $schema = $this->createMock(Schema::class));
        $query = $table->create()[0];

        $this->assertEquals($name, $query->get(CreateTable::NAME));
        $this->assertSame($schema, $query->get(CreateTable::SCHEMA));
        $this->assertTrue($query->get(CreateTable::IF_NOT_EXISTS));
        $this->assertNull($query->get(CreateTable::COLLATE));
        $this->assertInstanceOf(CreateTable::class, $query->getType());
    }

    public function testCreateIfNotExistsFalse()
    {
        $config = $this->createMock(Config::class);
        $config->expects($this->once())
               ->method('getQueryType')
               ->with(QueryType::CREATE_TABLE)
               ->willReturn(new CreateTable());

        $table = new Table($config, $name = 'test', $schema = $this->createMock(Schema::class));
        $query = $table->create(false)[0];

        $this->assertEquals($name, $query->get(CreateTable::NAME));
        $this->assertSame($schema, $query->get(CreateTable::SCHEMA));
        $this->assertFalse($query->get(CreateTable::IF_NOT_EXISTS));
        $this->assertNull($query->get(CreateTable::COLLATE));
    }

    public function testCreateCollate()
    {
        $config = $this->createMock(Config::class);
        $config->expects($this->once())
               ->method('getQueryType')
               ->with(QueryType::CREATE_TABLE)
               ->willReturn(new CreateTable());

        $table = new Table($config, $name = 'test', $schema = $this->createMock(Schema::class));
        $query = $table->create(true, $collate = 'utf8_unicode_ci')[0];

        $this->assertEquals($name, $query->get(CreateTable::NAME));
        $this->assertSame($schema, $query->get(CreateTable::SCHEMA));
        $this->assertTrue($query->get(CreateTable::IF_NOT_EXISTS));
        $this->assertEquals($collate, $query->get(CreateTable::COLLATE));
    }

    public function testCreateWithIndexes()
    {
        $config = $this->createMock(Config::class);
        $config->expects($this->exactly(3))
               ->method('getQueryType')
               ->withConsecutive([QueryType::CREATE_TABLE], [QueryType::CREATE_INDEX], [QueryType::CREATE_INDEX])
               ->willReturnOnConsecutiveCalls(new CreateTable(), new CreateIndex(), new CreateIndex());

        $schema = new Schema([], [], [], [
            'index1' => $index1 = new Schema\Index(false, []),
            'index2' => $index2 = new Schema\Index(false, []),
        ]);

        $table = new Table($config, 'test', $schema);
        $queries = $table->create();

        $this->assertCount(3, $queries);

        $this->assertInstanceOf(CreateIndex::class, $queries[1]->getType());
        $this->assertInstanceOf(CreateIndex::class, $queries[2]->getType());
        $this->assertEquals('test', $queries[1]->get(CreateIndex::TABLE));
        $this->assertEquals('test', $queries[2]->get(CreateIndex::TABLE));
        $this->assertEquals('index1', $queries[1]->get(CreateIndex::NAME));
        $this->assertEquals('index2', $queries[2]->get(CreateIndex::NAME));
        $this->assertEquals($index1, $queries[1]->get(CreateIndex::INDEX));
        $this->assertEquals($index2, $queries[2]->get(CreateIndex::INDEX));
    }

    public function testDrop()
    {
        $config = $this->createMock(Config::class);
        $config->expects($this->once())
               ->method('getQueryType')
               ->with(QueryType::DROP_TABLE)
               ->willReturn(new DropTable());

        $table = new Table($config, $name = 'test', $this->createMock(Schema::class));
        $query = $table->drop();

        $this->assertEquals($name, $query->get(DropTable::TABLE));
        $this->assertTrue($query->get(DropTable::IF_EXISTS));
        $this->assertFalse($query->get(DropTable::CASCADE));
    }

    public function testDropIfExistsFalse()
    {
        $config = $this->createMock(Config::class);
        $config->expects($this->once())
               ->method('getQueryType')
               ->with(QueryType::DROP_TABLE)
               ->willReturn(new DropTable());

        $table = new Table($config, $name = 'test', $this->createMock(Schema::class));
        $query = $table->drop(false);

        $this->assertEquals($name, $query->get(DropTable::TABLE));
        $this->assertFalse($query->get(DropTable::IF_EXISTS));
        $this->assertFalse($query->get(DropTable::CASCADE));
    }

    public function testDropCascade()
    {
        $config = $this->createMock(Config::class);
        $config->expects($this->once())
               ->method('getQueryType')
               ->with(QueryType::DROP_TABLE)
               ->willReturn(new DropTable());

        $table = new Table($config, $name = 'test', $this->createMock(Schema::class));
        $query = $table->drop(true, true);

        $this->assertEquals($name, $query->get(DropTable::TABLE));
        $this->assertTrue($query->get(DropTable::IF_EXISTS));
        $this->assertTrue($query->get(DropTable::CASCADE));
    }

    public function testSelect()
    {
        $config = $this->createMock(Config::class);
        $config->method('getQueryType')->with(QueryType::SELECT)->willReturn(new QueryType\Select());

        $table = new Table($config, $name = 'test');

        $columns = ['foo', 'bar'];
        $where = $this->createMock(ExprInterface::class);
        $order = [Order::by('foo')];
        $limit = 19;
        $offset = 4;

        $query = $table->select($columns, $where, $order, $limit, $offset);

        $this->assertEquals($name, $query->get(QueryType\Select::FROM));
        $this->assertEquals($columns, $query->get(QueryType\Select::COLUMNS));
        $this->assertEquals($where, $query->get(QueryType\Select::WHERE));
        $this->assertEquals($order, $query->get(QueryType\Select::ORDER));
        $this->assertEquals($limit, $query->get(QueryType\Select::LIMIT));
        $this->assertEquals($offset, $query->get(QueryType\Select::OFFSET));
    }

    public function testSelectWithWhereState()
    {
        $config = $this->createMock(Config::class);
        $config->method('getQueryType')->with(QueryType::SELECT)->willReturn(new QueryType\Select());

        $where = $this->createMock(ExprInterface::class);

        $table = new Table($config, 'test');
        $table = $table->where($where);

        $query = $table->select();

        $this->assertEquals($where, $query->get(QueryType\Select::WHERE));
    }

    public function testSelectWithWhereStateAndArg()
    {
        $config = $this->createMock(Config::class);
        $config->method('getQueryType')->with(QueryType::SELECT)->willReturn(new QueryType\Select());

        $whereState = $this->createMock(ExprInterface::class);
        $whereArg = $this->createMock(ExprInterface::class);
        $whereFinal = $this->createMock(BinaryExpr::class);

        $whereState->expects($this->once())->method('and')->with($whereArg)->willReturn($whereFinal);

        $table = new Table($config, 'test');
        $table = $table->where($whereState);

        $query = $table->select(null, $whereArg);

        $this->assertEquals($whereFinal, $query->get(QueryType\Select::WHERE));
    }

    public function testSelectWithOrderState()
    {
        $config = $this->createMock(Config::class);
        $config->method('getQueryType')->with(QueryType::SELECT)->willReturn(new QueryType\Select());

        $order = [Order::by('foo'), Order::by('bar')];

        $table = new Table($config, 'test');
        $table = $table->orderBy($order);

        $query = $table->select();

        $this->assertEquals($order, $query->get(QueryType\Select::ORDER));
    }

    public function testSelectWithOrderStateAndArg()
    {
        $config = $this->createMock(Config::class);
        $config->method('getQueryType')->with(QueryType::SELECT)->willReturn(new QueryType\Select());

        $order1 = Order::by('foo');
        $order2 = Order::by('bar');
        $order3 = Order::by('baz');
        $order4 = Order::by('qux');

        $table = new Table($config, 'test');
        $table = $table->orderBy([$order1, $order2]);

        $query = $table->select(null, null, [$order3, $order4]);

        $this->assertEquals([$order1, $order2, $order3, $order4], $query->get(QueryType\Select::ORDER));
    }

    public function testInsertColumns()
    {
        $config = $this->createMock(Config::class);
        $config->method('getQueryType')->with(QueryType::INSERT)->willReturn(new QueryType\Insert());

        $values = [
            ['a' => 1, 'b' => 2, 'c' => 3],
            ['d' => 4, 'e' => 5, 'f' => 6],
        ];

        $table = new Table($config, 'test');
        $query = $table->insert($values);

        $this->assertEquals(['a', 'b', 'c'], $query->get(QueryType\Insert::COLUMNS));
    }

    public function testInsertValues()
    {
        $config = $this->createMock(Config::class);
        $config->method('getQueryType')->with(QueryType::INSERT)->willReturn(new QueryType\Insert());

        $values = [
            ['a' => 1, 'b' => 2, 'c' => 3],
            ['a' => 4, 'b' => 5, 'd' => 6],
        ];

        $table = new Table($config, 'test');
        $query = $table->insert($values);

        $this->assertEquals($values, $query->get(QueryType\Insert::VALUES));
    }

    public function testInsertOnDuplicateKey()
    {
        $config = $this->createMock(Config::class);
        $config->method('getQueryType')->with(QueryType::INSERT)->willReturn(new QueryType\Insert());

        $records = [
            ['a' => 1, 'b' => 2, 'c' => 3],
            ['d' => 4, 'e' => 5, 'f' => 6],
        ];

        $assignList = [
            'a' => 'A',
            'b' => $this->createMock(ExprInterface::class),
            'c' => 88.8,
        ];

        $table = new Table($config, 'test');
        $query = $table->insert($records, $assignList);

        $this->assertEquals($assignList, $query->get(QueryType\Insert::ON_DUPLICATE_KEY));
    }

    public function testQuery()
    {
        $type = $this->createMock(QueryTypeInterface::class);

        $config = $this->createMock(Config::class);
        $config->method('getQueryType')->with('custom')->willReturn($type);

        $table = new Table($config, 'foobar');
        $query = $table->query('custom', [
            'data' => 123
        ]);

        $this->assertSame($type, $query->getType());
        $this->assertEquals('foobar', $query->get('table'));
        $this->assertEquals(123, $query->get('data'));
    }
}
