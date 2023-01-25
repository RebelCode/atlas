<?php

namespace RebelCode\Atlas\Query;

use DomainException;
use RebelCode\Atlas\Query;
use RebelCode\Atlas\DatabaseAdapter;
use RebelCode\Atlas\Exception\QueryCompileException;
use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Expression\Term;
use RebelCode\Atlas\Group;
use RebelCode\Atlas\Order;
use RebelCode\Atlas\QueryCompiler;
use Throwable;

class SelectQuery extends Query
{
    /** @var string|SelectQuery */
    protected $from;
    /** @var array<string|Term> */
    protected $columns;
    /** @var ExprInterface|null */
    protected $where;
    /** @var Group[] */
    protected $group;
    /** @var ExprInterface|null */
    protected $having;
    /** @var int|null */
    protected $limit;
    /** @var int|null */
    protected $offset;
    /** @var Order[] */
    protected $order;

    /**
     * Constructor.
     *
     * @param DatabaseAdapter|null $adapter Optional database adapter to execute the query.
     * @param string|SelectQuery $from The table to select from.
     * @param array<string|Term> $columns The columns to select.
     * @param ExprInterface|null $where The WHERE condition.
     * @param Group[] $group The GROUP BY clause.
     * @param ExprInterface|null $having The HAVING condition.
     * @param Order[] $order The ORDER BY clause.
     * @param int|null $limit The LIMIT clause.
     * @param int|null $offset The OFFSET clause.
     */
    public function __construct(
        ?DatabaseAdapter $adapter = null,
        $from = '',
        array $columns = [],
        ?ExprInterface $where = null,
        array $group = [],
        ?ExprInterface $having = null,
        array $order = [],
        ?int $limit = null,
        ?int $offset = null
    ) {
        parent::__construct($adapter);
        $this->from = $from;
        $this->columns = $columns;
        $this->where = $where;
        $this->group = $group;
        $this->having = $having;
        $this->limit = $limit;
        $this->offset = $offset;
        $this->order = $order;
    }

    /**
     * Creates a copy with a different FROM clause.
     *
     * @psalm-immutable
     * @param string|SelectQuery $from The new FROM clause.
     * @return static The new instance.
     */
    public function from($from): self
    {
        $new = clone $this;
        $new->from = $from;
        return $new;
    }

    /**
     * Creates a copy with a different column selection.
     *
     * @psalm-immutable
     * @param array<string|Term> $columns The new column selection.
     * @return static The new instance.
     */
    public function columns(array $columns): self
    {
        $new = clone $this;
        $new->columns = $columns;
        return $new;
    }

    /**
     * Creates a copy with a new WHERE condition.
     *
     * @psalm-immutable
     * @param ExprInterface|null $where The new WHERE condition.
     * @return static The new instance.
     */
    public function where(?ExprInterface $expr): self
    {
        $new = clone $this;
        $new->where = $expr;
        return $new;
    }

    /**
     * Creates a copy with a new GROUP BY clause.
     *
     * @psalm-immutable
     * @param Group[] $groupBy An array of {@link Group} instances.
     * @return static The new instance.
     */
    public function groupBy(array $groupBy): self
    {
        $new = clone $this;
        $new->group = $groupBy;
        return $new;
    }

    /**
     * Creates a copy with a new HAVING condition.
     *
     * @psalm-immutable
     * @param ExprInterface|null $having The new HAVING condition.
     * @return static The new instance.
     */
    public function having(?ExprInterface $expr): self
    {
        $new = clone $this;
        $new->having = $expr;
        return $new;
    }

    /**
     * Creates a copy with new ordering.
     *
     * @psalm-immutable
     * @param Order[] $order A list of {@link Order} instances.
     * @return static The new instance.
     */
    public function orderBy(array $order): self
    {
        $new = clone $this;
        $new->order = $order;
        return $new;
    }

    /**
     * Creates a copy with a new selection limit.
     *
     * @psalm-immutable
     * @param int|null $limit The new selection limit, or null for no limit.
     * @return static The new instance.
     */
    public function limit(?int $limit): self
    {
        $new = clone $this;
        $new->limit = $limit;
        return $new;
    }

    /**
     * Creates a copy with a new selection offset.
     *
     * @psalm-immutable
     * @param int|null $offset The new selection offset, or null or zero for no offset.
     * @return static The new instance.
     */
    public function offset(?int $offset): self
    {
        $new = clone $this;
        $new->offset = $offset;
        return $new;
    }

    /**
     * @inheritDoc
     * @psalm-mutation-free
     */
    public function compile(): string
    {
        try {
            $fromStr = QueryCompiler::compileFrom($this->from, null, true);
            if (empty($fromStr)) {
                throw new DomainException('The query source is missing or is invalid');
            }

            $columns = count($this->columns) > 0 ? $this->columns : ['*'];

            $result = [
                'SELECT',
                QueryCompiler::compileColumnList($columns, true),
                QueryCompiler::compileFrom($this->from, null, true),
                QueryCompiler::compileWhere($this->where),
                QueryCompiler::compileGroupBy($this->group),
                QueryCompiler::compileHaving($this->having),
                QueryCompiler::compileOrder($this->order),
                QueryCompiler::compileLimit($this->limit),
                QueryCompiler::compileOffset($this->offset),
            ];

            return implode(' ', array_filter($result));
        } catch (Throwable $e) {
            throw new QueryCompileException('Cannot compile SELECT query - ' . $e->getMessage(), $this, $e);
        }
    }

    /**
     * @inheritDoc
     *
     * @return array<string,mixed>[] A list of rows, where each row is a map of column names to values.
     */
    public function exec(): array
    {
        return $this->getAdapter()->queryResults($this->compile());
    }
}
