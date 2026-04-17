<?php

namespace Database\Seeders;

use App\Models\AbandonedCart;
use App\Models\AdminUser;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Media;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        AdminUser::updateOrCreate(
            ['email' => 'admin@bachastylo.com'],
            [
                'username' => 'admin',
                'password' => Hash::make('password'),
                'login_attempts' => 0,
            ]
        );

        $settings = [
            'business_name' => 'Bacha Stylo',
            'business_email' => 'support@bachastylo.com',
            'business_phone' => '923001234567',
            'business_address' => 'Main Boulevard, Lahore, Pakistan',
            'logo_url' => 'https://storage.googleapis.com/msgsndr/1a90x6oFSkLT95cM2HEE/media/698b19b767d7491d79450638.png',
            'favicon_url' => 'https://upload.wikimedia.org/wikipedia/commons/a/a5/Red_Logo.svg',
            'shipping_fee' => '250',
            'free_shipping_threshold' => '6000',
            'facebook_url' => 'https://facebook.com/bachastylo',
            'instagram_url' => 'https://instagram.com/bachastylo',
            'whatsapp_number' => '923001234567',
            'meta_title' => 'Bacha Stylo - Premium Kids Fashion',
            'meta_description' => 'Discover premium kids wear and accessories with fast delivery.',
            'meta_keywords' => 'kids fashion, boys clothes, girls clothes, pakistan',
            'canonical_base_url' => 'https://bachastylo.com',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['setting_key' => $key],
                ['setting_value' => $value]
            );
        }

        $categories = [
            [
                'name' => 'Shawls',
                'description' => 'Elegant and warm shawls for all seasons.',
                'image' => 'https://images.unsplash.com/photo-1544441893-675973e31985?auto=format&fit=crop&w=1200&q=80',
                'slug' => 'shawls',
                'meta_title' => 'Premium Shawls',
                'meta_description' => 'Premium quality shawls for kids and family.',
            ],
            [
                'name' => 'Kurta Sets',
                'description' => 'Traditional kurta sets with modern cuts.',
                'image' => 'https://images.unsplash.com/photo-1618886487325-f665032b6358?auto=format&fit=crop&w=1200&q=80',
                'slug' => 'kurta-sets',
                'meta_title' => 'Designer Kurta Sets',
                'meta_description' => 'Festive and casual kurta sets for every occasion.',
            ],
            [
                'name' => 'Accessories',
                'description' => 'Handpicked accessories to complete every look.',
                'image' => 'https://images.unsplash.com/photo-1512436991641-6745cdb1723f?auto=format&fit=crop&w=1200&q=80',
                'slug' => 'accessories',
                'meta_title' => 'Kids Accessories',
                'meta_description' => 'Accessories for boys and girls with premium finishing.',
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }

        $products = [
            [
                'name' => 'Ruby Winter Shawl',
                'slug' => 'ruby-winter-shawl',
                'description' => 'Soft textured winter shawl with rich ruby tone.',
                'category' => 'Shawls',
                'price' => '4500',
                'original_price' => '5200',
                'image' => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=1200&q=80',
                'lifestyle' => 'https://images.unsplash.com/photo-1529139574466-a303027c1d8b?auto=format&fit=crop&w=1200&q=80',
                'gallery' => json_encode([
                    'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=900&q=80',
                ]),
                'sizes' => json_encode(['S', 'M', 'L']),
                'video_url' => null,
                'is_new' => true,
                'accordions' => [
                    ['title' => 'Fabric', 'content' => 'Premium blended wool'],
                    ['title' => 'Care', 'content' => 'Dry clean recommended'],
                ],
            ],
            [
                'name' => 'Emerald Festive Kurta',
                'slug' => 'emerald-festive-kurta',
                'description' => 'Festive kurta set tailored for comfort and style.',
                'category' => 'Kurta Sets',
                'price' => '5200',
                'original_price' => '5800',
                'image' => 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?auto=format&fit=crop&w=1200&q=80',
                'lifestyle' => 'https://images.unsplash.com/photo-1496747611176-843222e1e57c?auto=format&fit=crop&w=1200&q=80',
                'gallery' => json_encode([
                    'https://images.unsplash.com/photo-1529635436167-bf0f036d4d72?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1503342452485-86ff0a5f6f48?auto=format&fit=crop&w=900&q=80',
                ]),
                'sizes' => json_encode(['M', 'L', 'XL']),
                'video_url' => null,
                'is_new' => false,
                'accordions' => [
                    ['title' => 'Material', 'content' => 'Cotton-silk blend'],
                    ['title' => 'Includes', 'content' => 'Kurta and trouser set'],
                ],
            ],
            [
                'name' => 'Classic Gift Box',
                'slug' => 'classic-gift-box',
                'description' => 'Signature accessory gift box for special occasions.',
                'category' => 'Accessories',
                'price' => '1800',
                'original_price' => '2200',
                'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=1200&q=80',
                'lifestyle' => 'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?auto=format&fit=crop&w=1200&q=80',
                'gallery' => json_encode([
                    'https://images.unsplash.com/photo-1491553895911-0055eca6402d?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1509695507497-903c140c43b0?auto=format&fit=crop&w=900&q=80',
                ]),
                'sizes' => json_encode(['Standard']),
                'video_url' => null,
                'is_new' => true,
                'accordions' => [
                    ['title' => 'What\'s in box', 'content' => '2 accessories and greeting card'],
                    ['title' => 'Packaging', 'content' => 'Premium hard box packaging'],
                ],
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['slug' => $product['slug']],
                $product
            );
        }

        $blogPosts = [
            [
                'title' => 'Top 5 Winter Styles For Kids',
                'slug' => 'top-5-winter-styles-for-kids',
                'content' => 'Discover warm layering tips, trending colors, and practical winter outfits for kids.',
                'image' => 'https://images.unsplash.com/photo-1475180098004-ca77a66827be?auto=format&fit=crop&w=1200&q=80',
                'status' => true,
            ],
            [
                'title' => 'How To Choose The Right Eid Outfit',
                'slug' => 'how-to-choose-the-right-eid-outfit',
                'content' => 'A quick guide for choosing festive outfits that balance comfort and elegance.',
                'image' => 'https://images.unsplash.com/photo-1492707892479-7bc8d5a4ee93?auto=format&fit=crop&w=1200&q=80',
                'status' => true,
            ],
            [
                'title' => 'Fabric Care 101: Keep Clothes New',
                'slug' => 'fabric-care-101-keep-clothes-new',
                'content' => 'Simple maintenance tips so your favorite pieces stay fresh and durable.',
                'image' => 'https://images.unsplash.com/photo-1514996937319-344454492b37?auto=format&fit=crop&w=1200&q=80',
                'status' => true,
            ],
        ];

        foreach ($blogPosts as $post) {
            BlogPost::updateOrCreate(
                ['slug' => $post['slug']],
                $post
            );
        }

        $customerA = Customer::updateOrCreate(
            ['email' => 'ali.customer@example.com'],
            [
                'name' => 'Ali Raza',
                'phone' => '923331112233',
                'address' => 'Model Town, Lahore',
                'orders_count' => 2,
                'total_spent' => 9700,
            ]
        );

        $customerB = Customer::updateOrCreate(
            ['email' => 'sara.customer@example.com'],
            [
                'name' => 'Sara Khan',
                'phone' => '923009998887',
                'address' => 'DHA, Karachi',
                'orders_count' => 1,
                'total_spent' => 4550,
            ]
        );

        Order::updateOrCreate(
            ['customer_email' => $customerA->email, 'total_amount' => 5000.00],
            [
                'customer_name' => $customerA->name,
                'customer_phone' => $customerA->phone,
                'shipping_address' => $customerA->address,
                'city' => 'Lahore',
                'country' => 'Pakistan',
                'items' => [
                    ['name' => 'Ruby Winter Shawl', 'price' => 4500, 'quantity' => 1, 'size' => 'M'],
                ],
                'subtotal' => 4500,
                'shipping_fee' => 500,
                'payment_method' => 'Cash on Delivery',
                'status' => 'Processing',
            ]
        );

        Order::updateOrCreate(
            ['customer_email' => $customerB->email, 'total_amount' => 4550.00],
            [
                'customer_name' => $customerB->name,
                'customer_phone' => $customerB->phone,
                'shipping_address' => $customerB->address,
                'city' => 'Karachi',
                'country' => 'Pakistan',
                'items' => [
                    ['name' => 'Classic Gift Box', 'price' => 1800, 'quantity' => 2, 'size' => 'Standard'],
                    ['name' => 'Shipping', 'price' => 950, 'quantity' => 1],
                ],
                'subtotal' => 3600,
                'shipping_fee' => 950,
                'payment_method' => 'Bank Transfer',
                'status' => 'Pending',
            ]
        );

        AbandonedCart::updateOrCreate(
            ['email' => 'lead@example.com'],
            [
                'phone' => '923001112244',
                'cart_data' => [
                    'items' => [
                        ['name' => 'Emerald Festive Kurta', 'price' => 5200, 'qty' => 1],
                    ],
                    'subtotal' => 5200,
                    'currency' => 'PKR',
                ],
            ]
        );

        Media::firstOrCreate(
            ['file_name' => 'demo-banner.jpg'],
            [
                'file_path' => 'uploads/demo/demo-banner.jpg',
                'file_type' => 'image/jpeg',
                'file_size' => 245000,
            ]
        );

        Media::firstOrCreate(
            ['file_name' => 'demo-lookbook.jpg'],
            [
                'file_path' => 'uploads/demo/demo-lookbook.jpg',
                'file_type' => 'image/jpeg',
                'file_size' => 198400,
            ]
        );
    }
}
