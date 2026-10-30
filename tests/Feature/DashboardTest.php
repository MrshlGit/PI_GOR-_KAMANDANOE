<?php

namespace Tests\Feature;

use Tests\TestCase;

class DashboardTest extends TestCase
{
    public function test_dashboard_displays_empty_statistics_without_database_data(): void
    {
        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Total Pengguna')
            ->assertSee('Pendapatan Bulanan')
            ->assertSee('Rp 0')
            ->assertSee('Belum ada pemesanan')
            ->assertSee('Belum terhubung ke database');
    }
}
