@extends('member.layouts.app')

@section('title', 'Society Notices')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);">
                <div class="card-body p-4 text-white d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="fw-bold mb-1">Society Notices</h3>
                        <p class="opacity-75 mb-0">Stay updated with the latest announcements from your society management.</p>
                    </div>
                    @if(has_permission('notice_create') || has_permission('resident_index') || (auth()->check() && auth()->user()->role === 'super_admin'))
                    <div>
                        <a href="{{ Route::has('member.notices.create') ? route('member.notices.create') : url('residents/notices/create') }}" class="btn fw-bold rounded-pill px-4 shadow-sm" style="background: #ffffff !important; color: #4f46e5 !important;">
                            <i class="mdi mdi-plus-circle me-1"></i> Add Notice
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <i class="mdi mdi-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        @forelse($notices as $notice)
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 transition-hover">
                <div class="row g-0 h-100">
                    <div class="col-4 position-relative overflow-hidden">
                        @if($notice->image)
                            <img src="{{ asset('storage/' . $notice->image) }}" class="img-fluid h-100 w-100" style="object-fit: cover; position: absolute; top:0; left:0;">
                        @else
                            <div class="bg-light d-flex align-items-center justify-content-center h-100 w-100" style="position: absolute; top:0; left:0;">
                                <i class="mdi mdi-bell-outline mdi-36px text-muted opacity-50"></i>
                            </div>
                        @endif
                    </div>
                    <div class="col-8">
                        <div class="card-body p-4 d-flex flex-column h-100">
                            <div class="mb-2 d-flex justify-content-between align-items-start">
                                <span class="badge bg-soft-primary text-primary rounded-pill px-3">
                                    {{ $notice->published_at ? $notice->published_at->format('M d, Y') : $notice->created_at->format('M d, Y') }}
                                </span>
                            </div>
                            <h5 class="fw-bold text-dark mb-2">{{ $notice->title }}</h5>
                            <p class="text-muted small mb-4 line-clamp-2">
                                {{ Str::limit(strip_tags($notice->description), 100) }}
                            </p>
                            <div class="mt-auto">
                                <a href="{{ route('member.notices.show', $notice->id) }}" class="btn btn-outline-primary btn-sm rounded-pill px-4">
                                    Read More
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <div class="bg-white rounded-4 shadow-sm p-5">
                <img src="{{ asset('assets/images/no-data.svg') }}" height="120" class="mb-4 opacity-50">
                <h4 class="fw-bold text-muted">No notices at the moment</h4>
                <p class="text-muted">You are all caught up! Check back later for new announcements.</p>
            </div>
        </div>
        @endforelse
    </div>

    @if($notices->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $notices->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

<style>
    .btn-white { background: white; border: none; }
    .btn-white:hover { background: #f8fafc; }
    .transition-hover {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .transition-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;  
        overflow: hidden;
    }
    .bg-soft-primary {
        background-color: #e0e7ff;
        color: #4338ca;
    }
</style>
@endsection
