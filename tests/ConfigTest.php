<?php

namespace RebelCode\Atlas\Test;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Config;
use RebelCode\Atlas\QueryType;
use RebelCode\Atlas\QueryType\CreateIndex;
use RebelCode\Atlas\QueryType\CreateTable;
use RebelCode\Atlas\QueryType\Delete;
use RebelCode\Atlas\QueryType\DropTable;
use RebelCode\Atlas\QueryType\Insert;
use RebelCode\Atlas\QueryType\Select;
use RebelCode\Atlas\QueryType\Update;
use RebelCode\Atlas\QueryTypeInterface;

class ConfigTest extends TestCase
{
    public function testConstructor()
    {
        $config = new Config($queryTypes = [
            'foo' => $this->createMock(QueryTypeInterface::class),
            'bar' => $this->createMock(QueryTypeInterface::class),
        ]);

        $this->assertSame($queryTypes, $config->getQueryTypes());
    }

    public function testCreateDefault()
    {
        $config = Config::createDefault();

        $this->assertInstanceOf(CreateTable::class, $config->getQueryType(QueryType::CREATE_TABLE));
        $this->assertInstanceOf(CreateIndex::class, $config->getQueryType(QueryType::CREATE_INDEX));
        $this->assertInstanceOf(DropTable::class, $config->getQueryType(QueryType::DROP_TABLE));
        $this->assertInstanceOf(Select::class, $config->getQueryType(QueryType::SELECT));
        $this->assertInstanceOf(Insert::class, $config->getQueryType(QueryType::INSERT));
        $this->assertInstanceOf(Update::class, $config->getQueryType(QueryType::UPDATE));
        $this->assertInstanceOf(Delete::class, $config->getQueryType(QueryType::DELETE));
    }

    public function testGetQueryTypeExists()
    {
        $config = new Config([
            'foo' => $foo = $this->createMock(QueryTypeInterface::class),
            'bar' => $bar = $this->createMock(QueryTypeInterface::class),
            'baz' => $baz = $this->createMock(QueryTypeInterface::class),
        ]);

        $this->assertSame($foo, $config->getQueryType('foo'));
        $this->assertSame($bar, $config->getQueryType('bar'));
        $this->assertSame($baz, $config->getQueryType('baz'));
    }

    public function testGetQueryTypeNotExists()
    {
        $config = new Config([
            'foo' => $this->createMock(QueryTypeInterface::class),
            'bar' => $this->createMock(QueryTypeInterface::class),
            'baz' => $this->createMock(QueryTypeInterface::class),
        ]);

        $this->assertNull($config->getQueryType('qux'));
    }

    public function testWithOverrides()
    {
        $config = new Config([
            'foo' => $foo = $this->createMock(QueryTypeInterface::class),
            'bar' => $this->createMock(QueryTypeInterface::class),
            'baz' => $baz = $this->createMock(QueryTypeInterface::class),
        ]);

        $bar = $this->createMock(QueryTypeInterface::class);
        $qux = $this->createMock(QueryTypeInterface::class);

        $newConfig = $config->withOverrides([
            'bar' => $bar,
            'qux' => $qux,
        ]);

        $expected = [
            'foo' => $foo,
            'bar' => $bar,
            'baz' => $baz,
            'qux' => $qux,
        ];

        $this->assertSame($expected, $newConfig->getQueryTypes());
    }
}
