@extends('layouts.admin')
@section('title', 'Settings')
@section('content')

<div class="ph">
    <div>
        <h4>Settings</h4>
        <div class="ph-sub">Manage your store configuration</div>
    </div>
</div>

<form id="settingsForm" action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div class="row g-4">
        <div class="col-lg-8">

            <div class="bcard">
                <div class="bcard-head"><span class="bcard-title">General</span></div>
                <div class="bcard-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Business Name</label>
                            <input type="text" name="business_name" class="form-control" value="{{ $settings['business_name'] ?? '' }}">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Business Email</label>
                            <input type="email" name="business_email" class="form-control" value="{{ $settings['business_email'] ?? '' }}">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Business Phone</label>
                            <input type="text" name="business_phone" class="form-control" value="{{ $settings['business_phone'] ?? '' }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="business_address" class="form-control" rows="2">{{ $settings['business_address'] ?? '' }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bcard">
                <div class="bcard-head"><span class="bcard-title">Branding</span></div>
                <div class="bcard-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Logo</label>
                            <input type="file" name="logo_file" class="form-control" accept="image/*" onchange="previewImg(this, 'logo-preview')">
                            <div id="logo-preview" style="margin-top:8px;">
                                @if(!empty($settings['logo_url']))
                                    <img src="{{ $settings['logo_url'] }}" alt="Logo" style="max-height:60px; border-radius:4px;">
                                @endif
                            </div>
                            <span class="btn-library" onclick="openMediaPicker('logo_url', false)"><i class="mdi mdi-folder-image"></i> Select from Library</span>
                            <input type="hidden" name="logo_url" value="{{ $settings['logo_url'] ?? '' }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Footer Logo</label>
                            <input type="file" name="footer_logo_file" class="form-control" accept="image/*" onchange="previewImg(this, 'footer-logo-preview')">
                            <div id="footer-logo-preview" style="margin-top:8px;">
                                @if(!empty($settings['footer_logo_url']))
                                    <img src="{{ $settings['footer_logo_url'] }}" alt="Footer Logo" style="max-height:60px; border-radius:4px;">
                                @endif
                            </div>
                            <span class="btn-library" onclick="openMediaPicker('footer_logo_url', false)"><i class="mdi mdi-folder-image"></i> Select from Library</span>
                            <input type="hidden" name="footer_logo_url" value="{{ $settings['footer_logo_url'] ?? '' }}">
                            <div class="form-hint">Logo shown in the website footer. Leave blank to use the main logo.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Favicon</label>
                            <input type="file" name="favicon_file" class="form-control" accept="image/*,.ico" onchange="previewImg(this, 'favicon-preview')">
                            <div id="favicon-preview" style="margin-top:8px;">
                                @if(!empty($settings['favicon_url']))
                                    <img src="{{ $settings['favicon_url'] }}" alt="Favicon" style="max-height:32px; border-radius:4px;">
                                @endif
                            </div>
                            <span class="btn-library" onclick="openMediaPicker('favicon_url', false)"><i class="mdi mdi-folder-image"></i> Select from Library</span>
                            <input type="hidden" name="favicon_url" value="{{ $settings['favicon_url'] ?? '' }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="bcard">
                <div class="bcard-head"><span class="bcard-title">Shipping</span></div>
                <div class="bcard-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label">Shipping Fee (Rs.)</label>
                            <input type="number" name="shipping_fee" class="form-control" value="{{ $settings['shipping_fee'] ?? '250' }}">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Free Shipping Threshold (Rs.)</label>
                            <input type="number" name="free_shipping_threshold" class="form-control" value="{{ $settings['free_shipping_threshold'] ?? '5000' }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="bcard">
                <div class="bcard-head"><span class="bcard-title">Homepage — Traditional Highlight</span></div>
                <div class="bcard-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Section Image</label>
                            <input type="file" name="home_highlight_image_file" class="form-control" accept="image/*" onchange="previewImg(this, 'home-highlight-preview')">
                            <div id="home-highlight-preview" style="margin-top:8px;">
                                @if(!empty($settings['home_highlight_image']))
                                    <img src="{{ $settings['home_highlight_image'] }}" alt="Highlight" style="max-height:120px; border-radius:6px;">
                                @endif
                            </div>
                            <span class="btn-library" onclick="openMediaPicker('home_highlight_image', false)"><i class="mdi mdi-folder-image"></i> Select from Library</span>
                            <input type="hidden" name="home_highlight_image" value="{{ $settings['home_highlight_image'] ?? '' }}">
                            <div class="form-hint">Cinematic heritage visual shown beside the heading.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Heading</label>
                            <input type="text" name="home_highlight_title" class="form-control" value="{{ $settings['home_highlight_title'] ?? '' }}" placeholder="Traditional wear with modern elegance.">
                            <div class="form-hint">Leave blank to use the default styled heading.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="home_highlight_description" class="form-control" rows="3" placeholder="Explore premium waistcoats, clothes, authentic Chitrali pakol caps...">{{ $settings['home_highlight_description'] ?? '' }}</textarea>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Button Label</label>
                            <input type="text" name="home_highlight_button" class="form-control" value="{{ $settings['home_highlight_button'] ?? '' }}" placeholder="View Collection">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Button Link</label>
                            <input type="text" name="home_highlight_link" class="form-control" value="{{ $settings['home_highlight_link'] ?? '' }}" placeholder="/products">
                        </div>
                    </div>
                </div>
            </div>

            <div class="bcard">
                <div class="bcard-head"><span class="bcard-title">Footer</span></div>
                <div class="bcard-body">
                    <div class="form-group">
                        <label class="form-label">Footer About Text</label>
                        <textarea name="footer_about" class="form-control" rows="3" placeholder="Bacha Stylo is a premium Pakistani fashion and lifestyle brand...">{{ $settings['footer_about'] ?? '' }}</textarea>
                        <div class="form-hint">Short brand description shown in the website footer.</div>
                    </div>
                </div>
            </div>

        </div>
        <div class="col-lg-4">
            <div class="side-stack">

            <div class="bcard">
                <div class="bcard-head"><span class="bcard-title">Email Notifications</span></div>
                <div class="bcard-body">
                    <div class="form-group">
                        <label class="form-label">Order Notification Email</label>
                        <input type="email" name="order_notification_email" class="form-control" value="{{ $settings['order_notification_email'] ?? '' }}">
                        <div class="form-hint">New order alerts will be sent here</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email From Name</label>
                        <input type="text" name="email_from_name" class="form-control" value="{{ $settings['email_from_name'] ?? '' }}">
                    </div>
                </div>
            </div>

            <div class="bcard">
                <div class="bcard-head"><span class="bcard-title">Social Links</span></div>
                <div class="bcard-body">
                    <div class="form-group">
                        <label class="form-label">Facebook URL</label>
                        <input type="text" name="facebook_url" class="form-control" value="{{ $settings['facebook_url'] ?? '' }}" placeholder="https://facebook.com/...">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Instagram URL</label>
                        <input type="text" name="instagram_url" class="form-control" value="{{ $settings['instagram_url'] ?? '' }}" placeholder="https://instagram.com/...">
                    </div>
                    <div class="form-group">
                        <label class="form-label">TikTok URL</label>
                        <input type="text" name="tiktok_url" class="form-control" value="{{ $settings['tiktok_url'] ?? '' }}" placeholder="https://tiktok.com/@...">
                    </div>
                    <div class="form-group">
                        <label class="form-label">WhatsApp Number</label>
                        <input type="text" name="whatsapp_number" class="form-control" value="{{ $settings['whatsapp_number'] ?? '' }}" placeholder="923001234567">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 btn-lg">
                <i class="mdi mdi-content-save-outline"></i> Save Settings
            </button>

            </div>

        </div>
    </div>
</form>

@include('admin.partials.upload-progress')

<script>
function previewImg(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview" style="max-height:60px; border-radius:4px;">';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
initUploadProgress('settingsForm', '{{ route("admin.settings.index") }}');
</script>
@include('admin.partials.media-picker')
@endsection
