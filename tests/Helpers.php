<?php

namespace RebelCode\Atlas\Test;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Exception\QueryCompileException;
use RebelCode\Atlas\Query;
use RebelCode\Atlas\QueryTypeInterface;

class Helpers
{
    public static function expectQueryCompileException(Query $query, QueryTypeInterface $queryType)
    {
        try {
            $queryType->compile($query);
            TestCase::fail('Expected a QueryCompileException to be thrown');
        } catch (QueryCompileException $exception) {
            TestCase::assertSame(
                $query,
                $exception->getQuery(),
                'The return value of QueryCompileException::getQuery() is not the query that was being compiled'
            );
        }
    }
}
