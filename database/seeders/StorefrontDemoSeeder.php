<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\Setting;
use App\Models\TeamMember;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

/**
 * Local demo catalogue for working on the storefront:
 *   php artisan db:seed --class=StorefrontDemoSeeder
 *
 * Categories with a parent/child pair, ten products (colours + sizes 14–50,
 * a sale item, letter sizes, one sold out), two journal posts, team members
 * and the shipping/payment/social settings the pages read. Safe to re-run.
 */
class StorefrontDemoSeeder extends Seeder
{
    private const IMG = 'https://images.unsplash.com/photo-%s?auto=format&fit=crop&w=1200&q=80';

    public function run(): void
    {
        $this->settings();
        $categories = $this->categories();
        $this->products($categories);
        $this->posts();
        $this->team();

        Cache::flush();
    }

    private function img(string $id): string
    {
        return sprintf(self::IMG, $id);
    }

    private function settings(): void
    {
        $settings = [
            'business_name' => 'Bacha Stylo',
            'business_email' => 'support@bachastylo.com',
            'order_notification_email' => 'support@bachastylo.com',
            'email_from_name' => 'Bacha Stylo',
            'business_phone' => '+92 340 9002068',
            'business_address' => 'Main Boulevard, Lahore, Pakistan',
            'whatsapp_number' => '+92 340 9002068',
            'facebook_url' => 'https://www.facebook.com/BachaStyloFashion',
            'instagram_url' => 'https://www.instagram.com/BachaStyloFashion',
            'tiktok_url' => 'https://www.tiktok.com/@BachaStylo2',
            'shipping_fee' => '250',
            'free_shipping_enabled' => '1',
            'free_shipping_threshold' => '5000',
            'delivery_policy' => "Orders are dispatched within 24 hours and delivered in 2–5 working days nationwide.\nAdvance payment via Bank Transfer, EasyPaisa or JazzCash confirms your order faster.",
            'meta_title' => 'Bacha Stylo | Premium Pakistani Fashion & Lifestyle',
            'meta_description' => 'Premium Pakistani fashion and lifestyle — traditional wear, waistcoats, Chitrali pakol caps, shawls, fragrances, footwear, and refined accessories. Nationwide delivery, Cash on Delivery, JazzCash and Easypaisa.',
            'meta_keywords' => 'fashion, traditional wear, waistcoats, shawls, accessories, pakistan',
            'canonical_base_url' => 'https://www.bachastylo.com',
            'robots_meta' => 'index, follow',
            'footer_about' => 'Bacha Stylo is a premium Pakistani fashion and lifestyle brand offering traditional wear, fragrances, footwear, accessories, and everyday essentials designed with elegance, simplicity, and authenticity.',
            'home_highlight_title' => 'Heritage, tailored for today',
            'home_highlight_description' => 'Every waistcoat is cut from premium Karandi and finished by hand in Lahore. Built for weddings, Eid and everything in between.',
            'home_highlight_image' => $this->img('1594938298603-c8148c4b4bf1'),
            'home_highlight_button' => 'Shop Waistcoats',
            'home_highlight_link' => '/products?category=waistcoats',
            'intro_enabled' => '0',
            'intro_bg_type' => 'image',
            'promo_enabled' => '0',
            'promo_media_type' => 'image',
            'cod_enabled' => '1',
            'bank_transfer_enabled' => '1',
            'easypaisa_enabled' => '1',
            'jazzcash_enabled' => '1',
            'bank_name' => 'Meezan Bank',
            'bank_account_title' => 'Bacha Stylo Fashion Home',
            'bank_account_number' => '0123 4567 8901 2345',
            'bank_iban' => 'PK36MEZN0001234567890123',
            'easypaisa_account_name' => 'Bacha Stylo',
            'easypaisa_number' => '0340 9002068',
            'jazzcash_account_name' => 'Bacha Stylo',
            'jazzcash_number' => '0340 9002068',
            'about_founder_name' => 'Hamza Bacha',
            'about_founder_role' => 'Founder & Creative Director',
            'about_founder_bio' => 'Hamza started Bacha Stylo from a single stall in Anarkali with one idea: Pakistani kids deserve clothes that are as proud of their heritage as they are comfortable to play in.',
            'about_founder_initials' => 'HB',
            'about_story_heading' => 'From one stall in Anarkali',
            'about_story_accent' => 'to wardrobes nationwide',
            'about_story_body' => "What began as a weekend stall is now a family of tailors, embroiderers and finishers working out of our Lahore atelier.\nEvery piece still passes through the same hands before it ships.",
            'about_story_location' => 'Lahore, Pakistan',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['setting_key' => $key], ['setting_value' => $value]);
        }
    }

    /** @return array<string,Category> */
    private function categories(): array
    {
        $rows = [
            ['name' => 'Waistcoats for Men & Boys', 'slug' => 'waistcoats', 'description' => 'Hand-finished Karandi and velvet waistcoats for every age.', 'image' => $this->img('1594938298603-c8148c4b4bf1')],
            ['name' => 'Kids Waistcoats', 'slug' => 'kids-waistcoats', 'parent' => 'waistcoats', 'description' => 'Sizes 14–26 for the little gentlemen.', 'image' => $this->img('1503944583220-79d8926ad5e2')],
            ['name' => 'Men Waistcoats', 'slug' => 'men-waistcoats', 'parent' => 'waistcoats', 'description' => 'Sizes 34–50, tailored fit.', 'image' => $this->img('1617137968427-85924c800a22')],
            ['name' => 'Shawls', 'slug' => 'shawls', 'description' => 'Elegant and warm shawls for all seasons.', 'image' => $this->img('1544441893-675973e31985')],
            ['name' => 'Kurta Sets', 'slug' => 'kurta-sets', 'description' => 'Traditional kurta sets with modern cuts.', 'image' => $this->img('1618886487325-f665032b6358')],
            ['name' => 'Pakol Caps', 'slug' => 'pakol-caps', 'description' => 'Chitrali pakol caps in pure wool.', 'image' => $this->img('1521369909029-2afed882baee')],
            ['name' => 'Accessories', 'slug' => 'accessories', 'description' => 'Handpicked accessories to complete every look.', 'image' => $this->img('1512436991641-6745cdb1723f')],
        ];

        $out = [];
        foreach ($rows as $row) {
            $parentSlug = $row['parent'] ?? null;
            unset($row['parent']);
            $row['parent_id'] = $parentSlug ? $out[$parentSlug]->id : null;
            $row['meta_title'] = $row['name'];
            $row['meta_description'] = $row['description'];
            $out[$row['slug']] = Category::updateOrCreate(['slug' => $row['slug']], $row);
        }

        return $out;
    }

    private function products(array $categories): void
    {
        $products = [
            [
                'name' => 'Royal Maroon Karandi Waistcoat',
                'slug' => 'royal-maroon-karandi-waistcoat',
                'sku' => 'BS-WC-001',
                'category' => 'men-waistcoats',
                'description' => '<p>Our signature Karandi waistcoat, cut slim with a mandarin collar and finished with hand-stitched piping. The maroon shade photographs beautifully at weddings and mehndis.</p><ul><li>Premium Karandi wool blend</li><li>Satin-lined with two inside pockets</li><li>Dry clean recommended</li></ul>',
                'meta_title' => 'Royal Maroon Karandi Waistcoat | Bacha Stylo',
                'meta_description' => 'Slim-fit maroon Karandi waistcoat with mandarin collar, available in sizes 14 to 50 and four colours.',
                'price' => '6500',
                'original_price' => '7500',
                'image' => $this->img('1594938298603-c8148c4b4bf1'),
                'gallery' => [$this->img('1617137968427-85924c800a22'), $this->img('1507679799987-c73779587ccf')],
                'sizes' => ['14', '16', '18', '20', '22', '24', '26', '28', '30', '32', '34', '36', '38', '40', '42', '44', '46', '48', '50'],
                'colors' => [
                    ['name' => 'Maroon', 'hex' => '#7a1f2b', 'image' => $this->img('1594938298603-c8148c4b4bf1'), 'stock' => 12, 'note' => 'Premium Karandi'],
                    ['name' => 'Navy', 'hex' => '#1f2a44', 'image' => $this->img('1617137968427-85924c800a22'), 'stock' => 8],
                    ['name' => 'Black', 'hex' => '#141414', 'image' => $this->img('1507679799987-c73779587ccf'), 'stock' => 2],
                    ['name' => 'Emerald', 'hex' => '#1f6f50', 'price' => 6900, 'stock' => 0, 'note' => 'Velvet finish'],
                ],
                'is_new' => true, 'is_featured' => true, 'is_best_seller' => true, 'label' => 'Wedding Season',
                'accordions' => [
                    ['title' => 'Fabric', 'content' => 'Karandi wool blend, satin lining'],
                    ['title' => 'Care', 'content' => 'Dry clean only. Store on a hanger.'],
                ],
            ],
            [
                'name' => 'Little Prince Velvet Waistcoat',
                'slug' => 'little-prince-velvet-waistcoat',
                'sku' => 'BS-WC-002',
                'category' => 'kids-waistcoats',
                'description' => '<p>Soft velvet waistcoat for boys with gold-tone buttons and an easy stretch back panel so it survives a full day of Eid running around.</p>',
                'price' => '3800',
                'original_price' => null,
                'image' => $this->img('1503944583220-79d8926ad5e2'),
                'gallery' => [$this->img('1519238263530-99bdd11df2ea')],
                'sizes' => ['14', '16', '18', '20', '22', '24', '26'],
                'colors' => [
                    ['name' => 'Royal Blue', 'hex' => '#1d4ed8', 'stock' => 15],
                    ['name' => 'Burgundy', 'hex' => '#7f1d1d', 'stock' => 9],
                ],
                'is_new' => true, 'is_featured' => true, 'is_best_seller' => false, 'label' => null,
            ],
            [
                'name' => 'Classic Black Waistcoat',
                'slug' => 'classic-black-waistcoat',
                'sku' => 'BS-WC-003',
                'category' => 'men-waistcoats',
                'description' => '<p>The one every wardrobe needs. Matte black Karandi, five buttons, straight hem.</p>',
                'price' => '5900',
                'original_price' => null,
                'image' => $this->img('1507679799987-c73779587ccf'),
                'gallery' => [],
                'sizes' => ['34', '36', '38', '40', '42', '44', '46', '48', '50'],
                'colors' => [],
                'is_new' => false, 'is_featured' => false, 'is_best_seller' => true, 'label' => null,
            ],
            [
                'name' => 'Ruby Winter Shawl',
                'slug' => 'ruby-winter-shawl',
                'sku' => 'BS-SH-001',
                'category' => 'shawls',
                'description' => '<p>Soft textured winter shawl with a rich ruby tone and hand-knotted tassels.</p>',
                'price' => '4500',
                'original_price' => '5200',
                'image' => $this->img('1483985988355-763728e1935b'),
                'gallery' => [$this->img('1515886657613-9f3515b0c78f'), $this->img('1524504388940-b1c1722653e1')],
                'sizes' => ['S', 'M', 'L'],
                'colors' => [],
                'is_new' => true, 'is_featured' => true, 'is_best_seller' => true, 'label' => 'Limited',
                'accordions' => [['title' => 'Fabric', 'content' => 'Premium blended wool'], ['title' => 'Care', 'content' => 'Dry clean recommended']],
            ],
            [
                'name' => 'Charcoal Pashmina Shawl',
                'slug' => 'charcoal-pashmina-shawl',
                'sku' => 'BS-SH-002',
                'category' => 'shawls',
                'description' => '<p>Feather-light pashmina blend in charcoal grey. One generous size.</p>',
                'price' => '7200',
                'original_price' => null,
                'image' => $this->img('1520903920243-00d872a2d1c9'),
                'gallery' => [],
                'sizes' => [],
                'colors' => [],
                'is_new' => false, 'is_featured' => false, 'is_best_seller' => false, 'label' => null,
            ],
            [
                'name' => 'Emerald Festive Kurta Set',
                'slug' => 'emerald-festive-kurta-set',
                'sku' => 'BS-KS-001',
                'category' => 'kurta-sets',
                'description' => '<p>Festive kurta set tailored for comfort and style, with matching shalwar.</p>',
                'price' => '5200',
                'original_price' => '5800',
                'image' => $this->img('1521572267360-ee0c2909d518'),
                'gallery' => [$this->img('1529635436167-bf0f036d4d72')],
                'sizes' => ['S', 'M', 'L', 'XL'],
                'colors' => [
                    ['name' => 'Emerald', 'hex' => '#10b981', 'stock' => 6],
                    ['name' => 'Ivory', 'hex' => '#f5f5f0', 'stock' => 4],
                ],
                'is_new' => false, 'is_featured' => false, 'is_best_seller' => true, 'label' => null,
            ],
            [
                'name' => 'Sunday Linen Kurta Set',
                'slug' => 'sunday-linen-kurta-set',
                'sku' => 'BS-KS-002',
                'category' => 'kurta-sets',
                'description' => '<p>Breathable linen for long summer afternoons.</p>',
                'price' => '4100',
                'original_price' => null,
                'image' => $this->img('1618886487325-f665032b6358'),
                'gallery' => [],
                'sizes' => ['S', 'M', 'L'],
                'colors' => [['name' => 'Sand', 'hex' => '#d6c3a5', 'stock' => 0]],
                'is_new' => false, 'is_featured' => false, 'is_best_seller' => false, 'label' => 'Sold Out',
            ],
            [
                'name' => 'Chitrali Pakol Cap',
                'slug' => 'chitrali-pakol-cap',
                'sku' => 'BS-PC-001',
                'category' => 'pakol-caps',
                'description' => '<p>Hand-rolled pure wool pakol from Chitral. The real thing.</p>',
                'price' => '1800',
                'original_price' => null,
                'image' => $this->img('1521369909029-2afed882baee'),
                'gallery' => [],
                'sizes' => [],
                'colors' => [
                    ['name' => 'Natural', 'hex' => '#c9b8a3', 'stock' => 30],
                    ['name' => 'Brown', 'hex' => '#5b3a29', 'stock' => 20],
                    ['name' => 'Grey', 'hex' => '#6b6b6b', 'stock' => 12],
                ],
                'is_new' => true, 'is_featured' => false, 'is_best_seller' => true, 'label' => null,
            ],
            [
                'name' => 'Oud Royale Attar',
                'slug' => 'oud-royale-attar',
                'sku' => 'BS-AC-001',
                'category' => 'accessories',
                'description' => '<p>Alcohol-free oud attar in a 12 ml roll-on. Lasts the whole day.</p>',
                'price' => '2400',
                'original_price' => '2900',
                'image' => $this->img('1541643600914-78b084683601'),
                'gallery' => [],
                'sizes' => [],
                'colors' => [],
                'is_new' => false, 'is_featured' => true, 'is_best_seller' => false, 'label' => null,
            ],
            [
                'name' => 'Leather Peshawari Chappal',
                'slug' => 'leather-peshawari-chappal',
                'sku' => 'BS-AC-002',
                'category' => 'accessories',
                'description' => '<p>Full-grain leather Peshawari chappal with tyre sole.</p>',
                'price' => '3900',
                'original_price' => null,
                'image' => $this->img('1543163521-1bf539c55dd2'),
                'gallery' => [],
                'sizes' => ['40', '41', '42', '43', '44'],
                'colors' => [['name' => 'Tan', 'hex' => '#b8865b', 'stock' => 7], ['name' => 'Black', 'hex' => '#141414', 'stock' => 5]],
                'is_new' => false, 'is_featured' => false, 'is_best_seller' => false, 'label' => null,
            ],
        ];

        foreach ($products as $row) {
            $row['colors'] = $row['colors'] === [] ? null : json_encode($row['colors']);
            $row['gallery'] = $row['gallery'] === [] ? null : json_encode($row['gallery']);
            $row['sizes'] = $row['sizes'] === [] ? null : json_encode($row['sizes']);
            $product = Product::updateOrCreate(['slug' => $row['slug']], $row);

            if ($product->reviews()->count() === 0 && in_array($row['slug'], ['royal-maroon-karandi-waistcoat', 'ruby-winter-shawl'], true)) {
                ProductReview::create(['product_id' => $product->id, 'author_name' => 'Ayesha K.', 'rating' => 5, 'comment' => 'Stitching is flawless and the colour is exactly as pictured.', 'is_approved' => true]);
                ProductReview::create(['product_id' => $product->id, 'author_name' => 'Bilal R.', 'rating' => 4, 'comment' => 'Fits true to size. Delivery took three days to Karachi.', 'is_approved' => true]);
            }
        }
    }

    private function posts(): void
    {
        $posts = [
            [
                'title' => 'How to size a waistcoat for a growing boy',
                'slug' => 'how-to-size-a-waistcoat-for-a-growing-boy',
                'excerpt' => 'Measure the chest, add two inches, and pick the band. Our fit guide for parents.',
                'category' => 'Fit Guide',
                'image' => $this->img('1503944583220-79d8926ad5e2'),
                'content' => '<p>Kids grow fast, and a waistcoat bought for Eid should still button up for the next wedding. Here is how we size at Bacha Stylo.</p><h2>Measure the chest</h2><p>Wrap a tape around the fullest part of the chest, under the arms. Add two inches of ease.</p><h2>Pick the band</h2><p>Sizes 14–26 are our Kids band, 28–32 Teens, 34–42 Gen Z and 44–50 Plus.</p>',
                'meta_title' => 'Waistcoat sizing guide for boys',
                'meta_description' => 'A simple chest-measurement guide to picking the right waistcoat size for kids and teens.',
                'status' => true,
            ],
            [
                'title' => 'Caring for Karandi: a five-minute routine',
                'slug' => 'caring-for-karandi',
                'excerpt' => 'Karandi rewards a little care. Brush, air, and never hang it in a damp wardrobe.',
                'category' => 'Care',
                'image' => $this->img('1594938298603-c8148c4b4bf1'),
                'content' => '<p>Karandi is a wool blend that holds its shape for years when treated well.</p><ul><li>Brush after every wear</li><li>Air for an hour before storing</li><li>Dry clean twice a season at most</li></ul>',
                'status' => true,
            ],
        ];

        foreach ($posts as $row) {
            BlogPost::updateOrCreate(['slug' => $row['slug']], $row);
        }
    }

    private function team(): void
    {
        $members = [
            ['title' => 'Design Studio', 'subtitle' => 'Patterns & embroidery', 'icon' => 'palette', 'sort_order' => 1],
            ['title' => 'Master Tailors', 'subtitle' => 'Cutting & stitching', 'icon' => 'gem', 'sort_order' => 2],
            ['title' => 'Quality Check', 'subtitle' => 'Every piece, every seam', 'icon' => 'shield', 'sort_order' => 3],
            ['title' => 'Customer Care', 'subtitle' => 'WhatsApp, email, calls', 'icon' => 'headphones', 'sort_order' => 4],
        ];
        foreach ($members as $m) {
            TeamMember::updateOrCreate(['title' => $m['title']], $m + ['is_active' => true]);
        }
    }
}
