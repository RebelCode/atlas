<?php

namespace RebelCode\Atlas;

use LogicException;
use Throwable;

class Query
{
    /** @var QueryTypeInterface */
    protected $type;
    /** @var array<string,mixed> */
    protected $data;
    /** @var DatabaseAdapter|null */
    private $adapter;

    /**
     * Constructor.
     *
     * @param QueryTypeInterface $type The query type.
     * @param array<string,mixed> $data An associative array of query data.
     * @param DatabaseAdapter|null $adapter Optional database adapter instance.
     */
    public function __construct(QueryTypeInterface $type, array $data, ?DatabaseAdapter $adapter = null)
    {
        $this->type = $type;
        $this->data = $data;
        $this->adapter = $adapter;
    }

    /** @psalm-mutation-free */
    public function getType(): QueryTypeInterface
    {
        return $this->type;
    }

    /**
     * @return static
     * @psalm-mutation-free
     */
    public function withType(QueryTypeInterface $type): self
    {
        $clone = clone $this;
        $clone->type = $type;
        return $clone;
    }

    /**
     * Retrieve a single entry from the query's data.
     *
     * @param string $key The key of the query data to retrieve.
     * @param mixed $default Optional default value to return if no query data corresponds with the given key.
     * @return mixed
     * @psalm-mutation-free
     */
    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * @return array<string,mixed>
     * @psalm-mutation-free
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array<string,mixed> $data An associative array of query data.
     * @return static
     * @psalm-mutation-free
     */
    public function withData(array $data): self
    {
        $clone = clone $this;
        $clone->data = $data;
        return $clone;
    }

    /**
     * @param array<string,mixed> $data An associative array of query data.
     * @return static
     * @psalm-mutation-free
     */
    public function withAddedData(array $data): self
    {
        $clone = clone $this;
        $clone->data = array_merge($clone->data, $data);
        return $clone;
    }

    /**
     * @param list<string> $keys A list of data keys to omit.
     * @return static
     * @psalm-mutation-free
     */
    public function withoutData(array $keys): self
    {
        $clone = clone $this;
        foreach ($keys as $key) {
            unset($clone->data[$key]);
        }
        return $clone;
    }

    /**
     * Compiles the query into a string.
     *
     * @return string
     * @psalm-mutation-free
     */
    public function compile(): string
    {
        return $this->type->compile($this);
    }

    /**
     * @return string
     * @psalm-mutation-free
     */
    public function __toString(): string
    {
        if (version_compare(PHP_VERSION, '7.4.0', '<')) {
            try {
                return $this->compile();
            } catch (Throwable $throwable) {
                return '';
            }
        } else {
            return $this->compile();
        }
    }

    /**
     * @return DatabaseAdapter|never-returns
     * @psalm-mutation-free
     */
    protected function getAdapter(): DatabaseAdapter
    {
        if ($this->adapter === null) {
            throw new LogicException('Cannot execute query; please provide a database adapter.');
        }

        return $this->adapter;
    }
}
