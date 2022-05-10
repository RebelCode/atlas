<?php /** @noinspection SqlDialectInspection */

namespace RebelCode\Atlas\Test\QueryType;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Schema\Column;
use RebelCode\Atlas\Schema\ForeignKey;
use RebelCode\Atlas\Schema\Key;
use RebelCode\Atlas\Query;
use RebelCode\Atlas\QueryType\CreateTable;
use RebelCode\Atlas\QueryTypeInterface;
use RebelCode\Atlas\Schema;
use RebelCode\Atlas\Test\Helpers;

class CreateTableTest extends TestCase
{
    public function testIsQueryType()
    {
        $this->assertInstanceOf(QueryTypeInterface::class, new CreateTable());
    }

    public function testCompileColumnWithDefault()
    {
        $subject = new CreateTable();

        $query = new Query($subject, [
            CreateTable::NAME => 'test_table',
            CreateTable::SCHEMA => new Schema([
                'foo' => new Column('INT', 1),
            ]),
        ]);

        $expected = <<<QUERY
CREATE TABLE `test_table` (
  `foo` INT DEFAULT 1
)
QUERY;

        $this->assertEquals($expected, $subject->compile($query));
    }

    public function testCompileColumnWithDefaultNullable()
    {
        $subject = new CreateTable();

        $query = new Query($subject, [
            CreateTable::NAME => 'test_table',
            CreateTable::SCHEMA => new Schema([
                'foo' => new Column('INT', 1, true),
            ]),
        ]);

        $expected = <<<QUERY
CREATE TABLE `test_table` (
  `foo` INT DEFAULT 1
)
QUERY;

        $this->assertEquals($expected, $subject->compile($query));
    }

    public function testCompileColumnWithoutDefaultNullable()
    {
        $subject = new CreateTable();

        $query = new Query($subject, [
            CreateTable::NAME => 'test_table',
            CreateTable::SCHEMA => new Schema([
                'foo' => new Column('INT', null, true),
            ]),
        ]);

        $expected = <<<QUERY
CREATE TABLE `test_table` (
  `foo` INT NULL
)
QUERY;

        $this->assertEquals($expected, $subject->compile($query));
    }

    public function testCompileColumnWithoutDefaultNotNullable()
    {
        $subject = new CreateTable();

        $query = new Query($subject, [
            CreateTable::NAME => 'test_table',
            CreateTable::SCHEMA => new Schema([
                'foo' => new Column('INT', null, false),
            ]),
        ]);

        $expected = <<<QUERY
CREATE TABLE `test_table` (
  `foo` INT NOT NULL
)
QUERY;

        $this->assertEquals($expected, $subject->compile($query));
    }

    public function testCompileColumnAutoInc()
    {
        $subject = new CreateTable();

        $query = new Query($subject, [
            CreateTable::NAME => 'test_table',
            CreateTable::SCHEMA => new Schema([
                'foo' => new Column('INT', null, false, true),
            ]),
        ]);

        $expected = <<<QUERY
CREATE TABLE `test_table` (
  `foo` INT NOT NULL AUTO_INCREMENT
)
QUERY;

        $this->assertEquals($expected, $subject->compile($query));
    }

    public function testCompileMultipleColumns()
    {
        $subject = new CreateTable();

        $query = new Query($subject, [
            CreateTable::NAME => 'test_table',
            CreateTable::SCHEMA => new Schema([
                'foo' => new Column('INT', null, false, true),
                'bar' => new Column('VARCHAR(10)', null, true),
                'baz' => new Column('REAL', '6.9', false, false),
            ]),
        ]);

        $actual = $subject->compile($query);
        $expected = <<<QUERY
CREATE TABLE `test_table` (
  `foo` INT NOT NULL AUTO_INCREMENT,
  `bar` VARCHAR(10) NULL,
  `baz` REAL DEFAULT 6.9
)
QUERY;

        $this->assertEquals($expected, $actual);
    }

    public function testCompileUniqueKey()
    {
        $subject = new CreateTable();

        $query = new Query($subject, [
            CreateTable::NAME => 'test_table',
            CreateTable::SCHEMA => new Schema(
                [], // Columns
                [
                    /* Keys */
                    'foo_bar_unique' => new Key(false, ['foo', 'bar']),
                ]
            ),
        ]);

        $actual = $subject->compile($query);
        $expected = <<<QUERY
CREATE TABLE `test_table` (
  CONSTRAINT `foo_bar_unique` UNIQUE (`foo`, `bar`)
)
QUERY;

        $this->assertEquals($expected, $actual);
    }

    public function testCompilePrimaryKey()
    {
        $subject = new CreateTable();

        $query = new Query($subject, [
            CreateTable::NAME => 'test_table',
            CreateTable::SCHEMA => new Schema(
                [], // Columns
                [
                    /* Keys */
                    'foo_bar_pk' => new Key(true, ['foo', 'bar']),
                ]
            ),
        ]);

        $actual = $subject->compile($query);
        $expected = <<<QUERY
CREATE TABLE `test_table` (
  CONSTRAINT `foo_bar_pk` PRIMARY KEY (`foo`, `bar`)
)
QUERY;

        $this->assertEquals($expected, $actual);
    }

    public function testCompileMultipleKeys()
    {
        $subject = new CreateTable();

        $query = new Query($subject, [
            CreateTable::NAME => 'test_table',
            CreateTable::SCHEMA => new Schema(
                [], // Columns
                [
                    /* Keys */
                    'foo_pk' => new Key(true, ['foo']),
                    'bar_unique' => new Key(false, ['bar']),
                ]
            ),
        ]);

        $actual = $subject->compile($query);
        $expected = <<<QUERY
CREATE TABLE `test_table` (
  CONSTRAINT `foo_pk` PRIMARY KEY (`foo`),
  CONSTRAINT `bar_unique` UNIQUE (`bar`)
)
QUERY;

        $this->assertEquals($expected, $actual);
    }

    public function testCompileForeignKeySingleMapping()
    {
        $subject = new CreateTable();

        $query = new Query($subject, [
            CreateTable::NAME => 'test_table',
            CreateTable::SCHEMA => new Schema(
                [], // Columns
                [], // Keys
                [
                    /* Foreign Keys */
                    'test_fk' => new ForeignKey('other_table', [
                        'foo' => 'bar',
                    ]),
                ]
            ),
        ]);

        $actual = $subject->compile($query);
        $expected = <<<QUERY
CREATE TABLE `test_table` (
  CONSTRAINT `test_fk` FOREIGN KEY (`foo`) REFERENCES `other_table` (`bar`)
)
QUERY;

        $this->assertEquals($expected, $actual);
    }

    public function testCompileForeignKeyMultipleMappings()
    {
        $subject = new CreateTable();

        $query = new Query($subject, [
            CreateTable::NAME => 'test_table',
            CreateTable::SCHEMA => new Schema(
                [], // Columns
                [], // Keys
                [
                    /* Foreign Keys */
                    'test_fk' => new ForeignKey('other_table', [
                        'foo' => 'bar',
                        'baz' => 'qux',
                    ]),
                ]
            ),
        ]);

        $actual = $subject->compile($query);
        $expected = <<<QUERY
CREATE TABLE `test_table` (
  CONSTRAINT `test_fk` FOREIGN KEY (`foo`, `baz`) REFERENCES `other_table` (`bar`, `qux`)
)
QUERY;

        $this->assertEquals($expected, $actual);
    }

    public function testCompileForeignKeyWithUpdateRule()
    {
        $subject = new CreateTable();

        $query = new Query($subject, [
            CreateTable::NAME => 'test_table',
            CreateTable::SCHEMA => new Schema(
                [], // Columns
                [], // Keys
                [
                    /* Foreign Keys */
                    'test_fk' => new ForeignKey('other_table', ['foo' => 'bar'], ForeignKey::CASCADE),
                ]
            ),
        ]);

        $actual = $subject->compile($query);
        $expected = <<<QUERY
CREATE TABLE `test_table` (
  CONSTRAINT `test_fk` FOREIGN KEY (`foo`) REFERENCES `other_table` (`bar`) ON UPDATE CASCADE
)
QUERY;

        $this->assertEquals($expected, $actual);
    }

    public function testCompileForeignKeyWithDeleteRule()
    {
        $subject = new CreateTable();

        $query = new Query($subject, [
            CreateTable::NAME => 'test_table',
            CreateTable::SCHEMA => new Schema(
                [], // Columns
                [], // Keys
                [
                    /* Foreign Keys */
                    'test_fk' => new ForeignKey('other_table', ['foo' => 'bar'], null, ForeignKey::SET_NULL),
                ]
            ),
        ]);

        $actual = $subject->compile($query);
        $expected = <<<QUERY
CREATE TABLE `test_table` (
  CONSTRAINT `test_fk` FOREIGN KEY (`foo`) REFERENCES `other_table` (`bar`) ON DELETE SET NULL
)
QUERY;

        $this->assertEquals($expected, $actual);
    }

    public function testCompileForeignKeyWithBothRules()
    {
        $subject = new CreateTable();

        $query = new Query($subject, [
            CreateTable::NAME => 'test_table',
            CreateTable::SCHEMA => new Schema(
                [], // Columns
                [], // Keys
                [
                    /* Foreign Keys */
                    'test_fk' => new ForeignKey('other_table', ['foo' => 'bar'], ForeignKey::SET_DEFAULT,
                        ForeignKey::CASCADE),
                ]
            ),
        ]);

        $actual = $subject->compile($query);
        $expected = <<<QUERY
CREATE TABLE `test_table` (
  CONSTRAINT `test_fk` FOREIGN KEY (`foo`) REFERENCES `other_table` (`bar`) ON UPDATE SET DEFAULT ON DELETE CASCADE
)
QUERY;

        $this->assertEquals($expected, $actual);
    }

    public function testCompileIfNotExists()
    {
        $subject = new CreateTable();

        $query = new Query($subject, [
            CreateTable::NAME => 'test_table',
            CreateTable::IF_NOT_EXISTS => true,
            CreateTable::SCHEMA => new Schema([
                'foo' => new Column('INT'),
            ]),
        ]);

        $actual = $subject->compile($query);
        $expected = <<<QUERY
CREATE TABLE IF NOT EXISTS `test_table` (
  `foo` INT NULL
)
QUERY;

        $this->assertEquals($expected, $actual);
    }

    public function provideInvalidTableNames(): array
    {
        return [
            [null],
            [''],
            ['   '],
            [0],
            [93],
            [14.5],
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
        $subject = new CreateTable();

        $query = new Query($subject, [
            CreateTable::NAME => $tableName,
            CreateTable::SCHEMA => new Schema([
                'foo' => new Column('INT'),
            ]),
        ]);

        Helpers::expectQueryCompileException($query, $subject);
    }

    public function provideInvalidSchemas(): array
    {
        return [
            [null],
            [''],
            ['   '],
            ['foobar'],
            [0],
            [93],
            [14.5],
            [false],
            [true],
            [[]],
            [[1, 2, 3]],
            [new \stdClass()],
        ];
    }

    /** @dataProvider provideInvalidSchemas */
    public function testCompileInvalidSchema($schema)
    {
        $subject = new CreateTable();

        $query = new Query($subject, [
            CreateTable::NAME => 'test_table',
            CreateTable::SCHEMA => $schema,
        ]);

        Helpers::expectQueryCompileException($query, $subject);
    }
}
