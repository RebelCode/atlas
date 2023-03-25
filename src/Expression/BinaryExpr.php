<?php

namespace RebelCode\Atlas\Expression;

/** @psalm-immutable */
class BinaryExpr extends BaseExpr
{
    const EQ = '=';
    const NEQ = '!=';
    const GT = '>';
    const LT = '<';
    const GTE = '>=';
    const LTE = '<=';
    const IS = 'IS';
    const IS_NOT = 'IS NOT';
    const IN = 'IN';
    const NOT_IN = 'NOT IN';
    const LIKE = 'LIKE';
    const NOT_LIKE = 'NOT LIKE';
    const REGEXP = 'REGEXP';
    const NOT_REGEXP = 'NOT REGEXP';
    const PLUS = '+';
    const MINUS = '-';
    const MULT = '*';
    const DIV = '/';
    const INT_DIV = 'DIV';
    const MOD = '%';
    const R_SHIFT = '>>';
    const L_SHIFT = '<<';
    const B_AND = '&';
    const B_OR = '|';
    const B_XOR = '^';
    const AND = 'AND';
    const OR = 'OR';
    const XOR = 'XOR';
    protected ExprInterface $left;
    protected string $operator;
    protected ExprInterface $right;

    /**
     * Constructor
     *
     * @param ExprInterface $left The left operand.
     * @param string $operator The operator.
     * @param ExprInterface $right The right operand.
     */
    public function __construct(ExprInterface $left, string $operator, ExprInterface $right)
    {
        $this->left = $left;
        $this->operator = $operator;
        $this->right = $right;
    }

    /** Retrieves the left operand. */
    public function getLeft(): ExprInterface
    {
        return $this->left;
    }

    /** Retrieves the operator. */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /** Retrieves the right operand. */
    public function getRight(): ExprInterface
    {
        return $this->right;
    }

    /** @inheritDoc */
    protected function toBaseString(): string
    {
        $left = $this->left->toSql();
        $right = $this->right->toSql();

        return "($left $this->operator $right)";
    }
}
