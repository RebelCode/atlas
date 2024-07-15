<?php

namespace RebelCode\Atlas\Expression;

class VarExpr extends BaseExpr
{
    protected string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    protected function toBaseString(): string
    {
        return '??{' . $this->name . '}??';
    }
}
