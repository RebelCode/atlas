<?php

namespace RebelCode\Atlas;

use DomainException;
use RebelCode\Atlas\Exception\MissingQueryTypeException;
use RebelCode\Atlas\Exception\NoTableSchemaException;
use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Expression\Term;
use RebelCode\Atlas\Query\DeleteQuery;
use RebelCode\Atlas\Query\InsertQuery;
use RebelCode\Atlas\Query\SelectQuery;
use RebelCode\Atlas\Query\UpdateQuery;
use RebelCode\Atlas\QueryType\CreateIndex;
use RebelCode\Atlas\QueryType\CreateTable;
use RebelCode\Atlas\QueryType\Delete;
use RebelCode\Atlas\QueryType\DropTable;
use RebelCode\Atlas\QueryType\Insert;
use RebelCode\Atlas\QueryType\Select;
use RebelCode\Atlas\QueryType\Update;

/** @psalm-immutable */
class Table
{
    /** @var Config */
    protected $config;

    /** @var string */
    protected $name;

    /** @var Schema|null */
    protected $schema;

    /** @var ExprInterface|null */
    protected $where;

    /** @var Order[] */
    protected $order;

    /**
     * Constructor.
     *
     * @param Config $config The Atlas configuration.
     * @param string $name The table name.
     * @param Schema|null $schema Optional table schema.
     */
    public function __construct(Config $config, string $name, ?Schema $schema = null)
    {
        $this->config = $config;
        $this->name = $name;
        $this->schema = $schema;
        $this->where = null;
        $this->order = [];
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

    /**
     * Begins building an expression with a table's column. Call methods on the returned object to continue building
     * the expression.
     *
     * @param string $column The column name.
     * @return Term The created term.
     */
    public function column(string $column): Term
    {
        if ($this->schema !== null && !array_key_exists($column, $this->schema->getColumns())) {
            throw new DomainException("Column \"$column\" does not exist on table \"$this->name\"");
        }

        return new Term(Term::COLUMN, $column);
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
     * @return Query[] The created queries: one to create the table and one to create the table indices, if any.
     */
    public function create(bool $ifNotExists = true, ?string $collate = null): array
    {
        if ($this->schema === null) {
            throw new NoTableSchemaException(
                "Cannot create CREATE TABLE query - table \"$this->name\" has no schema",
                $this
            );
        } else {
            $queries = [
                new Query($this->getQueryType(QueryType::CREATE_TABLE), [
                    CreateTable::NAME => $this->name,
                    CreateTable::SCHEMA => $this->schema,
                    CreateTable::IF_NOT_EXISTS => $ifNotExists,
                    CreateTable::COLLATE => $collate,
                ]),
            ];

            foreach ($this->schema->getIndexes() as $name => $index) {
                $queries[] = new Query($this->getQueryType(QueryType::CREATE_INDEX), [
                    CreateIndex::TABLE => $this->name,
                    CreateIndex::NAME => $name,
                    CreateIndex::INDEX => $index,
                ]);
            }

            return $queries;
        }
    }

    /**
     * Create a DROP TABLE query for the table.
     *
     * @param bool $ifExists If true, the created query will be a "DROP TABLE IF EXISTS". Default is true.
     * @param bool $cascade If true, the query will CASCADE.
     * @return Query The created query.
     */
    public function drop(bool $ifExists = true, bool $cascade = false): Query
    {
        if ($this->schema === null) {
            throw new NoTableSchemaException(
                "Cannot create DROP TABLE query - table \"$this->name\" has no schema",
                $this
            );
        } else {
            return new Query($this->getQueryType(QueryType::DROP_TABLE), [
                DropTable::TABLE => $this->name,
                DropTable::IF_EXISTS => $ifExists,
                DropTable::CASCADE => $cascade,
            ]);
        }
    }

    /**
     * Creates a SELECT query for the table.
     *
     * @param string[]|null $columns Optional list of columns to select.
     * @param ExprInterface|null $where Optional WHERE condition.
     * @param Order[]|null $order Optional list of order instances.
     * @param int|null $limit Optional LIMIT.
     * @param int|null $offset Optional OFFSET.
     * @return SelectQuery The created query.
     */
    public function select(
        ?array $columns = null,
        ?ExprInterface $where = null,
        ?array $order = null,
        ?int $limit = null,
        ?int $offset = null
    ): SelectQuery {
        return new SelectQuery($this->getQueryType(QueryType::SELECT), [
            Select::FROM => $this->name,
            Select::COLUMNS => empty($columns) ? ['*'] : $columns,
            Select::WHERE => $this->useWhereState($where),
            Select::ORDER => $this->useOrderState($order),
            Select::LIMIT => $limit,
            Select::OFFSET => $offset,
        ]);
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

        return new InsertQuery($this->getQueryType(QueryType::INSERT), [
            Insert::TABLE => $this->name,
            Insert::COLUMNS => array_keys($records[0]),
            Insert::VALUES => $records,
            Insert::ON_DUPLICATE_KEY => $assignList,
        ]);
    }

    /**
     * Create an UPDATE query for the table.
     *
     * @param array $set An associative array with column names as keys and the update values as array values.
     * @param ExprInterface|null $where Optional WHERE condition.
     * @param Order[]|null $order Optional list of order instances.
     * @param int|null $limit Optional LIMIT.
     * @return UpdateQuery The created query.
     */
    public function update(
        array $set,
        ?ExprInterface $where = null,
        ?array $order = null,
        ?int $limit = null
    ): UpdateQuery {
        return new UpdateQuery($this->getQueryType(QueryType::UPDATE), [
            Update::TABLE => $this->name,
            Update::SET => $set,
            Update::WHERE => $this->useWhereState($where),
            Update::ORDER => $this->useOrderState($order),
            Update::LIMIT => $limit,
        ]);
    }

    /**
     * Creates a DELETE query for the table.
     *
     * @param ExprInterface|null $where Optional WHERE condition.
     * @param Order[]|null $order Optional list of order instances.
     * @param int|null $limit Optional LIMIT.
     * @return DeleteQuery The created query.
     */
    public function delete(
        ?ExprInterface $where = null,
        ?array $order = null,
        ?int $limit = null
    ): DeleteQuery {
        return new DeleteQuery($this->getQueryType(QueryType::DELETE), [
            Delete::FROM => $this->name,
            Delete::WHERE => $this->useWhereState($where),
            Delete::ORDER => $this->useOrderState($order),
            Delete::LIMIT => $limit,
        ]);
    }

    /**
     * Creates a new query for the table.
     *
     * @param string $type The query type.
     * @param array<string, mixed> $data The query data.
     * @return Query The created query.
     */
    public function query(string $type, array $data): Query
    {
        return new Query($this->getQueryType($type), array_merge($data, [
            'table' => $this->name,
        ]));
    }

    /**
     * Utility method for retrieving a query type from the config, throwing an exception if it's not found.
     */
    protected function getQueryType(string $typeKey): QueryTypeInterface
    {
        $type = $this->config->getQueryType($typeKey);

        if ($type === null) {
            throw new MissingQueryTypeException("Query type \"$typeKey\" is missing in config", $typeKey);
        }

        return $type;
    }

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

    protected function useOrderState(?array $order): ?array
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
