<?php

namespace RebelCode\Atlas\Test\Query\Traits;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Query\Traits\HasOffsetTrait;
use RebelCode\Atlas\Test\Helpers\ReflectionHelper;

class HasOffsetTraitTest extends TestCase
{
    use ReflectionHelper;

    public function testSetOffset()
    {
        $mock = $this->getMockForTrait(HasOffsetTrait::class);
        $clone = $mock->offset(420);

        $this->assertNull($this->expose($mock)->offset);
        $this->assertEquals(420, $this->expose($clone)->offset);
    }

    public function testCompile()
    {
        $mock = $this->getMockForTrait(HasOffsetTrait::class)->offset(22);

        $this->assertEquals('OFFSET 22', $this->expose($mock)->compileOffset());
    }

    public function testCompileNoOffset()
    {
        $mock = $this->getMockForTrait(HasOffsetTrait::class);

        $this->assertEquals('', $this->expose($mock)->compileOffset());
    }
}
