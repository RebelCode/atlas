<?php /** @noinspection SqlDialectInspection */

namespace RebelCode\Atlas\Test\QueryType;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Exception\QueryCompileException;
use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Query;
use RebelCode\Atlas\QueryType\Insert;
use stdClass;

class InsertTest extends TestCase
{
    public function testCompile()
    {
        $insert = new Insert();
        $query = new Query($insert, [
            Insert::TABLE => 'foo',
            Insert::COLUMNS => ['a', 'b', 'c'],
            Insert::VALUES => [
                [1, 2, 3],
            ],
        ]);

        $expected = 'INSERT INTO `foo` (`a`, `b`, `c`) VALUES (1, 2, 3)';

        $this->assertEquals($expected, $insert->compile($query));
    }

    public function testCompileStrings()
    {
        $insert = new Insert();
        $query = new Query($insert, [
            Insert::TABLE => 'foo',
            Insert::COLUMNS => ['a', 'b', 'c'],
            Insert::VALUES => [
                ['hey', 'there', 'buddy'],
            ],
        ]);

        $expected = "INSERT INTO `foo` (`a`, `b`, `c`) VALUES ('hey', 'there', 'buddy')";

        $this->assertEquals($expected, $insert->compile($query));
    }

    public function testCompileMultipleValues()
    {
        $insert = new Insert();
        $query = new Query($insert, [
            Insert::TABLE => 'foo',
            Insert::COLUMNS => ['a', 'b', 'c'],
            Insert::VALUES => [
                [1, 2, 3],
                ['hey', 'there', 'buddy'],
                [4, 5, 6],
            ],
        ]);

        $expected = "INSERT INTO `foo` (`a`, `b`, `c`) VALUES (1, 2, 3), ('hey', 'there', 'buddy'), (4, 5, 6)";

        $this->assertEquals($expected, $insert->compile($query));
    }

    public function provideInvalidTableNames(): array
    {
        return [
            'null' => [null],
            'empty string' => [''],
            'whitespace' => ['    '],
            'integer' => [4],
            'true' => [true],
            'false' => [false],
            'array' => [[1, 2, 3]],
            'object' => [new stdClass()],
        ];
    }

    /** @dataProvider provideInvalidTableNames */
    public function testCompileInvalidTableName($table)
    {
        $insert = new Insert();
        $query = new Query($insert, [
            Insert::TABLE => $table,
            Insert::COLUMNS => ['a', 'b', 'c'],
            Insert::VALUES => [
                [1, 2, 3],
            ],
        ]);

        try {
            $insert->compile($query);
            $this->fail('Expected an exception to be thrown');
        } catch (QueryCompileException $e) {
            $this->assertSame($query, $e->getQuery(), "The exception's query is invalid");
        }
    }

    public function provideColumnExceptionCases(): array
    {
        return [
            [null],
            [''],
            ['foo'],
            [4],
            [true],
            [false],
            [[]],
            [new stdClass()],
        ];
    }

    /** @dataProvider provideColumnExceptionCases */
    public function testCompileInvalidColumns($columns)
    {
        $insert = new Insert();
        $query = new Query($insert, [
            Insert::TABLE => 'foo',
            Insert::COLUMNS => $columns,
            Insert::VALUES => [
                [1, 2, 3],
            ],
        ]);

        try {
            $insert->compile($query);
            $this->fail('Expected an exception to be thrown');
        } catch (QueryCompileException $e) {
            $this->assertSame($query, $e->getQuery(), "The exception's query is invalid");
        }
    }

    public function provideValuesExceptionCases(): array
    {
        return [
            [null],
            [''],
            ['foo'],
            [4],
            [true],
            [false],
            [new stdClass()],
            [[]],
            [[[]]],
            [[[1, 2, 3], [], [4, 5, 6]]],
            [[[1, 2, 3, 4]]], // more values than columns
            [[[1, 2]]], // less values than columns
        ];
    }

    /** @dataProvider provideValuesExceptionCases */
    public function testCompileInvalidValues($values)
    {
        $insert = new Insert();
        $query = new Query($insert, [
            Insert::TABLE => 'foo',
            Insert::COLUMNS => ['a', 'b', 'c'],
            Insert::VALUES => $values,
        ]);

        try {
            $insert->compile($query);
            $this->fail('Expected an exception to be thrown');
        } catch (QueryCompileException $e) {
            $this->assertSame($query, $e->getQuery(), "The exception's query is invalid");
        }
    }

    public function testCompileOnDuplicateKey()
    {
        $bExpr = $this->createMock(ExprInterface::class);
        $bExpr->expects($this->once())->method('toString')->willReturn('BBB');

        $insert = new Insert();
        $query = new Query($insert, [
            Insert::TABLE => 'foo',
            Insert::COLUMNS => ['a', 'b', 'c'],
            Insert::VALUES => [[1, 2, 3]],
            Insert::ON_DUPLICATE_KEY => [
                'a' => 'A',
                'b' => $bExpr,
            ]
        ]);

        $actual = $insert->compile($query);
        $expected = "INSERT INTO `foo` (`a`, `b`, `c`) VALUES (1, 2, 3) ON DUPLICATE KEY UPDATE `a` = 'A', `b` = BBB";

        $this->assertEquals($expected, $actual);
    }
}
