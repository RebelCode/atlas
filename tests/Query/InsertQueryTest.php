<?php

namespace RebelCode\Atlas\Test\Query;

use RebelCode\Atlas\Query;
use RebelCode\Atlas\Query\InsertQuery;
use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\QueryType\Insert;

class InsertQueryTest extends TestCase
{
    public function testIsQuery()
    {
        $this->assertInstanceOf(Query::class, new InsertQuery(new Insert(), []));
    }

    public function provideMethodsData(): array
    {
        return [
            'into' => [Insert::TABLE, 'into', 'foo', 'bar'],
            'columns' => [Insert::COLUMNS, 'columns', ['foo', 'bar'], ['baz', 'qux']],
            'values' => [Insert::VALUES, 'values', ['foo', 'bar'], ['baz', 'qux']],
        ];
    }

    /** @dataProvider provideMethodsData */
    public function testMethods($key, $method, $value1, $value2)
    {
        $subject1 = new InsertQuery(new Insert(), [$key => $value1]);
        $subject2 = $subject1->$method($value2);

        $this->assertEquals($value1, $subject1->get($key));
        $this->assertEquals($value2, $subject2->get($key));
    }
}
