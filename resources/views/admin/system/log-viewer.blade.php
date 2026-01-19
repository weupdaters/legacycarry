@extends('admin.layouts.app')

@section('title', 'Log Viewer - ' . $filename)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold">
            <i class="fas fa-file-alt me-2"></i>{{ $filename }}
        </h4>
        <a href="{{ route('admin.settings') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>Back to Settings
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Log Content</h6>
                <button class="btn btn-sm btn-outline-secondary" onclick="copyLogContent()">
                    <i class="fas fa-copy me-1"></i>Copy
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <pre class="bg-dark text-light p-3 m-0" style="max-height: 70vh; overflow: auto; font-family: monospace;" id="logContent">{{ $content }}</pre>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyLogContent() {
    const content = document.getElementById('logContent').innerText;
    navigator.clipboard.writeText(content).then(() => {
        alert('Log content copied to clipboard!');
    });
}
</script>
@endpush