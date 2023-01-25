<?php

namespace RebelCode\Atlas\Test;

use LogicException;
use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\DatabaseAdapter;
use RebelCode\Atlas\Query;
use Throwable;

class QueryTest extends TestCase
{
    public function testCtorNoArgs()
    {
        $subject = $this->getMockBuilder(Query::class)
                        ->setConstructorArgs([])
                        ->getMockForAbstractClass();

        $this->assertNull(Helpers::property($subject, 'adapter'));
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

    public function testToString()
    {
        $expected = 'SELECT * FROM `foo`';

        $subject = $this->getMockBuilder(Query::class)
                        ->enableProxyingToOriginalMethods()
                        ->onlyMethods(['compile'])
                        ->getMockForAbstractClass();

        $subject->expects($this->once())->method('compile')->willReturn($expected);

        $this->assertEquals($expected, (string) $subject);
    }
}
