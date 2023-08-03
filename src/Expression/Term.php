<?php

namespace RebelCode\Atlas\Expression;

use InvalidArgumentException;
use Iterator;
use Traversable;

/** @psalm-immutable */
class Term extends BaseExpr
{
    public const NUMBER = 0;
    public const STRING = 1;
    public const BOOLEAN = 2;
    public const LIST = 3;
    public const NULL = 4;

    /** @var mixed */
    protected $value;
    /** @psalm-var Term::* */
    protected int $type;

    /**
     * Constructor.
     *
     * @param int $type The term's type. See the constants in this class.
     * @param mixed $value The value.
     *
     * @psalm-param Term::* $type
     */
    public function __construct(int $type, $value)
    {
        $this->value = $value;
        $this->type = $type;
    }

    /** @psalm-return Term::* */
    public function getType(): int
    {
        return $this->type;
    }

    /** @return mixed */
    public function getValue()
    {
        return $this->value;
    }

    /** @psalm-suppress PossiblyInvalidCast */
    protected function toBaseString(): string
    {
        switch ($this->type) {
            case self::NULL:
                return 'NULL';
            case self::NUMBER:
                return (string) $this->value;
            case self::STRING:
                return "'$this->value'";
            case self::BOOLEAN:
                return $this->value ? 'TRUE' : 'FALSE';
            case self::LIST:
                /** @psalm-var ExprInterface[] $elements */
                $elements = $this->value;

                $elementsStr = array_map(function (ExprInterface $element) {
                    return $element->toSql();
                }, $elements);

                return '(' . implode(', ', $elementsStr) . ')';
            default:
                throw new InvalidArgumentException("Term has unknown type: \"$this->type\"");
        }
    }

    /**
     * Creates a term from a value, automatically detecting the type.
     *
     * @psalm-mutation-free
     *
     * @param mixed $value
     * @return ExprInterface
     */
    public static function create($value): ExprInterface
    {
        if ($value instanceof ExprInterface) {
            return $value;
        }

        $type = self::detectType($value);

        if ($type === self::LIST) {
            $list = [];
            foreach ($value as $i => $elem) {
                if (!$elem instanceof self) {
                    $list[$i] = self::create($elem);
                }
            }
            $value = $list;
        }

        return new self($type, $value);
    }

    /**
     * @psalm-mutation-free
     * @psalm-return Term::*
     */
    public static function detectType($value): int
    {
        $type = gettype($value);

        switch ($type) {
            case 'integer':
            case 'double':
                return self::NUMBER;
            case 'string':
                return self::STRING;
            case 'boolean':
                return self::BOOLEAN;
            case "array":
                return self::LIST;
            case "NULL":
                return self::NULL;
            default:
                if ($value instanceof Traversable) {
                    return self::LIST;
                } else {
                    throw new InvalidArgumentException('Unsupported type for term value: ' . gettype($value));
                }
        }
    }
}
