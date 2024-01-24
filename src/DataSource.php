<?php

namespace RebelCode\Atlas;

use RebelCode\Atlas\Exception\SqlCompileException;

/**
 * An interface for anything that can be used as a data source in a query.
 */
interface DataSource
{
    /**
     * Creates a copy with an alias.
     *
     * @param string|null $alias The string alias or null for no alias.
     * @return static The new instance.
     */
    public function as(?string $alias): self;

    /**
     * Retrieves the data source's alias, if it has one.
     *
     * @return string|null The string alias or null if the data source has no alias.
     */
    public function getAlias(): ?string;

    /**
     * Compiles the source into an SQL fragment.
     *
     * @return string The compiled SQL fragment.
     * @throws SqlCompileException If an error occurred while compiling.
     */
    public function compileSource(): string;
}
