<?php

namespace RebelCode\Atlas\Expression;

/** @psalm-immutable */
interface ExprInterface
{
    public function and($term): BinaryExpr;

    public function or($term): BinaryExpr;

    public function xor($term): BinaryExpr;

    public function eq($term): BinaryExpr;

    public function neq($term): BinaryExpr;

    public function gt($term): BinaryExpr;

    public function lt($term): BinaryExpr;

    public function gte($term): BinaryExpr;

    public function lte($term): BinaryExpr;

    public function is($term): BinaryExpr;

    public function isNot($term): BinaryExpr;

    public function in($term): BinaryExpr;

    public function notIn($term): BinaryExpr;

    public function like($term): BinaryExpr;

    public function notLike($term): BinaryExpr;

    public function betw($term1, $term2): BinaryExpr;

    public function notBetw($term1, $term2): BinaryExpr;

    public function regx($term): BinaryExpr;

    public function notRegx($term): BinaryExpr;

    public function plus($term): BinaryExpr;

    public function minus($term): BinaryExpr;

    public function mult($term): BinaryExpr;

    public function div($term): BinaryExpr;

    public function iDiv($term): BinaryExpr;

    public function mod($term): BinaryExpr;

    public function rShift($term): BinaryExpr;

    public function lShift($term): BinaryExpr;

    public function bAnd($term): BinaryExpr;

    public function bOr($term): BinaryExpr;

    public function bXor($term): BinaryExpr;

    public function bNeg(): UnaryExpr;

    public function not(): UnaryExpr;

    public function neg(): UnaryExpr;

    public function toString(): string;

    public function __toString(): string;
}
