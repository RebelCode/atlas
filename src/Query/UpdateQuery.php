<?php

namespace RebelCode\Atlas\Query;

use RebelCode\Atlas\DatabaseAdapter;
use RebelCode\Atlas\Exception\DatabaseException;
use RebelCode\Atlas\Exception\QuerySqlException;
use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Order;
use RebelCode\Atlas\Query;
use Throwable;

class UpdateQuery extends Query
{
    use Query\Traits\HasWhereTrait;
    use Query\Traits\HasOrderTrait;
    use Query\Traits\HasLimitTrait;
    use Query\Traits\HasAssignmentTrait {
        assign as set;
    }

    protected string $table;

    /**
     * Constructor.
     *
     * @param string $from The table to update.
     * @param array<string,mixed> $set An assoc array that maps column names to the update values.
     * @param ExprInterface|null $where Optional WHERE condition.
     * @param Order[] $order Optional list of order instances.
     * @param int|null $limit Optional LIMIT.
     */
    public function __construct(
        ?DatabaseAdapter $adapter = null,
        string $from = '',
        array $set = [],
        ?ExprInterface $where = null,
        array $order = [],
        ?int $limit = null
    ) {
        parent::__construct($adapter);
        $this->table = $from;
        $this->assign = $set;
        $this->where = $where;
        $this->order = $order;
        $this->limit = $limit;
    }

    /**
     * Creates a copy with a different table.
     *
     * @param string $table The table to update.
     * @return static The new instance.
     */
    public function table(string $table): self
    {
        $new = clone $this;
        $new->table = $table;
        return $new;
    }

    /** @inheritDoc */
    public function toSql(): string
    {
        try {
            $result = [
                "UPDATE `$this->table`",
                $this->compileAssignment('SET'),
                $this->compileWhere(),
                $this->compileOrder(),
                $this->compileLimit(),
            ];

            return implode(' ', array_filter($result));
        } catch (Throwable $e) {
            throw new QuerySqlException('Cannot compile UPDATE query - ' . $e->getMessage(), $this, $e);
        }
    }

    /**
     * Executes the UPDATE query.
     *
     * @return int The number of rows affected by the query.
     * @throws DatabaseException If an error occurred while executing the query.
     */
    public function exec(): int
    {
        return $this->getAdapter()->queryNumRows($this->toSql());
    }
}
