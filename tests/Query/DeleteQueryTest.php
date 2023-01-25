<?php

namespace RebelCode\Atlas\Test\Query;

use RebelCode\Atlas\Config;
use RebelCode\Atlas\DatabaseAdapter;
use RebelCode\Atlas\Expression\Term;
use RebelCode\Atlas\Order;
use RebelCode\Atlas\Query;
use RebelCode\Atlas\Query\DeleteQuery;
use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\QueryType;
use RebelCode\Atlas\QueryType\Delete;

class DeleteQueryTest extends TestCase
{
    public function testIsQuery()
    {
        $this->assertInstanceOf(Query::class, new DeleteQuery(new Delete(), []));
    }

    public function provideMethodsData(): array
    {
        return [
            'from' => [Delete::FROM, 'from', 'foo' , 'bar'],
            'where' => [Delete::WHERE, 'where', Term::create('foo') , Term::create('bar')],
            'orderBy' => [Delete::ORDER, 'orderBy', [Order::by('foo')] , [Order::by('bar')]],
            'limit' => [Delete::LIMIT, 'limit', 1 , 5],
        ];
    }

    /** @dataProvider provideMethodsData */
    public function testMethods($key, $method, $value1, $value2)
    {
        $subject1 = new DeleteQuery(new Delete(), [$key => $value1]);
        $subject2 = $subject1->$method($value2);

        $this->assertEquals($value1, $subject1->get($key));
        $this->assertEquals($value2, $subject2->get($key));
    }

    public function testExec()
    {
        $adapter = $this->createMock(DatabaseAdapter::class);
        $config = Config::createDefault($adapter);

        $query = new DeleteQuery($config->getQueryType(QueryType::DELETE), [
            Delete::FROM => 'foo',
        ], $adapter);

        $numRows = 123;
        $adapter->expects($this->once())->method('queryNumRows')->willReturn($numRows);

        $this->assertEquals($numRows, $query->exec());
    }
}
