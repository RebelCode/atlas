<?php

namespace RebelCode\Atlas\Test\Expression;

use RebelCode\Atlas\Expression\ColumnTerm;
use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Test\Helpers\ReflectionHelper;

class ColumnTermTest extends TestCase
{
    use ReflectionHelper;

    public function testImplementsExprInterface()
    {
        $this->assertInstanceOf(ExprInterface::class, new ColumnTerm(null, ''));
    }

    public function testCtor()
    {
        $col = new ColumnTerm('test', 'foo');

        $this->assertEquals('test', $this->expose($col)->table);
        $this->assertEquals('foo', $this->expose($col)->column);
        $this->assertFalse($this->expose($col)->distinct);
    }

    public function testCtorNullTable()
    {
        $col = new ColumnTerm(null, 'foo');

        $this->assertNull($this->expose($col)->table);
        $this->assertEquals('foo', $this->expose($col)->column);
        $this->assertFalse($this->expose($col)->distinct);
    }

    public function testCtorDistinctTrue()
    {
        $col = new ColumnTerm('test', 'foo', true);

        $this->assertEquals('test', $this->expose($col)->table);
        $this->assertEquals('foo', $this->expose($col)->column);
        $this->assertTrue($this->expose($col)->distinct);
    }

    public function testCtorDistinctFalse()
    {
        $col = new ColumnTerm('test', 'foo', false);

        $this->assertEquals('test', $this->expose($col)->table);
        $this->assertEquals('foo', $this->expose($col)->column);
        $this->assertFalse($this->expose($col)->distinct);
    }

    public function provideDataForToSqlTest(): array
    {
        return [
            'no table' => [null, 'foo', false, '`foo`'],
            'with table' => ['test', 'foo', false, '`test`.`foo`'],
            'no table, distinct' => [null, 'foo', true, 'DISTINCT `foo`'],
            'with table, distinct' => ['test', 'foo', true, 'DISTINCT `test`.`foo`'],
        ];
    }

    /** @dataProvider provideDataForToSqlTest */
    public function testToSqlNoTable(?string $table, string $column, bool $distinct, string $expected)
    {
        $col = new ColumnTerm($table, $column, $distinct);
        $this->assertEquals($expected, $col->toSql());
    }
}
