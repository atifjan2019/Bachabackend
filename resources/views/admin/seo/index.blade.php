@extends('layouts.admin')
@section('title', 'SEO Settings')
@section('content')

<div class="ph">
    <div>
        <h4>SEO Settings</h4>
        <div class="ph-sub">Search engine and analytics configuration</div>
    </div>
</div>

<form action="{{ route('admin.seo.update') }}" method="POST">
    @csrf @method('PUT')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="bcard">
                <div class="bcard-head"><span class="bcard-title">Homepage Meta Tags</span></div>
                <div class="bcard-body">
                    <div class="form-group">
                        <label class="form-label">Meta Title</label>
                        <input type="text" name="meta_title" class="form-control" value="{{ $settings['meta_title'] ?? '' }}" placeholder="Bacha Stylo — Premium Shawls & Fabrics">
                        <div class="form-hint">Recommended: 50–60 characters</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Meta Description</label>
                        <textarea name="meta_description" class="form-control" rows="3" placeholder="Shop premium quality shawls...">{{ $settings['meta_description'] ?? '' }}</textarea>
                        <div class="form-hint">Recommended: 150–160 characters</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Meta Keywords</label>
                        <input type="text" name="meta_keywords" class="form-control" value="{{ $settings['meta_keywords'] ?? '' }}" placeholder="shawls, fabric, premium, pakistan">
                    </div>
                    <div class="form-group">
                        <label class="form-label">OG / Social Share Image URL</label>
                        <input type="text" name="og_image" class="form-control" value="{{ $settings['og_image'] ?? '' }}" placeholder="https://...">
                        <div class="form-hint">Used when your site is shared on social media (1200×630px recommended)</div>
                    </div>
                </div>
            </div>

            <div class="bcard">
                <div class="bcard-head"><span class="bcard-title">Analytics & Tracking</span></div>
                <div class="bcard-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label">Google Analytics ID</label>
                            <input type="text" name="google_analytics_id" class="form-control" value="{{ $settings['google_analytics_id'] ?? '' }}" placeholder="G-XXXXXXXXXX">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Facebook Pixel ID</label>
                            <input type="text" name="facebook_pixel_id" class="form-control" value="{{ $settings['facebook_pixel_id'] ?? '' }}" placeholder="1234567890">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Google Search Console Verification</label>
                            <input type="text" name="google_site_verification" class="form-control" value="{{ $settings['google_site_verification'] ?? '' }}" placeholder="meta content value">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="side-stack">
            <div class="bcard">
                <div class="bcard-head"><span class="bcard-title">Indexing</span></div>
                <div class="bcard-body">
                    <div class="form-group">
                        <label class="form-label">Robots Meta</label>
                        <select name="robots_meta" class="form-select">
                            @foreach(['index, follow', 'noindex, nofollow', 'noindex, follow', 'index, nofollow'] as $opt)
                            <option value="{{ $opt }}" {{ ($settings['robots_meta'] ?? 'index, follow') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Canonical Base URL</label>
                        <input type="text" name="canonical_base_url" class="form-control" value="{{ $settings['canonical_base_url'] ?? '' }}" placeholder="https://bachastylo.com">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 btn-lg">
                <i class="mdi mdi-content-save-outline"></i> Save SEO Settings
            </button>
            </div>
        </div>
    </div>
</form>
@endsection
