<?php

namespace RebelCode\Atlas\Test\Expression;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Expression\BaseExpr;
use RebelCode\Atlas\Expression\BinaryExpr;
use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Expression\Term;
use RebelCode\Atlas\Expression\UnaryExpr;

class BaseExpressionTest extends TestCase
{
    public function testIsExpression()
    {
        $subject = $this->createMock(BaseExpr::class);

        $this->assertInstanceOf(ExprInterface::class, $subject);
    }

    public function provideBinaryExpData(): array
    {
        return [
            ['and', BinaryExpr::AND],
            ['or', BinaryExpr::OR],
            ['xor', BinaryExpr::XOR],
            ['equals', BinaryExpr::EQ],
            ['notEquals', BinaryExpr::NEQ],
            ['gt', BinaryExpr::GT],
            ['lt', BinaryExpr::LT],
            ['gte', BinaryExpr::GTE],
            ['lte', BinaryExpr::LTE],
            ['is', BinaryExpr::IS],
            ['isNot', BinaryExpr::IS_NOT],
            ['in', BinaryExpr::IN],
            ['notIn', BinaryExpr::NOT_IN],
            ['like', BinaryExpr::LIKE],
            ['notLike', BinaryExpr::NOT_LIKE],
            ['regexp', BinaryExpr::REGEXP],
            ['notRegexp', BinaryExpr::NOT_REGEXP],
            ['plus', BinaryExpr::PLUS],
            ['minus', BinaryExpr::MINUS],
            ['mult', BinaryExpr::MULT],
            ['div', BinaryExpr::DIV],
            ['intDiv', BinaryExpr::INT_DIV],
            ['mod', BinaryExpr::MOD],
            ['leftShift', BinaryExpr::L_SHIFT],
            ['rightShift', BinaryExpr::R_SHIFT],
            ['bitwiseAnd', BinaryExpr::B_AND],
            ['bitwiseOr', BinaryExpr::B_OR],
            ['bitwiseXor', BinaryExpr::B_XOR],
        ];
    }

    /** @dataProvider provideBinaryExpData */
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
            'between' => ['between', BinaryExpr::BETWEEN],
            'not between' => ['notBetween', BinaryExpr::NOT_BETWEEN],
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
            ['bitwiseNeg', UnaryExpr::B_NEG],
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
}
