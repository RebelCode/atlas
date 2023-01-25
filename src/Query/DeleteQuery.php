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

class DeleteQuery extends Query
{
    /** @var string */
    protected $from;
    /** @var ExprInterface|null */
    protected $where;
    /** @var Order[] */
    protected $order;
    /** @var int|null */
    protected $limit;

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
        ?DatabaseAdapter $adapter = null,
        string $from = '',
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
     * Creates a copy with a different table name.
     *
     * @psalm-mutation-free
     * @param string $table The new name of the table to delete from.
     * @return static The new instance.
     */
    public function from(string $table): self
    {
        $new = clone $this;
        $new->from = $table;
        return $new;
    }

    /**
     * Creates a copy with a different limit.
     *
     * @psalm-mutation-free
     * @param ExprInterface|null $expr The new WHERE expression.
     * @return static The new instance.
     */
    public function where(?ExprInterface $expr): self
    {
        $new = clone $this;
        $new->where = $expr;
        return $new;
    }

    /**
     * Creates a copy with a different limit.
     *
     * @psalm-mutation-free
     * @param Order[] $order The new list of columns to ORDER BY.
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
     * @psalm-mutation-free
     * @param int|null $limit The new LIMIT expression, or null for no limit.
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
            $table = trim($this->from);
            if (empty($table)) {
                throw new DomainException('Table name is missing');
            }

            $where = QueryCompiler::compileWhere($this->where);
            $hasWhere = !empty($where);

            $result = [
                'DELETE',
                QueryCompiler::compileFrom($table),
                $where,
                $hasWhere ? QueryCompiler::compileOrder($this->order) : null,
                QueryCompiler::compileLimit($this->limit),
            ];

            return implode(' ', array_filter($result));
        } catch (Throwable $e) {
            throw new QueryCompileException('Cannot compile DELETE query - ' . $e->getMessage(), $this, $e);
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
        return $this->getAdapter()->queryNumRows($this->compile());
    }
}
