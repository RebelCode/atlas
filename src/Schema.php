<?php

namespace RebelCode\Atlas;

use RebelCode\Atlas\Schema\Column;
use RebelCode\Atlas\Schema\Index;
use RebelCode\Atlas\Schema\Key;

class Schema
{
    /** @var array<string,Column> */
    protected array $columns;
    /** @var array<string,Key> */
    protected array $keys;
    /** @var array<string,Index> */
    protected array $indexes;

    /**
     * Constructor.
     *
     * @param array<string,Column> $columns A mapping of columns, keyed by their name.
     * @param array<string,Key> $keys A mapping of keys, keyed by their name.
     * @param array<string,Index> $indexes A mapping of indexes, keyed by their name.
     */
    public function __construct(array $columns, array $keys = [], array $indexes = [])
    {
        $this->columns = $columns;
        $this->keys = $keys;
        $this->indexes = $indexes;
    }

    /** @return array<string,Column> */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /** @return array<string,Key> */
    public function getKeys(): array
    {
        return $this->keys;
    }

    /** @return array<string,Index> */
    public function getIndexes(): array
    {
        return $this->indexes;
    }

    /**
     * @param array<string,Column> $columns A mapping of columns, keyed by their name.
     * @return static
     */
    public function withColumns(array $columns): self
    {
        $clone = clone $this;
        $clone->columns = $columns;
        return $clone;
    }

    /**
     * @param array<string,Column> $columns A mapping of columns, keyed by their name.
     * @return static
     */
    public function withAddedColumns(array $columns): self
    {
        $clone = clone $this;
        $clone->columns = array_merge($clone->columns, $columns);
        return $clone;
    }

    /**
     * @param list<string> $columns The names of the columns to omit.
     * @return static
     */
    public function withoutColumns(array $columns): self
    {
        $clone = clone $this;
        foreach ($columns as $column) {
            unset($clone->columns[$column]);
        }
        return $clone;
    }

    /**
     * @param array<string,Key> $keys A mapping of keys, keyed by their name.
     * @return static
     */
    public function withKeys(array $keys): self
    {
        $clone = clone $this;
        $clone->keys = $keys;
        return $clone;
    }

    /**
     * @param array<string,Key> $keys A mapping of keys, keyed by their name.
     * @return static
     */
    public function withAddedKeys(array $keys): self
    {
        $clone = clone $this;
        $clone->keys = array_merge($clone->keys, $keys);
        return $clone;
    }

    /**
     * @param list<string> $keys The names of the keys to omit.
     * @return static
     */
    public function withoutKeys(array $keys): self
    {
        $clone = clone $this;
        foreach ($keys as $key) {
            unset($clone->keys[$key]);
        }
        return $clone;
    }

    /**
     * @param array<string,Index> $indexes A mapping of indexes, keyed by their name.
     * @return static
     */
    public function withIndexes(array $indexes): self
    {
        $clone = clone $this;
        $clone->indexes = $indexes;
        return $clone;
    }

    /**
     * @param array<string,Index> $indexes A mapping of indexes, keyed by their name.
     * @return static
     */
    public function withAddedIndexes(array $indexes): self
    {
        $clone = clone $this;
        $clone->indexes = array_merge($clone->indexes, $indexes);
        return $clone;
    }

    /**
     * @param list<string> $indexes The names of the indexes to omit.
     * @return static
     */
    public function withoutIndexes(array $indexes): self
    {
        $clone = clone $this;
        foreach ($indexes as $key) {
            unset($clone->indexes[$key]);
        }
        return $clone;
    }
}
