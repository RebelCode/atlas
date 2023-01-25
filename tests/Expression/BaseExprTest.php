<?php

namespace RebelCode\Atlas\Test\Expression;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Expression\BaseExpr;
use RebelCode\Atlas\Expression\BinaryExpr;
use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Expression\Term;
use RebelCode\Atlas\Expression\UnaryExpr;

class BaseExprTest extends TestCase
{
    public function testIsExpression()
    {
        $subject = $this->createMock(BaseExpr::class);

        $this->assertInstanceOf(ExprInterface::class, $subject);
    }

    public function provideBinaryExprData(): array
    {
        return [
            'and' => ['and', BinaryExpr::AND],
            'or' => ['or', BinaryExpr::OR],
            'xor' => ['xor', BinaryExpr::XOR],
            'eq' => ['eq', BinaryExpr::EQ],
            'neq' => ['neq', BinaryExpr::NEQ],
            'gt' => ['gt', BinaryExpr::GT],
            'lt' => ['lt', BinaryExpr::LT],
            'gte' => ['gte', BinaryExpr::GTE],
            'lte' => ['lte', BinaryExpr::LTE],
            'is' => ['is', BinaryExpr::IS],
            'isNot' => ['isNot', BinaryExpr::IS_NOT],
            'in' => ['in', BinaryExpr::IN],
            'notIn' => ['notIn', BinaryExpr::NOT_IN],
            'like' => ['like', BinaryExpr::LIKE],
            'notLike' => ['notLike', BinaryExpr::NOT_LIKE],
            'regx' => ['regx', BinaryExpr::REGEXP],
            'notRegx' => ['notRegx', BinaryExpr::NOT_REGEXP],
            'plus' => ['plus', BinaryExpr::PLUS],
            'minus' => ['minus', BinaryExpr::MINUS],
            'mult' => ['mult', BinaryExpr::MULT],
            'div' => ['div', BinaryExpr::DIV],
            'iDiv' => ['iDiv', BinaryExpr::INT_DIV],
            'mod' => ['mod', BinaryExpr::MOD],
            'lShift' => ['lShift', BinaryExpr::L_SHIFT],
            'rShift' => ['rShift', BinaryExpr::R_SHIFT],
            'bAnd' => ['bAnd', BinaryExpr::B_AND],
            'bOr' => ['bOr', BinaryExpr::B_OR],
            'bXor' => ['bXor', BinaryExpr::B_XOR],
        ];
    }

    /** @dataProvider provideBinaryExprData */
    public function testBinaryExpr(string $method, string $operator)
    {
        $subject = $this->getMockBuilder(BaseExpr::class)
                        ->enableProxyingToOriginalMethods()
                        ->getMockForAbstractClass();

        $term = $this->createMock(ExprInterface::class);
        $result = call_user_func([$subject, $method], $term);

        $this->assertInstanceOf(BinaryExpr::class, $result, 'Result is not a binary expression instance');
        $this->assertSame($subject, $result->getLeft(), 'The left operand is not the subject');
        $this->assertSame($term, $result->getRight(), 'The right operand is not the argument');
        $this->assertEquals($operator, $result->getOperator(), 'The operator is incorrect');
    }

    public function provideBetweenExprData(): array
    {
        return [
            'between' => ['betw', BinaryExpr::BETWEEN],
            'not between' => ['notBetw', BinaryExpr::NOT_BETWEEN],
        ];
    }

    /** @dataProvider provideBetweenExprData */
    public function testBetweenExpr($method, $operator)
    {
        $subject = $this->getMockBuilder(BaseExpr::class)
                        ->enableProxyingToOriginalMethods()
                        ->getMockForAbstractClass();

        $term1 = $this->createMock(ExprInterface::class);
        $term2 = $this->createMock(ExprInterface::class);
        $result = call_user_func_array([$subject, $method], [$term1, $term2]);

        $right = $result->getRight();

        $this->assertInstanceOf(BinaryExpr::class, $result, 'Result is not a binary expression instance');
        $this->assertSame($subject, $result->getLeft(), 'The left operand is not the subject');
        $this->assertInstanceOf(Term::class, $right, 'The right operand is not a term');
        $this->assertEquals([$term1, $term2], $right->getValue(), 'The right operand is not an array of the arguments');
        $this->assertEquals($operator, $result->getOperator(), 'The operator is incorrect');
    }

    public function provideUnaryExprData(): array
    {
        return [
            ['not', UnaryExpr::NOT],
            ['neg', UnaryExpr::NEG],
            ['bNeg', UnaryExpr::B_NEG],
        ];
    }

    /** @dataProvider provideUnaryExprData */
    public function testUnaryExpr(string $method, string $operator)
    {
        $subject = $this->getMockBuilder(BaseExpr::class)
                        ->enableProxyingToOriginalMethods()
                        ->getMockForAbstractClass();

        $result = call_user_func([$subject, $method]);

        $this->assertInstanceOf(UnaryExpr::class, $result, 'Result is not a unary expression instance');
        $this->assertSame($subject, $result->getOperand(), 'The operand is not the subject');
        $this->assertEquals($operator, $result->getOperator(), 'The operator is incorrect');
    }

    public function testCastString()
    {
        $term = $this->createPartialMock(BaseExpr::class, ['toString']);
        $term->expects($this->once())->method('toString')->willReturn('69');

        $this->assertIsString((string) $term);
    }
}
