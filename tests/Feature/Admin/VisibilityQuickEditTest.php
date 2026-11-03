<?php

namespace Tests\Feature\Admin;

use App\Models\AdminUser;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class VisibilityQuickEditTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): AdminUser
    {
        return AdminUser::create([
            'username' => 'admin_user', 'email' => 'admin@example.com',
            'password' => Hash::make('secret123'), 'login_attempts' => 0,
        ]);
    }

    public function test_quick_edit_toggles_status_and_sets_order_for_categories_and_products(): void
    {
        $admin = $this->admin();
        $category = Category::create(['name' => 'Shawls', 'slug' => 'shawls']);
        $product = Product::create(['name' => 'Ruby Shawl', 'slug' => 'ruby-shawl', 'category' => 'shawls', 'price' => '1000']);

        $this->actingAs($admin, 'admin')->patchJson(route('admin.categories.quick', $category->id), ['is_active' => 0])
            ->assertOk()->assertJson(['ok' => true, 'is_active' => false]);
        $this->actingAs($admin, 'admin')->patchJson(route('admin.categories.quick', $category->id), ['sort_order' => 3])
            ->assertOk()->assertJson(['sort_order' => 3]);
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'is_active' => 0, 'sort_order' => 3]);

        $this->actingAs($admin, 'admin')->patchJson(route('admin.products.quick', $product->id), ['sort_order' => 7])
            ->assertOk()->assertJson(['sort_order' => 7]);
        $this->actingAs($admin, 'admin')->patchJson(route('admin.products.quick', $product->id), ['sort_order' => null])
            ->assertOk()->assertJson(['sort_order' => null]);
        $this->actingAs($admin, 'admin')->patchJson(route('admin.products.quick', $product->id), ['is_active' => 0])
            ->assertOk()->assertJson(['is_active' => false]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'is_active' => 0, 'sort_order' => null]);

        $this->actingAs($admin, 'admin')->patchJson(route('admin.products.quick', $product->id), ['sort_order' => -1])
            ->assertStatus(422);
    }

    public function test_quick_edit_requires_an_admin(): void
    {
        $product = Product::create(['name' => 'Ruby Shawl', 'slug' => 'ruby-shawl', 'price' => '1000']);

        $this->patchJson(route('admin.products.quick', $product->id), ['is_active' => 0])->assertUnauthorized();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'is_active' => 1]);
    }

    public function test_forms_save_status_and_order_and_the_lists_show_them(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')->post(route('admin.categories.store'), [
            'name' => 'Caps', 'slug' => 'caps', 'is_active' => 1, 'sort_order' => 2,
        ])->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', ['slug' => 'caps', 'is_active' => 1, 'sort_order' => 2]);

        $this->actingAs($admin, 'admin')->post(route('admin.products.store'), [
            'name' => 'Pakol', 'slug' => 'pakol', 'category' => 'caps', 'price' => '1800', 'sort_order' => '',
        ])->assertRedirect(route('admin.products.index'));
        // No checkbox posted means the switch was off.
        $this->assertDatabaseHas('products', ['slug' => 'pakol', 'is_active' => 0, 'sort_order' => null]);

        $this->actingAs($admin, 'admin')->get(route('admin.categories.index'))->assertOk()
            ->assertSee('data-quick-field="is_active"', false)->assertSee('value="2"', false);
        $this->actingAs($admin, 'admin')->get(route('admin.products.index'))->assertOk()
            ->assertSee('Hidden')->assertSee('data-quick-field="sort_order"', false);
        $this->actingAs($admin, 'admin')->get(route('admin.products.edit', Product::first()->id))->assertOk()
            ->assertSee('Active (shown on the website)');
    }

    public function test_drag_reorder_numbers_the_dropped_rows_from_the_page_offset(): void
    {
        $admin = $this->admin();
        $a = Category::create(['name' => 'A', 'slug' => 'a']);
        $b = Category::create(['name' => 'B', 'slug' => 'b']);
        $c = Category::create(['name' => 'C', 'slug' => 'c', 'sort_order' => 1]);

        $this->actingAs($admin, 'admin')->postJson(route('admin.categories.reorder'), ['ids' => [$b->id, $a->id, $c->id]])
            ->assertOk()->assertJson(['positions' => [$b->id => 1, $a->id => 2, $c->id => 3]]);
        $this->assertSame(['b', 'a', 'c'], Category::query()->ordered()->pluck('slug')->all());

        $p1 = Product::create(['name' => 'P1', 'slug' => 'p1', 'price' => '1']);
        $p2 = Product::create(['name' => 'P2', 'slug' => 'p2', 'price' => '1']);
        $this->actingAs($admin, 'admin')->postJson(route('admin.products.reorder'), ['ids' => [$p2->id, $p1->id], 'offset' => 20])
            ->assertOk()->assertJson(['positions' => [$p2->id => 21, $p1->id => 22]]);
        $this->assertDatabaseHas('products', ['id' => $p1->id, 'sort_order' => 22]);

        $this->actingAs($admin, 'admin')->postJson(route('admin.products.reorder'), ['ids' => []])->assertStatus(422);
    }
}
