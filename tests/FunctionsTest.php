<?php

namespace RebelCode\Atlas\Test;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Expression\Term;
use RebelCode\Atlas\Order;
use function RebelCode\Atlas\asc;
use function RebelCode\Atlas\col;
use function RebelCode\Atlas\desc;

class FunctionsTest extends TestCase
{
    public function testCol()
    {
        $this->assertEquals(col('name'), new Term(Term::COLUMN, 'name'));
    }

    public function testAsc()
    {
        $this->assertEquals(asc('name'), new Order('name', Order::ASC));
    }

    public function testDesc()
    {
        $this->assertEquals(desc('name'), new Order('name', Order::DESC));
    }
}
