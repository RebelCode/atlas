<?php

namespace RebelCode\Atlas\Test\Query\Traits;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Group;
use RebelCode\Atlas\Query\Traits\HasGroupByTrait;
use RebelCode\Atlas\Test\Helpers\ReflectionHelper;

class HasGroupByTraitTest extends TestCase
{
    use ReflectionHelper;

    public function testSetGroups()
    {
        $mock = $this->getMockForTrait(HasGroupByTrait::class);
        $clone = $mock->groupBy(
            $groups = [
                $this->createMock(Group::class),
                $this->createMock(Group::class),
            ]
        );

        $this->assertEquals([], $this->expose($mock)->groups);
        $this->assertSame($groups, $this->expose($clone)->groups);
    }

    public function testCompile()
    {
        $mock = $this->getMockForTrait(HasGroupByTrait::class)->groupBy([
            Group::by('foo')->asc(),
            Group::by('bar')->desc(),
        ]);

        $this->assertEquals('GROUP BY `foo` ASC, `bar` DESC', $this->expose($mock)->compileGroupBy());
    }

    public function testCompileNoGroups()
    {
        $mock = $this->getMockForTrait(HasGroupByTrait::class);

        $this->assertEquals('', $this->expose($mock)->compileGroupBy());
    }
}
