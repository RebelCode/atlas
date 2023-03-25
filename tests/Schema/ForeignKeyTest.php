<?php

namespace RebelCode\Atlas\Test\Schema;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Schema\ForeignKey;

class ForeignKeyTest extends TestCase
{
    public function testConstructorForeignTable()
    {
        $fk = new ForeignKey('foobar', []);

        $this->assertEquals('foobar', $fk->getTable());
    }

    public function testConstructorMappings()
    {
        $fk = new ForeignKey('', $mappings = [
            'foo' => 'bar',
            'baz' => 'qux',
        ]);

        $this->assertEquals($mappings, $fk->getMappings());
    }

    public function testConstructorDefaultUpdateRule()
    {
        $fk = new ForeignKey('', []);

        $this->assertEquals(ForeignKey::RESTRICT, $fk->getUpdateRule());
    }

    public function testConstructorDefaultDeleteRule()
    {
        $fk = new ForeignKey('', []);

        $this->assertEquals(ForeignKey::RESTRICT, $fk->getDeleteRule());
    }

    public function testConstructorUpdateRule()
    {
        $fk = new ForeignKey('', [], ForeignKey::CASCADE);

        $this->assertEquals(ForeignKey::CASCADE, $fk->getUpdateRule());
    }

    public function testConstructorDeleteRule()
    {
        $fk = new ForeignKey('', [], ForeignKey::RESTRICT, ForeignKey::RESTRICT);

        $this->assertEquals(ForeignKey::RESTRICT, $fk->getDeleteRule());
    }

    public function testConstructorNullUpdateRule()
    {
        $fk = new ForeignKey('', [], null);

        $this->assertEquals(ForeignKey::RESTRICT, $fk->getUpdateRule());
    }

    public function testConstructorNullDeleteRule()
    {
        $fk = new ForeignKey('', [], null, null);

        $this->assertEquals(ForeignKey::RESTRICT, $fk->getDeleteRule());
    }
}
