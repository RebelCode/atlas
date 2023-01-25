<?php

namespace RebelCode\Atlas\Test\Query;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Config;
use RebelCode\Atlas\DatabaseAdapter;
use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Expression\Term;
use RebelCode\Atlas\Group;
use RebelCode\Atlas\Order;
use RebelCode\Atlas\Query;
use RebelCode\Atlas\Query\SelectQuery;
use RebelCode\Atlas\QueryType;
use RebelCode\Atlas\QueryType\Select;

class SelectQueryTest extends TestCase
{
    public function testIsQuery()
    {
        $this->assertInstanceOf(Query::class, new SelectQuery(new Select(), []));
    }

    public function provideMethodsData(): array
    {
        return [
            'from' => [Select::FROM, 'from', 'foo', 'bar'],
            'columns' => [Select::COLUMNS, 'columns', ['a', 'b'], ['c', 'd']],
            'where' => [Select::WHERE, 'where', Term::create('foo'), Term::create('bar')],
            'groupBy' => [Select::GROUP, 'groupBy', [Group::by('foo')], [Group::by('bar')]],
            'having' => [Select::HAVING, 'having', Term::create('foo'), Term::create('bar')],
            'orderBy' => [Select::ORDER, 'orderBy', [Order::by('foo')], [Order::by('bar')]],
            'limit' => [Select::LIMIT, 'limit', 1, 3],
            'offset' => [Select::OFFSET, 'offset', 5, 10],
        ];
    }

    /** @dataProvider provideMethodsData */
    public function testMethods($key, $method, $value1, $value2)
    {
        $subject1 = new SelectQuery(new Select(), [$key => $value1]);
        $subject2 = $subject1->$method($value2);

        $this->assertEquals($value1, $subject1->get($key));
        $this->assertEquals($value2, $subject2->get($key));
    }

    public function testExec()
    {
        $adapter = $this->createMock(DatabaseAdapter::class);
        $config = Config::createDefault($adapter);

        $query = new SelectQuery($config->getQueryType(QueryType::SELECT), [
            Select::FROM => 'foo',
        ], $adapter);

        $expected = [
            ['id' => 1, 'name' => 'Alice'],
            ['id' => 2, 'name' => 'Bob'],
            ['id' => 3, 'name' => 'Charlie']
        ];

        $adapter->expects($this->once())->method('queryResults')->willReturn($expected);

        $this->assertEquals($expected, $query->exec());
    }
}
