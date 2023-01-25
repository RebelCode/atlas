<?php

namespace RebelCode\Atlas\Test;

use RebelCode\Atlas\Atlas;
use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Config;
use RebelCode\Atlas\DatabaseAdapter;
use RebelCode\Atlas\Schema;
use RebelCode\Atlas\Table;

class AtlasTest extends TestCase
{
    public function testConstructor()
    {
        $config = $this->createMock(Config::class);
        $atlas = new Atlas($config);

        $this->assertSame($config, $atlas->getConfig());
        $this->assertCount(0, $atlas->getTables());
    }

    public function testDefault()
    {
        $atlas = Atlas::createDefault();

        $this->assertEquals(Config::createDefault(), $atlas->getConfig());
        $this->assertCount(0, $atlas->getTables());
    }

    public function testDefaultWithAdapter()
    {
        $adapter = $this->createMock(DatabaseAdapter::class);
        $atlas = Atlas::createDefault($adapter);

        $this->assertSame($adapter, $atlas->getConfig()->getDbAdapter());
    }

    public function testTable()
    {
        $config = $this->createMock(Config::class);
        $atlas = new Atlas($config);

        $table = $atlas->table('test');

        $this->assertEquals('test', $table->getName());
        $this->assertNull($table->getSchema());
    }

    public function testTableWithSchema()
    {
        $config = $this->createMock(Config::class);
        $atlas = new Atlas($config);

        $schema = $this->createMock(Schema::class);
        $table = $atlas->table('test', $schema);

        $this->assertEquals('test', $table->getName());
        $this->assertSame($schema, $table->getSchema());
    }

    public function testTableCache()
    {
        $config = $this->createMock(Config::class);
        $atlas = new Atlas($config);

        $table1 = $atlas->table('test');
        $table2 = $atlas->table('test');

        $this->assertSame($table1, $table2);
    }

    public function testTableCacheWithSchema()
    {
        $config = $this->createMock(Config::class);
        $atlas = new Atlas($config);

        $schema = $this->createMock(Schema::class);
        $table1 = $atlas->table('test');
        $table2 = $atlas->table('test', $schema);

        $this->assertNull($table1->getSchema());
        $this->assertSame($schema, $table2->getSchema());
    }
}
