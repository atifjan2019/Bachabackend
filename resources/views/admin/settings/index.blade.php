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
