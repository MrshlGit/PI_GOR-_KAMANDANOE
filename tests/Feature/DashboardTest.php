<?php

namespace Tests\Feature;

use Tests\TestCase;

class DashboardTest extends TestCase
{
    public function test_admin_and_user_dashboards_require_authentication(): void
    {
        $this->get('/dashboard')
            ->assertRedirect(route('login'));

        $this->get('/admin')
            ->assertRedirect(route('login'));
    }
}
