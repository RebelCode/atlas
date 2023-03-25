<?php

namespace RebelCode\Atlas\Expression;

/** @psalm-immutable */
interface ExprInterface
{
    /**
     * Creates a copy of the expression with an alias.
     *
     * @param string|null $alias The alias string, or null for no alias.
     * @return static
     */
    public function as(?string $alias): self;

    /** Creates an AND logical expression. */
    public function and($term): BinaryExpr;

    /** Creates an OR logical expression. */
    public function or($term): BinaryExpr;

    /** Creates an XOR logical expression. */
    public function xor($term): BinaryExpr;

    /** Creates an "equals" comparison expression. */
    public function eq($term): BinaryExpr;

    /** Creates a "not equals" comparison expression. */
    public function neq($term): BinaryExpr;

    /** Creates a "greater than" comparison expression. */
    public function gt($term): BinaryExpr;

    /** Creates a "less than" comparison expression. */
    public function lt($term): BinaryExpr;

    /** Creates a "greater than or equal to" comparison expression. */
    public function gte($term): BinaryExpr;

    /** Creates a "less than or equal to" comparison expression. */
    public function lte($term): BinaryExpr;

    /** Creates an "IS" comparison expression. */
    public function is($term): BinaryExpr;

    /** Creates an "IS NOT" comparison expression. */
    public function isNot($term): BinaryExpr;

    /** Creates an "IN" comparison expression. */
    public function in($term): BinaryExpr;

    /** Creates a "NOT IN" comparison expression. */
    public function notIn($term): BinaryExpr;

    /** Creates a "LIKE" comparison expression. */
    public function like($term): BinaryExpr;

    /** Creates a "NOT LIKE" comparison expression. */
    public function notLike($term): BinaryExpr;

    /** Creates a "BETWEEN" comparison expression. */
    public function between($term1, $term2): BetweenExpr;

    /** Creates a "NOT BETWEEN" comparison expression. */
    public function notBetween($term1, $term2): BetweenExpr;

    /** Creates a "REGEXP" comparison expression. */
    public function regex($term): BinaryExpr;

    /** Creates a "NOT REGEXP" comparison expression. */
    public function notRegex($term): BinaryExpr;

    /** Creates an addition arithmetic expression. */
    public function plus($term): BinaryExpr;

    /** Creates a subtraction arithmetic expression. */
    public function minus($term): BinaryExpr;

    /** Creates a multiplication arithmetic expression. */
    public function mult($term): BinaryExpr;

    /** Creates a division arithmetic expression. */
    public function div($term): BinaryExpr;

    /** Creates an integer division arithmetic expression. */
    public function iDiv($term): BinaryExpr;

    /** Creates a modulo arithmetic expression. */
    public function mod($term): BinaryExpr;

    /** Creates a right bit shift arithmetic expression. */
    public function rShift($term): BinaryExpr;

    /** Creates a left bit shift arithmetic expression. */
    public function lShift($term): BinaryExpr;

    /** Creates a bitwise AND expression. */
    public function bAnd($term): BinaryExpr;

    /** Creates a bitwise OR expression. */
    public function bOr($term): BinaryExpr;

    /** Creates a bitwise XOR expression. */
    public function bXor($term): BinaryExpr;

    /** Creates a bitwise negation expression. */
    public function bNeg(): UnaryExpr;

    /** Creates a boolean NOT expression. */
    public function not(): UnaryExpr;

    /** Creates a number negation expression. */
    public function neg(): UnaryExpr;

    /** Compiles the expression into an SQL expression string. */
    public function toSql(): string;
}
