<?php

namespace RebelCode\Atlas\Test\Query;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Expression\Term;
use RebelCode\Atlas\Order;
use RebelCode\Atlas\Query;
use RebelCode\Atlas\Query\UpdateQuery;
use RebelCode\Atlas\QueryType\Update;

class UpdateQueryTest extends TestCase
{
    public function testIsQuery()
    {
        $this->assertInstanceOf(Query::class, new UpdateQuery(new Update(), []));
    }

    public function provideMethodsData(): array
    {
        return [
            'table' => [Update::TABLE, 'table', 'foo', 'bar'],
            'set' => [Update::SET, 'set', ['foo' => 1], ['bar' => 2]],
            'where' => [Update::WHERE, 'where', Term::create('foo'), Term::create('bar')],
            'order' => [Update::ORDER, 'orderBy', [Order::by('foo')], [Order::by('bar')]],
            'limit' => [Update::LIMIT, 'limit', 5, 9],
        ];
    }

    /** @dataProvider provideMethodsData */
    public function testMethods($key, $method, $value1, $value2)
    {
        $subject1 = new UpdateQuery(new Update(), [$key => $value1]);
        $subject2 = $subject1->$method($value2);

        $this->assertEquals($value1, $subject1->get($key));
        $this->assertEquals($value2, $subject2->get($key));
    }
}
