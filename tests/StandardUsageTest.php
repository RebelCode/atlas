<?php

namespace RebelCode\Atlas\Test;

use PHPUnit\Framework\TestCase;
use RebelCode\Atlas\Atlas;
use RebelCode\Atlas\Join;
use RebelCode\Atlas\Schema;
use RebelCode\Atlas\Schema\Column;
use RebelCode\Atlas\Schema\ForeignKey;
use RebelCode\Atlas\Schema\Index;
use RebelCode\Atlas\Schema\PrimaryKey;
use function RebelCode\Atlas\col;
use function RebelCode\Atlas\using;

class StandardUsageTest extends TestCase
{
    public function testGetTable()
    {
        $atlas = new Atlas();
        $users = $atlas->table('users');

        $this->assertEquals('users', $users->getName());
        $this->assertNull($users->getAlias());
        $this->assertNull($users->getSchema());
    }

    public function testGetTableWithSchema()
    {
        $atlas = new Atlas();
        $users = $atlas->table(
            'users',
            $schema = new Schema(
                [
                    'id' => Column::ofType('BIGINT UNSIGNED')->autoInc(),
                    'name' => Column::ofType('VARCHAR(100'),
                    'email' => Column::ofType('VARCHAR(200)'),
                    'role' => Column::ofType('VARCHAR(20)')->default('subscriber'),
                    'team' => Column::ofType('BIGINT UNSIGNED')->nullable(),
                ],
                [
                    'users_id_pk' => new PrimaryKey(['id']),
                ],
                [
                    'user_team_fk1' => new ForeignKey(
                        'teams',
                        ['team' => 'id'],
                        ForeignKey::CASCADE,
                        ForeignKey::SET_NULL
                    ),
                ],
                [
                    'users_email_index' => new Index(true, ['email']),
                ]
            )
        );

        $this->assertSame($schema, $users->getSchema());
    }

    public function testSelectOnTableWithArgs()
    {
        $atlas = new Atlas();
        $users = $atlas->table('users');
        $query = $users->select(['id', 'name', 'email'], col('id')->eq(123), [], 1);
        $expect = 'SELECT `id`, `name`, `email` FROM `users` WHERE (`id` = 123) LIMIT 1';

        $this->assertEquals($expect, $query->toSql());
    }

    public function testSelectOnTableWithChain()
    {
        $atlas = new Atlas();
        $users = $atlas->table('users');
        $query = $users->select(['id', 'name', 'email'])
                       ->where(col('id')->eq(123))
                       ->limit(1);
        $expect = 'SELECT `id`, `name`, `email` FROM `users` WHERE (`id` = 123) LIMIT 1';

        $this->assertEquals($expect, $query->toSql());
    }

    public function testSelectJoinWithTable()
    {
        $atlas = new Atlas();
        $users = $atlas->table('users');
        $teams = $atlas->table('teams');

        $query = $users->select([$users->col('id'), 'name', 'email', $teams->col('name')->as('team')])
                       ->join([
                           using(Join::LEFT)->with($teams)->on(
                               $users->col('team')->eq($teams->col('id'))
                           ),
                       ]);

        $expect = <<<SQL
        SELECT `users`.`id`, `name`, `email`, `teams`.`name` AS `team`
        FROM `users`
        LEFT JOIN `teams` ON (`users`.`team` = `teams`.`id`)
        SQL;

        $expect = str_replace("\n", ' ', $expect);

        $this->assertEquals($expect, $query->toSql());
    }
}
