<?php /** @noinspection SqlDialectInspection */

namespace RebelCode\Atlas\Test\QueryType;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Exception\QueryCompileException;
use RebelCode\Atlas\Order;
use RebelCode\Atlas\Query;
use RebelCode\Atlas\QueryType\Update;
use Throwable;

class UpdateTest extends TestCase
{
    public function testCompileSimple()
    {
        $subject = new Update();

        $query = new Query($subject, [
            Update::TABLE => 'table',
            Update::SET => [
                'foo' => 1,
                'bar' => 'baz',
            ],
        ]);

        $expected = "UPDATE `table` SET `foo` = 1, `bar` = 'baz'";
        $actual = $subject->compile($query);

        $this->assertEquals($expected, $actual);
    }

    public function testCompileNoTable()
    {
        $subject = new Update();
        $query = new Query($subject, [
            Update::SET => [
                'foo' => 1,
                'bar' => '`baz`',
            ],
        ]);

        try {
            $subject->compile($query);
            $this->fail('Expected an exception to be thrown');
        } catch (Throwable $e) {
            $this->assertInstanceOf(QueryCompileException::class, $e);
            $this->assertSame($query, $e->getQuery());
        }
    }

    public function testCompileNoSet()
    {
        $subject = new Update();
        $query = new Query($subject, [
            Update::TABLE => 'table',
        ]);

        try {
            $subject->compile($query);
            $this->fail('Expected an exception to be thrown');
        } catch (Throwable $e) {
            $this->assertInstanceOf(QueryCompileException::class, $e);
            $this->assertSame($query, $e->getQuery());
        }
    }

    public function testCompileLimit()
    {
        $subject = new Update();
        $query = new Query($subject, [
            Update::TABLE => 'table',
            Update::SET => ['foo' => 1],
            Update::LIMIT => 5,
        ]);

        $expected = 'UPDATE `table` SET `foo` = 1 LIMIT 5';
        $actual = $subject->compile($query);

        $this->assertEquals($expected, $actual);
    }

    public function testCompileSingleOrder()
    {
        $subject = new Update();
        $query = new Query($subject, [
            Update::TABLE => 'table',
            Update::SET => ['foo' => 1],
            Update::ORDER => [
                new Order('bar', Order::DESC)
            ],
        ]);

        $expected = 'UPDATE `table` SET `foo` = 1 ORDER BY `bar` DESC';
        $actual = $subject->compile($query);

        $this->assertEquals($expected, $actual);
    }

    public function testCompileMultipleOrders()
    {
        $subject = new Update();
        $query = new Query($subject, [
            Update::TABLE => 'table',
            Update::SET => ['foo' => 1],
            Update::ORDER => [
                new Order('bar', Order::DESC),
                new Order('baz', Order::ASC),
            ],
        ]);

        $expected = 'UPDATE `table` SET `foo` = 1 ORDER BY `bar` DESC, `baz` ASC';
        $actual = $subject->compile($query);

        $this->assertEquals($expected, $actual);
    }

    public function testCompileLimitMultipleOrders()
    {
        $subject = new Update();
        $query = new Query($subject, [
            Update::TABLE => 'table',
            Update::SET => ['foo' => 1],
            Update::LIMIT => 5,
            Update::ORDER => [
                new Order('bar', Order::DESC),
                new Order('baz', Order::ASC),
            ],
        ]);

        $expected = 'UPDATE `table` SET `foo` = 1 ORDER BY `bar` DESC, `baz` ASC LIMIT 5';
        $actual = $subject->compile($query);

        $this->assertEquals($expected, $actual);
    }
}
