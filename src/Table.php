<?php

namespace RebelCode\Atlas;

use DomainException;
use RebelCode\Atlas\Exception\NoTableSchemaException;
use RebelCode\Atlas\Expression\ColumnTerm;
use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Expression\Term;
use RebelCode\Atlas\Query\CompoundQuery;
use RebelCode\Atlas\Query\CreateIndexQuery;
use RebelCode\Atlas\Query\CreateTableQuery;
use RebelCode\Atlas\Query\DeleteQuery;
use RebelCode\Atlas\Query\DropTableQuery;
use RebelCode\Atlas\Query\InsertQuery;
use RebelCode\Atlas\Query\SelectQuery;
use RebelCode\Atlas\Query\UpdateQuery;

/** @psalm-immutable */
class Table implements DataSource
{
    protected string $name;
    protected ?string $alias = null;
    protected ?Schema $schema = null;
    protected ?DatabaseAdapter $adapter = null;
    protected ?ExprInterface $where = null;
    /** @var Order[] */
    protected array $order = [];

    /**
     * Constructor.
     *
     * @param string $name The table name.
     * @param Schema|null $schema Optional table schema.
     * @param DatabaseAdapter|null $adapter Optional database adapter to be able to execute queries.
     */
    public function __construct(string $name, ?Schema $schema = null, ?DatabaseAdapter $adapter = null)
    {
        $this->name = $name;
        $this->schema = $schema;
        $this->adapter = $adapter;
    }

    /**
     * Magic getter for retrieving a column term for this table.
     *
     * @param string $name The name of the column.
     */
    public function __get($name): ColumnTerm
    {
        return $this->col($name);
    }

    /** Retrieves the table's name. */
    public function getName(): string
    {
        return $this->name;
    }

    /** Retrieves the table's schema, if it has one. */
    public function getSchema(): ?Schema
    {
        return $this->schema;
    }

    /**
     * Retrieves the database adapter that is used to execute queries.
     *
     * @return DatabaseAdapter|null The database adapter instance, or null if no adapter is set.
     */
    public function getDbAdapter(): ?DatabaseAdapter
    {
        return $this->adapter;
    }

    /** Retrieves the table's WHERE condition. */
    public function getWhere(): ?ExprInterface
    {
        return $this->where;
    }

    /**
     * Retrieves the table's ordering.
     *
     * @return Order[] A list of order instances.
     */
    public function getOrder(): array
    {
        return $this->order;
    }

    /** @inheritDoc */
    public function getAlias(): ?string
    {
        return $this->alias;
    }

    /** @inheritDoc */
    public function compileSource(): string
    {
        $nameStr = '`' . $this->name . '`';

        if ($this->alias !== null) {
            return $nameStr . ' AS `' . $this->alias . '`';
        } else {
            return $nameStr;
        }
    }

    /** @inheritDoc */
    public function as(?string $alias): DataSource
    {
        $table = clone $this;
        $table->alias = $alias;
        return $table;
    }

    /**
     * Creates a column term for a column in this table.
     *
     * This is a good way of starting an expression. Call methods on the returned object to continue building
     * the expression.
     *
     * @param string $column The column name.
     * @return ColumnTerm The created term.
     */
    public function col(string $column): ColumnTerm
    {
        if ($this->schema !== null && !array_key_exists($column, $this->schema->getColumns())) {
            throw new DomainException("Column \"$column\" does not exist on table \"$this->name\"");
        }

        return new ColumnTerm($this->alias ?? $this->name, $column);
    }

    /**
     * Creates a copy of the table with a built-in WHERE condition that is used for all queries.
     * If the table already has a WHERE condition, the new condition will be added using an AND operator.
     *
     * @param ExprInterface $expr The expression.
     * @return Table The new table instance.
     */
    public function where(ExprInterface $expr): Table
    {
        $clone = clone $this;
        $clone->where = ($clone->where === null)
            ? $expr
            : $clone->where->and($expr);

        return $clone;
    }

    /**
     * Creates a copy of the table with a built-in WHERE condition that is used for all queries.
     * If the table already has a WHERE condition, the new condition will be added using an OR operator.
     *
     * @param ExprInterface $expr The expression.
     * @return Table The new table instance.
     */
    public function orWhere(ExprInterface $expr): Table
    {
        $clone = clone $this;
        $clone->where = ($clone->where === null)
            ? $expr
            : $clone->where->or($expr);

        return $clone;
    }

    /**
     * Creates a copy of the table with built-in ordering.
     * If the table already has ordering in its state, the new ordering will be appended.
     *
     * @param Order[] $order A list of {@link Order} instances.
     * @return Table The created table instance.
     */
    public function orderBy(array $order): Table
    {
        $clone = clone $this;
        $clone->order = $clone->order ? array_merge($clone->order, $order) : $order;

        return $clone;
    }

    /**
     * Create a CREATE TABLE query.
     *
     * @param bool $ifNotExists If true, the created query will be a "CREATE TABLE IF NOT EXISTS". Default is true.
     * @param string|null $collate Optional collation name.
     * @return CompoundQuery A compound query that contains the table creation query and any index creation queries.
     */
    public function create(bool $ifNotExists = true, ?string $collate = null): CompoundQuery
    {
        if ($this->schema === null) {
            throw new NoTableSchemaException(
                "Cannot create CREATE TABLE query - table \"$this->name\" has no schema",
                $this
            );
        } else {
            $queries = [
                new CreateTableQuery($this->adapter, $this->name, $ifNotExists, $this->schema, $collate),
            ];

            foreach ($this->schema->getIndexes() as $name => $index) {
                $queries[] = new CreateIndexQuery($this->adapter, $this->name, $name, $index);
            }

            return new CompoundQuery($queries);
        }
    }

    /**
     * Create a DROP TABLE query for the table.
     *
     * @param bool $ifExists If true, the created query will be a "DROP TABLE IF EXISTS". Default is true.
     * @param bool $cascade If true, the query will CASCADE.
     * @return DropTableQuery The created query.
     */
    public function drop(bool $ifExists = true, bool $cascade = false): DropTableQuery
    {
        if ($this->schema === null) {
            throw new NoTableSchemaException(
                "Cannot create DROP TABLE query - table \"$this->name\" has no schema",
                $this
            );
        } else {
            return new DropTableQuery(
                $this->adapter,
                $this->name,
                $ifExists,
                $cascade
            );
        }
    }

    /**
     * Creates a SELECT query for the table.
     *
     * @param array<ExprInterface|string> $columns The columns to select.
     * @param ExprInterface|null $where The WHERE condition.
     * @param Order[] $order The ORDER BY clause.
     * @param int|null $limit The LIMIT clause.
     * @param int|null $offset The OFFSET clause.
     */
    public function select(
        array $columns = [],
        ?ExprInterface $where = null,
        array $order = [],
        ?int $limit = null,
        ?int $offset = null
    ): SelectQuery {
        return new SelectQuery(
            $this->adapter,
            $this,
            $columns,
            $this->useWhereState($where),
            $this->useOrderState($order),
            $limit,
            $offset
        );
    }

    /**
     * Create an INSERT query for the table.
     *
     * @param array<string, mixed>[] $records A list of associative arrays, each representing a row to be inserted.
     * @param array<string, mixed> $assignList Optional assignment list to use in the "ON DUPLICATE KEY" clause.
     */
    public function insert(array $records, array $assignList = []): InsertQuery
    {
        if (empty($records)) {
            throw new DomainException('List of values to insert is empty');
        }

        return new InsertQuery($this->adapter, $this->name, array_keys($records[0]), $records, $assignList);
    }

    /**
     * Create an UPDATE query for the table.
     *
     * @param array $set An associative array with column names as keys and the update values as array values.
     * @param ExprInterface|null $where Optional WHERE condition.
     * @param Order[] $order Optional list of order instances.
     * @param int|null $limit Optional LIMIT.
     * @return UpdateQuery The created query.
     */
    public function update(
        array $set,
        ?ExprInterface $where = null,
        array $order = [],
        ?int $limit = null
    ): UpdateQuery {
        return new UpdateQuery(
            $this->adapter,
            $this->name,
            $set,
            $this->useWhereState($where),
            $this->useOrderState($order),
            $limit
        );
    }

    /**
     * Creates a DELETE query for the table.
     *
     * @param ExprInterface|null $where Optional WHERE condition.
     * @param Order[] $order Optional list of order instances.
     * @param int|null $limit Optional LIMIT.
     * @return DeleteQuery The created query.
     */
    public function delete(
        ?ExprInterface $where = null,
        array $order = [],
        ?int $limit = null
    ): DeleteQuery {
        return new DeleteQuery(
            $this->adapter,
            $this->name,
            $this->useWhereState($where),
            $this->useOrderState($order),
            $limit
        );
    }

    /**
     * Adds the table's WHERE condition to the given WHERE condition.
     *
     * @param ExprInterface|null $where The WHERE condition to add the table's WHERE condition to.
     * @return ExprInterface|null The merged WHERE condition.
     */
    protected function useWhereState(?ExprInterface $where): ?ExprInterface
    {
        if (empty($this->where)) {
            return $where;
        } else {
            return empty($where)
                ? $this->where
                : $this->where->and($where);
        }
    }

    /**
     * Adds the table's ordering to the given list of orders.
     *
     * @param Order[] $order The list of orders to add the table's ordering to.
     * @return Order[] The merged list of orders.
     */
    protected function useOrderState(array $order): array
    {
        if (empty($this->order)) {
            return $order;
        } else {
            return empty($order)
                ? $this->order
                : array_merge($this->order, $order);
        }
    }
}
