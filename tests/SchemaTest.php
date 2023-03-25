<?php

namespace RebelCode\Atlas\Test;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Schema;
use RebelCode\Atlas\Schema\Column;
use RebelCode\Atlas\Schema\Index;
use RebelCode\Atlas\Schema\Key;

class SchemaTest extends TestCase
{
    public function testCtor()
    {
        $columns = [
            'foo' => $this->createMock(Column::class),
            'bar' => $this->createMock(Column::class),
        ];

        $keys = [
            'foo' => $this->createMock(Key::class),
            'bar' => $this->createMock(Key::class),
        ];

        $indexes = [
            'foo' => $this->createMock(Index::class),
            'bar' => $this->createMock(Index::class),
        ];

        $schema = new Schema($columns, $keys, $indexes);

        $this->assertEquals($columns, $schema->getColumns());
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
        $schema = new Schema([], [], $indexes1 = [
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
        $schema = new Schema([], [], $indexes = [
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
