<?php /** @noinspection SqlDialectInspection */

namespace RebelCode\Atlas\Test\QueryType;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Exception\QueryCompileException;
use RebelCode\Atlas\Query;
use RebelCode\Atlas\QueryTypeInterface;
use RebelCode\Atlas\QueryType\DropTable;

class DropTableTest extends TestCase
{
    public function testIsQueryType()
    {
        $this->assertInstanceOf(QueryTypeInterface::class, new DropTable());
    }

    public function testCompile()
    {
        $subject = new DropTable();
        $query = new Query($subject, [
            DropTable::TABLE => 'test',
        ]);

        $expected = 'DROP TABLE `test`';

        $this->assertEquals($expected, $subject->compile($query));
    }

    public function testCompileIfExists()
    {
        $subject = new DropTable();
        $query = new Query($subject, [
            DropTable::TABLE => 'test',
            DropTable::IF_EXISTS => true,
        ]);

        $expected = 'DROP TABLE IF EXISTS `test`';

        $this->assertEquals($expected, $subject->compile($query));
    }

    public function testCompileCascade()
    {
        $subject = new DropTable();
        $query = new Query($subject, [
            DropTable::TABLE => 'test',
            DropTable::CASCADE => true,
        ]);

        $expected = 'DROP TABLE `test` CASCADE';

        $this->assertEquals($expected, $subject->compile($query));
    }

    public function testCompileIfExistsCascade()
    {
        $subject = new DropTable();
        $query = new Query($subject, [
            DropTable::TABLE => 'test',
            DropTable::IF_EXISTS => true,
            DropTable::CASCADE => true,
        ]);

        $expected = 'DROP TABLE IF EXISTS `test` CASCADE';

        $this->assertEquals($expected, $subject->compile($query));
    }

    public function provideInvalidTableNames(): array
    {
        return [
            [null],
            [''],
            ['   '],
            [0],
            [101],
            [12.1],
            [false],
            [true],
            [[]],
            [[1, 2, 3]],
            [new \stdClass()],
        ];
    }

    /** @dataProvider provideInvalidTableNames */
    public function testCompileInvalidTableName($tableName)
    {
        $subject = new DropTable();
        $query = new Query($subject, [
            DropTable::TABLE => $tableName,
            DropTable::IF_EXISTS => true,
            DropTable::CASCADE => true,
        ]);

        try {
            $subject->compile($query);
            $this->fail('Expected an exception to be thrown');
        } catch (QueryCompileException $e) {
            $this->assertSame($query, $e->getQuery(), 'Exception query is invalid');
        }
    }
}
