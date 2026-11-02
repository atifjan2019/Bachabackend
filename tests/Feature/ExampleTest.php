<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /** The storefront home now lives at the root instead of redirecting to the admin. */
    public function test_root_renders_the_storefront_home(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Timeless Pakistani style');
    }

    public function test_admin_login_still_works(): void
    {
        $this->get(route('admin.login'))->assertOk();
    }
}
