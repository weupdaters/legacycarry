@extends('admin.layouts.app')

@section('content')

<style>
    .glass-card {
        backdrop-filter: blur(12px);
        background: rgba(255,255,255,0.55);
        border-radius: 18px;
        border: 1px solid rgba(255,255,255,0.25);
        box-shadow: 0 8px 24px rgba(0,0,0,0.08);
    }
    .btn-modern {
        background: linear-gradient(135deg, #4e73df, #1cc88a);
        color: white;
        border-radius: 12px;
        padding: 10px 22px;
        font-weight: 600;
        border: none;
        transition: 0.2s;
    }
    .btn-modern:hover {
        opacity: 0.85;
        transform: translateY(-1px);
    }
</style>

<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-semibold m-0">
            <i class="bi bi-gear-fill text-primary"></i> Update Setting
        </h4>

        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-sm">Back</a>
    </div>

    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- SETTINGS CARD --}}
    <div class="glass-card p-4">

        <h5 class="fw-bold mb-3">{{ ucwords(str_replace('_',' ', $setting->key)) }}</h5>

        <form method="POST" action="{{ route('settings.update', $setting->key) }}">
            @csrf

            {{-- Detect JSON or plain text --}}
            @php
                $isJson = $setting->type === 'json';

                // unify value handling (string or array)
                if ($isJson) {
                    $jsonValue = is_array($setting->value)
                        ? $setting->value
                        : json_decode($setting->value, true);

                    // Final JSON for display
                    $formattedJson = json_encode($jsonValue, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                }
            @endphp

            <div class="mb-3">
                <label class="form-label fw-semibold">Content</label>

                @if($isJson)
                    <textarea name="value" rows="10" class="form-control rounded" style="font-family: monospace;">{{ $formattedJson }}</textarea>
                    <small class="text-muted">This setting uses JSON format (auto beautified).</small>
                @else
                    <textarea name="value" rows="10" class="form-control rounded">{{ $setting->value }}</textarea>
                @endif
            </div>

            <button class="btn-modern">
                <i class="bi bi-cloud-upload"></i> Update
            </button>

        </form>

    </div>

</div>

@endsection
