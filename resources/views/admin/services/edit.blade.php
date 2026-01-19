@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">

    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">
                Edit Service: {{ $service->name }}
            </h4>
        </div>

        <div class="text-end">
            <a href="{{ route('services.index') }}" class="btn btn-secondary btn-sm">Back to Services</a>
        </div>
    </div>


    <div class="row">
        <div class="col-12">
            <div class="card">

                <div class="card-body">

                    <form action="{{ route('services.update', $service->id) }}" method="POST" class="row g-3">
                        @csrf
                        @method('PUT')

                        <!-- NAME -->
                        <div class="col-12">
                            <label class="form-label">Service Name</label>
                            <input name="name" class="form-control"
                                   value="{{ old('name', $service->name) }}" required>
                        </div>

                        <!-- CATEGORY DROPDOWN -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">Category</label>
                            <select name="category_option" id="category_option" class="form-select" required>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}"
                                        {{ $service->category == $cat ? 'selected' : '' }}>
                                        {{ $cat }}
                                    </option>
                                @endforeach

                                <option value="new">➕ Add New Category</option>
                            </select>
                        </div>

                        <!-- NEW CATEGORY -->
                        <div class="col-12 col-md-6 d-none" id="newCategoryBox">
                            <label class="form-label">New Category</label>
                            <input type="text" name="category_new" class="form-control">
                        </div>

                        <!-- DESCRIPTION -->
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="3" class="form-control">
                                {{ old('description', $service->description) }}
                            </textarea>
                        </div>

                        <!-- STATUS -->
                        <div class="col-12 col-md-3">
                            <label class="form-label">Status</label>
                            <select name="is_active" class="form-select">
                                <option value="1" {{ $service->is_active ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ !$service->is_active ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <!-- SAVE -->
                        <div class="col-12 d-flex gap-2">
                            <button class="btn btn-primary">Save Changes</button>
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
    document.getElementById('newCategoryBox').classList.toggle(
        'd-none',
        this.value !== 'new'
    );
});
</script>
@endsection
