<?php

namespace RebelCode\Atlas\Test\Query\Traits;

use RebelCode\Atlas\Query\Traits\HasLimitTrait;
use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Test\Helpers\ReflectionHelper;

class HasLimitTraitTest extends TestCase
{
    use ReflectionHelper;

    public function testSetLimit()
    {
        $mock = $this->getMockForTrait(HasLimitTrait::class);
        $clone = $mock->limit(999);

        $this->assertNull($this->expose($mock)->limit);
        $this->assertSame(999, $this->expose($clone)->limit);
    }

    public function testCompile()
    {
        $mock = $this->getMockForTrait(HasLimitTrait::class)->limit(15);

        $this->assertEquals('LIMIT 15', $this->expose($mock)->compileLimit());
    }

    public function testCompileNoLimit()
    {
        $mock = $this->getMockForTrait(HasLimitTrait::class);

        $this->assertEquals('', $this->expose($mock)->compileLimit());
    }
}
