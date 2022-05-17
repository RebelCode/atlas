<?php

namespace RebelCode\Atlas\Test\Expression;

use DateTime;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Expression\BaseExpr;
use RebelCode\Atlas\Expression\BinaryExpr;
use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Expression\Term;
use RebelCode\Atlas\Expression\UnaryExpr;

class TermTest extends TestCase
{
    public function testClassInterface()
    {
        $term = new Term(0, null);
        $this->assertInstanceOf(BaseExpr::class, $term);
        $this->assertInstanceOf(ExprInterface::class, $term);
    }

    public function testConstructor()
    {
        $term = new Term(Term::STRING, 'test', true);

        $this->assertEquals(Term::STRING, $term->getType());
        $this->assertEquals('test', $term->getValue());
        $this->assertEquals(true, $term->isDistinct());
    }

    public function testDistinctTrue()
    {
        $column = new Term(Term::COLUMN, 'foo', false);
        $newColumn = $column->distinct();

        $this->assertNotSame($column, $newColumn, 'The new column should be a new instance');
        $this->assertFalse($column->isDistinct(), 'The original column should not be mutated');
        $this->assertTrue($newColumn->isDistinct(), 'The new column should have distinct = true');
    }

    public function testDistinctFalse()
    {
        $column = new Term(Term::COLUMN, 'foo', true);
        $newColumn = $column->distinct(false);

        $this->assertNotSame($column, $newColumn, 'The new column should be a new instance');
        $this->assertTrue($column->isDistinct(), 'The original column should not be mutated');
        $this->assertFalse($newColumn->isDistinct(), 'The new column should have distinct = false');
    }

    public function testDistinctSame()
    {
        $column = new Term(Term::COLUMN, 'foo', false);
        $newColumn = $column->distinct(false);

        $this->assertSame($column, $newColumn, 'The new column should be a new instance');
        $this->assertFalse($column->isDistinct(), 'The original column should not be mutated');
    }

    public function provideDetectTypeData(): array
    {
        return [
            'null' => [null, Term::NULL],
            'string' => ['test', Term::STRING],
            'int 0' => [0, Term::NUMBER],
            'int' => [1234, Term::NUMBER],
            'negative int' => [-45092, Term::NUMBER],
            'float' => [1234.56789, Term::NUMBER],
            'negative float' => [-450.92, Term::NUMBER],
            'true' => [true, Term::BOOLEAN],
            'false' => [false, Term::BOOLEAN],
            'list' => [[1, 2], Term::LIST],
        ];
    }

    /** @dataProvider provideDetectTypeData */
    public function testDetectType($value, $expected)
    {
        $this->assertEquals($expected, Term::detectType($value));
    }

    public function testDetectTypeFail()
    {
        $this->expectException(InvalidArgumentException::class);
        Term::create(new DateTime());
    }

    /** @dataProvider provideDetectTypeData */
    public function testCreateTypes($value, $expectedType)
    {
        $actual = Term::create($value);

        $this->assertInstanceOf(Term::class, $actual);
        $this->assertEquals($expectedType, $actual->getType());
    }

    public function provideCreateValuesData(): array
    {
        return [
            'null' => [null, null],
            'string' => ['test', 'test'],
            'int 0' => [0, 0],
            'int' => [1234, 1234],
            'negative int' => [-45092, -45092],
            'float' => [1234.56789, 1234.56789],
            'negative float' => [-450.92, -450.92],
            'true' => [true, true],
            'false' => [false, false],
            'list' => [[1, 2], [new Term(Term::NUMBER, 1), new Term(Term::NUMBER, 2)]],
        ];
    }

    /** @dataProvider provideCreateValuesData */
    public function testCreateValues($inValue, $outValue)
    {
        $actual = Term::create($inValue);

        if ($actual instanceof Term) {
            $this->assertEquals($outValue, $actual->getValue());
        } else {
            $this->assertEquals($outValue, $inValue);
        }
    }

    public function provideCreateWithExprData(): array
    {
        return [
            'expr term' => [$term = new Term(Term::STRING, 'foo')],
            'unary expr' => [$unary = new UnaryExpr(UnaryExpr::NOT, $term)],
            'binary expr' => [new BinaryExpr($term, BinaryExpr::PLUS, $unary)],
        ];
    }

    /** @dataProvider provideCreateWithExprData */
    public function testCreateWithExpr(ExprInterface $expr)
    {
        $this->assertSame($expr, Term::create($expr));
    }

    public function provideToStringData(): array
    {
        return [
            'string' => [Term::create('test'), "'test'"],
            'int' => [Term::create(1234), "1234"],
            'negative int' => [Term::create(-1234), "-1234"],
            'float' => [Term::create(12.34), "12.34"],
            'negative float' => [Term::create(-12.34), "-12.34"],
            'false' => [Term::create(false), "FALSE"],
            'true' => [Term::create(true), "TRUE"],
            'null' => [Term::create(null), "NULL"],
            'empty array' => [Term::create([]), "()"],
            'array with ints' => [Term::create([1, 2]), "(1, 2)"],
            'array with strings' => [Term::create(['foo', 'bar']), "('foo', 'bar')"],
            'array with bools' => [Term::create([true, false]), "(TRUE, FALSE)"],
            'array with mixed' => [Term::create(['foo', false, 1, null]), "('foo', FALSE, 1, NULL)"],
            'column' => [new Term(Term::COLUMN, 'foo'), "`foo`"],
            'column with dot' => [new Term(Term::COLUMN, 'foo.name'), "`foo`.`name`"],
            'column as array' => [new Term(Term::COLUMN, ['foo', 'name']), "`foo`.`name`"],
            'column as array with dot' => [new Term(Term::COLUMN, ['foo.name']), "`foo`.`name`"],
        ];
    }

    public function testColumn()
    {
        $term = Term::column('foo');

        $this->assertEquals('foo', $term->getValue());
        $this->assertEquals(Term::COLUMN, $term->getType());
    }

    /** @dataProvider provideToStringData */
    public function testToString(Term $term, $expected)
    {
        $this->assertEquals($expected, $term->toString());
    }
}
