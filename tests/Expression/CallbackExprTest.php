<?php

namespace RebelCode\Atlas\Test\Expression;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Expression\CallbackExpr;

class CallbackExprTest extends TestCase
{
    public function testRenderClosure()
    {
        $closure = new CallbackExpr(function () {
            return 'foobar';
        });

        $this->assertEquals('foobar', $closure->toSql());
    }

    public function testRenderArrowFn()
    {
        $var = 'foobar';
        $closure = new CallbackExpr(fn () => $var);

        $this->assertEquals('foobar', $closure->toSql());
    }
}
