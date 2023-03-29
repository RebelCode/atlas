# Atlas

A lightweight SQL builder library that does not require a database connection.

[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/rebelcode/atlas/continuous-integration.yml?branch=main&style=flat-square)][github-ci]
[![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/rebelcode/atlas?style=flat-square)][packagist]
[![Packagist Version](https://img.shields.io/packagist/v/rebelcode/atlas?style=flat-square)][packagist]
[![Packagist License](https://img.shields.io/packagist/l/rebelcode/atlas?style=flat-square)][packagist]

## About

The primary goal for Atlas is to replace SQL strings in your codebase with immutable objects that can be safely shared
between different parts of your application. Atlas is **NOT** an ORM! But queries can be executed by implementing a thin
database adapter interface.

Atlas' design philosophy is simple: to mirror SQL syntax as closely as possible. This means that the library will do
its best to not throw any exceptions or errors if your SQL queries are invalid. It is up to you to write valid
queries, just as you would with SQL strings. It is your database's job to report errors in SQL.

This keeps Atlas's runtime footprint at a minimum, allowing you to focus on writing short, readable queries.

## Installation

Install with [Composer](https://getcomposer.org/):

```
composer require rebelcode/atlas
```

## Quick Start

Create an `Atlas` instance.

```php
use RebelCode\Atlas\Atlas;

$atlas = new Atlas();
```

Get a table using the `table()` method:

```php
$users = $atlas->table('users');
```

Create queries from tables:

```php
$query = $users->select(/* ... */);
$query = $users->insert(/* ... */);
$query = $users->update(/* ... */);
$query = $users->delete(/* ... */);
$query = $users->create(/* ... */);
$query = $users->drop(/* ... */);
```

Modify queries, if necessary:

```php
use function RebelCode\Atlas\asc;

$query = $query->where($users->role->eq('admin'))
               ->orderBy(asc($users->name))
               ->limit(20)
               ->offset(10);
```

Render the query into SQL or execute it:

```php
$sql = $query->render();
$result = $query->exec();
```

Finally, RTFM! Check out the [documentation][docs] to learn what more you can with Atlas.

## Why "Atlas"?

[Atlas](https://en.wikipedia.org/wiki/Atlas_(mythology)) is the Greek god that holds up the heavens on his shoulders.

Initially, we picked the codename ironically. The package is intended to be lightweight, though it's unclear at what
threshold a package becomes "light" or "heavy". We figured that the heavens must be pretty heavy, even for a god.
So we used the codename "Atlas".

We decide to keep the name officially, because it's a good fit. Consider how unwieldy SQL strings can be in code, and
how important databases are for our applications. So, you  can think of Atlas (this package) as shouldering the burden
of carrying our database, keeping SQL strings away from our code in the same way that Atlas (the god) keeps the heavens
from falling to Earth.

## License

GPL-3.0 © [RebelCode](https://rebelcode.com/)

Read the full license [here](LICENSE).

[docs]: https://github.com/rebelcode/atlas/wiki
[github-ci]: https://github.com/RebelCode/atlas/actions/workflows/continuous-integration.yml
[packagist]: https://packagist.org/packages/rebelcode/atlas
