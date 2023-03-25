<?php

namespace RebelCode\Atlas\Test\Schema;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Expression\Term;
use RebelCode\Atlas\Schema\Column;

class ColumnTest extends TestCase
{
    public function testConstructDefaults()
    {
        $column = new Column('test');

        $this->assertEquals('test', $column->getType(), 'The type was not properly set by the ctor');
        $this->assertNull($column->getDefaultValue(), 'The default value should be set to null by default');
        $this->assertTrue($column->getIsNullable(), 'The nullability flag should be set to true by default');
        $this->assertFalse($column->getIsAutoInc(), 'The auto-increment flag should be set to false by default');
    }

    public function testConstruct()
    {
        $column = new Column('test', 'foobar', true, true);

        $this->assertEquals('test', $column->getType(), 'The type was not properly set by the ctor');
        $this->assertInstanceOf(ExprInterface::class, $column->getDefaultValue(), 'The default value is not an expression');
        $this->assertTrue($column->getIsNullable(), 'The nullability flag was not properly set by the ctor');
        $this->assertTrue($column->getIsAutoInc(), 'The auto-increment flag was not properly set by the ctor');
    }

    public function testWithType()
    {
        $column = new Column($type = 'test', 'foobar', true, true);
        $clone = $column->type($newType = 'newType');

        $this->assertEquals($newType, $clone->getType(), 'The new type was not set to the clone');
        $this->assertEquals($type, $column->getType(), 'The original instance should not be mutated');
    }

    public function testWithDefaultValue()
    {
        $column = new Column('test', $def = 'foobar', true, true);
        $clone = $column->default($newDef = 'hiMom');

        $this->assertInstanceOf(Term::class, $clone->getDefaultValue(), 'The new default value is not a Term');
        $this->assertEquals($newDef, $clone->getDefaultValue()->getValue(), 'The new default value was not set in the clone');
        $this->assertEquals($def, $column->getDefaultValue()->getValue(), 'The original instance should not be mutated');
    }

    public function testWithDefaultValueNull()
    {
        $column = new Column('test', $def = 'foobar', true, true);
        $clone = $column->default(null);

        $this->assertNull($clone->getDefaultValue(), 'The default value in the clone was not set to null');
        $this->assertEquals($def, $column->getDefaultValue()->getValue(), 'The original instance should not be mutated');
    }

    public function testWithIsNullableFalse()
    {
        $column = new Column('test', 'foobar', true, true);
        $clone = $column->nullable(false);

        $this->assertFalse($clone->getIsNullable(), 'The nullability in the clone was not set to false');
        $this->assertTrue($column->getIsNullable(), 'The original instance should not be mutated');
    }

    public function testWithIsNullableTrue()
    {
        $column = new Column('test', 'foobar', false, true);
        $clone = $column->nullable(true);

        $this->assertTrue($clone->getIsNullable(), 'The nullability in the clone was not set to true');
        $this->assertFalse($column->getIsNullable(), 'The original instance should not be mutated');
    }

    public function testWithIsNullableNoArg()
    {
        $column = new Column('test', 'foobar', false, true);
        $clone = $column->nullable();

        $this->assertTrue($clone->getIsNullable(), 'The nullability in the clone was not set to true');
        $this->assertFalse($column->getIsNullable(), 'The original instance should not be mutated');
    }

    public function testNotNull()
    {
        $column = new Column('test', 'foobar', true);
        $clone = $column->notNull();

        $this->assertFalse($clone->getIsNullable(), 'The nullability in the clone was not set to false');
        $this->assertTrue($column->getIsNullable(), 'The original instance should not be mutated');
    }

    public function testWithAutoIncrementFalse()
    {
        $column = new Column('test', 'foobar', true, true);
        $clone = $column->autoInc(false);

        $this->assertFalse($clone->getIsAutoInc(), 'The auto-increment flag in the clone was not set to false');
        $this->assertTrue($column->getIsAutoInc(), 'The original instance should not be mutated');
    }

    public function testWithAutoIncrementTrue()
    {
        $column = new Column('test', 'foobar', true, false);
        $clone = $column->autoInc(true);

        $this->assertTrue($clone->getIsAutoInc(), 'The auto-increment flag in the clone was not set to true');
        $this->assertFalse($column->getIsAutoInc(), 'The original instance should not be mutated');
    }

    public function testWithAutoIncrementNoArg()
    {
        $column = new Column('test', 'foobar', true, false);
        $clone = $column->autoInc();

        $this->assertTrue($clone->getIsAutoInc(), 'The auto-increment flag in the clone was not set to true');
        $this->assertFalse($column->getIsAutoInc(), 'The original instance should not be mutated');
    }

    public function testStaticOfType()
    {
        $column = Column::ofType('TEST');

        $this->assertEquals('TEST', $column->getType(), 'The type was not properly set');
    }

    public function testToSql()
    {
        $column = new Column('TEST');
        $actual = $column->toSql('foo');

        $this->assertEquals('`foo` TEST NULL', $actual, 'The SQL string was not properly generated');
    }

    public function testToSqlWithDefault()
    {
        $column = new Column('TEST', Term::create('bar'));
        $actual = $column->toSql('foo');

        $this->assertEquals('`foo` TEST DEFAULT \'bar\'', $actual, 'The SQL string was not properly generated');
    }

    public function testToSqlNotNull()
    {
        $column = new Column('TEST', null, false);
        $actual = $column->toSql('foo');

        $this->assertEquals('`foo` TEST NOT NULL', $actual, 'The SQL string was not properly generated');
    }

    public function testToSqlWithAutoInc()
    {
        $column = new Column('TEST', null, false, true);
        $actual = $column->toSql('foo');

        $this->assertEquals('`foo` TEST NOT NULL AUTO_INCREMENT', $actual, 'The SQL string was not properly generated');
    }
}
