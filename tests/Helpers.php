<?php

namespace RebelCode\Atlas\Test;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Query;
use RebelCode\Atlas\Exception\QueryCompileException;

class Helpers
{
    public static function expectQueryCompileException(Query $query)
    {
        try {
            $query->compile();
            TestCase::fail('Expected a QueryCompileException to be thrown');
        } catch (QueryCompileException $exception) {
            TestCase::assertSame(
                $query,
                $exception->getQuery(),
                'The return value of QueryCompileException::getQuery() is not the query that was being compiled'
            );
        }
    }

    public static function property($object, string $name)
    {
        $ref = new \ReflectionProperty(get_class($object), $name);
        $ref->setAccessible(true);
        return $ref->getValue($object);
    }
}
