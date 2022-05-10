<?php

namespace RebelCode\Atlas\QueryType;

use InvalidArgumentException;
use RebelCode\Atlas\Exception\QueryCompileException;
use RebelCode\Atlas\Expression\Term;
use RebelCode\Atlas\Query;
use RebelCode\Atlas\QueryCompiler;
use RebelCode\Atlas\QueryTypeInterface;
use RebelCode\Atlas\QueryUtils;
use Throwable;

/** @psalm-immutable */
class Update implements QueryTypeInterface
{
    public const TABLE = 'table';
    public const SET = 'set';
    public const WHERE = 'where';
    public const LIMIT = 'limit';
    public const ORDER = 'order';

    public function compile(Query $query): string
    {
        try {
            $table = QueryUtils::getTableName(self::TABLE, $query);
            $set = $query->get(self::SET);
            $where = $query->get(self::WHERE);
            $order = $query->get(self::ORDER, []);
            $limit = $query->get(self::LIMIT);

            $updateSet = self::compileUpdateSet($set);
            if (empty($updateSet)) {
                throw new \DomainException('UPDATE SET is missing');
            }

            $result = [
                "UPDATE `$table`",
                $updateSet,
                QueryCompiler::compileWhere($where),
                QueryCompiler::compileOrder($order),
                QueryCompiler::compileLimit($limit),
            ];

            return implode(' ', array_filter($result));
        } catch (Throwable $e) {
            throw new QueryCompileException('Cannot compile UPDATE query - ' . $e->getMessage(), $query, $e);
        }
    }

    /**
     * Compiles the SET fragment of the UPDATE query.
     *
     * @psalm-mutation-free
     *
     * @param mixed $updateSet An associative array that maps column names to their values, which can be either scalar
     *                         values or {@link ExprInterface} instances.
     * @return string
     */
    public static function compileUpdateSet($updateSet): string
    {
        if ($updateSet !== null && !is_array($updateSet)) {
            throw new InvalidArgumentException('UPDATE SET is not an array');
        }

        if (empty($updateSet)) {
            return '';
        }

        $list = [];
        foreach ($updateSet as $col => $value) {
            $list[] = "`$col` = " . Term::create($value)->toString();
        }

        return 'SET ' . implode(', ', $list);
    }
}
