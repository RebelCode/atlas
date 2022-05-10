<?php

namespace RebelCode\Atlas\Test\Schema;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Schema\Key;

class KeyTest extends TestCase
{
    public function testConstructIsPrimary()
    {
        $key = new Key(true, ['foo', 'bar']);

        $this->assertTrue($key->isPrimary());
        $this->assertEquals(['foo', 'bar'], $key->getColumns());
    }

    public function testConstructNotIsPrimary()
    {
        $key = new Key(false, ['storm', 'light']);

        $this->assertFalse($key->isPrimary());
        $this->assertEquals(['storm', 'light'], $key->getColumns());
    }
}
