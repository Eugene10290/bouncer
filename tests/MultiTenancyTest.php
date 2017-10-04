<?php

use Illuminate\Events\Dispatcher;
use Silber\Bouncer\Database\Role;
use Silber\Bouncer\Database\Models;
use Silber\Bouncer\Database\Ability;

class MultiTenancyTest extends BaseTestCase
{
    /**
     * Reset any scopes that have been applied in a test.
     *
     * @return void
     */
    public function tearDown()
    {
        Models::scope()->reset();

        parent::tearDown();
    }

    public function test_creating_roles_and_abilities_automatically_scopes_them()
    {
        $bouncer = $this->bouncer();

        $bouncer->scopeTo(1);

        $bouncer->allow('admin')->to('create', User::class);

        $this->assertEquals(1, $bouncer->ability()->query()->value('scope'));
        $this->assertEquals(1, $bouncer->role()->query()->value('scope'));
    }

    public function test_relation_queries_are_properly_scoped()
    {
        $bouncer = $this->bouncer($user = User::create());

        $bouncer->scopeRelationsTo(1);
        $bouncer->allow($user)->to('create', User::class);

        $bouncer->scopeRelationsTo(2);
        $bouncer->allow($user)->to('delete', User::class);

        $bouncer->scopeRelationsTo(1);
        $abilities = $user->abilities()->get();

        $this->assertCount(1, $abilities);
        $this->assertEquals('create', $abilities->first()->name);
        $this->assertTrue($bouncer->allows('create', User::class));
        $this->assertTrue($bouncer->denies('delete', User::class));

        $bouncer->scopeRelationsTo(2);
        $abilities = $user->abilities()->get();

        $this->assertCount(1, $abilities);
        $this->assertEquals('delete', $abilities->first()->name);
        $this->assertTrue($bouncer->allows('delete', User::class));
        $this->assertTrue($bouncer->denies('create', User::class));
    }
}
