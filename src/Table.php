<?php

namespace RebelCode\Atlas;

use DomainException;
use RebelCode\Atlas\Exception\MissingQueryTypeException;
use RebelCode\Atlas\Exception\NoTableSchemaException;
use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Expression\Term;
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

    public function __construct(Config $config, string $name, ?Schema $schema = null)
    {
        $this->config = $config;
        $this->name = $name;
        $this->schema = $schema;
        $this->where = null;
        $this->order = [];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSchema(): ?Schema
    {
        return $this->schema;
    }

    public function getWhere(): ?ExprInterface
    {
        return $this->where;
    }

    /** @return Order[] */
    public function getOrder(): array
    {
        return $this->order;
    }

    public function column(string $column): Term
    {
        if ($this->schema !== null && !array_key_exists($column, $this->schema->getColumns())) {
            throw new DomainException("Column \"$column\" does not exist on table \"$this->name\"");
        }

        return new Term(Term::COLUMN, $column);
    }

    public function where(ExprInterface $expr): Table
    {
        $clone = clone $this;
        $clone->where = ($clone->where === null)
            ? $expr
            : $clone->where->and($expr);

        return $clone;
    }

    public function orWhere(ExprInterface $expr): Table
    {
        $clone = clone $this;
        $clone->where = ($clone->where === null)
            ? $expr
            : $clone->where->or($expr);

        return $clone;
    }

    /**
     * @param Order[]
     * @return Table
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
     * @return Query[]
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
                $this->createQuery(QueryType::CREATE_TABLE, [
                    CreateTable::NAME => $this->name,
                    CreateTable::SCHEMA => $this->schema,
                    CreateTable::IF_NOT_EXISTS => $ifNotExists,
                    CreateTable::COLLATE => $collate,
                ]),
            ];

            foreach ($this->schema->getIndexes() as $name => $index) {
                $queries[] = $this->createQuery(QueryType::CREATE_INDEX, [
                    CreateIndex::TABLE => $this->name,
                    CreateIndex::NAME => $name,
                    CreateIndex::INDEX => $index,
                ]);
            }

            return $queries;
        }
    }

    /** Create a DROP TABLE query. */
    public function drop(bool $ifExists = true, bool $cascade = false): Query
    {
        if ($this->schema === null) {
            throw new NoTableSchemaException(
                "Cannot create DROP TABLE query - table \"$this->name\" has no schema",
                $this
            );
        } else {
            return $this->createQuery(QueryType::DROP_TABLE, [
                DropTable::TABLE => $this->name,
                DropTable::IF_EXISTS => $ifExists,
                DropTable::CASCADE => $cascade,
            ]);
        }
    }

    /** Create a SELECT query. */
    public function select(
        ?array $columns = null,
        ?ExprInterface $where = null,
        ?array $order = null,
        ?int $limit = null,
        ?int $offset = null
    ): Query {
        return $this->createQuery(QueryType::SELECT, [
            Select::FROM => $this->name,
            Select::COLUMNS => empty($columns) ? ['*'] : $columns,
            Select::WHERE => $this->useWhereState($where),
            Select::ORDER => $this->useOrderState($order),
            Select::LIMIT => $limit,
            Select::OFFSET => $offset,
        ]);
    }

    /**
     * Create an INSERT query.
     *
     * @param array<string, mixed>[] $records
     * @return Query
     */
    public function insert(array $records): Query
    {
        if (empty($records)) {
            throw new DomainException('List of values to insert is empty');
        }

        return $this->createQuery(QueryType::INSERT, [
            Insert::TABLE => $this->name,
            Insert::COLUMNS => array_keys($records[0]),
            Insert::VALUES => $records,
        ]);
    }

    public function update(
        array $set,
        ?ExprInterface $where = null,
        ?array $order = null,
        ?int $limit = null
    ): Query {
        return $this->createQuery(QueryType::SELECT, [
            Update::TABLE => $this->name,
            Update::SET => $set,
            Update::WHERE => $this->useWhereState($where),
            Update::ORDER => $this->useOrderState($order),
            Update::LIMIT => $limit,
        ]);
    }

    public function delete(
        ?ExprInterface $where = null,
        ?array $order = null,
        ?int $limit = null
    ): Query {
        return $this->createQuery(QueryType::SELECT, [
            Delete::FROM => $this->name,
            Delete::WHERE => $this->useWhereState($where),
            Delete::ORDER => $this->useOrderState($order),
            Delete::LIMIT => $limit,
        ]);
    }

    /** Utility method for creating queries with the type extracted from config. */
    protected function createQuery(string $typeKey, array $data): Query
    {
        $type = $this->config->getQueryType($typeKey);

        if ($type === null) {
            throw new MissingQueryTypeException("Query type \"$typeKey\" is missing in config", $typeKey);
        }

        return new Query($type, $data);
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
