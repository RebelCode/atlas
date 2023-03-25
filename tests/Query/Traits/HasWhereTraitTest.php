<?php

namespace RebelCode\Atlas\Test\Query\Traits;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Query\Traits\HasWhereTrait;
use RebelCode\Atlas\Test\Helpers\ReflectionHelper;

class HasWhereTraitTest extends TestCase
{
    use ReflectionHelper;

    public function testSetWhere()
    {
        $mock = $this->getMockForTrait(HasWhereTrait::class);
        $clone = $mock->where($expr = $this->createMock(ExprInterface::class));

        $this->assertNull($this->expose($mock)->where);
        $this->assertSame($expr, $this->expose($clone)->where);
    }

    public function testCompile()
    {
        $expr = $this->createMock(ExprInterface::class);
        $expr->expects($this->once())->method('toSql')->willReturn('[expr]');

        $mock = $this->getMockForTrait(HasWhereTrait::class)->where($expr);

        $this->assertEquals('WHERE [expr]', $this->expose($mock)->compileWhere());
    }

    public function testCompileNoWhere()
    {
        $mock = $this->getMockForTrait(HasWhereTrait::class);

        $this->assertEquals('', $this->expose($mock)->compileWhere());
    }
}
