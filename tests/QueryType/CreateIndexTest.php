<?php /** @noinspection SqlDialectInspection */

namespace RebelCode\Atlas\Test\QueryType;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Exception\QueryCompileException;
use RebelCode\Atlas\Order;
use RebelCode\Atlas\Query;
use RebelCode\Atlas\QueryType\CreateIndex;
use RebelCode\Atlas\Schema\Index;
use Throwable;

class CreateIndexTest extends TestCase
{
    public function testCompile()
    {
        $type = new CreateIndex();

        $query = new Query($type, [
            CreateIndex::TABLE => 'test',
            CreateIndex::NAME => 'my_index',
            CreateIndex::INDEX => new Index(false, [
                'foo' => Order::ASC,
                'bar' => Order::DESC,
            ]),
        ]);

        $expected = 'CREATE INDEX `my_index` ON `test` (`foo` ASC, `bar` DESC)';

        $this->assertEquals($expected, $type->compile($query));
    }

    public function testCompileNoTable()
    {
        $type = new CreateIndex();

        $query = new Query($type, [
            CreateIndex::NAME => 'my_index',
            CreateIndex::INDEX => new Index(false, [
                'foo' => Order::ASC,
            ]),
        ]);

        try {
            $type->compile($query);
            $this->fail('Expected exception to be thrown');
        } catch (Throwable $e) {
            $this->assertInstanceOf(QueryCompileException::class, $e);
            $this->assertSame($query, $e->getQuery());
        }
    }

    public function testCompileNoName()
    {
        $type = new CreateIndex();

        $query = new Query($type, [
            CreateIndex::TABLE => 'test',
            CreateIndex::INDEX => new Index(false, [
                'foo' => Order::ASC,
            ]),
        ]);

        try {
            $type->compile($query);
            $this->fail('Expected exception to be thrown');
        } catch (Throwable $e) {
            $this->assertInstanceOf(QueryCompileException::class, $e);
            $this->assertSame($query, $e->getQuery());
        }
    }

    public function testCompileNoIndex()
    {
        $type = new CreateIndex();

        $query = new Query($type, [
            CreateIndex::TABLE => 'test',
            CreateIndex::NAME => 'my_index',
        ]);

        try {
            $type->compile($query);
            $this->fail('Expected exception to be thrown');
        } catch (Throwable $e) {
            $this->assertInstanceOf(QueryCompileException::class, $e);
            $this->assertSame($query, $e->getQuery());
        }
    }

    public function testCompileIndexNoColumns()
    {
        $type = new CreateIndex();

        $query = new Query($type, [
            CreateIndex::TABLE => 'test',
            CreateIndex::NAME => 'my_index',
            CreateIndex::INDEX => new Index(false, []),
        ]);

        try {
            $type->compile($query);
            $this->fail('Expected exception to be thrown');
        } catch (Throwable $e) {
            $this->assertInstanceOf(QueryCompileException::class, $e);
            $this->assertSame($query, $e->getQuery());
        }
    }
}
