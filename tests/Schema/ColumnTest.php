<?php

namespace RebelCode\Atlas\Test\Schema;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Schema\Column;

class ColumnTest extends TestCase
{
    public function testConstructDefaults()
    {
        $column = new Column('test');

        $this->assertEquals('test', $column->getType(), 'The type was not properly set by the ctor');
        $this->assertNull($column->getDefaultValue(), 'The default value should be set to null by default');
        $this->assertTrue($column->isNullable(), 'The nullability flag should be set to true by default');
        $this->assertFalse($column->isAutoInc(), 'The auto-increment flag should be set to false by default');
    }

    public function testConstruct()
    {
        $column = new Column('test', 'foobar', true, true);

        $this->assertEquals('test', $column->getType(), 'The type was not properly set by the ctor');
        $this->assertEquals('foobar', $column->getDefaultValue(), 'The default value was not properly set by the ctor');
        $this->assertTrue($column->isNullable(), 'The nullability flag was not properly set by the ctor');
        $this->assertTrue($column->isAutoInc(), 'The auto-increment flag was not properly set by the ctor');
    }

    public function testWithType()
    {
        $column = new Column($type = 'test', 'foobar', true, true);
        $clone = $column->withType($newType = 'newType');

        $this->assertEquals($newType, $clone->getType(), 'The new type was not set to the clone');
        $this->assertEquals($type, $column->getType(), 'The original instance should not be mutated');
    }

    public function testWithDefaultValue()
    {
        $column = new Column('test', $def = 'foobar', true, true);
        $clone = $column->withDefaultVal($newDef = 'hiMom');

        $this->assertEquals($newDef, $clone->getDefaultValue(), 'The new default value was not set in the clone');
        $this->assertEquals($def, $column->getDefaultValue(), 'The original instance should not be mutated');
    }

    public function testWithDefaultValueNull()
    {
        $column = new Column('test', $def = 'foobar', true, true);
        $clone = $column->withDefaultVal(null);

        $this->assertNull($clone->getDefaultValue(), 'The default value in the clone was not set to null');
        $this->assertEquals($def, $column->getDefaultValue(), 'The original instance should not be mutated');
    }

    public function testWithIsNullableFalse()
    {
        $column = new Column('test', 'foobar', true, true);
        $clone = $column->withIsNullable(false);

        $this->assertFalse($clone->isNullable(), 'The nullability in the clone was not set to false');
        $this->assertTrue($column->isNullable(), 'The original instance should not be mutated');
    }

    public function testWithIsNullableTrue()
    {
        $column = new Column('test', 'foobar', false, true);
        $clone = $column->withIsNullable(true);

        $this->assertTrue($clone->isNullable(), 'The nullability in the clone was not set to true');
        $this->assertFalse($column->isNullable(), 'The original instance should not be mutated');
    }

    public function testWithAutoIncrementFalse()
    {
        $column = new Column('test', 'foobar', true, true);
        $clone = $column->withAutoInc(false);

        $this->assertFalse($clone->isAutoInc(), 'The auto-increment flag in the clone was not set to false');
        $this->assertTrue($column->isAutoInc(), 'The original instance should not be mutated');
    }

    public function testWithAutoIncrementTrue()
    {
        $column = new Column('test', 'foobar', true, false);
        $clone = $column->withAutoInc(true);

        $this->assertTrue($clone->isAutoInc(), 'The auto-increment flag in the clone was not set to true');
        $this->assertFalse($column->isAutoInc(), 'The original instance should not be mutated');
    }
}
