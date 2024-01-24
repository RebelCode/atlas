<?php

namespace RebelCode\Atlas\Expression;

class BetweenExpr extends BaseExpr
{
    const BETWEEN = 'BETWEEN';
    const NOT_BETWEEN = 'NOT BETWEEN';
    protected ExprInterface $left;
    protected ExprInterface $low;
    protected ExprInterface $high;
    protected bool $not;

    /**
     * Constructor.
     *
     * @param ExprInterface $left The left operand.
     * @param ExprInterface $low The low operand.
     * @param ExprInterface $high The high operand.
     * @param bool $not True if the expression is negated, false otherwise.
     */
    public function __construct(ExprInterface $left, ExprInterface $low, ExprInterface $high, bool $not = false)
    {
        $this->left = $left;
        $this->low = $low;
        $this->high = $high;
        $this->not = $not;
    }

    public function getLeft(): ExprInterface
    {
        return $this->left;
    }

    public function isNegated(): bool
    {
        return $this->not;
    }

    public function getLow(): ExprInterface
    {
        return $this->low;
    }

    public function getHigh(): ExprInterface
    {
        return $this->high;
    }

    /** @inheritDoc */
    protected function toBaseString(): string
    {
        $operator = $this->not ? self::NOT_BETWEEN : self::BETWEEN;
        $left = $this->left->toSql();
        $low = $this->low->toSql();
        $high = $this->high->toSql();

        return "$left $operator($low AND $high)";
    }
}
