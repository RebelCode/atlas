<?php

namespace RebelCode\Atlas\Test\Query\Traits;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\DataSource;
use RebelCode\Atlas\Join;
use RebelCode\Atlas\Query\Traits\HasJoinsTrait;
use RebelCode\Atlas\Test\Helpers\ReflectionHelper;

class HasJoinsTraitTest extends TestCase
{
    use ReflectionHelper;

    public function testSetJoins()
    {
        $ds1 = $this->createMock(DataSource::class);
        $ds2 = $this->createMock(DataSource::class);
        $expr = $this->createMock(ExprInterface::class);

        $mock = $this->getMockForTrait(HasJoinsTrait::class);
        $clone = $mock->join([
            new Join(Join::CROSS, $ds1),
            new Join(Join::INNER, $ds2, $expr),
        ]);

        $this->assertNotSame($mock, $clone);
        $this->assertEquals([], $this->expose($mock)->joins);
        $this->assertCount(2, $this->expose($clone)->joins);
        $this->assertEquals(new Join(Join::CROSS, $ds1), $this->expose($clone)->joins[0]);
        $this->assertEquals(new Join(Join::INNER, $ds2, $expr), $this->expose($clone)->joins[1]);
    }

    public function testCompile()
    {
        $ds1 = $this->createMock(DataSource::class);
        $ds2 = $this->createMock(DataSource::class);
        $expr = $this->createMock(ExprInterface::class);

        $ds1->expects($this->once())->method('compileSource')->willReturn('`foo`');
        $ds2->expects($this->once())->method('compileSource')->willReturn('`bar`');
        $expr->expects($this->once())->method('toSql')->willReturn('[expr]');

        $mock = $this->getMockForTrait(HasJoinsTrait::class)
                     ->join([
                         new Join(Join::LEFT, $ds1),
                         new Join(Join::NATURAL_RIGHT, $ds2, $expr),
                     ]);

        $this->assertEquals(
            'LEFT JOIN `foo` NATURAL RIGHT JOIN `bar` ON [expr]',
            $this->expose($mock)->compileJoins()
        );
    }

    public function testCompileNoJoins()
    {
        $mock = $this->getMockForTrait(HasJoinsTrait::class);

        $this->assertEquals('', $this->expose($mock)->compileJoins());
    }
}
