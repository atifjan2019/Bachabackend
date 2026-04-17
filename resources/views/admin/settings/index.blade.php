@extends('layouts.admin')
@section('title', 'Settings')
@section('content')

<div class="ph">
    <div>
        <h4>Settings</h4>
        <div class="ph-sub">Manage your store configuration</div>
    </div>
</div>

<form action="{{ route('admin.settings.update') }}" method="POST">
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
                            <label class="form-label">Logo URL</label>
                            <input type="text" name="logo_url" class="form-control" value="{{ $settings['logo_url'] ?? '' }}" placeholder="https://...">
                            @if(!empty($settings['logo_url']))
                                <div class="preview-box"><img src="{{ $settings['logo_url'] }}" alt="Logo Preview"></div>
                            @endif
                        </div>
                        <div class="col-12">
                            <label class="form-label">Favicon URL</label>
                            <input type="text" name="favicon_url" class="form-control" value="{{ $settings['favicon_url'] ?? '' }}" placeholder="https://...">
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
@endsection
