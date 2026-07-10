@extends('layouts.admin')
@section('title', 'Settings')
@section('content')

<style>
    /* Floating Save button — pinned to the viewport so it's reachable from
       anywhere in the long settings form. */
    .settings-fab {
        position: fixed;
        right: 24px;
        bottom: 24px;
        z-index: 1050;
        min-width: 190px;
        border-radius: 999px;
        box-shadow: 0 10px 30px -6px rgba(0, 0, 0, 0.35);
        font-weight: 600;
        letter-spacing: .02em;
    }
    /* Keep the last card clear of the floating button so nothing hides behind it. */
    #settingsForm { padding-bottom: 90px; }
    @media (max-width: 575.98px) {
        .settings-fab { right: 16px; bottom: 16px; left: 16px; min-width: 0; }
    }
</style>

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
                <div class="bcard-head"><span class="bcard-title">Homepage — Intro Banner / Video</span></div>
                <div class="bcard-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" role="switch" id="intro_enabled" name="intro_enabled" value="1" {{ !empty($settings['intro_enabled']) && $settings['intro_enabled'] === '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="intro_enabled">Show intro banner / video on the homepage</label>
                            </div>
                            <div class="form-hint">When enabled, the hero background is replaced by your banner image or video. Visitors can Play the promo or Skip to reveal the default hero.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label d-block">Step 1 — What should the background be?</label>
                            @php $introBg = $settings['intro_bg_type'] ?? 'image'; @endphp
                            <div class="btn-group" role="group" aria-label="Background type">
                                <input type="radio" class="btn-check" name="intro_bg_type" id="intro_bg_image" value="image" {{ $introBg !== 'video' ? 'checked' : '' }} onchange="toggleIntroBg()">
                                <label class="btn btn-outline-primary" for="intro_bg_image"><i class="mdi mdi-image"></i> Image</label>
                                <input type="radio" class="btn-check" name="intro_bg_type" id="intro_bg_video" value="video" {{ $introBg === 'video' ? 'checked' : '' }} onchange="toggleIntroBg()">
                                <label class="btn btn-outline-primary" for="intro_bg_video"><i class="mdi mdi-video"></i> Video</label>
                            </div>
                        </div>

                        {{-- IMAGE background --}}
                        <div class="col-12" id="intro-image-group">
                            <label class="form-label">Step 2 — Banner Image</label>
                            <input type="file" name="intro_image_file" class="form-control" accept="image/*" onchange="previewImg(this, 'intro-image-preview')">
                            <div id="intro-image-preview" style="margin-top:8px;">
                                @if(!empty($settings['intro_image']))
                                    <img src="{{ $settings['intro_image'] }}" alt="Intro banner" style="max-height:120px; border-radius:6px;">
                                @endif
                            </div>
                            <span class="btn-library" onclick="openMediaPicker('intro_image', false)"><i class="mdi mdi-folder-image"></i> Select from Library</span>
                            <input type="text" name="intro_image" class="form-control mt-2" value="{{ $settings['intro_image'] ?? '' }}" placeholder="…or paste an image URL">
                            @if(!empty($settings['intro_image']))
                                <div class="form-check mt-2">
                                    <input type="checkbox" class="form-check-input" id="remove_intro_image" name="remove_intro_image" value="1">
                                    <label class="form-check-label text-danger" for="remove_intro_image">Remove current image</label>
                                </div>
                            @endif
                            <div class="form-hint">Upload a file, pick from the library, or paste an image URL.</div>
                        </div>

                        {{-- VIDEO background --}}
                        <div class="col-12" id="intro-video-group">
                            <label class="form-label">Step 2 — Background Video</label>
                            <input type="file" name="intro_video_file" class="form-control" accept="video/mp4,video/webm,video/ogg,video/quicktime">
                            <input type="text" name="intro_video_url" class="form-control mt-2" value="{{ $settings['intro_video_url'] ?? '' }}" placeholder="…or paste a YouTube / Vimeo / .mp4 link">
                            @if(!empty($settings['intro_video_url']))
                                <div class="form-hint">Current: <a href="{{ $settings['intro_video_url'] }}" target="_blank">{{ $settings['intro_video_url'] }}</a></div>
                                <div class="form-check mt-2">
                                    <input type="checkbox" class="form-check-input" id="remove_intro_video" name="remove_intro_video" value="1">
                                    <label class="form-check-label text-danger" for="remove_intro_video">Remove current video</label>
                                </div>
                            @endif
                            <div class="form-hint">Upload a short MP4/WebM (max 50 MB), or paste a YouTube / Vimeo / video link. Plays muted &amp; looped.</div>
                        </div>

                        {{-- Play button — video background only --}}
                        <div class="col-12" id="intro-play-group">
                            <div class="row g-3">
                                <div class="col-12"><hr class="my-1"></div>
                                <div class="col-12">
                                    <label class="form-label">Step 3 — Play Button <span class="text-muted fw-normal">(optional)</span></label>
                                    <input type="text" name="intro_social_url" class="form-control" value="{{ $settings['intro_social_url'] ?? '' }}" placeholder="Promo link — Facebook / Instagram / TikTok / YouTube">
                                    <div class="form-hint">Adds a Play button over the video that opens this promo link in a new tab. Leave blank for no Play button.</div>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Play Button Text</label>
                                    <input type="text" name="intro_button_text" class="form-control" value="{{ $settings['intro_button_text'] ?? '' }}" placeholder="Watch Video">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bcard">
                <div class="bcard-head"><span class="bcard-title">Homepage — Promotional Popup</span></div>
                <div class="bcard-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" role="switch" id="promo_enabled" name="promo_enabled" value="1" {{ !empty($settings['promo_enabled']) && $settings['promo_enabled'] === '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="promo_enabled">Show promotional popup when visitors open the site</label>
                            </div>
                            <div class="form-hint">Appears automatically above the hero the first time a visitor opens the homepage each session. They can Skip to close it, or use the button to open your promo link.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label d-block">Step 1 — What should the popup show?</label>
                            @php $promoType = $settings['promo_media_type'] ?? 'image'; @endphp
                            <div class="btn-group" role="group" aria-label="Promo type">
                                <input type="radio" class="btn-check" name="promo_media_type" id="promo_type_image" value="image" {{ $promoType === 'image' ? 'checked' : '' }} onchange="togglePromoType()">
                                <label class="btn btn-outline-primary" for="promo_type_image"><i class="mdi mdi-image"></i> Banner / Image</label>
                                <input type="radio" class="btn-check" name="promo_media_type" id="promo_type_video" value="video" {{ $promoType === 'video' ? 'checked' : '' }} onchange="togglePromoType()">
                                <label class="btn btn-outline-primary" for="promo_type_video"><i class="mdi mdi-video"></i> Video</label>
                            </div>
                            <div class="form-hint">Choose an animated banner/image or an uploaded/embedded video. Add a Watch/Open link below to redirect visitors to an external video (TikTok, YouTube, etc.).</div>
                        </div>

                        {{-- IMAGE / banner --}}
                        <div class="col-12" id="promo-image-group">
                            <label class="form-label">Banner Image</label>
                            <input type="file" name="promo_image_file" class="form-control" accept="image/*" onchange="previewImg(this, 'promo-image-preview')">
                            <div id="promo-image-preview" style="margin-top:8px;">
                                @if(!empty($settings['promo_image']))
                                    <img src="{{ $settings['promo_image'] }}" alt="Promo banner" style="max-height:120px; border-radius:6px;">
                                @endif
                            </div>
                            <span class="btn-library" onclick="openMediaPicker('promo_image', false)"><i class="mdi mdi-folder-image"></i> Select from Library</span>
                            <input type="text" name="promo_image" class="form-control mt-2" value="{{ $settings['promo_image'] ?? '' }}" placeholder="…or paste an image / animated GIF URL">
                            @if(!empty($settings['promo_image']))
                                <div class="form-check mt-2">
                                    <input type="checkbox" class="form-check-input" id="remove_promo_image" name="remove_promo_image" value="1">
                                    <label class="form-check-label text-danger" for="remove_promo_image">Remove current image</label>
                                </div>
                            @endif
                            <div class="form-hint">Upload a file, pick from the library, or paste an image / animated GIF URL.</div>
                        </div>

                        {{-- VIDEO --}}
                        <div class="col-12" id="promo-video-group">
                            <label class="form-label">Video</label>
                            <input type="file" name="promo_video_file" class="form-control" accept="video/mp4,video/webm,video/ogg,video/quicktime">
                            <input type="text" name="promo_video_url" class="form-control mt-2" value="{{ $settings['promo_video_url'] ?? '' }}" placeholder="…or paste a YouTube / Vimeo / .mp4 link">
                            @if(!empty($settings['promo_video_url']))
                                <div class="form-hint">Current: <a href="{{ $settings['promo_video_url'] }}" target="_blank">{{ $settings['promo_video_url'] }}</a></div>
                                <div class="form-check mt-2">
                                    <input type="checkbox" class="form-check-input" id="remove_promo_video" name="remove_promo_video" value="1">
                                    <label class="form-check-label text-danger" for="remove_promo_video">Remove current video</label>
                                </div>
                            @endif
                            <div class="form-hint">Upload a short MP4/WebM (max 50 MB), or paste a YouTube / Vimeo / video link. Plays muted inside the popup.</div>
                        </div>

                        {{-- Common: link + copy --}}
                        <div class="col-12"><hr class="my-1"></div>
                        <div class="col-sm-6">
                            <label class="form-label">Popup Heading <span class="text-muted fw-normal">(optional)</span></label>
                            <input type="text" name="promo_title" class="form-control" value="{{ $settings['promo_title'] ?? '' }}" placeholder="Big Winter Sale">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Button Text</label>
                            <input type="text" name="promo_button_text" class="form-control" value="{{ $settings['promo_button_text'] ?? '' }}" placeholder="Watch Now">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Subtext <span class="text-muted fw-normal">(optional)</span></label>
                            <input type="text" name="promo_subtitle" class="form-control" value="{{ $settings['promo_subtitle'] ?? '' }}" placeholder="Up to 50% off — this week only">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Watch / Open Link <span class="text-muted fw-normal">(external redirect)</span></label>
                            <input type="text" name="promo_link" class="form-control" value="{{ $settings['promo_link'] ?? '' }}" placeholder="https://www.tiktok.com/@yourbrand or a YouTube link">
                            <div class="form-hint">The Watch/Open button redirects visitors here (TikTok, YouTube, Instagram, or any URL). Required for the button; leave blank to hide it.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bcard">
                <div class="bcard-head"><span class="bcard-title">Payment Methods (Checkout)</span></div>
                <div class="bcard-body">
                    <p class="form-hint mb-3">These details are shown to customers on the checkout page for each online payment method. Cash on Delivery needs no details.</p>
                    <div class="row g-3">
                        <div class="col-12 d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 text-uppercase" style="letter-spacing:.05em;font-size:12px;color:var(--red);"><i class="mdi mdi-truck-delivery-outline"></i> Cash on Delivery</h6>
                            <div class="form-check form-switch mb-0">
                                <input type="checkbox" class="form-check-input" role="switch" id="cod_enabled" name="cod_enabled" value="1" {{ ($settings['cod_enabled'] ?? '1') !== '0' ? 'checked' : '' }}>
                                <label class="form-check-label" for="cod_enabled" style="font-size:12px;">Enabled</label>
                            </div>
                        </div>
                        <div class="col-12"><span class="form-hint">No details needed — customers pay at delivery.</span></div>

                        <div class="col-12"><hr class="my-1"></div>
                        <div class="col-12 d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 text-uppercase" style="letter-spacing:.05em;font-size:12px;color:var(--red);"><i class="mdi mdi-bank"></i> Bank Transfer</h6>
                            <div class="form-check form-switch mb-0">
                                <input type="checkbox" class="form-check-input" role="switch" id="bank_transfer_enabled" name="bank_transfer_enabled" value="1" {{ ($settings['bank_transfer_enabled'] ?? '1') !== '0' ? 'checked' : '' }}>
                                <label class="form-check-label" for="bank_transfer_enabled" style="font-size:12px;">Enabled</label>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Bank Name</label>
                            <input type="text" name="bank_name" class="form-control" value="{{ $settings['bank_name'] ?? '' }}" placeholder="e.g. Meezan Bank">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Account Title</label>
                            <input type="text" name="bank_account_title" class="form-control" value="{{ $settings['bank_account_title'] ?? '' }}" placeholder="Account holder name">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Account Number</label>
                            <input type="text" name="bank_account_number" class="form-control" value="{{ $settings['bank_account_number'] ?? '' }}" placeholder="Account number">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">IBAN</label>
                            <input type="text" name="bank_iban" class="form-control" value="{{ $settings['bank_iban'] ?? '' }}" placeholder="PK00XXXX0000000000000000">
                        </div>

                        <div class="col-12"><hr class="my-1"></div>
                        <div class="col-12 d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 text-uppercase" style="letter-spacing:.05em;font-size:12px;color:var(--red);"><i class="mdi mdi-cellphone"></i> EasyPaisa</h6>
                            <div class="form-check form-switch mb-0">
                                <input type="checkbox" class="form-check-input" role="switch" id="easypaisa_enabled" name="easypaisa_enabled" value="1" {{ ($settings['easypaisa_enabled'] ?? '1') !== '0' ? 'checked' : '' }}>
                                <label class="form-check-label" for="easypaisa_enabled" style="font-size:12px;">Enabled</label>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Account Name</label>
                            <input type="text" name="easypaisa_account_name" class="form-control" value="{{ $settings['easypaisa_account_name'] ?? '' }}" placeholder="Account holder name">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">EasyPaisa Number</label>
                            <input type="text" name="easypaisa_number" class="form-control" value="{{ $settings['easypaisa_number'] ?? '' }}" placeholder="03XXXXXXXXX">
                        </div>

                        <div class="col-12"><hr class="my-1"></div>
                        <div class="col-12 d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 text-uppercase" style="letter-spacing:.05em;font-size:12px;color:var(--red);"><i class="mdi mdi-cellphone"></i> JazzCash</h6>
                            <div class="form-check form-switch mb-0">
                                <input type="checkbox" class="form-check-input" role="switch" id="jazzcash_enabled" name="jazzcash_enabled" value="1" {{ ($settings['jazzcash_enabled'] ?? '1') !== '0' ? 'checked' : '' }}>
                                <label class="form-check-label" for="jazzcash_enabled" style="font-size:12px;">Enabled</label>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Account Name</label>
                            <input type="text" name="jazzcash_account_name" class="form-control" value="{{ $settings['jazzcash_account_name'] ?? '' }}" placeholder="Account holder name">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">JazzCash Number</label>
                            <input type="text" name="jazzcash_number" class="form-control" value="{{ $settings['jazzcash_number'] ?? '' }}" placeholder="03XXXXXXXXX">
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

            {{-- Floating save button — stays visible while scrolling the long settings form --}}
            <button type="submit" class="btn btn-primary btn-lg settings-fab">
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

// Show only the fields for the selected background type. The Play button is
// video-only; an image background is simply the hero, with nothing over it.
function toggleIntroBg() {
    var isVideo = document.getElementById('intro_bg_video').checked;
    document.getElementById('intro-image-group').style.display = isVideo ? 'none' : '';
    document.getElementById('intro-video-group').style.display = isVideo ? '' : 'none';
    document.getElementById('intro-play-group').style.display = isVideo ? '' : 'none';
}
toggleIntroBg();

// Promotional popup: show only the media fields for the selected type. The
// Watch/Open link and copy apply to every type, so they stay visible.
function togglePromoType() {
    var type = document.querySelector('input[name="promo_media_type"]:checked');
    type = type ? type.value : 'image';
    document.getElementById('promo-image-group').style.display = type === 'image' ? '' : 'none';
    document.getElementById('promo-video-group').style.display = type === 'video' ? '' : 'none';
}
togglePromoType();
</script>
@include('admin.partials.media-picker')
@endsection
