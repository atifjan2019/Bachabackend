<?php

namespace Tests\Feature\Shop;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrimaryHostRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_bare_domain_redirects_to_www_keeping_path_and_query(): void
    {
        config(['app.primary_host' => 'www.bachastylo.com']);

        $this->get('https://bachastylo.com/products?category=shawls')
            ->assertRedirect('https://www.bachastylo.com/products?category=shawls')
            ->assertStatus(301);
    }

    public function test_other_hosts_are_left_alone(): void
    {
        config(['app.primary_host' => 'www.bachastylo.com']);

        $this->get('https://www.bachastylo.com/robots.txt')->assertOk();
        $this->get('https://admin.bachastylo.com/robots.txt')->assertOk();
    }
}
