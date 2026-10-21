@extends('member.layouts.app')

@section('title', 'Notice Details')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            {{-- BACK BUTTON --}}
            <div class="mb-4">
                <a href="{{ route('member.notices.index') }}" class="btn btn-light rounded-pill px-4">
                    <i class="mdi mdi-arrow-left me-1"></i> Back to Notices
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                @if($notice->image)
                    <div class="image-popup-hover w-100" 
                         data-preview-image="{{ asset('storage/' . $notice->image) }}"
                         data-preview-title="{{ $notice->title }}"
                         title="Click to view full image">
                        <img src="{{ asset('storage/' . $notice->image) }}" class="card-img-top w-100 img-popup-clickable" style="max-height: 400px; object-fit: cover;" alt="{{ $notice->title }}">
                        <div class="image-zoom-overlay d-flex align-items-center justify-content-center">
                            <i class="mdi mdi-arrow-expand-all text-white font-24"></i>
                        </div>
                    </div>
                @endif
                
                <div class="card-body p-5">
                    <div class="d-flex align-items-center mb-4">
                        <div class="me-3">
                            <span class="badge bg-soft-primary text-primary rounded-pill px-3 py-2">
                                <i class="mdi mdi-calendar-clock me-1"></i>
                                {{ $notice->published_at ? $notice->published_at->format('M d, Y') : $notice->created_at->format('M d, Y') }}
                            </span>
                        </div>
                        @if($notice->notice_type == 'single')
                        <div>
                            <span class="badge bg-soft-warning text-warning rounded-pill px-3 py-2">
                                <i class="mdi mdi-account-star me-1"></i>
                                Priority Update
                            </span>
                        </div>
                        @endif
                    </div>

                    <h1 class="fw-bold text-dark mb-4">{{ $notice->title }}</h1>

                    <div class="notice-content fs-5 text-secondary lh-lg mb-5">
                        {!! nl2br(e($notice->description)) !!}
                    </div>

                    <div class="border-top pt-4 mt-5">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded-circle p-3 me-3">
                                    <i class="mdi mdi-shield-check mdi-24px text-primary"></i>
                                </div>
                                <div>
                                    <p class="mb-0 fw-bold">Society Management</p>
                                    <p class="mb-0 text-muted small">Sent via Official Portal</p>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <button onclick="window.print()" class="btn btn-outline-secondary rounded-pill px-4 btn-sm">
                                    <i class="mdi mdi-printer me-1"></i> Print
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-soft-primary {
        background-color: #e0e7ff;
        color: #4338ca;
    }
    .bg-soft-warning {
        background-color: #fff7ed;
        color: #c2410c;
    }
    .notice-content {
        white-space: pre-line;
    }
</style>
@endsection
