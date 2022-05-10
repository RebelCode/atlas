<?php

namespace RebelCode\Atlas\Test;

use RebelCode\Atlas\Schema\Column;
use RebelCode\Atlas\Schema\ForeignKey;
use RebelCode\Atlas\Schema\Index;
use RebelCode\Atlas\Schema\Key;
use RebelCode\Atlas\Schema;
use PHPUnit\Framework\TestCase;

class SchemaTest extends TestCase
{
    public function testConstructorColumns()
    {
        $schema = new Schema($columns = [
            'foo' => $this->createMock(Column::class),
            'bar' => $this->createMock(Column::class),
        ]);

        $this->assertEquals($columns, $schema->getColumns());
    }

    public function testConstructorKeys()
    {
        $schema = new Schema([], $keys = [
            'foo' => $this->createMock(Key::class),
            'bar' => $this->createMock(Key::class),
        ]);

        $this->assertEquals($keys, $schema->getKeys());
    }

    public function testConstructorForeignKeys()
    {
        $schema = new Schema([], [], $foreignKeys = [
            'foo' => $this->createMock(ForeignKey::class),
            'bar' => $this->createMock(ForeignKey::class),
        ]);

        $this->assertEquals($foreignKeys, $schema->getForeignKeys());
    }

    public function testConstructorIndexes()
    {
        $schema = new Schema([], [], [], $indexes = [
            'foo' => $this->createMock(Index::class),
            'bar' => $this->createMock(Index::class),
        ]);

        $this->assertEquals($indexes, $schema->getIndexes());
    }

    public function testWithColumns()
    {
        $schema = new Schema([
            'foo' => $this->createMock(Column::class),
            'bar' => $this->createMock(Column::class),
        ]);

        $clone = $schema->withColumns($newColumns = [
            'baz' => $this->createMock(Column::class),
            'qux' => $this->createMock(Column::class),
        ]);

        $this->assertEquals($newColumns, $clone->getColumns());
    }

    public function testWithAddedColumns()
    {
        $schema = new Schema($columns1 = [
            'foo' => $this->createMock(Column::class),
            'bar' => $this->createMock(Column::class),
        ]);

        $clone = $schema->withAddedColumns($columns2 = [
            'bar' => $this->createMock(Column::class), // Overwrites
            'baz' => $this->createMock(Column::class),
        ]);

        $newColumns = [
            'foo' => $columns1['foo'],
            'bar' => $columns2['bar'],
            'baz' => $columns2['baz'],
        ];

        $this->assertSame($newColumns, $clone->getColumns());
    }

    public function testWithoutColumns()
    {
        $schema = new Schema($columns = [
            'foo' => $this->createMock(Column::class),
            'bar' => $this->createMock(Column::class),
            'baz' => $this->createMock(Column::class),
        ]);

        $clone = $schema->withoutColumns(['bar', 'qux' /* doesn't exist */]);

        $newColumns = [
            'foo' => $columns['foo'],
            'baz' => $columns['baz'],
        ];

        $this->assertSame($newColumns, $clone->getColumns());
    }

    public function testWithKeys()
    {
        $schema = new Schema([], [
            'foo' => $this->createMock(Key::class),
            'bar' => $this->createMock(Key::class),
        ]);

        $clone = $schema->withKeys($newKeys = [
            'baz' => $this->createMock(Key::class),
            'qux' => $this->createMock(Key::class),
        ]);

        $this->assertEquals($newKeys, $clone->getKeys());
    }

    public function testWithAddedKeys()
    {
        $schema = new Schema([], $columns1 = [
            'foo' => $this->createMock(Key::class),
            'bar' => $this->createMock(Key::class),
        ]);

        $clone = $schema->withAddedKeys($columns2 = [
            'bar' => $this->createMock(Key::class), // Overwrites
            'baz' => $this->createMock(Key::class),
        ]);

        $newKeys = [
            'foo' => $columns1['foo'],
            'bar' => $columns2['bar'],
            'baz' => $columns2['baz'],
        ];

        $this->assertSame($newKeys, $clone->getKeys());
    }

    public function testWithoutKeys()
    {
        $schema = new Schema([], $columns = [
            'foo' => $this->createMock(Key::class),
            'bar' => $this->createMock(Key::class),
            'baz' => $this->createMock(Key::class),
        ]);

        $clone = $schema->withoutKeys(['bar', 'qux' /* doesn't exist */]);

        $newKeys = [
            'foo' => $columns['foo'],
            'baz' => $columns['baz'],
        ];

        $this->assertSame($newKeys, $clone->getKeys());
    }

    public function testWithForeignKeys()
    {
        $schema = new Schema([], [], [
            'foo' => $this->createMock(ForeignKey::class),
            'bar' => $this->createMock(ForeignKey::class),
        ]);

        $clone = $schema->withForeignKeys($newForeignKeys = [
            'baz' => $this->createMock(ForeignKey::class),
            'qux' => $this->createMock(ForeignKey::class),
        ]);

        $this->assertEquals($newForeignKeys, $clone->getForeignKeys());
    }

    public function testWithAddedForeignKeys()
    {
        $schema = new Schema([], [], $foreignKeys1 = [
            'foo' => $this->createMock(ForeignKey::class),
            'bar' => $this->createMock(ForeignKey::class),
        ]);

        $clone = $schema->withAddedForeignKeys($foreignKeys2 = [
            'bar' => $this->createMock(ForeignKey::class), // Overwrites
            'baz' => $this->createMock(ForeignKey::class),
        ]);

        $newForeignKeys = [
            'foo' => $foreignKeys1['foo'],
            'bar' => $foreignKeys2['bar'],
            'baz' => $foreignKeys2['baz'],
        ];

        $this->assertSame($newForeignKeys, $clone->getForeignKeys());
    }

    public function testWithoutForeignKeys()
    {
        $schema = new Schema([], [], $foreignKeys = [
            'foo' => $this->createMock(ForeignKey::class),
            'bar' => $this->createMock(ForeignKey::class),
            'baz' => $this->createMock(ForeignKey::class),
        ]);

        $clone = $schema->withoutForeignKeys(['bar', 'qux' /* doesn't exist */]);

        $newForeignKeys = [
            'foo' => $foreignKeys['foo'],
            'baz' => $foreignKeys['baz'],
        ];

        $this->assertSame($newForeignKeys, $clone->getForeignKeys());
    }

    public function testWithIndexes()
    {
        $schema = new Schema([], [], [], [
            'foo' => $this->createMock(Index::class),
            'bar' => $this->createMock(Index::class),
        ]);

        $clone = $schema->withIndexes($newIndexes = [
            'baz' => $this->createMock(Index::class),
            'qux' => $this->createMock(Index::class),
        ]);

        $this->assertEquals($newIndexes, $clone->getIndexes());
    }

    public function testWithAddedIndexes()
    {
        $schema = new Schema([], [], [], $indexes1 = [
            'foo' => $this->createMock(Index::class),
            'bar' => $this->createMock(Index::class),
        ]);

        $clone = $schema->withAddedIndexes($indexes2 = [
            'bar' => $this->createMock(Index::class), // Overwrites
            'baz' => $this->createMock(Index::class),
        ]);

        $newIndexs = [
            'foo' => $indexes1['foo'],
            'bar' => $indexes2['bar'],
            'baz' => $indexes2['baz'],
        ];

        $this->assertSame($newIndexs, $clone->getIndexes());
    }

    public function testWithoutIndexes()
    {
        $schema = new Schema([], [], [], $indexes = [
            'foo' => $this->createMock(Index::class),
            'bar' => $this->createMock(Index::class),
            'baz' => $this->createMock(Index::class),
        ]);

        $clone = $schema->withoutIndexes(['bar', 'qux' /* doesn't exist */]);

        $newIndexes = [
            'foo' => $indexes['foo'],
            'baz' => $indexes['baz'],
        ];

        $this->assertSame($newIndexes, $clone->getIndexes());
    }
}
