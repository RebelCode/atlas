<?php

namespace RebelCode\Atlas\Expression;

/** Represents a SQL function expression. */
class FnExpr extends BaseExpr
{
    protected string $name;
    /** @var ExprInterface[] */
    protected array $args;

    /**
     * Constructor.
     *
     * @param string $name The function name.
     * @param ExprInterface[] $args The function arguments, as a list of expression instances.
     */
    public function __construct(string $name, array $args = [])
    {
        $this->name = $name;
        $this->args = $args;
    }

    /** @inheritDoc */
    protected function toBaseString(): string
    {
        $args = [];
        foreach ($this->args as $arg) {
            $args[] = $arg->toSql();
        }

        return $this->name . '(' . implode(', ', $args) . ')';
    }
}
