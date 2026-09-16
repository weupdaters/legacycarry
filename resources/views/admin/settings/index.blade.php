@extends('admin.layouts.app')

@section('title', 'Pro Settings')

@section('content')

@php
    $activeTab = request('tab');   // ⭐ UPDATED
@endphp


<div class="pro-settings-wrapper d-flex">

    <!-- ==========================
         LEFT SIDEBAR NAVIGATION
         ========================== -->
    <aside class="pro-sidebar shadow-sm">
        <h5 class="ps-3 pt-3 mb-3 fw-bold text-primary">⚙️ Settings</h5>

        <ul class="pro-nav">

            @foreach($settings as $group => $groupSettings)
            @php
                $icons = [
                    'general' => 'fas fa-sliders-h',
                    'payment' => 'fas fa-credit-card',
                    'business' => 'fas fa-briefcase',
                    'invoice' => 'fas fa-file-invoice',
                    'notifications' => 'fas fa-bell',
                    'seo' => 'fas fa-globe',
                    'social' => 'fas fa-share-alt',
                    'security' => 'fas fa-shield-alt',
                    'guard' => 'fas fa-user-shield',
                ];
                $iconClass = $icons[strtolower($group)] ?? 'fas fa-circle';
            @endphp
            <li class="pro-nav-item 
                {{ $activeTab == Str::slug($group) ? 'active' : ($loop->first && !$activeTab ? 'active' : '') }}"
                data-target="#{{ Str::slug($group) }}">
                <i class="{{ $iconClass }} me-2"></i>{{ ucfirst($group) }}
            </li>
            @endforeach
        
            <li class="pro-nav-item {{ $activeTab == 'withdrawal' ? 'active' : '' }}" data-target="#withdrawal">
                <i class="fas fa-hand-holding-usd me-2"></i>Withdrawal
            </li>

            <li class="pro-nav-item {{ $activeTab == 'admin' ? 'active' : '' }}" data-target="#admin">
                <i class="fas fa-user-shield me-2"></i>Admin Profile
            </li>
        
            <li class="pro-nav-item {{ $activeTab == 'system' ? 'active' : '' }}" data-target="#system">
                <i class="fas fa-server me-2"></i>System
            </li>
        
        </ul>
        
    </aside>

    <!-- ==========================
         RIGHT CONTENT
         ========================== -->
    <main class="pro-content-area flex-grow-1">

        <!-- Sticky save bar -->
        <div class="pro-save-bar shadow-sm">
            <button id="save-all-btn" class="btn btn-primary px-4 py-2">
                <i class="fas fa-save me-2"></i>Save All Changes
            </button>
        </div>

        <div class="content-scroll p-4">

            <!-- Alerts -->
            @if(session('success'))
            <div class="alert alert-success shadow-sm rounded">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-danger shadow-sm rounded">
                <strong>Fix the following:</strong>
                <ul class="mt-2 mb-0">
                    @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
            @endif


            <!-- =========================================
                 SETTINGS GROUPS (AUTO-GENERATED)
                 ========================================= -->
            @foreach($settings as $group => $groupSettings)
            @if($group === 'payment' || $group === 'withdrawal')
                @continue
            @endif
            <div class="pro-section {{ $activeTab == Str::slug($group) ? 'active' : ($loop->first && !$activeTab ? 'active' : '') }}"
                id="{{ Str::slug($group) }}">
           

                <h4 class="fw-bold mb-3 text-primary">{{ ucfirst($group) }}</h4>

                <form class="settings-form" data-group="{{ $group }}">

                    @foreach($groupSettings as $setting)
                        @php
                            // Skip theme color settings
                            if(in_array($setting->key, ['theme_color', 'primary_color', 'secondary_color', 'accent_color'])) {
                                continue;
                            }
                        @endphp
                        
                        <div class="pro-card shadow-sm p-4 mb-4">

                            <label class="fw-semibold mb-1">{{ $setting->label }}</label>

                            @if($setting->description)
                                <div class="text-muted small mb-2">{{ $setting->description }}</div>
                            @endif

                            @switch($setting->type)

                                @case('textarea')
                                    <textarea class="form-control pro-input"
                                              rows="3"
                                              name="{{ $setting->key }}">{{ $setting->value }}</textarea>
                                @break

                                @case('json')
                                    <textarea class="form-control pro-json"
                                              rows="8"
                                              name="{{ $setting->key }}">{{ json_encode(json_decode($setting->value ?? '{}', true), JSON_PRETTY_PRINT) }}</textarea>
                                    <button type="button" class="btn btn-outline-secondary btn-sm mt-2 json-format">
                                        Format JSON
                                    </button>
                                @break

                                @case('boolean')
                                    <label class="pro-switch">
                                        <input type="checkbox"
                                               name="{{ $setting->key }}"
                                               value="1"
                                               {{ $setting->value ? 'checked' : '' }}>
                                        <span class="slider"></span>
                                    </label>
                                    <span class="ms-2 fw-bold toggle-text">
                                        {{ $setting->value ? 'Enabled' : 'Disabled' }}
                                    </span>
                                @break

                                @case('select')
                                    @if($setting->key == 'payment_gateway')
                                        <select name="{{ $setting->key }}" class="form-select pro-input" id="payment_gateway">
                                            @foreach($setting->options as $option)
                                            <option value="{{ $option }}" {{ $setting->value == $option ? 'selected' : '' }}>
                                                {{ ucfirst($option) }}
                                            </option>
                                            @endforeach
                                        </select>
                                    @elseif($setting->key == 'currency')
                                        <select name="{{ $setting->key }}" class="form-select pro-input">
                                            @foreach($setting->options as $option)
                                            <option value="{{ $option }}" {{ $setting->value == $option ? 'selected' : '' }}>
                                                {{ $option == 'INR' ? '₹ Indian Rupee (INR)' : $option }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted mt-1 d-block">
                                            <i class="fas fa-info-circle me-1"></i>All prices will be displayed in Indian Rupees
                                        </small>
                                    @else
                                        <select name="{{ $setting->key }}" class="form-select pro-input">
                                            @foreach($setting->options as $option)
                                            <option value="{{ $option }}" {{ $setting->value == $option ? 'selected' : '' }}>
                                                {{ ucfirst($option) }}
                                            </option>
                                            @endforeach
                                        </select>
                                    @endif
                                @break

                                @case('file')
                                    <input type="file" class="form-control pro-input" name="{{ $setting->key }}">
                                    @if($setting->value)
                                    <a href="{{ asset('storage/' . $setting->value) }}"
                                       target="_blank"
                                       class="btn btn-outline-primary btn-sm mt-2">View File</a>
                                    @endif
                                @break

                                @case('amount')
                                    <div class="input-group">
                                        <span class="input-group-text">₹</span>
                                        <input type="text"
                                               class="form-control pro-input"
                                               value="{{ number_format($setting->value, 2) }}"
                                               name="{{ $setting->key }}">
                                        <span class="input-group-text">INR</span>
                                    </div>
                                @break

                                @default
                                    @if(in_array($setting->key, ['razorpay_key', 'razorpay_secret', 'cashfree_app_id', 'cashfree_secret_key']))
                                        <div class="gateway-field {{ str_contains($setting->key, 'razorpay') ? 'razorpay-fields' : 'cashfree-fields' }}"
                                             style="display: {{ ($setting->key == 'razorpay_key' || $setting->key == 'razorpay_secret') && ($groupSettings->where('key', 'payment_gateway')->first()->value ?? '') != 'razorpay' ? 'none' : '' }};
                                                    {{ ($setting->key == 'cashfree_app_id' || $setting->key == 'cashfree_secret_key') && ($groupSettings->where('key', 'payment_gateway')->first()->value ?? '') != 'cashfree' ? 'none' : '' }}">
                                            <input type="{{ $setting->type }}"
                                                   class="form-control pro-input"
                                                   value="{{ $setting->value }}"
                                                   name="{{ $setting->key }}"
                                                   placeholder="{{ $setting->key == 'razorpay_key' ? 'Razorpay API Key' : ($setting->key == 'razorpay_secret' ? 'Razorpay Secret Key' : ($setting->key == 'cashfree_app_id' ? 'Cashfree App ID' : 'Cashfree Secret Key')) }}">
                                            <small class="text-muted mt-1 d-block">
                                                <i class="fas fa-key me-1"></i>
                                                @if(str_contains($setting->key, 'razorpay'))
                                                    Get this from <a href="https://dashboard.razorpay.com" target="_blank">Razorpay Dashboard</a>
                                                @else
                                                    Get this from <a href="https://merchant.cashfree.com" target="_blank">Cashfree Dashboard</a>
                                                @endif
                                            </small>
                                        </div>
                                    @else
                                        <input type="{{ $setting->type }}"
                                               class="form-control pro-input"
                                               value="{{ $setting->value }}"
                                               name="{{ $setting->key }}">
                                    @endif
                            @endswitch

                        </div>
                    @endforeach

                </form>
            </div>
            @endforeach


            <!-- =========================================
                 PAYMENT SETTINGS CUSTOM SECTION
                 ========================================= -->
            <div id="payment" class="pro-section {{ $activeTab == 'payment' ? 'active' : '' }}">
                <h4 class="fw-bold mb-3 text-primary">Payment Settings</h4>

                <div class="alert alert-info shadow-sm mb-4">
                    <i class="fas fa-rupee-sign me-2"></i>
                    <strong>Indian Payments Setup:</strong> Configure payment gateways for accepting payments in Indian Rupees (₹)
                </div>

                <form class="settings-form" data-group="payment">

                    <!-- Currency Setting -->
                    <div class="pro-card shadow-sm p-4 mb-4">
                        <label class="fw-semibold mb-1">Currency</label>
                        <div class="text-muted small mb-2">Default currency for all transactions</div>
                        <select name="currency" class="form-select pro-input" disabled>
                            <option value="INR" selected>₹ Indian Rupee (INR)</option>
                        </select>
                        <input type="hidden" name="currency" value="INR">
                        <div class="alert alert-success mt-2 p-2">
                            <i class="fas fa-check-circle me-2"></i>
                            Indian Rupee (₹) is set as default currency. All amounts will be displayed in INR.
                        </div>
                    </div>

                    <!-- Payment Gateway -->
                    <div class="pro-card shadow-sm p-4 mb-4">
                        <label class="fw-semibold mb-1">Payment Gateway</label>
                        <div class="text-muted small mb-2">Select your preferred payment gateway for Indian payments</div>
                        <select name="payment_gateway" class="form-select pro-input" id="main_payment_gateway">
                            <option value="cashfree" {{ ($settings['payment']->where('key', 'payment_gateway')->first()->value ?? '') == 'cashfree' ? 'selected' : '' }}>Cashfree (Recommended)</option>
                            <option value="razorpay" {{ ($settings['payment']->where('key', 'payment_gateway')->first()->value ?? '') == 'razorpay' ? 'selected' : '' }}>Razorpay</option>
                            <option value="stripe" {{ ($settings['payment']->where('key', 'payment_gateway')->first()->value ?? '') == 'stripe' ? 'selected' : '' }}>Stripe</option>
                            <option value="paypal" {{ ($settings['payment']->where('key', 'payment_gateway')->first()->value ?? '') == 'paypal' ? 'selected' : '' }}>PayPal</option>
                            <option value="cod" {{ ($settings['payment']->where('key', 'payment_gateway')->first()->value ?? '') == 'cod' ? 'selected' : '' }}>Cash on Delivery</option>
                        </select>
                    </div>

                    <!-- Cashfree Fields -->
                    @php
                        $cfCurrentEnv = $settings['payment']->where('key', 'cashfree_environment')->first()->value ?? 'sandbox';
                        $isCfLive = in_array(strtolower($cfCurrentEnv), ['production', 'live']);
                    @endphp
                    <div id="cashfree-section" class="payment-gateway-section" 
                         style="display: {{ ($settings['payment']->where('key', 'payment_gateway')->first()->value ?? '') == 'cashfree' ? 'block' : 'none' }};">
                        <div class="pro-card shadow-sm p-4 mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0"><i class="fas fa-wallet me-2 text-success"></i>Cashfree Payment Gateway Settings</h5>
                                <span class="badge {{ $isCfLive ? 'bg-success' : 'bg-warning text-dark' }} fs-6">
                                    <i class="fas {{ $isCfLive ? 'fa-check-circle' : 'fa-flask' }} me-1"></i>
                                    {{ $isCfLive ? 'Live (Production) Active' : 'Testing (Sandbox) Active' }}
                                </span>
                            </div>

                            <!-- Environment Mode -->
                            <div class="mb-4 p-3 bg-light rounded border">
                                <label class="fw-semibold mb-1 text-dark"><i class="fas fa-toggle-on me-1 text-primary"></i>Active Environment Mode</label>
                                <div class="text-muted small mb-2">Switch between Testing Mode (Sandbox) and Live Mode (Production)</div>
                                <select name="cashfree_environment" id="cashfree_environment_select" class="form-select pro-input fw-bold">
                                    <option value="sandbox" {{ !$isCfLive ? 'selected' : '' }}>🧪 Sandbox (Testing Mode)</option>
                                    <option value="production" {{ $isCfLive ? 'selected' : '' }}>🚀 Production (Live Mode)</option>
                                </select>
                                <div class="small mt-2 {{ $isCfLive ? 'text-success' : 'text-warning text-dark' }}">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ $isCfLive ? 'Live mode is selected: Real payments will be processed via Cashfree Production.' : 'Testing mode is selected: Fake test transactions will be processed via Cashfree Sandbox.' }}
                                </div>
                            </div>

                            <div class="row">
                                <!-- Sandbox / Testing Credentials -->
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100 border-warning shadow-none bg-light">
                                        <div class="card-header bg-warning bg-opacity-25 fw-bold text-dark">
                                            <i class="fas fa-vial me-1"></i>Testing (Sandbox) Keys
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="fw-semibold mb-1 small">Sandbox App ID (Client ID)</label>
                                                <input type="text" 
                                                       class="form-control pro-input" 
                                                       name="cashfree_sandbox_app_id"
                                                       value="{{ $settings['payment']->where('key', 'cashfree_sandbox_app_id')->first()->value ?? ($settings['payment']->where('key', 'cashfree_app_id')->first()->value ?? '') }}"
                                                       placeholder="e.g. TESTXXXXXXXXXX or CF_APP_XXX">
                                            </div>
                                            <div class="mb-2">
                                                <label class="fw-semibold mb-1 small">Sandbox Secret Key</label>
                                                <input type="password" 
                                                       class="form-control pro-input" 
                                                       name="cashfree_sandbox_secret_key"
                                                       value="{{ $settings['payment']->where('key', 'cashfree_sandbox_secret_key')->first()->value ?? ($settings['payment']->where('key', 'cashfree_secret_key')->first()->value ?? '') }}"
                                                       placeholder="Sandbox Secret Key">
                                            </div>
                                            <small class="text-muted d-block mt-2">
                                                <i class="fas fa-external-link-alt me-1"></i>Get from <a href="https://sandbox.cashfree.com" target="_blank">Cashfree Sandbox Dashboard</a>
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Production / Live Credentials -->
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100 border-success shadow-none bg-light">
                                        <div class="card-header bg-success bg-opacity-25 fw-bold text-dark">
                                            <i class="fas fa-rocket me-1"></i>Production (Live) Keys
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="fw-semibold mb-1 small">Production App ID (Client ID)</label>
                                                <input type="text" 
                                                       class="form-control pro-input" 
                                                       name="cashfree_live_app_id"
                                                       value="{{ $settings['payment']->where('key', 'cashfree_live_app_id')->first()->value ?? ($settings['payment']->where('key', 'cashfree_app_id')->first()->value ?? '') }}"
                                                       placeholder="e.g. CF_APP_XXXXXXXXXXXX">
                                            </div>
                                            <div class="mb-2">
                                                <label class="fw-semibold mb-1 small">Production Secret Key</label>
                                                <input type="password" 
                                                       class="form-control pro-input" 
                                                       name="cashfree_live_secret_key"
                                                       value="{{ $settings['payment']->where('key', 'cashfree_live_secret_key')->first()->value ?? ($settings['payment']->where('key', 'cashfree_secret_key')->first()->value ?? '') }}"
                                                       placeholder="Production Secret Key">
                                            </div>
                                            <small class="text-muted d-block mt-2">
                                                <i class="fas fa-external-link-alt me-1"></i>Get from <a href="https://merchant.cashfree.com" target="_blank">Cashfree Merchant Dashboard</a>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Fallback Direct Keys (Hidden / Auto-synced) -->
                            <input type="hidden" name="cashfree_app_id" value="{{ $settings['payment']->where('key', 'cashfree_app_id')->first()->value ?? '' }}">
                            <input type="hidden" name="cashfree_secret_key" value="{{ $settings['payment']->where('key', 'cashfree_secret_key')->first()->value ?? '' }}">
                        </div>
                    </div>

                    <!-- Razorpay Fields -->
                    <div id="razorpay-section" class="payment-gateway-section" 
                         style="display: {{ ($settings['payment']->where('key', 'payment_gateway')->first()->value ?? '') == 'razorpay' ? 'block' : 'none' }};">
                        <div class="pro-card shadow-sm p-4 mb-4">
                            <h5 class="mb-3"><i class="fas fa-credit-card me-2 text-primary"></i>Razorpay Settings</h5>
                            
                            <div class="mb-3">
                                <label class="fw-semibold mb-1">Razorpay Key ID</label>
                                <input type="text" 
                                       class="form-control pro-input" 
                                       name="razorpay_key"
                                       value="{{ $settings['payment']->where('key', 'razorpay_key')->first()->value ?? '' }}"
                                       placeholder="rzp_test_xxxxxxxxxxxxx">
                                <small class="text-muted mt-1 d-block">
                                    <i class="fas fa-key me-1"></i>Get from <a href="https://dashboard.razorpay.com/app/keys" target="_blank">Razorpay Dashboard → API Keys</a>
                                </small>
                            </div>

                            <div class="mb-3">
                                <label class="fw-semibold mb-1">Razorpay Key Secret</label>
                                <input type="password" 
                                       class="form-control pro-input" 
                                       name="razorpay_secret"
                                       value="{{ $settings['payment']->where('key', 'razorpay_secret')->first()->value ?? '' }}"
                                       placeholder="Your secret key">
                            </div>

                            <div class="mb-3">
                                <label class="fw-semibold mb-1">Webhook Secret</label>
                                <input type="password" 
                                       class="form-control pro-input" 
                                       name="razorpay_webhook_secret"
                                       value="{{ $settings['payment']->where('key', 'razorpay_webhook_secret')->first()->value ?? '' }}"
                                       placeholder="Webhook secret key">
                            </div>

                            <div class="alert alert-warning p-3">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Important:</strong> Set webhook URL in Razorpay dashboard to: 
                                <code>{{ url('/api/webhook/razorpay') }}</code>
                            </div>
                        </div>
                    </div>

                    <!-- Minimum Amount -->
                    <div class="pro-card shadow-sm p-4 mb-4">
                        <label class="fw-semibold mb-1">Minimum Transaction Amount</label>
                        <div class="text-muted small mb-2">Minimum amount required for online payment</div>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="text" 
                                   class="form-control pro-input" 
                                   name="minimum_payment_amount"
                                   value="{{ number_format($settings['payment']->where('key', 'minimum_payment_amount')->first()->value ?? 10, 2) }}">
                            <span class="input-group-text">INR</span>
                        </div>
                        <small class="text-muted mt-1 d-block">
                            <i class="fas fa-info-circle me-1"></i>Transactions below this amount will require Cash on Delivery
                        </small>
                    </div>

                    <!-- GST & UPI Settings -->
                    <div class="pro-card shadow-sm p-4 mb-4">
                        <h5 class="mb-3"><i class="fas fa-percentage me-2 text-info"></i>Tax & UPI Settings</h5>

                        <div class="mb-3">
                            <label class="fw-semibold mb-1">Enable GST Calculation</label>
                            <div class="d-flex align-items-center mt-1">
                                <label class="pro-switch">
                                    <input type="checkbox" name="enable_gst" value="1" {{ ($settings['payment']->where('key', 'enable_gst')->first()->value ?? '0') == '1' ? 'checked' : '' }}>
                                    <span class="slider"></span>
                                </label>
                                <span class="ms-2 fw-bold toggle-text">{{ ($settings['payment']->where('key', 'enable_gst')->first()->value ?? '0') == '1' ? 'Enabled' : 'Disabled' }}</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="fw-semibold mb-1">GST Percentage (%)</label>
                            <input type="number" step="0.01" class="form-control pro-input" name="gst_percentage" value="{{ $settings['payment']->where('key', 'gst_percentage')->first()->value ?? '18' }}">
                        </div>

                        <div class="mb-2">
                            <label class="fw-semibold mb-1">Enable UPI Payments</label>
                            <div class="d-flex align-items-center mt-1">
                                <label class="pro-switch">
                                    <input type="checkbox" name="enable_upi" value="1" {{ ($settings['payment']->where('key', 'enable_upi')->first()->value ?? '0') == '1' ? 'checked' : '' }}>
                                    <span class="slider"></span>
                                </label>
                                <span class="ms-2 fw-bold toggle-text">{{ ($settings['payment']->where('key', 'enable_upi')->first()->value ?? '0') == '1' ? 'Enabled' : 'Disabled' }}</span>
                            </div>
                        </div>
                    </div>

                </form>
            </div>

            <!-- =========================================
                 WITHDRAWAL SETTINGS CUSTOM SECTION
                 ========================================= -->
            <div id="withdrawal" class="pro-section {{ $activeTab == 'withdrawal' ? 'active' : '' }}">
                <h4 class="fw-bold mb-3 text-primary">
                    <i class="fas fa-hand-holding-usd me-2"></i>Withdrawal & Payout Settings
                </h4>

                <div class="alert alert-info shadow-sm mb-4">
                    <div class="d-flex align-items-start gap-2">
                        <i class="fas fa-info-circle fs-5 mt-1 text-primary"></i>
                        <div>
                            <strong>Payout Logic & Cashfree Credentials:</strong>
                            <ul class="mb-0 ps-3 mt-1 small">
                                <li><strong>Auto-Payout:</strong> Automatically initiates instant bank/UPI transfer via Cashfree Payout API if the withdrawal amount is <strong>&le; Max Auto Limit</strong> (e.g. ₹1,000).</li>
                                <li><strong>Manual Approval:</strong> If the requested amount exceeds the Auto Limit (e.g. &gt; ₹1,000) or Auto-Payout is disabled, requests remain in <strong>Pending</strong> state for Admin to approve via Cashfree API or mark as Paid with bank UTR.</li>
                                <li><strong>Cashfree Credentials:</strong> As per security requirements, credentials are maintained securely in your <code>.env</code> file (<code>CASHFREE_PAYOUT_CLIENT_ID</code>, <code>CASHFREE_PAYOUT_CLIENT_SECRET</code>, <code>CASHFREE_PAYOUT_ENV</code>).</li>
                            </ul>
                        </div>
                    </div>
                </div>

                @php
                    $payoutEnv = \App\Models\Setting::getValue('cashfree_payout_environment', env('CASHFREE_PAYOUT_ENV', 'production'));
                    $isPayoutLive = in_array(strtolower($payoutEnv), ['production', 'live']);
                    $serverIp = '49.43.109.122';
                @endphp

                <form class="settings-form" data-group="withdrawal">

                    <!-- Cashfree Payout API Credentials Card -->
                    <div class="pro-card shadow-sm p-4 mb-4 border-start border-4 border-primary">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 fw-bold text-dark">
                                <i class="fas fa-key text-primary me-2"></i>Cashfree Payout API Credentials
                            </h5>
                            <span class="badge {{ $isPayoutLive ? 'bg-success' : 'bg-warning text-dark' }} fs-6">
                                <i class="fas {{ $isPayoutLive ? 'fa-rocket' : 'fa-flask' }} me-1"></i>
                                {{ $isPayoutLive ? 'Live (Production) Payout' : 'Sandbox (Testing) Payout' }}
                            </span>
                        </div>

                        <!-- 2FA IP Whitelist notice -->
                        <div class="alert alert-warning border shadow-sm mb-3">
                            <div class="d-flex align-items-start gap-2">
                                <i class="fas fa-shield-alt fs-4 text-danger mt-1"></i>
                                <div>
                                    <strong class="text-dark">Cashfree Two-Factor Authentication (2FA) Requirement:</strong>
                                    <p class="mb-1 small">Cashfree enforces IP whitelisting for all Payout API calls. Ensure your server IP is added in your Cashfree Payouts dashboard:</p>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="badge bg-dark fs-6 py-2 px-3">Current IP: <strong>{{ $serverIp }}</strong></span>
                                        <a href="https://merchant.cashfree.com" target="_blank" class="btn btn-sm btn-outline-dark">
                                            <i class="fas fa-external-link-alt me-1"></i> Open Cashfree Dashboard &rarr; Payouts &rarr; Developers &rarr; 2FA
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Environment Mode Select -->
                        <div class="mb-3 p-3 bg-light rounded border">
                            <label class="fw-semibold mb-1 text-dark">Payout Environment Mode</label>
                            <select name="cashfree_payout_environment" id="cashfree_payout_env_select" class="form-select pro-input fw-bold">
                                <option value="production" {{ $isPayoutLive ? 'selected' : '' }}>🚀 Production (Live Mode)</option>
                                <option value="sandbox" {{ !$isPayoutLive ? 'selected' : '' }}>🧪 Sandbox (Testing Mode)</option>
                            </select>
                        </div>

                        <div class="row">
                            <!-- Production Live Credentials -->
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 border-success shadow-none bg-light">
                                    <div class="card-header bg-success bg-opacity-25 fw-bold text-dark">
                                        <i class="fas fa-check-circle text-success me-1"></i>Live (Production) Keys
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="fw-semibold mb-1 small">Live Client ID</label>
                                            <input type="text" 
                                                   class="form-control pro-input" 
                                                   name="cashfree_payout_live_client_id"
                                                   value="{{ \App\Models\Setting::getValue('cashfree_payout_live_client_id', env('CASHFREE_PAYOUT_CLIENT_ID', '')) }}"
                                                   placeholder="Enter Live Client ID">
                                        </div>
                                        <div class="mb-2">
                                            <label class="fw-semibold mb-1 small">Live Client Secret</label>
                                            <input type="password" 
                                                   class="form-control pro-input" 
                                                   name="cashfree_payout_live_client_secret"
                                                   value="{{ \App\Models\Setting::getValue('cashfree_payout_live_client_secret', env('CASHFREE_PAYOUT_CLIENT_SECRET', '')) }}"
                                                   placeholder="Enter Live Client Secret">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sandbox Testing Credentials -->
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 border-warning shadow-none bg-light">
                                    <div class="card-header bg-warning bg-opacity-25 fw-bold text-dark">
                                        <i class="fas fa-vial text-warning me-1"></i>Sandbox (Testing) Keys
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="fw-semibold mb-1 small">Sandbox Client ID</label>
                                            <input type="text" 
                                                   class="form-control pro-input" 
                                                   name="cashfree_payout_sandbox_client_id"
                                                   value="{{ \App\Models\Setting::getValue('cashfree_payout_sandbox_client_id', env('CASHFREE_PAYOUT_SANDBOX_CLIENT_ID', '')) }}"
                                                   placeholder="Enter Sandbox Client ID">
                                        </div>
                                        <div class="mb-2">
                                            <label class="fw-semibold mb-1 small">Sandbox Client Secret</label>
                                            <input type="password" 
                                                   class="form-control pro-input" 
                                                   name="cashfree_payout_sandbox_client_secret"
                                                   value="{{ \App\Models\Setting::getValue('cashfree_payout_sandbox_client_secret', env('CASHFREE_PAYOUT_SANDBOX_CLIENT_SECRET', '')) }}"
                                                   placeholder="Enter Sandbox Client Secret">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Fund Source ID Config -->
                        <div class="mt-3 p-3 bg-white rounded border">
                            <div class="row align-items-center">
                                <div class="col-md-5">
                                    <label class="fw-semibold mb-1 small text-dark"><i class="fas fa-wallet me-1 text-primary"></i>Cashfree Fund Source ID / Wallet Name</label>
                                    <div class="text-muted small">Must match the active fund source name in your Cashfree Payout dashboard.</div>
                                </div>
                                <div class="col-md-7">
                                    <input type="text" 
                                           class="form-control pro-input font-monospace fw-bold" 
                                           name="cashfree_payout_fundsource_id"
                                           value="{{ \App\Models\Setting::getValue('cashfree_payout_fundsource_id', env('CASHFREE_PAYOUT_FUNDSOURCE_ID', 'LEGACY CARRY PRIVATE LIMITED')) }}"
                                           placeholder="LEGACY CARRY PRIVATE LIMITED">
                                </div>
                            </div>
                        </div>

                        <!-- Test Connection Action -->
                        <div class="d-flex align-items-center gap-2 mt-3 pt-2 border-top">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="test-payout-btn">
                                <i class="fas fa-plug me-1"></i> Test Live Payout Connection
                            </button>
                            <span id="test-payout-result" class="small"></span>
                        </div>

                        <!-- Sync fallback inputs -->
                        <input type="hidden" name="cashfree_payout_client_id" value="{{ \App\Models\Setting::getValue('cashfree_payout_live_client_id', env('CASHFREE_PAYOUT_CLIENT_ID', '')) }}">
                        <input type="hidden" name="cashfree_payout_client_secret" value="{{ \App\Models\Setting::getValue('cashfree_payout_live_client_secret', env('CASHFREE_PAYOUT_CLIENT_SECRET', '')) }}">
                    </div>

                    <!-- Direct Send Money Payout Tool Card -->
                    <div class="pro-card shadow-sm p-4 mb-4 border-start border-4 border-success bg-white">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 fw-bold text-dark">
                                <i class="fas fa-paper-plane text-success me-2"></i>Send Direct Money Payout
                            </h5>
                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                <i class="fas fa-bolt me-1"></i>Live Cashfree Transfer
                            </span>
                        </div>
                        <div class="text-muted small mb-3">
                            Transfer money directly to any beneficiary bank account or UPI ID instantly using Cashfree Payout API.
                        </div>

                        <div id="direct-payout-box" class="p-3 bg-light rounded border">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="fw-semibold mb-1 small">Payout Rail / Type</label>
                                    <select class="form-select pro-input" id="direct_payout_type">
                                        <option value="bank" selected>🏦 Bank Account (IMPS - Instant)</option>
                                        <option value="upi">📱 UPI ID (VPA)</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="fw-semibold mb-1 small">Recipient Full Name</label>
                                    <input type="text" class="form-control pro-input" id="direct_payout_name" placeholder="e.g. RANJIT SINGH" value="RANJIT SINGH">
                                </div>
                                <div class="col-md-4">
                                    <label class="fw-semibold mb-1 small">Recipient Phone Number</label>
                                    <input type="text" class="form-control pro-input" id="direct_payout_phone" placeholder="e.g. 9915041743" value="9915041743">
                                </div>

                                <!-- Bank Account Fields -->
                                <div class="col-md-6 direct-bank-field">
                                    <label class="fw-semibold mb-1 small">Bank Account Number</label>
                                    <input type="text" class="form-control pro-input" id="direct_payout_account" placeholder="Enter bank account number" value="50100474836561">
                                </div>
                                <div class="col-md-6 direct-bank-field">
                                    <label class="fw-semibold mb-1 small">Bank IFSC Code</label>
                                    <input type="text" class="form-control pro-input text-uppercase" id="direct_payout_ifsc" placeholder="e.g. HDFC0001583" value="HDFC0001583">
                                </div>

                                <!-- UPI Field -->
                                <div class="col-md-12 direct-upi-field" style="display: none;">
                                    <label class="fw-semibold mb-1 small">UPI VPA Handle</label>
                                    <input type="text" class="form-control pro-input" id="direct_payout_upi" placeholder="e.g. 9915041743@ptsbi" value="9915041743@ptsbi">
                                    <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i>Note: UPI mode must be enabled on your Cashfree wallet.</div>
                                </div>

                                <div class="col-md-4">
                                    <label class="fw-semibold mb-1 small">Payout Amount (₹)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white fw-bold">₹</span>
                                        <input type="number" step="0.01" min="1" class="form-control pro-input fw-bold" id="direct_payout_amount" value="1.00">
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <label class="fw-semibold mb-1 small">Remarks / Purpose</label>
                                    <input type="text" class="form-control pro-input" id="direct_payout_remarks" placeholder="e.g. Admin Payout Transfer" value="Admin Payout Transfer">
                                </div>

                                <div class="col-12 mt-3">
                                    <button type="button" class="btn btn-success px-4" id="send-direct-payout-btn">
                                        <i class="fas fa-paper-plane me-1"></i> ⚡ Send Money Payout Now
                                    </button>
                                </div>
                            </div>

                            <!-- Live Result Banner -->
                            <div id="direct-payout-result" class="mt-3" style="display: none;"></div>
                        </div>
                    </div>

                    <!-- Enable Withdrawals Toggle -->
                    <div class="pro-card shadow-sm p-4 mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <label class="fw-semibold mb-1 fs-6">Enable User Withdrawals</label>
                                <div class="text-muted small">Allow service providers and users to request withdrawals from their earned balance</div>
                            </div>
                            <div class="form-check form-switch fs-4">
                                <input type="hidden" name="withdrawal_enabled" value="0">
                                <input class="form-check-input" type="checkbox" name="withdrawal_enabled" value="1" id="withdrawal_enabled"
                                    {{ \App\Models\Setting::getValue('withdrawal_enabled', '1') == '1' ? 'checked' : '' }}>
                            </div>
                        </div>
                    </div>

                    <!-- Auto-Withdrawal Toggle -->
                    <div class="pro-card shadow-sm p-4 mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <label class="fw-semibold mb-1 fs-6">Enable Auto Cashfree Payout</label>
                                <div class="text-muted small">Automatically trigger Cashfree API transfers for small withdrawal amounts below the auto-limit threshold</div>
                            </div>
                            <div class="form-check form-switch fs-4">
                                <input type="hidden" name="auto_withdrawal_enabled" value="0">
                                <input class="form-check-input" type="checkbox" name="auto_withdrawal_enabled" value="1" id="auto_withdrawal_enabled"
                                    {{ \App\Models\Setting::getValue('auto_withdrawal_enabled', '1') == '1' ? 'checked' : '' }}>
                            </div>
                        </div>
                    </div>

                    <!-- Auto-Withdrawal Max Limit -->
                    <div class="pro-card shadow-sm p-4 mb-4">
                        <label class="fw-semibold mb-1">Max Auto-Withdrawal Limit (₹)</label>
                        <div class="text-muted small mb-2">Amounts up to this threshold will be transferred automatically. Any withdrawal request ABOVE this amount will require manual Admin approval.</div>
                        <div class="input-group" style="max-width: 300px;">
                            <span class="input-group-text bg-light fw-bold">₹</span>
                            <input type="number" step="1" min="1" class="form-control pro-input" name="auto_withdrawal_max_limit"
                                value="{{ \App\Models\Setting::getValue('auto_withdrawal_max_limit', '1000') }}">
                        </div>
                        <div class="form-text mt-1 text-primary"><i class="fas fa-shield-alt me-1"></i>Default: ₹1,000. Requests &gt; ₹1,000 stay in pending for manual verification.</div>
                    </div>

                    <!-- Min & Max Withdrawal Limits -->
                    <div class="pro-card shadow-sm p-4 mb-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="fw-semibold mb-1">Minimum Withdrawal Amount (₹)</label>
                                <div class="text-muted small mb-2">Minimum balance user must request to withdraw</div>
                                <div class="input-group">
                                    <span class="input-group-text bg-light fw-bold">₹</span>
                                    <input type="number" step="1" min="1" class="form-control pro-input" name="min_withdrawal_amount"
                                        value="{{ \App\Models\Setting::getValue('min_withdrawal_amount', '100') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="fw-semibold mb-1">Maximum Withdrawal Amount Per Request (₹)</label>
                                <div class="text-muted small mb-2">Maximum allowed amount in a single withdrawal request</div>
                                <div class="input-group">
                                    <span class="input-group-text bg-light fw-bold">₹</span>
                                    <input type="number" step="1" min="1" class="form-control pro-input" name="max_withdrawal_amount"
                                        value="{{ \App\Models\Setting::getValue('max_withdrawal_amount', '50000') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Navigation to Withdrawals List -->
                    <div class="pro-card shadow-sm p-4 mb-4 bg-light border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold text-dark mb-1"><i class="fas fa-list-alt me-2 text-primary"></i>Withdrawal Requests Dashboard</h6>
                                <div class="text-muted small">View, approve, or mark manual payouts for all user withdrawal requests.</div>
                            </div>
                            <a href="{{ route('admin.withdrawals.index') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-external-link-alt me-1"></i> Open Withdrawals Dashboard
                            </a>
                        </div>
                    </div>

                </form>
            </div>

            <!-- =========================================
                 ADMIN PROFILE
                 ========================================= -->
          
                <!-- =========================================
     ADMIN PROFILE SECTION
     ========================================= -->
     <div id="admin" class="pro-section {{ $activeTab == 'admin' ? 'active' : '' }}">


    <h4 class="fw-bold mb-3 text-primary">
        <i class="fas fa-user-shield me-2"></i>Admin Profile
    </h4>

    <div class="row">
        <div class="col-md-3">
            <!-- Profile Picture -->
            <div class="pro-card p-4 shadow-sm mb-4 text-center">
                <div class="mb-3">
                    <img src="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : asset('images/default-avatar.png') }}"
                         class="rounded-circle" 
                         width="120" 
                         height="120"
                         id="avatar-preview"
                         alt="Profile Picture">
                </div>
                
                <form method="POST" action="{{ route('admin.settings.update-avatar') }}" 
                      enctype="multipart/form-data" class="mb-3">
                    @csrf
                    <input type="file" 
                           name="avatar" 
                           id="avatar-input" 
                           class="form-control form-control-sm"
                           accept="image/*"
                           style="display: none;">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="change-avatar-btn">
                        <i class="fas fa-camera me-1"></i>Change Photo
                    </button>
                    @if(auth()->user()->avatar)
                    <button type="submit" 
                            form="remove-avatar-form"
                            class="btn btn-outline-danger btn-sm mt-2">
                        <i class="fas fa-trash me-1"></i>Remove
                    </button>
                    @endif
                </form>
                
                <form method="POST" action="{{ route('admin.settings.remove-avatar') }}" 
                      id="remove-avatar-form" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
                
                <h5 class="mt-3 mb-1">{{ auth()->user()->name }}</h5>
                <p class="text-muted small mb-0">{{ auth()->user()->email }}</p>
                <p class="text-muted small">
                    <i class="fas fa-user-tag me-1"></i>{{ ucfirst(auth()->user()->role) }}
                </p>
            </div>
            
            <!-- Account Stats -->
            <div class="pro-card p-4 shadow-sm">
                <h6 class="fw-bold mb-3">Account Info</h6>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="fas fa-calendar-alt me-2 text-primary"></i>
                        <strong>Joined:</strong> 
                        {{ auth()->user()->created_at->format('d M Y') }}
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-clock me-2 text-primary"></i>
                        <strong>Last Login:</strong> 
                        {{ auth()->user()->last_login_at ? auth()->user()->last_login_at->diffForHumans() : 'Never' }}
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-id-card me-2 text-primary"></i>
                        <strong>User ID:</strong> 
                        <span class="text-muted">#{{ auth()->user()->id }}</span>
                    </li>
                    <li>
                        <i class="fas fa-shield-alt me-2 text-primary"></i>
                        <strong>Status:</strong> 
                        <span class="badge bg-success">Active</span>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="col-md-9">
            <!-- Profile Update Form -->
            <form method="POST" action="{{ route('admin.settings.profile') }}" id="profile-form">
                @csrf
                
                <div class="pro-card p-4 shadow-sm mb-4">
                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-user-edit me-2"></i>Personal Information
                    </h5>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Full Name *</label>
                            <input type="text" 
                                   class="form-control pro-input" 
                                   name="name"
                                   value="{{ old('name', auth()->user()->name) }}" 
                                   required>
                            @error('name')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Email Address *</label>
                            <input type="email" 
                                   class="form-control pro-input" 
                                   name="email"
                                   value="{{ old('email', auth()->user()->email) }}" 
                                   required>
                            @error('email')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text">+91</span>
                                <input type="text" 
                                       class="form-control pro-input" 
                                       name="phone"
                                       value="{{ old('phone', auth()->user()->phone) }}"
                                       placeholder="9876543210">
                            </div>
                            @error('phone')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Role</label>
                            <input type="text" 
                                   class="form-control pro-input" 
                                   value="{{ ucfirst(auth()->user()->role) }}" 
                                   disabled>
                            <small class="text-muted">Contact super admin to change role</small>
                        </div>
                    </div>
                    
                   
                </div>
                
                <!-- Change Password Section -->
                <div class="pro-card p-4 shadow-sm mb-4">
                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-key me-2"></i>Change Password
                    </h5>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Leave password fields empty if you don't want to change password
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Current Password</label>
                            <input type="password" 
                                   class="form-control pro-input" 
                                   name="current_password"
                                   id="current_password">
                            @error('current_password')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">New Password</label>
                            <input type="password" 
                                   class="form-control pro-input" 
                                   name="new_password"
                                   id="new_password">
                            @error('new_password')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Confirm New Password</label>
                            <input type="password" 
                                   class="form-control pro-input" 
                                   name="new_password_confirmation">
                        </div>
                    </div>
                    
                    <div class="password-strength mb-3" style="display: none;">
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                        </div>
                        <small class="password-strength-text"></small>
                    </div>
                    
                    <div class="password-requirements small text-muted">
                        <strong>Password must contain:</strong>
                        <ul class="mb-0">
                            <li>At least 8 characters</li>
                            <li>One uppercase letter</li>
                            <li>One lowercase letter</li>
                            <li>One number</li>
                            <li>One special character</li>
                        </ul>
                    </div>
                </div>
                
                <!-- Two-Factor Authentication -->
                {{-- <div class="pro-card p-4 shadow-sm mb-4">
                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-mobile-alt me-2"></i>Two-Factor Authentication
                    </h5>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">SMS Verification</h6>
                            <p class="text-muted small mb-0">
                                Receive OTP on your phone for additional security
                            </p>
                        </div>
                        <label class="pro-switch">
                            <input type="checkbox" 
                                   name="two_factor_enabled"
                                   value="1"
                                   {{ auth()->user()->two_factor_enabled ? 'checked' : '' }}>
                            <span class="slider"></span>
                        </label>
                    </div>
                    
                    @if(auth()->user()->two_factor_enabled)
                    <div class="alert alert-success mt-3 p-3">
                        <i class="fas fa-check-circle me-2"></i>
                        Two-factor authentication is enabled for your account
                    </div>
                    @endif
                </div> --}}
                
                <!-- Save Changes Button -->
                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" id="discard-changes">
                        <i class="fas fa-times me-2"></i>Discard Changes
                    </button>
                    <button type="submit" class="btn btn-primary px-4" id="save-profile">
                        <i class="fas fa-save me-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
    


            <!-- =========================================
                 SYSTEM SETTINGS
                 ========================================= -->
           <!-- =========================================
     SYSTEM TOOLS SECTION
     ========================================= -->
     <div id="system" class="pro-section {{ $activeTab == 'system' ? 'active' : '' }}">

    <h4 class="fw-bold mb-3 text-primary">
        <i class="fas fa-server me-2"></i>System Tools & Maintenance
    </h4>

    @if(session('system_message'))
    <div class="alert alert-info alert-dismissible fade show shadow-sm">
        <i class="fas fa-info-circle me-2"></i>
        {{ session('system_message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <!-- Database Management -->
        <div class="col-md-6 mb-4">
            <div class="pro-card p-4 shadow-sm h-100">
                <h5 class="fw-bold mb-3">
                    <i class="fas fa-database text-info me-2"></i>Database Information
                </h5>
                
                <div class="database-info mb-3">
                    <h6 class="fw-bold mb-2">Database Stats</h6>
                    <ul class="list-unstyled">
                        <li class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Total Settings:</span>
                            <span class="fw-bold">{{ \App\Models\Setting::count() }}</span>
                        </li>
                        <li class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Last Updated:</span>
                            @php
                                $latestSetting = \App\Models\Setting::latest('updated_at')->first();
                                $lastUpdated = $latestSetting ? $latestSetting->updated_at : null;
                            @endphp
                            <span class="fw-bold">{{ $lastUpdated ? $lastUpdated->format('d M Y, h:i A') : 'Never' }}</span>
                        </li>
                        <li class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Database Size:</span>
                            <span class="fw-bold">{{ $dbSize ?? 'Calculating...' }}</span>
                        </li>
                        <li class="d-flex justify-content-between">
                            <span class="text-muted">Tables Count:</span>
                            <span class="fw-bold">{{ $tableCount ?? '0' }}</span>
                        </li>
                    </ul>
                </div>

                <div class="database-actions">
                    <h6 class="fw-bold mb-2">Quick Actions</h6>
                    <div class="d-grid gap-2">
                        <form method="POST" action="{{ route('admin.system.optimize-db') }}" 
                              onsubmit="return confirm('Are you sure you want to optimize the database?');">
                            @csrf
                            <button type="submit" class="btn btn-outline-info w-100">
                                <i class="fas fa-wrench me-2"></i>Optimize Database
                            </button>
                        </form>
                        
                        <button type="button" class="btn btn-outline-success w-100" data-bs-toggle="modal" data-bs-target="#backupModal">
                            <i class="fas fa-download me-2"></i>Create Backup
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- System Information -->
        <div class="col-md-6 mb-4">
            <div class="pro-card p-4 shadow-sm h-100">
                <h5 class="fw-bold mb-3">
                    <i class="fas fa-info-circle text-primary me-2"></i>System Information
                </h5>
                
                <div class="system-details">
                    <ul class="list-unstyled">
                        <li class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Laravel Version:</span>
                            <span class="fw-bold">{{ app()->version() }}</span>
                        </li>
                        <li class="d-flex justify-content-between mb-2">
                            <span class="text-muted">PHP Version:</span>
                            <span class="fw-bold">{{ phpversion() }}</span>
                        </li>
                        <li class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Server Software:</span>
                            <span class="fw-bold">{{ request()->server('SERVER_SOFTWARE') }}</span>
                        </li>
                        <li class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Environment:</span>
                            <span class="badge bg-{{ app()->environment('production') ? 'success' : 'warning' }}">
                                {{ app()->environment() }}
                            </span>
                        </li>
                        <li class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Debug Mode:</span>
                            <span class="badge bg-{{ config('app.debug') ? 'danger' : 'success' }}">
                                {{ config('app.debug') ? 'Enabled' : 'Disabled' }}
                            </span>
                        </li>
                        <li class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Timezone:</span>
                            <span class="fw-bold">{{ config('app.timezone') }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Additional sections here... -->
    </div>
</div>

        </div>

    </main>

</div>

@endsection


@push('styles')
<style>
/* Full layout and modern UI styles */
.pro-settings-wrapper{height:calc(100vh - 80px);}
.pro-sidebar{width:260px;background:#fff;border-right:1px solid #eee;position:sticky;top:0;}
.pro-nav{
    list-style: none;
    padding: 0px;
    margin: 0;
}
.pro-nav-item{padding:12px 20px;cursor:pointer;color:#444;font-weight:500;}
.pro-nav-item:hover{background:#eef5ff;}
.pro-nav-item.active{background:#ddecff;color:#0d6efd;border-left:4px solid #0d6efd;}
.pro-save-bar{position:sticky;top:0;background:#fff;padding:15px;z-index:10;border-bottom:1px solid #eee;  text-align: right;}
.pro-card{background:white;border-radius:14px;transition:.2s;}
.pro-card:hover{box-shadow:0 4px 18px rgba(0,0,0,0.08);}
.pro-input{padding:10px 14px;border-radius:10px;}
.pro-json{font-family:"JetBrains Mono";background:#eef6ff;color:#003366;}
.pro-section{display:none;}
.pro-section.active{display:block;}
.pro-switch{position:relative;width:50px;height:24px;display:inline-block;}
.pro-switch input{opacity:0;width:0;height:0;}
.slider{position:absolute;background:#ccc;border-radius:34px;inset:0;transition:.4s;}
.slider:before{content:"";width:18px;height:18px;background:white;border-radius:50%;position:absolute;left:3px;bottom:3px;transition:.4s;}
input:checked+.slider{background:#0d6efd;}
input:checked+.slider:before{transform:translateX(26px);}

.content-scroll{max-height:calc(100vh - 160px);overflow-x:scroll;}

/* Currency specific styles */
.currency-display {font-family: monospace; font-weight: bold; color: #28a745;}
.input-group-text {background: #f8f9fa; border: 1px solid #dee2e6;}

/* Payment gateway sections */
.payment-gateway-section {animation: fadeIn 0.3s ease;}
@keyframes fadeIn {from {opacity: 0;} to {opacity: 1;}}



/* Avatar styles */
#avatar-preview {
    border: 3px solid #e9ecef;
    object-fit: cover;
}

/* Password strength */
.password-strength .progress {
    height: 5px;
    margin-top: 5px;
    border-radius: 3px;
}

.password-strength-text {
    font-size: 12px;
    font-weight: 500;
}

/* Form validation */
.form-control.is-invalid {
    border-color: #dc3545;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(.375em + .1875rem) center;
    background-size: calc(.75em + .375rem) calc(.75em + .375rem);
}

/* Profile card hover */
.pro-card:hover {
    transform: translateY(-2px);
    transition: all 0.3s ease;
}

/* Role badge */
.badge {
    padding: 4px 8px;
    font-size: 11px;
    font-weight: 500;
}

/* Input group */
.input-group-text {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    color: #6c757d;
}
</style>

@endpush


@push('scripts')
<script>
$(function () {

    // Sidebar navigation handler
    $(".pro-nav-item").click(function () {
        $(".pro-nav-item").removeClass("active");
        $(this).addClass("active");

        $(".pro-section").removeClass("active");
        $($(this).data("target")).addClass("active");
    });

    // Switch toggle label
    $("input[type=checkbox]").change(function () {
        $(this).closest(".pro-card").find(".toggle-text")
            .text(this.checked ? "Enabled" : "Disabled");
    });

    // JSON Formatter
    $(".json-format").click(function () {
        let area = $(this).siblings(".pro-json");
        try {
            let json = JSON.parse(area.val());
            area.val(JSON.stringify(json, null, 4));
        } catch (e) {
            alert("Invalid JSON format!");
        }
    });

    // Payment Gateway Selection Handler
    function updateGatewaySections() {
        const gateway = $('#main_payment_gateway').val() || $('#payment_gateway').val();
        
        // Hide all gateway sections
        $('.payment-gateway-section').hide();
        
        // Show selected gateway section
        if (gateway === 'razorpay') {
            $('#razorpay-section').slideDown(200);
        } else if (gateway === 'cashfree') {
            $('#cashfree-section').slideDown(200);
        }
        
        // Also handle gateway fields in dynamic sections
        $('.gateway-field').hide();
        $('.' + gateway + '-fields').show();
    }

    // Attach payment gateway change handlers
    $('#payment_gateway, #main_payment_gateway').on('change', updateGatewaySections);

    // Format currency inputs on blur
    $('input[name*="amount"], input[name*="price"]').on('blur', function() {
        let value = $(this).val().replace(/[^0-9.]/g, '');
        if (value) {
            $(this).val(parseFloat(value).toFixed(2));
        }
    });

    // Initialize payment gateway display on page load
    updateGatewaySections();

    // Cashfree Environment select live change feedback
    $('#cashfree_environment_select').on('change', function() {
        const isLive = $(this).val() === 'production';
        const badge = $(this).closest('.pro-card').find('.badge');
        if (isLive) {
            badge.removeClass('bg-warning text-dark').addClass('bg-success').html('<i class="fas fa-check-circle me-1"></i>Live (Production) Active');
        } else {
            badge.removeClass('bg-success').addClass('bg-warning text-dark').html('<i class="fas fa-flask me-1"></i>Testing (Sandbox) Active');
        }
    });

    // Cashfree Payout Environment select live change feedback
    $('#cashfree_payout_env_select').on('change', function() {
        const isLive = $(this).val() === 'production';
        const badge = $(this).closest('.pro-card').find('.badge');
        if (isLive) {
            badge.removeClass('bg-warning text-dark').addClass('bg-success').html('<i class="fas fa-rocket me-1"></i>Live (Production) Payout');
        } else {
            badge.removeClass('bg-success').addClass('bg-warning text-dark').html('<i class="fas fa-flask me-1"></i>Sandbox (Testing) Payout');
        }
    });

    // Test Live Payout Connection Button
    $('#test-payout-btn').on('click', function() {
        const $btn = $(this);
        const $res = $('#test-payout-result');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Connecting to Cashfree...');
        $res.html('');

        $.ajax({
            url: "{{ route('admin.withdrawals.testConnection') }}",
            method: "POST",
            data: { _token: "{{ csrf_token() }}" },
            success: function(resp) {
                $btn.prop('disabled', false).html('<i class="fas fa-plug me-1"></i> Test Live Payout Connection');
                if (resp.success) {
                    $res.html('<span class="text-success fw-bold"><i class="fas fa-check-circle me-1"></i>' + resp.message + '</span>');
                } else {
                    $res.html('<span class="text-danger fw-bold"><i class="fas fa-exclamation-triangle me-1"></i>' + (resp.message || 'Authorization failed') + '</span>');
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html('<i class="fas fa-plug me-1"></i> Test Live Payout Connection');
                $res.html('<span class="text-danger"><i class="fas fa-times-circle me-1"></i> Server communication error</span>');
            }
        });
    });

    // Toggle Bank vs UPI fields in Direct Payout Tool
    $('#direct_payout_type').on('change', function() {
        if ($(this).val() === 'upi') {
            $('.direct-bank-field').hide();
            $('.direct-upi-field').show();
        } else {
            $('.direct-bank-field').show();
            $('.direct-upi-field').hide();
        }
    });

    // Send Direct Money Payout Button
    $('#send-direct-payout-btn').on('click', function() {
        const $btn = $(this);
        const $res = $('#direct-payout-result');
        const payoutType = $('#direct_payout_type').val();
        const amount = parseFloat($('#direct_payout_amount').val());

        if (!amount || amount < 1) {
            alert('Please enter a valid payout amount (min ₹1.00)');
            return;
        }

        const data = {
            _token: "{{ csrf_token() }}",
            payout_type: payoutType,
            name: $('#direct_payout_name').val(),
            phone: $('#direct_payout_phone').val(),
            amount: amount,
            remarks: $('#direct_payout_remarks').val(),
            account_number: $('#direct_payout_account').val(),
            ifsc: $('#direct_payout_ifsc').val(),
            upi_id: $('#direct_payout_upi').val()
        };

        if (payoutType === 'bank' && (!data.account_number || !data.ifsc)) {
            alert('Please enter Bank Account Number and IFSC Code.');
            return;
        }

        if (payoutType === 'upi' && !data.upi_id) {
            alert('Please enter UPI VPA ID.');
            return;
        }

        if (!confirm('Are you sure you want to send ₹' + amount.toFixed(2) + ' directly via Cashfree Payout now?')) {
            return;
        }

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Processing Transfer via Cashfree API...');
        $res.show().html('<div class="alert alert-info mb-0 py-2"><i class="fas fa-spinner fa-spin me-2"></i>Connecting to Cashfree Payout Gateway...</div>');

        $.ajax({
            url: "{{ route('admin.withdrawals.testPayoutTransfer') }}",
            method: "POST",
            data: data,
            success: function(resp) {
                $btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-1"></i> ⚡ Send Money Payout Now');
                if (resp.success) {
                    const st = (resp.status || 'SUCCESS').toUpperCase();
                    const badgeClass = st === 'SUCCESS' ? 'bg-success' : 'bg-warning text-dark';
                    $res.html(`
                        <div class="alert alert-success border shadow-sm mb-0">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong class="text-success"><i class="fas fa-check-circle me-1"></i>Payout Dispatched Successfully!</strong>
                                <span class="badge ${badgeClass}">${st}</span>
                            </div>
                            <div class="small">
                                <div><strong>Transfer ID:</strong> <code>${resp.transfer_id || 'N/A'}</code></div>
                                <div><strong>Cashfree Reference ID:</strong> <code>${resp.reference_id || 'N/A'}</code></div>
                                <div><strong>Message:</strong> ${resp.message || 'Payment initiated'}</div>
                            </div>
                        </div>
                    `);
                } else {
                    $res.html(`
                        <div class="alert alert-danger border shadow-sm mb-0">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <strong class="text-danger"><i class="fas fa-times-circle me-1"></i>Transfer Rejected / Failed</strong>
                                <span class="badge bg-danger">${resp.status || 'FAILED'}</span>
                            </div>
                            <div class="small">${resp.message || 'Transfer could not be processed.'}</div>
                        </div>
                    `);
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-1"></i> ⚡ Send Money Payout Now');
                let err = 'Server communication error';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    err = xhr.responseJSON.message;
                }
                $res.html(`
                    <div class="alert alert-danger border shadow-sm mb-0">
                        <i class="fas fa-exclamation-triangle me-1"></i> <strong>Error:</strong> ${err}
                    </div>
                `);
            }
        });
    });

    // Save All Settings
    $("#save-all-btn").click(function () {
        let requests = [];

        $(".settings-form").each(function () {
            let fd = new FormData(this);
            fd.append("_token", "{{ csrf_token() }}");

            requests.push(
                $.ajax({
                    url: "{{ route('admin.settings.update') }}",
                    method: "POST",
                    data: fd,
                    contentType: false,
                    processData: false
                })
            );
        });

        Promise.all(requests)
            .then(() => {
                alert("All settings saved successfully!");
                location.reload();
            })
            .catch(() => alert("Some settings failed to save."));
    });

});

$(function () {
    // Avatar upload
    $('#change-avatar-btn').click(function() {
        $('#avatar-input').click();
    });
    
    $('#avatar-input').change(function() {
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#avatar-preview').attr('src', e.target.result);
            };
            reader.readAsDataURL(this.files[0]);
            $(this).closest('form').submit();
        }
    });
    
    // Password strength meter
    $('#new_password').on('keyup', function() {
        var password = $(this).val();
        var strength = 0;
        var text = '';
        
        if (password.length >= 8) strength++;
        if (password.match(/[a-z]+/)) strength++;
        if (password.match(/[A-Z]+/)) strength++;
        if (password.match(/[0-9]+/)) strength++;
        if (password.match(/[!@#$%^&*(),.?":{}|<>]+/)) strength++;
        
        var $meter = $('.password-strength');
        var $bar = $meter.find('.progress-bar');
        var $text = $meter.find('.password-strength-text');
        
        if (password.length > 0) {
            $meter.show();
            
            if (strength < 2) {
                $bar.css('width', '20%').removeClass('bg-warning bg-success').addClass('bg-danger');
                text = 'Very Weak';
            } else if (strength < 3) {
                $bar.css('width', '40%').removeClass('bg-danger bg-success').addClass('bg-warning');
                text = 'Weak';
            } else if (strength < 4) {
                $bar.css('width', '60%').removeClass('bg-danger bg-success').addClass('bg-warning');
                text = 'Fair';
            } else if (strength < 5) {
                $bar.css('width', '80%').removeClass('bg-danger bg-warning').addClass('bg-success');
                text = 'Good';
            } else {
                $bar.css('width', '100%').removeClass('bg-danger bg-warning').addClass('bg-success');
                text = 'Strong';
            }
            
            $text.text(text);
        } else {
            $meter.hide();
        }
    });
    
    // Discard changes
    $('#discard-changes').click(function() {
        if (confirm('Are you sure you want to discard all changes?')) {
            location.reload();
        }
    });
    
    // Form submission
    $('#profile-form').submit(function(e) {
        var newPassword = $('#new_password').val();
        var currentPassword = $('#current_password').val();
        
        if (newPassword && !currentPassword) {
            e.preventDefault();
            alert('Please enter your current password to change password.');
            $('#current_password').focus();
        }
    });
    

    let activeTab = "{{ $activeTab }}";

    if (activeTab) {
        // Remove old active state
        $(".pro-nav-item").removeClass("active");
        $(".pro-section").removeClass("active");

        // Activate sidebar
        $(`.pro-nav-item[data-target="#${activeTab}"]`).addClass("active");

        // Activate section
        $(`#${activeTab}`).addClass("active");
    }
});

</script>
@endpush