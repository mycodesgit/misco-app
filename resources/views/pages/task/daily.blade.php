@extends('layouts.app')

@section('title')
    MIS Ticketing | Daily Task
@endsection

@section('body')
    <div class="row">
        <div class="col-12">
            <div class="mb-4">

                <!-- Dashboard Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <div>
                        <h1 class="h4 fw-bold mb-1">IT Support Daily Task</h1>
                        <p class="text-muted small mb-0">Manage your daily tasks and activities.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-secondary">
                            Export Report
                        </button>
                    </div>
                </div>

                <!-- Top Row: Metric Cards -->
                <div class="row g-3 mb-5">
                    <div class="col-md-4">
                        <div class="card card-animate">
                            <div class="card-header pt-3">
                                <h6 class="card-title">
                                    <i class="ti ti-plus"></i> Add New
                                </h6>
                            </div>
                            <div class="card-body">
                                <form method="POST" id="adDailyTask">
                                    @csrf

                                    <div class="form-group mb-3">
                                        <div class="row g-3">
                                            <div class="col-md-12">
                                                <label class="form-label fw-semibold">Category: <span class="text-danger">*</span></label>
                                                <select name="cat_id" id="categorySelect" class="form-control" required>
                                                    <option value="" disabled selected>Select Category</option>
                                                    @foreach($cat as $category)
                                                        <option value="{{ $category->id }}">{{ $category->ticketcatname }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label fw-semibold">Sub Category: <span class="text-danger">*</span></label>
                                                <select name="subcat_id" id="subcategorySelect" class="form-control" required disabled>
                                                    <option value="" disabled selected>Select Sub Category</option>
                                                </select>
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label fw-semibold">Task: <span class="text-danger">*</span></label>
                                                <textarea name="dailytaskdesc" rows="4" class="form-control" placeholder="Enter task details" required></textarea>
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
                                    <i class="fas fa-server"></i> Daily Task List Section
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3 align-items-center">
                                    <div class="col-md-3">
                                        <label for="filterMonth" class="form-label fw-semibold">Filter by Month:</label>
                                        <select id="filterMonth" class="form-select form-select-sm">
                                            <option value="">All Months</option>
                                            <option value="01">January</option>
                                            <option value="02">February</option>
                                            <option value="03">March</option>
                                            <option value="04">April</option>
                                            <option value="05">May</option>
                                            <option value="06">June</option>
                                            <option value="07">July</option>
                                            <option value="08">August</option>
                                            <option value="09">September</option>
                                            <option value="10">October</option>
                                            <option value="11">November</option>
                                            <option value="12">December</option>
                                        </select>
                                    </div>
                                </div>
                                <table id="dailyTaskTable" class="table table-hover" style="width: 100%">
                                    <thead>
                                        <tr>
                                            <th>Category</th>
                                            <th>Sub Category</th>
                                            <th>Task</th>
                                            <th>Status</th>
                                            <th>Completed</th>
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

    <div class="modal fade" id="editDailyTaskModal" tabindex="-1" role="dialog" aria-labelledby="editDailyTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="editDailyTaskModalLabel">
                        <i class="ti ti-pencil"></i> Edit Daily Task
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editDailyTaskForm">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="editDailyTaskId">
                        <div class="col-md-12 mb-3">
                            <label for="editDailyTaskDescription" class="form-label fw-semibold">Task Description: <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="editDailyTaskDescription" name="dailytaskdesc" required></textarea>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="editDailyTaskCategory" class="form-label fw-semibold">Category: <span class="text-danger">*</span></label>
                            <select name="cat_id" id="editDailyTaskCategory" class="form-control" required>
                                <option value="">Select Category</option>
                                @foreach($cat as $category)
                                    <option value="{{ $category->id }}">{{ $category->ticketcatname }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="editDailyTaskSubcategory" class="form-label fw-semibold">Sub Category: <span class="text-danger">*</span></label>
                            <select name="subcat_id" id="editDailyTaskSubcategory" class="form-control" required>
                                <option value="">Select Sub Category</option>
                                @foreach($subcat as $subcategory)
                                    <option value="{{ $subcategory->id }}">{{ $subcategory->ticketsubcatname }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="editDailyTaskStatus" class="form-label fw-semibold">Status: <span class="text-danger">*</span></label>
                            <select name="status" id="editDailyTaskStatus" class="form-control" required>
                                <option value="">Select Status</option>
                                <option value="Pending">Pending</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Completed">Completed</option>
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

    <script>
        var subcategorySelect = "{{ route('daily-task.getSubcategories', ['categoryId' => ':categoryId']) }}";
        var dailyTaskCreateRoute = "{{ route('daily-task.create') }}";
        var dailyTaskReadRoute = "{{ route('daily-task.show') }}";
        var dailyTaskUpdateRoute = "{{ route('daily-task.update') }}";
    </script>
@endsection
