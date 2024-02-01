<?php

namespace RebelCode\Atlas\Query;

use RebelCode\Atlas\DatabaseAdapter;
use RebelCode\Atlas\DataSource;
use RebelCode\Atlas\Exception\QuerySqlException;
use RebelCode\Atlas\Expression\ColumnTerm;
use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Expression\Term;
use RebelCode\Atlas\Order;
use RebelCode\Atlas\Query;
use Throwable;

class SelectQuery extends Query implements DataSource
{
    use Query\Traits\HasJoinsTrait;
    use Query\Traits\HasWhereTrait;
    use Query\Traits\HasGroupByTrait;
    use Query\Traits\HasHavingTrait;
    use Query\Traits\HasOrderTrait;
    use Query\Traits\HasLimitTrait;
    use Query\Traits\HasOffsetTrait;

    protected ?DataSource $source = null;
    protected ?string $alias = null;
    /** @var array<string|Term> */
    protected array $columns = [];
    protected bool $calcFoundRows = false;

    /**
     * Constructor.
     *
     * @param DatabaseAdapter|null $adapter Optional database adapter to execute the query.
     * @param DataSource $from The data source to select from.
     * @param ExprInterface|null $where The WHERE condition.
     * @param Order[] $order The ORDER BY clause.
     * @param int|null $limit The LIMIT clause.
     * @param int|null $offset The OFFSET clause.
     */
    public function __construct(
        ?DatabaseAdapter $adapter = null,
        ?DataSource $from = null,
        array $columns = [],
        ?ExprInterface $where = null,
        array $order = [],
        ?int $limit = null,
        ?int $offset = null
    ) {
        parent::__construct($adapter);
        $this->source = $from;
        $this->columns = $columns;
        $this->where = $where;
        $this->limit = $limit;
        $this->offset = $offset;
        $this->order = $order;
    }

    /**
     * Creates a copy with a different FROM clause.
     *
     * @param DataSource $source The source for the FROM clause.
     * @return static The new instance.
     */
    public function from(DataSource $source): self
    {
        $new = clone $this;
        $new->source = $source;
        return $new;
    }

    /**
     * Creates a copy with a different column selection.
     *
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
     * Creates a copy with a different calcFoundRows setting.
     *
     * @param bool $calcFoundRows Whether to calculate the number of rows that
     *        would have been returned if there was no LIMIT clause.
     * @return static The new instance.
     */
    public function calcFoundRows(bool $calcFoundRows = true): self
    {
        $new = clone $this;
        $new->calcFoundRows = $calcFoundRows;
        return $new;
    }

    /**
     * Creates a copy with a new selection offset, based on the current limit and the given page number.
     *
     * @param int $pageNum The page number, starting from 1.
     * @param int|null $pageSize The page size (limit). If null, the limit and offset are removed from the query.
     * @return SelectQuery
     */
    public function page(int $pageNum, ?int $pageSize): self
    {
        if ($pageSize === null) {
            $offset = null;
        } else {
            $offset = ($pageNum - 1) * $pageSize;
        }

        return $this->limit($pageSize)->offset($offset);
    }

    /** @inheritdoc */
    public function as(?string $alias): DataSource
    {
        $clone = clone $this;
        $clone->alias = $alias;
        return $clone;
    }

    /** @inheritdoc */
    public function getAlias(): ?string
    {
        return $this->alias;
    }

    /** @inheritDoc */
    public function toSql(): string
    {
        try {
            $from = ($this->source !== null)
                ? 'FROM ' . $this->source->compileSource()
                : '';

            $calc = $this->calcFoundRows ? 'SQL_CALC_FOUND_ROWS ' : '';

            $result = [
                'SELECT ' . $calc . $this->compileColumnList(),
                $from,
                $this->compileJoins(),
                $this->compileWhere(),
                $this->compileGroupBy(),
                $this->compileHaving(),
                $this->compileOrder(),
            ];

            $limit = $this->compileLimit();
            if ($limit !== '') {
                $result[] = $limit;
                $result[] = $this->compileOffset();
            }

            return implode(' ', array_filter($result));
        } catch (Throwable $e) {
            throw new QuerySqlException('Cannot compile SELECT query - ' . $e->getMessage(), $this, $e);
        }
    }

    /**
     * Compiles the list of columns.
     *
     * @return string
     */
    protected function compileColumnList(): string
    {
        if (empty($this->columns)) {
            return '*';
        }

        $list = [];
        foreach ($this->columns as $key => $value) {
            if ($value === '*') {
                $list[] = '*';
            } else {
                $expr = ($value instanceof ExprInterface)
                    ? $value
                    : new ColumnTerm(null, $value);

                $list[] = $expr->toSql() . (is_numeric($key) ? '' : " AS `$key`");
            }
        }

        return implode(', ', $list);
    }

    /** @inheritDoc */
    public function compileSource(): string
    {
        $result = '(' . $this->toSql() . ')';

        if ($this->alias !== null) {
            $result .= " AS {$this->alias}";
        }

        return $result;
    }

    /**
     * @inheritDoc
     *
     * @return array<string,mixed>[] A list of rows, where each row is a map of column names to values.
     */
    public function exec(): array
    {
        return $this->getAdapter()->queryResults($this->toSql());
    }
}
