<?php

/*
 * This file is part of ianm/twofactor.
 *
 * Copyright (c) 2023 IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace IanM\TwoFactor\Tests\integration\api;

use Flarum\Group\Group;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use IanM\TwoFactor\Model\TwoFactor;
use IanM\TwoFactor\Tests\integration\GeneratesTwoFactorCodes;
use PHPUnit\Framework\Attributes\Test;

class TwoFactorUserFilterTest extends TestCase
{
    use RetrievesAuthorizedUsers;
    use GeneratesTwoFactorCodes;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extension('ianm-twofactor');

        $this->prepareDatabase([
            User::class => [
                $this->normalUser(),
                ['id' => 3, 'username' => 'enrolled', 'email' => 'enrolled@machine.local', 'is_email_confirmed' => 1],
                ['id' => 4, 'username' => 'pending', 'email' => 'pending@machine.local', 'is_email_confirmed' => 1],
                ['id' => 5, 'username' => 'watcher', 'email' => 'watcher@machine.local', 'is_email_confirmed' => 1],
                ['id' => 6, 'username' => 'enrolled2', 'email' => 'enrolled2@machine.local', 'is_email_confirmed' => 1],
                ['id' => 7, 'username' => 'enrolled3', 'email' => 'enrolled3@machine.local', 'is_email_confirmed' => 1],
                ['id' => 8, 'username' => 'plain1', 'email' => 'plain1@machine.local', 'is_email_confirmed' => 1],
                ['id' => 9, 'username' => 'plain2', 'email' => 'plain2@machine.local', 'is_email_confirmed' => 1],
            ],
            TwoFactor::class => [
                $this->twoFactorRow(3),
                ['is_active' => false] + $this->twoFactorRow(4),
                $this->twoFactorRow(6),
                $this->twoFactorRow(7),
            ],
            'group_user' => [
                ['user_id' => 5, 'group_id' => 5],
            ],
            Group::class => [
                ['id' => 5, 'name_singular' => 'Watcher', 'name_plural' => 'Watchers'],
            ],
            'group_permission' => [
                ['group_id' => 5, 'permission' => 'ianm-twofactor.seeTwoFactorStatus'],
                ['group_id' => 5, 'permission' => 'searchUsers'],
                ['group_id' => Group::MEMBER_ID, 'permission' => 'searchUsers'],
            ],
        ]);
    }

    #[Test]
    public function users_with_2fa_enabled_can_be_listed()
    {
        $this->assertEquals(['3', '6', '7'], $this->userIds(1, ['twofactor' => '']));
    }

    #[Test]
    public function users_without_2fa_enabled_can_be_listed()
    {
        $this->assertEquals(['1', '2', '4', '5', '8', '9'], $this->userIds(1, ['-twofactor' => '']));
    }

    #[Test]
    public function the_filter_works_for_anyone_who_may_see_2fa_status()
    {
        $this->assertEquals(['3', '6', '7'], $this->userIds(5, ['twofactor' => '']));
    }

    #[Test]
    public function the_filter_is_ignored_for_users_who_may_not_see_2fa_status()
    {
        $all = ['1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $this->assertEquals($all, $this->userIds(2, ['twofactor' => '']));
        $this->assertEquals($all, $this->userIds(2, ['-twofactor' => '']));
    }

    #[Test]
    public function listing_users_does_not_create_2fa_records()
    {
        $this->userIds(1, []);

        $this->assertEquals(4, TwoFactor::query()->count());
    }

    #[Test]
    public function the_user_list_shows_2fa_status_to_those_who_may_see_it()
    {
        $users = $this->users(5);

        $this->assertTrue($users['3']['attributes']['twoFactorEnabled']);
        $this->assertArrayHasKey('backupCodesRemaining', $users['3']['attributes']);
        $this->assertFalse($users['4']['attributes']['twoFactorEnabled']);
        $this->assertFalse($users['8']['attributes']['twoFactorEnabled']);
    }

    #[Test]
    public function the_2fa_record_is_only_included_for_those_who_may_see_2fa_status()
    {
        $this->assertNotNull($this->twoFactorLinkage(3, 3));
        $this->assertNotNull($this->twoFactorLinkage(5, 3));
        $this->assertNotNull($this->twoFactorLinkage(1, 3));
        $this->assertNull($this->twoFactorLinkage(2, 3));
    }

    #[Test]
    public function the_forum_says_who_may_use_the_filter()
    {
        $this->assertTrue($this->forumAttribute(5, 'canSeeTwoFactorStatus'));
        $this->assertFalse($this->forumAttribute(2, 'canSeeTwoFactorStatus'));
    }

    protected function userIds(int $actorId, array $filter): array
    {
        $response = $this->send(
            $this->request('GET', '/api/users', ['authenticatedAs' => $actorId])
                ->withQueryParams(['filter' => $filter, 'sort' => 'username'])
        );

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());

        $ids = array_column(json_decode((string) $response->getBody(), true)['data'], 'id');
        sort($ids);

        return $ids;
    }

    protected function twoFactorLinkage(int $actorId, int $userId): ?array
    {
        $response = $this->send(
            $this->request('GET', "/api/users/$userId", ['authenticatedAs' => $actorId])
        );

        return json_decode((string) $response->getBody(), true)['data']['relationships']['twoFactor']['data'] ?? null;
    }

    protected function users(int $actorId): array
    {
        $response = $this->send(
            $this->request('GET', '/api/users', ['authenticatedAs' => $actorId])
        );

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());

        return array_column(json_decode((string) $response->getBody(), true)['data'], null, 'id');
    }

    protected function forumAttribute(int $actorId, string $name): mixed
    {
        $response = $this->send($this->request('GET', '/api', ['authenticatedAs' => $actorId]));

        return json_decode((string) $response->getBody(), true)['data']['attributes'][$name] ?? null;
    }
}
