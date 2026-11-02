<?php

namespace Tests\Feature\Shop;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentPagesTest extends TestCase
{
    use RefreshDatabase;

    private function seedCatalogue(): void
    {
        Setting::create(['setting_key' => 'canonical_base_url', 'setting_value' => 'https://www.bachastylo.com']);
        $parent = Category::create(['name' => 'Waistcoats', 'slug' => 'waistcoats']);
        Category::create(['name' => 'Kids Waistcoats', 'slug' => 'kids-waistcoats', 'parent_id' => $parent->id]);
        Product::create([
            'name' => 'Royal Maroon Waistcoat',
            'slug' => 'royal-maroon-waistcoat',
            'category' => 'kids-waistcoats',
            'price' => '6500',
            'original_price' => '7500',
            'image' => 'https://example.com/a.jpg',
            'is_featured' => true,
            'is_best_seller' => true,
        ]);
        BlogPost::create([
            'title' => 'How to size a waistcoat',
            'slug' => 'how-to-size-a-waistcoat',
            'content' => '<p>Measure the chest.</p>',
            'category' => 'Fit Guide',
            'status' => true,
        ]);
        BlogPost::create([
            'title' => 'Draft post',
            'slug' => 'draft-post',
            'content' => '<p>Not yet.</p>',
            'status' => false,
        ]);
    }

    public function test_home_renders_with_catalogue_data(): void
    {
        $this->seedCatalogue();

        $this->get('/')
            ->assertOk()
            ->assertSee('Royal Maroon Waistcoat')
            ->assertSee('Explore Our Collection')
            ->assertSee('"@type":"Organization"', false);
    }

    public function test_content_pages_render(): void
    {
        $this->seedCatalogue();

        foreach (['/about', '/faq', '/contact', '/privacy', '/terms', '/refund', '/blogs'] as $path) {
            $this->get($path)->assertOk();
        }

        $this->get('/about')->assertSee('The people behind');
        $this->get('/terms')->assertSee('Delivery charge Rs. 250');
        $this->get('/blogs')->assertSee('How to size a waistcoat')->assertDontSee('Draft post');
    }

    public function test_blog_post_renders_and_drafts_are_not_found(): void
    {
        $this->seedCatalogue();

        $this->get('/blogs/how-to-size-a-waistcoat')
            ->assertOk()
            ->assertSee('Measure the chest.', false)
            ->assertSee('"@type":"Article"', false);

        $this->get('/blogs/draft-post')->assertNotFound();
        $this->get('/blogs/nope')->assertNotFound()->assertSee('This page is out of stock');
    }

    public function test_journal_empty_state_never_uses_mock_posts(): void
    {
        $this->get('/blogs')->assertOk()->assertSee('No stories published yet.');
    }

    public function test_sitemap_lists_products_categories_and_posts(): void
    {
        $this->seedCatalogue();

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('https://www.bachastylo.com/products/royal-maroon-waistcoat', false)
            ->assertSee('https://www.bachastylo.com/products?category=kids-waistcoats', false)
            ->assertSee('https://www.bachastylo.com/blogs/how-to-size-a-waistcoat', false)
            ->assertDontSee('draft-post');
    }

    public function test_robots_disallows_private_paths(): void
    {
        $this->seedCatalogue();

        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /account')
            ->assertSee('Disallow: /admin')
            ->assertSee('Sitemap: https://www.bachastylo.com/sitemap.xml');
    }

    public function test_robots_blocks_everything_when_noindex(): void
    {
        Setting::create(['setting_key' => 'robots_meta', 'setting_value' => 'noindex, nofollow']);

        $this->get('/robots.txt')->assertOk()->assertSee("Disallow: /\n", false)->assertDontSee('Disallow: /account');
    }

    public function test_unknown_storefront_url_shows_shop_404(): void
    {
        $this->get('/definitely-not-a-page')->assertNotFound()->assertSee('This page is out of stock');
    }
}
