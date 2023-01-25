<?php

namespace RebelCode\Atlas\Test;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Atlas;
use RebelCode\Atlas\DatabaseAdapter;
use RebelCode\Atlas\Schema;

class AtlasTest extends TestCase
{
    public function testConstructor()
    {
        $atlas = new Atlas();

        $this->assertNull($atlas->getDbAdapter());
        $this->assertCount(0, $atlas->getTables());
    }

    public function testConstructorWithAdapter()
    {
        $adabter = $this->createMock(DatabaseAdapter::class);
        $atlas = new Atlas($adabter);

        $this->assertSame($adabter, $atlas->getDbAdapter());
        $this->assertCount(0, $atlas->getTables());
    }

    public function testDefault()
    {
        $atlas = Atlas::createDefault();

        $this->assertNull($atlas->getDbAdapter());
        $this->assertCount(0, $atlas->getTables());
    }

    public function testDefaultWithAdapter()
    {
        $adapter = $this->createMock(DatabaseAdapter::class);
        $atlas = Atlas::createDefault($adapter);

        $this->assertSame($adapter, $atlas->getDbAdapter());
    }

    public function testTable()
    {
        $adapter = $this->createMock(DatabaseAdapter::class);
        $atlas = new Atlas($adapter);

        $schema = $this->createMock(Schema::class);
        $table = $atlas->table('test', $schema);

        $this->assertEquals('test', $table->getName());
        $this->assertSame($schema, $table->getSchema());
        $this->assertSame($adapter, $table->getDbAdapter());
    }

    public function testTableCache()
    {
        $atlas = new Atlas();

        $table1 = $atlas->table('test');
        $table2 = $atlas->table('test');

        $this->assertSame($table1, $table2);
    }

    public function testTableCacheWithSchema()
    {
        $atlas = new Atlas();

        $schema = $this->createMock(Schema::class);
        $table1 = $atlas->table('test');
        $table2 = $atlas->table('test', $schema);

        $this->assertNull($table1->getSchema());
        $this->assertSame($schema, $table2->getSchema());
    }
}
