<?php

namespace RebelCode\Atlas\Test\Query\Traits;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Query\Traits\HasHavingTrait;
use RebelCode\Atlas\Test\Helpers\ReflectionHelper;

class HasHavingTraitTest extends TestCase
{
    use ReflectionHelper;

    public function testSetHaving()
    {
        $mock = $this->getMockForTrait(HasHavingTrait::class);
        $clone = $mock->having($expr = $this->createMock(ExprInterface::class));

        $this->assertNull($this->expose($mock)->having);
        $this->assertSame($expr, $this->expose($clone)->having);
    }

    public function testCompile()
    {
        $expr = $this->createMock(ExprInterface::class);
        $expr->expects($this->once())->method('toSql')->willReturn('foo');

        $mock = $this->getMockForTrait(HasHavingTrait::class)->having($expr);

        $this->assertEquals('HAVING foo', $this->expose($mock)->compileHaving());
    }

    public function testCompileNoHavings()
    {
        $mock = $this->getMockForTrait(HasHavingTrait::class);

        $this->assertEquals('', $this->expose($mock)->compileHaving());
    }
}
