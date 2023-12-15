<?php

namespace RebelCode\Atlas;

use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Expression\FnExpr;
use RebelCode\Atlas\Expression\Term;

/**
 * Helper class for easily creating unary expressions for SQL functions.
 *
 * STRING SQL FUNCTIONS
 * @method static FnExpr ASCII(...$args)
 * @method static FnExpr CHAR_LENGTH(...$args)
 * @method static FnExpr CHARACTER_LENGTH(...$args)
 * @method static FnExpr CONCAT(...$args)
 * @method static FnExpr CONCAT_WS(...$args)
 * @method static FnExpr FIELD(...$args)
 * @method static FnExpr FIND_IN_SET(...$args)
 * @method static FnExpr FORMAT(...$args)
 * @method static FnExpr INSERT(...$args)
 * @method static FnExpr INSTR(...$args)
 * @method static FnExpr LCASE(...$args)
 * @method static FnExpr LEFT(...$args)
 * @method static FnExpr LENGTH(...$args)
 * @method static FnExpr LOCATE(...$args)
 * @method static FnExpr LOWER(...$args)
 * @method static FnExpr LPAD(...$args)
 * @method static FnExpr LTRIM(...$args)
 * @method static FnExpr MAKE_SET(...$args)
 * @method static FnExpr MID(...$args)
 * @method static FnExpr OCT(...$args)
 * @method static FnExpr OCTET_LENGTH(...$args)
 * @method static FnExpr ORD(...$args)
 * @method static FnExpr POSITION(...$args)
 * @method static FnExpr QUOTE(...$args)
 * @method static FnExpr REPEAT(...$args)
 * @method static FnExpr REPLACE(...$args)
 * @method static FnExpr REVERSE(...$args)
 * @method static FnExpr RIGHT(...$args)
 * @method static FnExpr RPAD(...$args)
 * @method static FnExpr RTRIM(...$args)
 * @method static FnExpr SOUNDEX(...$args)
 * @method static FnExpr SPACE(...$args)
 * @method static FnExpr STRCMP(...$args)
 * @method static FnExpr SUBSTRING(...$args)
 * @method static FnExpr SUBSTRING_INDEX(...$args)
 * @method static FnExpr TRIM(...$args)
 * @method static FnExpr UCASE(...$args)
 * @method static FnExpr UPPER(...$args)
 *
 * INTEGER SQL FUNCTIONS
 * @method static FnExpr ABS(...$args)
 * @method static FnExpr ACOS(...$args)
 * @method static FnExpr ASIN(...$args)
 * @method static FnExpr ATAN(...$args)
 * @method static FnExpr ATAN2(...$args)
 * @method static FnExpr CEIL(...$args)
 * @method static FnExpr CEILING(...$args)
 * @method static FnExpr COS(...$args)
 * @method static FnExpr COT(...$args)
 * @method static FnExpr CRC32(...$args)
 * @method static FnExpr COUNT(...$args)
 * @method static FnExpr DEGREES(...$args)
 * @method static FnExpr DIV(...$args)
 * @method static FnExpr EXP(...$args)
 * @method static FnExpr FLOOR(...$args)
 * @method static FnExpr GREATEST(...$args)
 * @method static FnExpr LEAST(...$args)
 * @method static FnExpr LN(...$args)
 * @method static FnExpr LOG(...$args)
 * @method static FnExpr LOG10(...$args)
 * @method static FnExpr LOG2(...$args)
 * @method static FnExpr MAX(...$args)
 * @method static FnExpr MIN(...$args)
 * @method static FnExpr MOD(...$args)
 * @method static FnExpr PI(...$args)
 * @method static FnExpr POW(...$args)
 * @method static FnExpr POWER(...$args)
 * @method static FnExpr RADIANS(...$args)
 * @method static FnExpr RAND(...$args)
 * @method static FnExpr ROUND(...$args)
 * @method static FnExpr SIGN(...$args)
 * @method static FnExpr SIN(...$args)
 * @method static FnExpr SQRT(...$args)
 * @method static FnExpr SUM(...$args)
 * @method static FnExpr TAN(...$args)
 * @method static FnExpr TRUNCATE(...$args)
 *
 * DATE SQL FUNCTIONS
 * @method static FnExpr ADDDATE(...$args)
 * @method static FnExpr ADDTIME(...$args)
 * @method static FnExpr CONVERT_TZ(...$args)
 * @method static FnExpr CURDATE(...$args)
 * @method static FnExpr CURRENT_DATE(...$args)
 * @method static FnExpr CURRENT_TIME(...$args)
 * @method static FnExpr CURRENT_TIMESTAMP(...$args)
 * @method static FnExpr CURTIME(...$args)
 * @method static FnExpr DATE(...$args)
 * @method static FnExpr DATEDIFF(...$args)
 * @method static FnExpr DATE_ADD(...$args)
 * @method static FnExpr DATE_FORMAT(...$args)
 * @method static FnExpr DATE_SUB(...$args)
 * @method static FnExpr DAY(...$args)
 * @method static FnExpr DAYNAME(...$args)
 * @method static FnExpr DAYOFMONTH(...$args)
 * @method static FnExpr DAYOFWEEK(...$args)
 * @method static FnExpr DAYOFYEAR(...$args)
 * @method static FnExpr EXTRACT(...$args)
 * @method static FnExpr FROM_DAYS(...$args)
 * @method static FnExpr FROM_UNIXTIME(...$args)
 * @method static FnExpr GET_FORMAT(...$args)
 * @method static FnExpr HOUR(...$args)
 * @method static FnExpr LAST_DAY(...$args)
 * @method static FnExpr LOCALTIME(...$args)
 * @method static FnExpr LOCALTIMESTAMP(...$args)
 * @method static FnExpr MAKEDATE(...$args)
 * @method static FnExpr MAKETIME(...$args)
 * @method static FnExpr MICROSECOND(...$args)
 * @method static FnExpr MINUTE(...$args)
 * @method static FnExpr MONTH(...$args)
 * @method static FnExpr MONTHNAME(...$args)
 * @method static FnExpr NOW(...$args)
 * @method static FnExpr PERIOD_ADD(...$args)
 * @method static FnExpr PERIOD_DIFF(...$args)
 * @method static FnExpr QUARTER(...$args)
 * @method static FnExpr SEC_TO_TIME(...$args)
 * @method static FnExpr SECOND(...$args)
 * @method static FnExpr STR_TO_DATE(...$args)
 * @method static FnExpr SUBDATE(...$args)
 * @method static FnExpr SUBTIME(...$args)
 * @method static FnExpr SYSDATE(...$args)
 * @method static FnExpr TIME(...$args)
 * @method static FnExpr TIME_FORMAT(...$args)
 * @method static FnExpr TIME_TO_SED(...$args)
 * @method static FnExpr TIMEDIFF(...$args)
 * @method static FnExpr TIMESTAMP(...$args)
 * @method static FnExpr TO_DAYS(...$args)
 * @method static FnExpr UNIX_TIMESTAMP(...$args)
 * @method static FnExpr UTC_DATE(...$args)
 * @method static FnExpr UTC_TIME(...$args)
 * @method static FnExpr UTC_TIMESTAMP(...$args)
 * @method static FnExpr WEEK(...$args)
 * @method static FnExpr WEEKDAY(...$args)
 * @method static FnExpr WEEKOFYEAR(...$args)
 * @method static FnExpr YEAR(...$args)
 * @method static FnExpr YEARWEEK(...$args)
 *
 * OTHER SQL FUNCTIONS
 * @method static FnExpr BENCHMARK(...$args)
 * @method static FnExpr BIN(...$args)
 * @method static FnExpr BINARY(...$args)
 * @method static FnExpr CASE(...$args)
 * @method static FnExpr CAST(...$args)
 * @method static FnExpr COALESCE(...$args)
 * @method static FnExpr CONNECTION_ID(...$args)
 * @method static FnExpr CONV(...$args)
 * @method static FnExpr CONVERT(...$args)
 * @method static FnExpr CURRENT_USER(...$args)
 * @method static FnExpr DATABASE(...$args)
 * @method static FnExpr FOUND_ROWS(...$args)
 * @method static FnExpr GROUP_CONCAT(...$args)
 * @method static FnExpr IF(...$args)
 * @method static FnExpr IFNULL(...$args)
 * @method static FnExpr LAST_INSERT_ID(...$args)
 * @method static FnExpr NULLIF(...$args)
 * @method static FnExpr SESSION_USER(...$args)
 * @method static FnExpr SYSTEM_USER(...$args)
 * @method static FnExpr USER(...$args)
 * @method static FnExpr VERSION(...$args)
 */
abstract class F
{
    /**
     * Creates a function expression.
     *
     * @param string $operator The called method name, which corresponds to the operator (a.k.a. function name).
     * @param list<mixed|ExprInterface> $arguments The call arguments.
     * @return FnExpr The created function expression.
     */
    public static function __callStatic(string $fnName, array $args): FnExpr
    {
        if ($fnName === 'COUNT' && (count($args) === 0 || $args[0] === '*')) {
            $args = [new Term(Term::SPECIAL, '*')];
        }

        return new FnExpr($fnName, array_map([Term::class, 'create'], $args));
    }
}
