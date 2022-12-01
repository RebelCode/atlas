# Atlas

[![GitHub Workflow Status](https://img.shields.io/github/workflow/status/rebelcode/atlas/Continuous%20Integration?style=flat-square)][github-ci]
[![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/rebelcode/atlas?style=flat-square)][packagist]
[![Packagist Version](https://img.shields.io/packagist/v/rebelcode/atlas?style=flat-square)][packagist]
[![Packagist License](https://img.shields.io/packagist/l/rebelcode/atlas?style=flat-square)][packagist]

A lightweight SQL builder library without any query execution or database connection requirements.

The primary goals of this package are:

1. To provide an easy-to-use, yet flexible, API for building SQL queries,
2. To allow customization without polluting the API with all possibilities,
3. To be independent of database connections and query execution - just build SQL,
4. To be dependency-injection friendly.

## Installation

Install with [Composer](https://getcomposer.org/):

```
composer require rebelcode/atlas
```

## Introduction

Start by creating an `Atlas` instance, then create table helper objects:

```php
use RebelCode\Atlas\Atlas;

$atlas = Atlas::createDefault();

$users = $atlas->table('users');
$logs = $atlas->table('logs');
```

Note: _The `Atlas::table()` method creates table objects on-demand if they don't exist, and will return the same
instance on
subsequent calls. If you prefer explicit control over this behavior, you can use the `Atlas::getTable()` (which doesn't
automatically create table instances) and the `Atlas::addTable()` (which will override existing tables with the
same name)._

The large majority of Atlas' API is available from table objects:

```php
$query = $users->select(/* ... */);
$query = $users->insert(/* ... */);
$query = $users->update(/* ... */);
$query = $users->delete(/* ... */);
$query = $users->create(/* ... */);
$query = $users->drop(/* ... */);
```

These methods return `Query` objects, which can be turned into strings using the `Query::compile()` method:

```php
$sql = $user->select(/*...*/)->compile();
```

## Queries

### `SELECT`

Signature:

```php
$table->select($columns, $where, $order, $limit, $offset);
```

Examples:

```php
use RebelCode\Atlas\Order;

$table = $atlas->table('users');

$table->select();      // SELECT * FROM users
$table->select(null);  // SELECT * FROM users
$table->select(['*']); // SELECT * FROM users

$table->select(['id', 'name', 'email']);
// SELECT id, name, email
// FROM users

$table->select(null, $table->colum('email')->equals('person@domain.com'));
// SELECT *
// FROM users
// WHERE email = 'person@domain.com'

$table->select(null, null, [
    Order::by('name')->desc(),
    Order::by('email')->asc(),
]);
// SELECT *
// FROM users
// ORDER BY name DESC, email ASC

$table->select(null, null, [], 10, 5);
// SELECT *
// FROM users
// LIMIT 10
// OFFSET 5
```

### `INSERT`

Signature:

```php
$table->insert($listOrRecords);
```

Example:

```php
$table = $atlas->table('users');

$table->insert([
    [
        'name' => 'Keanu Reeves',
        'email' => 'keanu.reaves@example.com'
    ],
    [
        'name' => 'Tom Hardy',
        'email' => 'tom.hardy@example.com'
    ],
]);
// INSERT INTO users (name, email) VALUES
// ('Keanu Reeves', 'keanu.reaves@example.com'),
// ('Tom Hardy', 'tom.hardy@example.com')
```

### `UPDATE`

Signature:

```php
$table->update($set, $where, $order, $limit);
```

Examples:

```php
$table = $atlas->table('users');

$table->update([
    'name' => 'John Doe',
    'email' => 'john.doe@example.com'
]);
// UPDATE users
// SET name = 'John Doe', email = 'john.doe@example.com'

$table->update(
    ['status' => 'verified'],
    $table->column('id')->equals(123)
);
// UPDATE users
// SET status = 'verified'
// WHERE id = 123

$table->update(
    ['status' => 'verified'],
    $table->column('type')->equals('member'),
    [
        new Order('name', Order::DESC),
        new Order('email', Order::ASC),
    ],
    10
);
// UPDATE users
// SET status = 'verified'
// WHERE type = 'member'
// ORDER BY name DEC, email ASC
// LIMIT 10
```

### `DELETE`

Signature:

```php
$table->delete($where, $order, $limit);
```

Examples:

```php
use RebelCode\Atlas\Order;

$table = $atlas->table('users');

$table->delete();
// DELETE FROM users

$table->delete($table->column('id')->equals(123));
// DELETE FROM users
// WHERE id = 123

$table->delete(
    $table->column('type')->equals('admin'),
    [Order::by('date_joined')->asc()],
    10
);
// DELETE FROM users
// WHERE id = 123
// ORDER BY date_joined ASC
// LIMIT 10
```

### `CREATE TABLE`

Signature:

```php
$table->create($ifNotExists, $collate): array;
```

To create tables, you need to specify a `Schema` when you first call the `Atlas::table()` method for that table:

```php
$table = $atlas->table('users', new Schema(/*...*/));

$table->create();
// CREATE TABLE IF NOT EXISTS users (...)

$table->create(false);
// CREATE TABLE users (...)

$table->create(false, 'utf8_general_ci');
// CREATE TABLE users (...) COLLATE utf8_general_ci
```

**Note**: The `create()` method, unlike the other methods, does not return a `Query` object, but rather an _array_ of
`Query` objects. The first query in the array is always the `CREATE TABLE` query. The rest of the queries are
`CREATE INDEX` queries, one for each index in the table's schema.

#### Table Schema

A table schema is composed of four parts:

* The table columns
* An optional list of keys
* An optional list of foreign keys
* An optional list of indexes

Example:

```php
use RebelCode\Atlas\Schema;
use RebelCode\Atlas\Order;

$columns = [
    'id' => new Schema\Column(
        'BIGINT', // Type
        null,     // (optional) Default value
        false,    // (optional) Can be null?
        true      // (optional) Auto increment
    ),
    'email' => new Schema\Column('TEXT'),
    'age' => new Schema\Column('TINYINT UNSIGNED', 0),
    'group' => new Schema\Column('BIGINT', null, true),
];

$keys = [
    'users_pk' => new Schema\Key(
        true,    // Is primary?
        ['id']   // Columns
    ),
    'users_unique_email' => new Schema\Key(false, ['email'])
];

$foreignKeys = [
    'users_group_fk' => new Schema\ForeignKey(
        'groups',                    // Foreign table 
        ['group' => 'id'],           // [local => foreign] column mappings
        Schema\ForeignKey::CASCADE,  // Update rule
        Schema\ForeignKey::SET_NULL, // Delete rule
    ),
];

$indexes = [
    'users_age_index' => new Schema\Index(
        false,                 // Is unique?
        ['age' => Order::ASC], // Sorting
    )
];

$schema = new Schema($columns, $keys, $foreignKeys, $indexes);
```

### `DROP TABLE`

Signature:

```php
$table->drop($ifExists, $cascade);
```

Examples:

```php
$table = $atlas->table('users');

$table->drop();
// DROP TABLE IF EXISTS users

$table->drop(false);
// DROP TABLE users

$table->drop(false, true);
// DROP TABLE users CASCADE
```

## Expressions

Atlas includes a simple expression-building API. Expressions are used for WHERE conditions, SELECT columns, UPDATE 
values, etc.

The system recognizes 3 types of expressions: terms, unary expressions, and binary expressions. Each type can be used
to build larger expressions from it using fluent methods. See the [`BaseExpr`](src/Expression/BaseExpr.php) class.

```php
$expr->equals(/* ... */);
$expr->gt(/* ... */);
$expr->notIn(/* ... */);
$expr->and(/* ... */);
$expr->like(/* ... */);
$expr->regexp(/* ... */);
$expr->plus(/* ... */);
$expr->intDiv(/* ... */);
```

### Terms

Terms are single values, and can be created using the `Term::create()` method:

```php
use RebelCode\Atlas\Expression\Term;

Term::create(1);
Term::create('hello');
Term::create(true);
Term::create(false);
Term::create([1, 2, 3]);
```

Column terms can be created using the `Term::column()` method:

```php
use RebelCode\Atlas\Expression\Term;

Term::column('name');               // `name`
Term::column('users.name');         // `users`.`name`
Term::column(['users', 'name']);    // `users`.`name`
```

You don't usually need to create terms manually, since the fluent expression methods will automatically convert
scalar/array values into term objects. Additionally, column terms can be created directly from a table object:

```php
$table   = $atlas->table('users');
$nameCol = $users->column('name');
```

### Unary Expressions

Unary expressions represent expressions with only a single operand:

```php
use RebelCode\Atlas\Expression\UnaryExpr;
use RebelCode\Atlas\Expression\Term;

new UnaryExpr(UnaryExpr::NOT, Term::column('is_admin')); // !`is_admin`

$users = $atlas->table('users')
$users->column('is_admin')->not();       // !`users`.`is_admin`
```

### Binary Expressions

Binary expressions represent expressions with two operands:

```php
use \RebelCode\Atlas\Expression\BinaryExpr;
use RebelCode\Atlas\Expression\Term;

new BinaryExpr(Term::column('name'), BinaryExpr::EQ, Term::create('John'));
// `name` = `John`

$users = $atlas->table('users');
$users->column('name')->equals('John');
// `users`.`name` = 'John'
```

## Stateful Tables

Table instances can store WHERE conditions and sorting information. This information is then used in these queries:

* SELECT
* UPDATE
* DELETE

Note that tables are **immutable**. When adding state to a table, a **new** table is returned that is identical to the
original table in every way except for the relevant state.

### WHERE conditions

You can add a WHERE condition to a table using the `where()` method:

```php
$users = $atlas->table('users');
$admins = $users->where($users->column('type')->equals('admin'));
```

Multiple calls to this method will merge the conditions using an `AND` expression. The `orWhere()` method can be used
to join the conditions using an `OR` expression instead.

```php
$users = $atlas->table('users');

$admins = $users->where($users->column('type')->equals('admin'));

$adminsAndOnline = $admins->where($users->column('status')->equals('online'));
$adminsOrOnline = $admins->orWhere($users->column('status')->equals('online'));

$adminsAndOnline->select();
// SELECT *
// FROM users
// WHERE type = 'admin' AND status = 'online'

$adminsOrOnline->select();
// SELECT *
// FROM users
// WHERE type = 'admin' OR status = 'online'
```

The condition is used in queries as a default, if no condition is specified as an argument:

```php
$admins->select();
// SELECT *
// FROM users
// WHERE type = 'admin'
```

If an argument is specified, the argument and state conditions are combined using an `AND` expression:

```php
$admins->select(null, $admins->column('id')->in([1, 2, 3]));
// SELECT *
// FROM users
// WHERE type = 'admin' AND id IN (1, 2, 3)
```

### Sorting

You can add sorting to a table using the `orderBy()` method:

```php
$users = $atlas->table('users');

$nameSorted = $users->orderBy([Order::by('name')->asc()]);
```

Multiple calls to this method will merge the order lists, with prior sorting taking precedence:

```php
$users = $atlas->table('users');

$nameSorted = $users->orderBy([Order::by('name')->asc()]);

$nameEmailSorted = $nameSorted->orderBy([Order::by('email')->desc()]);
```

When creating queries, the table's sorting is used as a default when the order argument is not specified:

```php
$nameSorted->select();
// SELECT *
// FROM users
// ORDER BY name ASC
```

If the order argument is specified, the order lists are merged:

```php
$nameSorted->select(null, null, [Order::by('email')->desc()]);
// SELECT *
// FROM users
// ORDER BY name ASC, email DESC
```

## Custom Queries

When creating an `Atlas` instance, you can supply your own `Config`. This allows you to modify how the queries are
generated.

Currently, the `Config` only accepts a map of string keys to `QueryTypeInterface` instances. These instances are
responsible for converting `Query` objects into strings. They are picked from the `Config` by the `Table` class when
a query needs to be created.

For instance, when you call the `Table::insert()` method, the table will pick the query type that corresponds to the
`"INSERT"` key.

If you need to customize the query generation, you write your own query types to override the default ones. You can
even add new types:

```php
use RebelCode\Atlas\Atlas;
use RebelCode\Atlas\Config;
use RebelCode\Atlas\QueryType;

$config = new Config([
    QueryType::SELECT => new QueryType\Select(),
    QueryType::INSERT => new QueryType\Insert(),
    'custom_type' => new MyCustomType(),
]);

$atlas = new Atlas($config);
```

**Important**: You will need to specify _all_ the types. If you only want to make modifications to the default config,
you should use `Config::withOverrides()`:

```php
use RebelCode\Atlas\Config;

$config = Config::createDefault()->withOverrides([
    QueryType::INSERT => new CustomInsert(),
    'custom_type' => new MyCustomType(),
]);
```

Refer to [`QueryTypeInterface`](src/QueryTypeInterface.php) to see what you need to implement to create your own types,
and use the existing implementations as guides.

## Why "Atlas"?

The name is ironic.

The package is meant to be "lightweight" - we're not sure what the threshold for that criteria is. So we named the
package after [Atlas](https://en.wikipedia.org/wiki/Atlas_(mythology)), the Greek god that holds up the world on his
shoulders. Because well ... presumably, the world is pretty heavy, even for a god.


[github-ci]: https://github.com/RebelCode/wp-http/actions/workflows/continuous-integration.yml
[packagist]: https://packagist.org/packages/rebelcode/wp-http
