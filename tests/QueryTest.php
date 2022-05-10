<?php

namespace RebelCode\Atlas\Test;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Query;
use RebelCode\Atlas\QueryTypeInterface;
use stdClass;

class QueryTest extends TestCase
{
    public function testConstructorType()
    {
        $query = new Query($type = $this->createMock(QueryTypeInterface::class), []);

        $this->assertSame($type, $query->getType());
    }

    public function testConstructorData()
    {
        $query = new Query($this->createMock(QueryTypeInterface::class), $data = [
            'foo' => new stdClass(), // We use objects to be able to do strict checks via `assertSame()`
            'bar' => new stdClass(),
        ]);

        $this->assertSame($data, $query->getData());
    }

    public function testGet()
    {
        $query = new Query($this->createMock(QueryTypeInterface::class), $data = [
            'foo' => new stdClass(),
            'bar' => new stdClass(),
        ]);

        $this->assertSame($data['foo'], $query->get('foo'));
    }

    public function testGetDefault()
    {
        $query = new Query($this->createMock(QueryTypeInterface::class), [
            'foo' => new stdClass(),
            'bar' => new stdClass(),
        ]);

        $default = 'default';

        $this->assertEquals($default, $query->get('qux', $default));
    }

    public function testGetNoDefault()
    {
        $query = new Query($this->createMock(QueryTypeInterface::class), [
            'foo' => new stdClass(),
            'bar' => new stdClass(),
        ]);

        $this->assertNull($query->get('qux'));
    }

    public function testWithType()
    {
        $query = new Query($type1 = $this->createMock(QueryTypeInterface::class), []);
        $clone = $query->withType($type2 = $this->createMock(QueryTypeInterface::class));

        $this->assertSame($type2, $clone->getType());
        $this->assertSame($type1, $query->getType(), 'The original instance should not be mutated');
    }

    public function testWithData()
    {
        $query = new Query($this->createMock(QueryTypeInterface::class), $data1 = [
            'foo' => new stdClass(),
            'bar' => new stdClass(),
        ]);

        $clone = $query->withData($data2 = [
            'baz' => new stdClass(),
            'qux' => new stdClass(),
        ]);

        $this->assertSame($data2, $clone->getData());
        $this->assertSame($data1, $query->getData(), 'The original instance should not be mutated');
    }

    public function testWithAddedData()
    {
        $query = new Query($this->createMock(QueryTypeInterface::class), $data1 = [
            'foo' => new stdClass(),
            'bar' => new stdClass(),
        ]);

        $clone = $query->withAddedData($data2 = [
            'bar' => new stdClass(),
            'baz' => new stdClass(),
        ]);

        $newData = [
            'foo' => $data1['foo'],
            'bar' => $data2['bar'],
            'baz' => $data2['baz'],
        ];

        $this->assertSame($newData, $clone->getData());
        $this->assertSame($data1, $query->getData(), 'The original instance should not be mutated');
    }

    public function testWithoutData()
    {
        $query = new Query($this->createMock(QueryTypeInterface::class), $data = [
            'foo' => new stdClass(),
            'bar' => new stdClass(),
            'baz' => new stdClass(),
        ]);

        $clone = $query->withoutData(['bar', 'qux' /* does not exist */]);

        $newData = [
            'foo' => $data['foo'],
            'baz' => $data['baz'],
        ];

        $this->assertSame($newData, $clone->getData());
        $this->assertSame($data, $query->getData(), 'The original instance should not be mutated');
    }

    public function testCompile()
    {
        $type = $this->createMock(QueryTypeInterface::class);
        $query = new Query($type, []);

        $expected = 'COMPILED QUERY RESULT';
        $type->expects($this->once())->method('compile')->with($query)->willReturn($expected);

        $this->assertEquals($expected, $query->compile());
    }

    public function testToString()
    {
        $type = $this->createMock(QueryTypeInterface::class);
        $query = new Query($type, []);

        $expected = 'COMPILED QUERY RESULT';
        $type->expects($this->once())->method('compile')->with($query)->willReturn($expected);

        $this->assertEquals($expected, (string) $query);
    }
}
