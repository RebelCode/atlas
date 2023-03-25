<?php

namespace RebelCode\Atlas\Test\Query\Traits;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Expression\Term;
use RebelCode\Atlas\Query\Traits\HasSelectColumnListTrait;
use RebelCode\Atlas\Test\Helpers\ReflectionHelper;

class HasSelectColumnListTraitTest extends TestCase
{
    use ReflectionHelper;

    public function testWithColumns()
    {
        $mock = $this->getMockForTrait(HasSelectColumnListTrait::class);
        $clone = $mock->columns(['foo', 'bar']);

        $this->assertEquals([], $this->expose($mock)->columns);
        $this->assertEquals(['foo', 'bar'], $this->expose($clone)->columns);
    }

    public function testCompile()
    {
        $expr = Term::column('test')->gt(5);
        $subject = $this->getMockForTrait(HasSelectColumnListTrait::class)
                        ->columns(['foo', $expr, '*', 'baz' => 'bar']);

        $this->assertEquals('`foo`, (`test` > 5), *, `bar` AS `baz`', $this->expose($subject)->compileColumnList());
    }

    public function testCompileNoColumns()
    {
        $mock = $this->getMockForTrait(HasSelectColumnListTrait::class);

        $this->assertEquals('*', $this->expose($mock)->compileColumnList());
    }
}
