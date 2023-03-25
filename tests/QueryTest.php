<?php

namespace RebelCode\Atlas\Test;

use LogicException;
use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\DatabaseAdapter;
use RebelCode\Atlas\Query;
use RebelCode\Atlas\Test\Helpers\ReflectionHelper;
use Throwable;

class QueryTest extends TestCase
{
    use ReflectionHelper;

    public function testCtorNoArgs()
    {
        $subject = $this->getMockBuilder(Query::class)
                        ->setConstructorArgs([])
                        ->getMockForAbstractClass();

        $this->assertNull($this->expose($subject)->adapter);
    }

    public function testCtorWithAdapter()
    {
        $adapter = $this->createMock(DatabaseAdapter::class);

        $subject = $this->getMockBuilder(Query::class)
                        ->setConstructorArgs([$adapter])
                        ->getMockForAbstractClass();

        $getAdapter = new \ReflectionMethod($subject, 'getAdapter');
        $getAdapter->setAccessible(true);
        $this->assertSame($adapter, $getAdapter->invoke($subject));
    }

    public function testGetAdapterWhenNull()
    {
        $subject = $this->getMockBuilder(Query::class)
                        ->setConstructorArgs([])
                        ->getMockForAbstractClass();

        $getAdapter = new \ReflectionMethod($subject, 'getAdapter');
        $getAdapter->setAccessible(true);

        try {
            $getAdapter->invoke($subject);
            $this->fail('Expected exception to be thrown');
        } catch (Throwable $e) {
            $this->assertInstanceOf(LogicException::class, $e);
        }
    }
}
