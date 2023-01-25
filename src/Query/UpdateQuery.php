<?php

namespace RebelCode\Atlas\Query;

use DomainException;
use RebelCode\Atlas\Query;
use RebelCode\Atlas\DatabaseAdapter;
use RebelCode\Atlas\Exception\DatabaseException;
use RebelCode\Atlas\Exception\QueryCompileException;
use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Order;
use RebelCode\Atlas\QueryCompiler;
use Throwable;

class UpdateQuery extends Query
{
    /** @var string */
    protected $table;
    /** @var array<string,mixed> */
    protected $set;
    /** @var ExprInterface|null */
    protected $where;
    /** @var Order[] */
    protected $order;
    /** @var int|null */
    protected $limit;

    /**
     * Constructor.
     *
     * @param string $table The table to update.
     * @param array<string,mixed> $set An assoc array that maps column names to the update values.
     * @param ExprInterface|null $where Optional WHERE condition.
     * @param Order[] $order Optional list of order instances.
     * @param int|null $limit Optional LIMIT.
     */
    public function __construct(
        ?DatabaseAdapter $adapter = null,
        string $table = '',
        array $set = [],
        ?ExprInterface $where = null,
        array $order = [],
        ?int $limit = null
    ) {
        parent::__construct($adapter);
        $this->table = $table;
        $this->set = $set;
        $this->where = $where;
        $this->order = $order;
        $this->limit = $limit;
    }

    /**
     * Creates a copy with a different table.
     *
     * @psalm-immutable
     * @param string $table The table to update.
     * @return static The new instance.
     */
    public function table(string $table): self
    {
        $new = clone $this;
        $new->table = $table;
        return $new;
    }

    /**
     * Creates a copy with a different set of columns to update.
     *
     * @psalm-immutable
     * @param array<string,mixed> $set An assoc array that maps column names to the update values.
     * @return static The new instance.
     */
    public function set(array $set): self
    {
        $new = clone $this;
        $new->set = $set;
        return $new;
    }

    /**
     * Creates a copy with a different WHERE condition.
     *
     * @psalm-immutable
     * @param ExprInterface|null $expr The new WHERE condition.
     * @return static The new instance.
     */
    public function where(?ExprInterface $expr): self
    {
        $new = clone $this;
        $new->where = $expr;
        return $new;
    }

    /**
     * Creates a copy with different ordering.
     *
     * @psalm-immutable
     * @param Order[] $order A list or {@link Order} instances.
     * @return static The new instance.
     */
    public function orderBy(array $order): self
    {
        $new = clone $this;
        $new->order = $order;
        return $new;
    }

    /**
     * Creates a copy with a different limit.
     *
     * @psalm-immutable
     * @param int|null $limit The new limit.
     * @return static The new instance.
     */
    public function limit(?int $limit): self
    {
        $new = clone $this;
        $new->limit = $limit;
        return $new;
    }

    /**
     * @inheritDoc
     * @psalm-mutation-free
     */
    public function compile(): string
    {
        try {
            $table = trim($this->table);
            if (empty($table)) {
                throw new DomainException('Table name is missing');
            }

            $updateSet = QueryCompiler::compileAssignmentList('SET', $this->set);
            if (empty($updateSet)) {
                throw new DomainException('UPDATE SET is missing');
            }

            $result = [
                "UPDATE `$table`",
                $updateSet,
                QueryCompiler::compileWhere($this->where),
                QueryCompiler::compileOrder($this->order),
                QueryCompiler::compileLimit($this->limit),
            ];

            return implode(' ', array_filter($result));
        } catch (Throwable $e) {
            throw new QueryCompileException('Cannot compile UPDATE query - ' . $e->getMessage(), $this, $e);
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
        return $this->getAdapter()->queryNumRows($this->compile());
    }
}
