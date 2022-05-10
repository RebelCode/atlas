<?php

namespace RebelCode\Atlas\Test\Exception;

use RebelCode\Atlas\Exception\QueryCompileException;
use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Query;
use RuntimeException;
use Throwable;

class QueryCompileExceptionTest extends TestCase
{
    public function testIsThrowable()
    {
        $query = $this->createMock(Query::class);
        $exception = new QueryCompileException('', $query);

        $this->assertInstanceOf(Throwable::class, $exception);
    }

    public function testIsRuntime()
    {
        $query = $this->createMock(Query::class);
        $exception = new QueryCompileException('', $query);

        $this->assertInstanceOf(RuntimeException::class, $exception);
    }

    public function testConstructorNoPrevious()
    {
        $query = $this->createMock(Query::class);
        $exception = new QueryCompileException($msg = 'Foo bar test message', $query);

        $this->assertEquals($msg, $exception->getMessage());
        $this->assertSame($query, $exception->getQuery());
    }

    public function testConstructorWithPrevious()
    {
        $query = $this->createMock(Query::class);
        $previous = $this->createMock(Throwable::class);
        $exception = new QueryCompileException($msg = 'Foo bar test message', $query, $previous);

        $this->assertEquals($msg, $exception->getMessage());
        $this->assertSame($query, $exception->getQuery());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
