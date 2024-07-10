<?php

namespace RebelCode\Atlas\Expression;

/** Renders using a callback. */
class CallbackExpr extends BaseExpr
{
    /** @var callable():string */
    private $callback;

    /**
     * Creates a new callback expression.
     * @param callable():string $callback A function that renders the expr SQL.
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /** @inheritdoc */
    protected function toBaseString(): string
    {
        return call_user_func($this->callback);
    }
}
