@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">

    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">Add New Service</h4>
        </div>

        <div class="text-end">
            <a href="{{ route('services.index') }}" class="btn btn-secondary btn-sm">Back to Services</a>
        </div>
    </div>


    <div class="row">
        <div class="col-12">

            <div class="card">
                <div class="card-body">

                    <form action="{{ route('services.store') }}" method="POST" class="row g-3">
                        @csrf

                        

                        {{-- NAME --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold">Service Name</label>
                            <input name="name" class="form-control" placeholder="Enter service name" required>
                        </div>

                        {{-- CATEGORY --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Category</label>
                            <select name="category_option" id="category_option" class="form-select" required>
                                <option value="">-- Select Category --</option>

                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach

                                <option value="new">➕ Add New Category</option>
                            </select>
                        </div>

                        {{-- NEW CATEGORY --}}
                        <div class="col-12 col-md-6 d-none" id="newCategoryBox">
                            <label class="form-label fw-semibold">New Category</label>
                            <input type="text" name="category_new" class="form-control"
                                   placeholder="Enter new category name">
                            <small class="text-muted">This will automatically save as a new category.</small>
                        </div>

                        {{-- DESCRIPTION --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" rows="3" class="form-control"
                                      placeholder="Enter description (optional)"></textarea>
                        </div>

                        {{-- STATUS --}}
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="is_active" class="form-select">
                                <option value="1" selected>Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>

                        {{-- BUTTONS --}}
                        <div class="col-12 d-flex gap-2 pt-2">
                            <button class="btn btn-primary">Save Service</button>
                            <a href="{{ route('services.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>

</div>

<script>
document.getElementById('category_option').addEventListener('change', function () {
    const box = document.getElementById('newCategoryBox');
    box.classList.toggle('d-none', this.value !== 'new');
});
</script>

@endsection
