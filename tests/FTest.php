<?php

namespace RebelCode\Atlas\Test;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Expression\Term;
use RebelCode\Atlas\Expression\UnaryExpr;
use RebelCode\Atlas\F;

class FTest extends TestCase
{
    public function testMagicStaticCall()
    {
        $term = $this->createMock(Term::class);
        $expr = F::foo($term);

        $this->assertInstanceOf(UnaryExpr::class, $expr);
        $this->assertEquals('foo',$expr->getOperator());
        $this->assertSame($term, $expr->getOperand());
    }
}
