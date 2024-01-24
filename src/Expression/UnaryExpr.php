<?php

namespace RebelCode\Atlas\Expression;

class UnaryExpr extends BaseExpr
{
    public const NOT = '!';
    public const NEG = '-';
    public const B_NEG = '~';
    protected string $operator;
    protected ExprInterface $operand;

    /** Constructor */
    public function __construct(string $operator, ExprInterface $term)
    {
        $this->operator = $operator;
        $this->operand = $term;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function getOperand(): ExprInterface
    {
        return $this->operand;
    }

    protected function toBaseString(): string
    {
        $term = $this->operand->toSql();

        return "$this->operator($term)";
    }
}
