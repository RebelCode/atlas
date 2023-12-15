<?php

namespace RebelCode\Atlas\Test;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Expression\FnExpr;
use RebelCode\Atlas\Expression\Term;
use RebelCode\Atlas\F;
use RebelCode\Atlas\Test\Helpers\ReflectionHelper;

class FTest extends TestCase
{
    use ReflectionHelper;

    public function testMagicStaticCall()
    {
        $term1 = $this->createMock(Term::class);
        $term2 = $this->createMock(Term::class);
        $expr = F::foo($term1, $term2);

        $this->assertInstanceOf(FnExpr::class, $expr);
        $this->assertEquals('foo', $this->expose($expr)->name);
        $this->assertSame([$term1, $term2], $this->expose($expr)->args);
    }

    public function testCountNoArgs()
    {
        $count = F::COUNT();
        $star = new Term(Term::SPECIAL, '*');

        $this->assertEquals('COUNT', $this->expose($count)->name);
        $this->assertEquals([$star], $this->expose($count)->args);
    }

    public function testCountWithStar()
    {
        $count = F::COUNT('*');
        $star = new Term(Term::SPECIAL, '*');

        $this->assertEquals('COUNT', $this->expose($count)->name);
        $this->assertEquals([$star], $this->expose($count)->args);
    }
}
