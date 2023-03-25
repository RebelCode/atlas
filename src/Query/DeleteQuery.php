<?php

namespace RebelCode\Atlas\Query;

use RebelCode\Atlas\DatabaseAdapter;
use RebelCode\Atlas\Exception\DatabaseException;
use RebelCode\Atlas\Exception\QuerySqlException;
use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Order;
use RebelCode\Atlas\Query;
use Throwable;

/** @psalm-immutable */
class DeleteQuery extends Query
{
    use Query\Traits\HasWhereTrait;
    use Query\Traits\HasOrderTrait;
    use Query\Traits\HasLimitTrait;

    protected string $from;

    /**
     * Constructor.
     *
     * @param DatabaseAdapter|null $adapter The database adapter to execute the query.
     * @param string $from The name of the table to delete from.
     * @param ExprInterface|null $where Optional WHERE expression.
     * @param Order[] $order The list of columns to ORDER BY.
     * @param int|null $limit The limit, or null for no limit.
     */
    public function __construct(
        ?DatabaseAdapter $adapter,
        string $from,
        ?ExprInterface $where = null,
        array $order = [],
        ?int $limit = null
    ) {
        parent::__construct($adapter);
        $this->from = $from;
        $this->where = $where;
        $this->order = $order;
        $this->limit = $limit;
    }

    /**
     * Creates a copy with a different FROM clause.
     *
     * @psalm-mutation-free
     * @param string $from The table name.
     * @return static The new instance.
     */
    public function from(string $from): self
    {
        $clone = clone $this;
        $clone->from = $from;
        return $clone;
    }

    /**
     * @inheritDoc
     * @psalm-mutation-free
     */
    public function toSql(): string
    {
        try {
            $where = $this->compileWhere();
            $hasWhere = !empty($where);

            $result = [
                'DELETE FROM `' . $this->from . '`',
                $where,
                $hasWhere ? $this->compileOrder() : '',
                $this->compileLimit(),
            ];

            return implode(' ', array_filter($result));
        } catch (Throwable $e) {
            throw new QuerySqlException('Cannot compile DELETE query - ' . $e->getMessage(), $this, $e);
        }
    }

    /**
     * Executes the DELETE query.
     *
     * @return int The number of rows affected by the query.
     * @throws DatabaseException If an error occurred while executing the query.
     */
    public function exec(): int
    {
        return $this->getAdapter()->queryNumRows($this->toSql());
    }
}
