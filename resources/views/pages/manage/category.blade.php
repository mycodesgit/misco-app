@extends('layouts.app')

@section('title')
    All Tickets
@endsection

@section('body')
    <div class="row">
        <div class="col-12">
            <div class="mb-4">

                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <div>
                        <h1 class="h4 fw-bold mb-1">IT Support Categories</h1>
                        <p class="text-muted small mb-0">Manage IT Support Categories.</p>
                    </div>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-12">
                        <ul class="nav nav-pills bg-light p-2 card-animate rounded-2 d-inline-flex mb-2" id="pills-tab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="pills-one-tab" data-bs-toggle="pill"
                                    data-bs-target="#pills-one" type="button" role="tab"
                                    aria-controls="pills-one" aria-selected="true">
                                    Categories
                                </button>
                            </li>
                            &nbsp;
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="pills-two-tab" data-bs-toggle="pill"
                                    data-bs-target="#pills-two" type="button" role="tab"
                                    aria-controls="pills-two" aria-selected="false" tabindex="-1">
                                    Sub-categories
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade show active" id="pills-one" role="tabpanel" aria-labelledby="pills-one-tab" tabindex="0">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="card card-animate">
                                        <div class="card-header pt-3">
                                            <h6 class="card-title">
                                                <i class="ti ti-plus"></i> Add New
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <form method="POST" id="adCategory">
                                                @csrf

                                                <div class="form-group mb-3">
                                                    <div class="row g-3">
                                                        <div class="col-md-12">
                                                            <label class="form-label fw-semibold">Category Name: <span class="text-danger">*</span></label>
                                                            <input type="text" name="ticketcatname" class="form-control form-control-sm" placeholder="Enter category name" required>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <div class="row">
                                                        <div class="col-md-12 d-flex justify-content-between">
                                                            <button type="reset" class="btn btn-light"><i class="ti ti-restore"></i> Clear</button>
                                                            <button type="submit" class="btn btn-success"><i class="ti ti-device-floppy"></i>  Save</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="card card-animate">
                                        <div class="card-header pt-3">
                                            <h6 class="card-title">
                                                <i class="fas fa-server"></i> List of all categories section
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive p-2">
                                                <table id="categoryTable" class="table table-hover" style="width: 100%">
                                                    <thead>
                                                        <tr>
                                                            <th>Category</th>
                                                            <th>Status</th>
                                                            <th width="10%">Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>

                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="pills-two" role="tabpanel" aria-labelledby="pills-two-tab" tabindex="0">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="card card-animate">
                                        <div class="card-header pt-3">
                                            <h6 class="card-title">
                                                <i class="ti ti-plus"></i> Add New
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <form method="POST" id="adSubCategory">
                                                @csrf

                                                <div class="form-group mb-3">
                                                    <div class="row g-3">
                                                        <div class="col-md-12">
                                                            <label class="form-label fw-semibold">Category Name: <span class="text-danger">*</span></label>
                                                            <select name="cat_id" class="form-control form-control-sm" required>
                                                                <option value="">Select Category</option>
                                                                @foreach ($cat as $category)
                                                                    <option value="{{ $category->id }}">{{ $category->ticketcatname }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="col-md-12">
                                                            <label class="form-label fw-semibold">Sub-category: <span class="text-danger">*</span></label>
                                                            <input type="text" name="ticketsubcatname" class="form-control form-control-sm" placeholder="Enter sub-category name" required>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <div class="row">
                                                        <div class="col-md-12 d-flex justify-content-between">
                                                            <button type="reset" class="btn btn-light"><i class="ti ti-restore"></i> Clear</button>
                                                            <button type="submit" class="btn btn-success"><i class="ti ti-device-floppy"></i>  Save</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="card">
                                        <div class="card-header pt-3">
                                            <h6 class="card-title">
                                                <i class="fas fa-server"></i> List of all sub-categories section
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive p-2">
                                                <table id="subcategoryTable" class="table table-hover" style="width: 100%">
                                                    <thead>
                                                        <tr>
                                                            <th>Category</th>
                                                            <th>Sub-Category</th>
                                                            <th>Status</th>
                                                            <th width="10%">Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>

                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editCategoryModal" tabindex="-1" role="dialog" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="editCategoryModalLabel">
                        <i class="fas fa-edit"></i> Edit Category Name
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editCategoryForm">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="editCategoryId">
                        <div class="col-md-12 mb-3">
                            <label for="editCategoryName" class="form-label fw-semibold">Category Name: <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editCategoryName" name="ticketcatname" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="editIsStatus" class="form-label fw-semibold">Status: <span class="text-danger">*</span></label>
                            <select name="status" id="editIsStatus" class="form-control" required>
                                <option value="">Select</option>
                                <option value="2">Not Available</option>
                                <option value="1">Available</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editCategorySubModal" tabindex="-1" role="dialog" aria-labelledby="editCategorySubModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="editCategorySubModalLabel">
                        <i class="fas fa-edit"></i> Edit Sub Category Name
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editCategorySubForm">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="editCategorySubId">
                        <div class="col-md-12 mb-3">
                            <label for="editCatName" class="form-label fw-semibold">Category Name: <span class="text-danger">*</span></label>
                            <select name="cat_id" class="form-control form-control-sm" id="editCatName" required>
                                <option value="">Select Category</option>
                                @foreach ($cat as $category)
                                    <option value="{{ $category->id }}">{{ $category->ticketcatname }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="editCategorySubName" class="form-label fw-semibold">Sub Category Name: <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editCategorySubName" name="ticketsubcatname" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="editIssubStatus" class="form-label fw-semibold">Status: <span class="text-danger">*</span></label>
                            <select name="status" id="editIssubStatus" class="form-control" required>
                                <option value="">Select</option>
                                <option value="2">Not Available</option>
                                <option value="1">Available</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .avatar-circle-sm {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
        }
    </style>

    <script>
        var categoryCreateRoute = "{{ route('category.create') }}";
        var categoryReadRoute   = "{{ route('category.show') }}";
        var categoryUpdateRoute = "{{ route('category.update', ['id' => ':id']) }}";
        var categoryDeleteRoute = "{{ route('category.delete', ['id' => ':id']) }}";

        var subcategoryCreateRoute = "{{ route('subcategory.create') }}";
        var subcategoryReadRoute   = "{{ route('subcategory.show') }}";
        var subcategoryUpdateRoute = "{{ route('subcategory.update', ['id' => ':id']) }}";
        var subcategoryDeleteRoute = "{{ route('subcategory.delete', ['id' => ':id']) }}";
    </script>
@endsection
