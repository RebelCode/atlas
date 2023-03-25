<?php

namespace RebelCode\Atlas\Test\Query;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\DatabaseAdapter;
use RebelCode\Atlas\Exception\QuerySqlException;
use RebelCode\Atlas\Query;
use RebelCode\Atlas\Query\CreateTableQuery;
use RebelCode\Atlas\Schema;
use RebelCode\Atlas\Schema\Column;
use RebelCode\Atlas\Schema\ForeignKey;
use RebelCode\Atlas\Schema\Key;
use RebelCode\Atlas\Test\Helpers\ReflectionHelper;

class CreateTableQueryTest extends TestCase
{
    use ReflectionHelper;

    public function testIsQuery()
    {
        $schema = $this->createMock(Schema::class);
        $this->assertInstanceOf(Query::class, new CreateTableQuery(null, 'foo', false, $schema));
    }

    public function testCtor()
    {
        $adapter = $this->createMock(DatabaseAdapter::class);
        $table = 'foo';
        $ifNotExists = true;
        $schema = $this->createMock(Schema::class);

        $query = new CreateTableQuery($adapter, $table, $ifNotExists, $schema);

        $this->assertSame($adapter, $this->expose($query)->adapter);
        $this->assertSame($table, $this->expose($query)->name);
        $this->assertSame($ifNotExists, $this->expose($query)->ifNotExists);
        $this->assertSame($schema, $this->expose($query)->schema);
    }

    public function testExec()
    {
        $adapter = $this->createMock(DatabaseAdapter::class);
        $schema = new Schema(['foo' => Schema\Column::ofType('INT')]);

        $query = new CreateTableQuery($adapter, 'foo', true, $schema);

        $adapter->expects($this->once())->method('query')->willReturn(true);

        $this->assertTrue($query->exec());
    }

    // ---

    public function testCompileColumnWithDefault()
    {
        $schema = new Schema([
            'foo' => new Column('INT', 1),
        ]);

        $query = new CreateTableQuery(null, 'test_table', false, $schema);

        $expected = <<<QUERY
CREATE TABLE `test_table` (
  `foo` INT DEFAULT 1
)
QUERY;

        $this->assertEquals($expected, $query->toSql());
    }

    public function testCompileColumnWithDefaultNullable()
    {
        $schema = new Schema([
            'foo' => new Column('INT', 1, true),
        ]);
        $query = new CreateTableQuery(null, 'test_table', false, $schema);

        $expected = <<<QUERY
CREATE TABLE `test_table` (
  `foo` INT DEFAULT 1
)
QUERY;

        $this->assertEquals($expected, $query->toSql());
    }

    public function testCompileColumnWithoutDefaultNullable()
    {
        $schema = new Schema([
            'foo' => new Column('INT', null, true),
        ]);
        $query = new CreateTableQuery(null, 'test_table', false, $schema);

        $expected = <<<QUERY
CREATE TABLE `test_table` (
  `foo` INT NULL
)
QUERY;

        $this->assertEquals($expected, $query->toSql());
    }

    public function testCompileColumnWithoutDefaultNotNullable()
    {
        $schema = new Schema([
            'foo' => new Column('INT', null, false),
        ]);
        $query = new CreateTableQuery(null, 'test_table', false, $schema);

        $expected = <<<QUERY
CREATE TABLE `test_table` (
  `foo` INT NOT NULL
)
QUERY;

        $this->assertEquals($expected, $query->toSql());
    }

    public function testCompileColumnAutoInc()
    {
        $schema = new Schema([
            'foo' => new Column('INT', null, false, true),
        ]);
        $query = new CreateTableQuery(null, 'test_table', false, $schema);

        $expected = <<<QUERY
CREATE TABLE `test_table` (
  `foo` INT NOT NULL AUTO_INCREMENT
)
QUERY;

        $this->assertEquals($expected, $query->toSql());
    }

    public function testCompileMultipleColumns()
    {
        $schema = new Schema([
            'foo' => new Column('INT', null, false, true),
            'bar' => new Column('VARCHAR(10)', null, true),
            'baz' => new Column('REAL', 6.9, false, false),
        ]);
        $query = new CreateTableQuery(null, 'test_table', false, $schema);

        $actual = $query->toSql();
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
        $schema = new Schema(
            [], // Columns
            [
                /* Keys */
                'foo_bar_unique' => new Key(false, ['foo', 'bar']),
            ]
        );
        $query = new CreateTableQuery(null, 'test_table', false, $schema);

        $actual = $query->toSql();
        $expected = <<<QUERY
CREATE TABLE `test_table` (
  CONSTRAINT `foo_bar_unique` UNIQUE (`foo`, `bar`)
)
QUERY;

        $this->assertEquals($expected, $actual);
    }

    public function testCompilePrimaryKey()
    {
        $schema = new Schema(
            [], // Columns
            [
                /* Keys */
                'foo_bar_pk' => new Key(true, ['foo', 'bar']),
            ]
        );
        $query = new CreateTableQuery(null, 'test_table', false, $schema);

        $actual = $query->toSql();
        $expected = <<<QUERY
CREATE TABLE `test_table` (
  CONSTRAINT `foo_bar_pk` PRIMARY KEY (`foo`, `bar`)
)
QUERY;

        $this->assertEquals($expected, $actual);
    }

    public function testCompileMultipleKeys()
    {
        $schema = new Schema(
            [], // Columns
            [
                /* Keys */
                'foo_pk' => new Key(true, ['foo']),
                'bar_unique' => new Key(false, ['bar']),
            ]
        );
        $query = new CreateTableQuery(null, 'test_table', false, $schema);

        $actual = $query->toSql();
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
        $schema = new Schema(
            [], // Columns
            [], // Keys
            [
                /* Foreign Keys */
                'test_fk' => new ForeignKey('other_table', [
                    'foo' => 'bar',
                ]),
            ]
        );
        $query = new CreateTableQuery(null, 'test_table', false, $schema);

        $actual = $query->toSql();
        $expected = <<<QUERY
CREATE TABLE `test_table` (
  CONSTRAINT `test_fk` FOREIGN KEY (`foo`) REFERENCES `other_table` (`bar`)
)
QUERY;

        $this->assertEquals($expected, $actual);
    }

    public function testCompileForeignKeyMultipleMappings()
    {
        $schema = new Schema(
            [], // Columns
            [], // Keys
            [
                /* Foreign Keys */
                'test_fk' => new ForeignKey('other_table', [
                    'foo' => 'bar',
                    'baz' => 'qux',
                ]),
            ]
        );
        $query = new CreateTableQuery(null, 'test_table', false, $schema);

        $actual = $query->toSql();
        $expected = <<<QUERY
CREATE TABLE `test_table` (
  CONSTRAINT `test_fk` FOREIGN KEY (`foo`, `baz`) REFERENCES `other_table` (`bar`, `qux`)
)
QUERY;

        $this->assertEquals($expected, $actual);
    }

    public function testCompileForeignKeyWithUpdateRule()
    {
        $schema = new Schema(
            [], // Columns
            [], // Keys
            [
                /* Foreign Keys */
                'test_fk' => new ForeignKey('other_table', ['foo' => 'bar'], ForeignKey::CASCADE),
            ]
        );
        $query = new CreateTableQuery(null, 'test_table', false, $schema);

        $actual = $query->toSql();
        $expected = <<<QUERY
CREATE TABLE `test_table` (
  CONSTRAINT `test_fk` FOREIGN KEY (`foo`) REFERENCES `other_table` (`bar`) ON UPDATE CASCADE
)
QUERY;

        $this->assertEquals($expected, $actual);
    }

    public function testCompileForeignKeyWithDeleteRule()
    {
        $schema = new Schema(
            [], // Columns
            [], // Keys
            [
                /* Foreign Keys */
                'test_fk' => new ForeignKey('other_table', ['foo' => 'bar'], null, ForeignKey::SET_NULL),
            ]
        );
        $query = new CreateTableQuery(null, 'test_table', false, $schema);

        $actual = $query->toSql();
        $expected = <<<QUERY
CREATE TABLE `test_table` (
  CONSTRAINT `test_fk` FOREIGN KEY (`foo`) REFERENCES `other_table` (`bar`) ON DELETE SET NULL
)
QUERY;

        $this->assertEquals($expected, $actual);
    }

    public function testCompileForeignKeyWithBothRules()
    {
        $schema = new Schema(
            [], // Columns
            [], // Keys
            [
                /* Foreign Keys */
                'test_fk' => new ForeignKey('other_table', ['foo' => 'bar'], ForeignKey::SET_DEFAULT,
                    ForeignKey::CASCADE),
            ]
        );
        $query = new CreateTableQuery(null, 'test_table', false, $schema);

        $actual = $query->toSql();
        $expected = <<<QUERY
CREATE TABLE `test_table` (
  CONSTRAINT `test_fk` FOREIGN KEY (`foo`) REFERENCES `other_table` (`bar`) ON UPDATE SET DEFAULT ON DELETE CASCADE
)
QUERY;

        $this->assertEquals($expected, $actual);
    }

    public function testCompileIfNotExists()
    {
        $schema = new Schema([
            'foo' => new Column('INT'),
        ]);
        $query = new CreateTableQuery(null, 'test_table', true, $schema);

        $actual = $query->toSql();
        $expected = <<<QUERY
CREATE TABLE IF NOT EXISTS `test_table` (
  `foo` INT NULL
)
QUERY;

        $this->assertEquals($expected, $actual);
    }

    public function testCompileInvalidTableName()
    {
        $schema = new Schema([
            'foo' => new Column('INT'),
        ]);
        $query = new CreateTableQuery(null, '', false, $schema);

        try {
            $query->toSql();
            TestCase::fail('Expected a QueryCompileException to be thrown');
        } catch (QuerySqlException $exception) {
            TestCase::assertSame(
                $query,
                $exception->getQuery(),
                'The return value of QueryCompileException::getQuery() is not the query that was being compiled'
            );
        }
    }
}
