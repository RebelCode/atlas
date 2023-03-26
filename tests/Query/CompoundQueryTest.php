<?php

namespace RebelCode\Atlas\Test\Query;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Query;
use RebelCode\Atlas\Query\CompoundQuery;
use RebelCode\Atlas\Test\Helpers\ReflectionHelper;

class CompoundQueryTest extends TestCase
{
    use ReflectionHelper;

    public function testCtor()
    {
        $query1 = $this->createMock(Query::class);
        $query2 = $this->createMock(Query::class);

        $compound = new CompoundQuery([$query1, $query2]);

        $this->assertSame([$query1, $query2], $compound->getQueries());
    }

    public function testExec()
    {
        $query1 = $this->createMock(Query::class);
        $query2 = $this->createMock(Query::class);

        $query1->expects($this->once())->method('exec')->willReturn('foo');
        $query2->expects($this->once())->method('exec')->willReturn('bar');

        $compound = new CompoundQuery([$query1, $query2]);

        $actual = $compound->exec();
        $this->assertEquals(['foo', 'bar'], $actual);
    }

    public function testToSql()
    {
        $query1 = $this->createMock(Query::class);
        $query2 = $this->createMock(Query::class);

        $query1->expects($this->once())->method('toSql')->willReturn('QUERY 1');
        $query2->expects($this->once())->method('toSql')->willReturn('QUERY 2');

        $compound = new CompoundQuery([$query1, $query2]);

        $actual = $compound->toSql();
        $expected = "QUERY 1;\nQUERY 2;";

        $this->assertEquals($expected, $actual);
    }
}
