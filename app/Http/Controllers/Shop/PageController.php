<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Support\Storefront\AboutContent;
use App\Support\Storefront\SiteSettings;
use Illuminate\Contracts\View\View;

class PageController extends Controller
{
    public const MISSION = [
        'Deliver high-quality fashion, fragrances, beauty care, and lifestyle products at fair prices.',
        'Build trust through honest and transparent business practices.',
        'Make traditional and modern lifestyle products accessible to all.',
        'Represent Pakistani culture with elegance and professionalism.',
        'Ensure consistent customer satisfaction through service and quality.',
    ];

    public const VALUES = [
        ['icon' => 'tag', 'title' => 'Honest Pricing', 'desc' => 'Fair prices with no inflated margins or misleading offers.'],
        ['icon' => 'gem', 'title' => 'Quality Craftsmanship', 'desc' => 'Carefully selected materials and refined finishing on every piece.'],
        ['icon' => 'shield-check', 'title' => 'Customer Trust', 'desc' => 'Transparent service that puts our customers first, always.'],
        ['icon' => 'landmark', 'title' => 'Cultural Authenticity', 'desc' => 'Pakistani heritage represented with elegance and pride.'],
        ['icon' => 'repeat', 'title' => 'Consistent Service', 'desc' => 'Dependable quality and support, order after order.'],
    ];

    public const DEFAULT_ADDRESS = 'Ouch, Tehsil Adenzai, District Lower Dir, KPK, Pakistan';

    public function about(): View
    {
        return view('shop.pages.about', AboutContent::resolved() + [
            'mission' => self::MISSION,
            'values' => self::VALUES,
        ]);
    }

    public function contact(SiteSettings $settings): View
    {
        $email = $settings->get('business_email') ?: $settings->supportEmail();
        // Phone + WhatsApp pinned to the canonical support line for consistency.
        $phone = SiteSettings::WHATSAPP_NUMBER;
        $address = $settings->get('business_address') ?: self::DEFAULT_ADDRESS;

        return view('shop.pages.contact', [
            'email' => $email,
            'phone' => $phone,
            'phoneDigits' => preg_replace('/[^0-9]/', '', $phone),
            'telHref' => 'tel:'.preg_replace('/[^0-9+]/', '', $phone),
            'address' => $address,
            'mapEmbed' => 'https://www.google.com/maps?q='.rawurlencode($address).'&output=embed',
            'directionsUrl' => 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($address),
        ]);
    }

    public function faq(SiteSettings $settings): View
    {
        $phone = $settings->supportPhone();
        $email = $settings->supportEmail();

        $categories = [
            ['id' => 'general', 'icon' => 'info', 'title' => 'General Information', 'items' => [
                ['q' => 'What is Bacha Stylo Fashion?', 'a' => 'Bacha Stylo Fashion is a fashion and lifestyle brand offering modern, seasonal, and traditional clothing along with premium accessories at affordable prices.'],
                ['q' => 'Where is your store located?', 'a' => 'Our physical store is located in Ouch, Tehsil Adenzai, District Lower Dir, KPK, Pakistan.'],
                ['q' => 'Do you have an online store?', 'a' => 'Yes, we operate through our official website and also take orders via WhatsApp and social media platforms.'],
            ]],
            ['id' => 'products', 'icon' => 'shopping-bag', 'title' => 'Products & Categories', 'items' => [
                ['q' => 'What products do you sell?', 'a' => 'We offer a wide range of fashion products including coats, waistcoats, Chitrali pakol, Swati shawls, footwear, perfumes, watches, caps, beauty & personal care, and other accessories.'],
                ['q' => 'Do you offer seasonal collections?', 'a' => 'Yes, we regularly launch seasonal collections according to weather and fashion trends.'],
                ['q' => 'Are your products local or imported?', 'a' => 'We offer a mix of carefully selected local and quality-based imported fashion items.'],
            ]],
            ['id' => 'orders', 'icon' => 'truck', 'title' => 'Orders & Delivery', 'items' => [
                ['q' => 'How can I place an order?', 'a' => 'You can place orders through our website, WhatsApp, or official social media pages.'],
                ['q' => 'Do you offer Cash on Delivery?', 'a' => 'Yes, Cash on Delivery (COD) is available for most locations in Pakistan.'],
                ['q' => 'How long does delivery take?', 'a' => 'Delivery usually takes 3 to 7 working days depending on your location.'],
            ]],
            ['id' => 'returns', 'icon' => 'refresh-cw', 'title' => 'Returns & Exchanges', 'items' => [
                ['q' => 'Do you offer returns or exchanges?', 'a' => 'Yes, we offer exchange in case of size issues, wrong item delivery, or damaged products.'],
                ['q' => 'What is the time limit for exchange?', 'a' => 'Exchange requests must be made within a limited time after receiving the order.'],
                ['q' => 'Are all products eligible for return?', 'a' => 'Only eligible items under our policy can be exchanged; used or damaged items by the customer are not accepted.'],
            ]],
            ['id' => 'payment', 'icon' => 'shield-check', 'title' => 'Payment & Trust', 'items' => [
                ['q' => 'What payment methods do you accept?', 'a' => 'We accept Cash on Delivery (COD) and other available online payment methods when required.'],
                ['q' => 'Is online shopping safe with Bacha Stylo Fashion?', 'a' => 'Yes, we ensure safe and reliable transactions with proper order verification before dispatch.'],
            ]],
            ['id' => 'support', 'icon' => 'headphones', 'title' => 'Customer Support', 'items' => [
                ['q' => 'How can I contact you?', 'a' => "You can reach our support team on WhatsApp or by phone at {$phone}, email us at {$email}, or message us on our social media pages."],
                ['q' => 'Do you respond quickly to queries?', 'a' => 'Yes, we aim to respond as quickly as possible during business hours to assist customers efficiently.'],
            ]],
        ];

        return view('shop.pages.faq', ['faqCategories' => $categories]);
    }

    public function privacy(): View
    {
        return view('shop.pages.privacy');
    }

    public function terms(SiteSettings $settings): View
    {
        return view('shop.pages.terms', [
            'shipping' => $settings->shippingRule(),
            'policy' => $settings->deliveryPolicyParagraphs(),
        ]);
    }

    public function refund(): View
    {
        return view('shop.pages.refund');
    }
}
