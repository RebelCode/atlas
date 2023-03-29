<?php

namespace RebelCode\Atlas\Test;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\DataSource;
use RebelCode\Atlas\Expression\ColumnTerm;
use RebelCode\Atlas\TableRef;

class TableRefTest extends TestCase
{
    public function testImplementsDataSource()
    {
        $this->assertInstanceOf(DataSource::class, new TableRef(''));
    }

    public function testCtor()
    {
        $ref = new TableRef('users');

        $this->assertEquals('users', $ref->getName());
    }

    public function testCtorWithAlias()
    {
        $ref = new TableRef('users', 'u');

        $this->assertEquals('users', $ref->getName());
        $this->assertEquals('u', $ref->getAlias());
    }

    public function testAs()
    {
        $ref1 = new TableRef('users');
        $ref2 = $ref1->as('u');

        $this->assertNotSame($ref1, $ref2);
        $this->assertEquals('users', $ref1->getName(), 'Ref 1 should keep its original name');
        $this->assertNull($ref1->getAlias(), 'Ref 1 should not have an alias');
        $this->assertEquals('users', $ref2->getName(), 'Ref 2 should keep the original name');
        $this->assertEquals('u', $ref2->getAlias(), 'Ref 2 should have the new alias');
    }

    public function testCol()
    {
        $ref = new TableRef('users');

        $this->assertEquals(new ColumnTerm('users', 'id'), $ref->col('id'));
    }

    public function testColWithAlias()
    {
        $ref = new TableRef('users', 'u');

        $this->assertEquals(new ColumnTerm('u', 'id'), $ref->col('id'));
    }

    public function testMagicGetter()
    {
        $ref = new TableRef('users');

        $this->assertEquals(new ColumnTerm('users', 'id'), $ref->id);
    }

    public function testMagicGetterWithAlias()
    {
        $ref = new TableRef('users', 'u');

        $this->assertEquals(new ColumnTerm('u', 'id'), $ref->id);
    }

    public function testMagicGetterWithTableRefPropNames()
    {
        $ref = new TableRef('users');

        $this->assertEquals(new ColumnTerm('users', 'name'), $ref->name);
        $this->assertEquals(new ColumnTerm('users', 'alias'), $ref->alias);
    }

    public function testCompileSource()
    {
        $ref = new TableRef('users');

        $this->assertEquals('`users`', $ref->compileSource());
    }

    public function testCompileSourceWithAlias()
    {
        $ref = new TableRef('users', 'u');
        $this->assertEquals('`users` AS `u`', $ref->compileSource());
    }
}
