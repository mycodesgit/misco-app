@extends('layouts.app')

@section('title')
    Ticketing | Users
@endsection

@section('body')
    <div class="row">
        <div class="col-12">
            <div class="mb-4">

                <!-- Dashboard Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <div>
                        <h1 class="h4 fw-bold mb-1">User's Management</h1>
                        <p class="text-muted small mb-0">Manage user accounts, assign role permissions, and configure office access levels across the system.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-secondary">
                            Export Report
                        </button>
                        <button class="btn btn-sm btn-success text-white" data-bs-toggle="modal" data-bs-target="#createUserModal">
                            <i class="ti ti-plus"></i> Create New User
                        </button>
                    </div>
                </div>

                <!-- Top Row: Metric Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <div class="card card-animate">
                            <div class="card-header pt-3">
                                <h6 class="card-title">
                                    <i class="fas fa-users"></i> List of Users
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive mt-2 p-2">
                                    <table id="userviewTable" class="table table-hover styled-table" style="width: 100%">
                                        <thead>
                                            <tr>
                                                <th>Last Name</th>
                                                <th>First Name</th>
                                                <th>Middle Name</th>
                                                {{-- <th>Campus</th> --}}
                                                {{-- <th>Office</th> --}}
                                                <th>Username</th>
                                                <th>Role</th>
                                                <th>Status</th>
                                                <th>Allowed</th>
                                                <th>Action</th>
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

    <div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title" id="createUserModalLabel">
                        <i class="fas fa-user-plus me-2"></i> Add New User
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="#" method="POST" id="addUser">
                    @csrf
                    <!-- Modal Body -->
                    <div class="modal-body p-4">

                        <!-- Section 1: Personal Details -->
                        <h6 class="text-uppercase text-muted fw-bold mb-3 small">1. Personal Information</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="fname" oninput="this.value = this.value.toUpperCase()" placeholder="Enter First Name" class="form-control form-control-sm" required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Middle Name</label>
                                <input type="text" name="mname" oninput="this.value = this.value.toUpperCase()" placeholder="Enter Middle Name" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="lname" oninput="this.value = this.value.toUpperCase()" placeholder="Enter Last Name" class="form-control form-control-sm" required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Extension Name</label>
                                <select name="ext" class="form-control form-control-sm">
                                    <option value="">--- Select Extension ---</option>
                                    <option value="Jr.">Jr.</option>
                                    <option value="Sr.">Sr.</option>
                                    <option value="II">II</option>
                                    <option value="III">III</option>
                                    <option value="IV">IV</option>
                                    <option value="V">V</option>
                                    <option value="VI">VI</option>
                                </select>
                            </div>
                        </div>

                        <hr class="my-3 text-muted opacity-25">

                        <!-- Section 2: Account & Authentication -->
                        <h6 class="text-uppercase text-muted fw-bold mb-3 small">2. Credentials & Profile</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                <input type="email" id="email" name="email" placeholder="Enter Email" class="form-control form-control-sm" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" placeholder="Enter Password" class="form-control form-control-sm" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Gender <span class="text-danger">*</span></label>
                                <select name="gender" class="form-control form-control-sm" required>
                                    <option value="" selected disabled>--- Select Gender ---</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>

                        <hr class="my-3 text-muted opacity-25">

                        <!-- Section 3: Organizational Assignment -->
                        <h6 class="text-uppercase text-muted fw-bold mb-3 small">3. Organizational Role & Location</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Campus <span class="text-danger">*</span></label>
                                <select name="campus_id" class="form-control form-control-sm">
                                    <option value="" selected disabled>--- Select Campus ---</option>
                                    <option value="MC">Main</option>
                                    <option value="VC">Victorias</option>
                                    <option value="SCC">San Carlos</option>
                                    <option value="HC">Hinigaran</option>
                                    <option value="MP">Moises Padilla</option>
                                    <option value="IC">Ilog</option>
                                    <option value="CA">Candoni</option>
                                    <option value="CC">Cauayan</option>
                                    <option value="SC">Sipalay</option>
                                    <option value="HinC">Hinobaan</option>
                                    {{-- Loop through campuses --}}
                                    {{-- @foreach($campuses as $campus) --}}
                                    {{--    <option value="{{ $campus->id }}">{{ $campus->campus_name }}</option> --}}
                                    {{-- @endforeach --}}
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                                <select class="form-control form-control-sm" name="role" required>
                                    <option value="" selected disabled>--- Select Role ---</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->rolename }}">
                                            {{ $role->rolename }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Office <span class="text-danger">*</span></label>
                                <select class="form-control form-control-sm select2" name="office_id">
                                    <option value="" selected disabled>--- Select Office ---</option>
                                    @foreach ($offices as $data)
                                        <option value="{{ $data->id }}">{{ $data->office_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer with Justified Action Buttons -->
                    <div class="modal-footer d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Close
                        </button>
                        <button type="submit" class="btn btn-success text-white">
                            <i class="fas fa-save me-1"></i> Save User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editInfoModal" tabindex="-1" aria-labelledby="editInfoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title" id="editInfoModalLabel">
                        <i class="ti ti-user-edit me-2"></i> Edit User Information
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="editInfoForm">
                    @csrf
                    <input type="hidden" name="id" id="edituserId">

                    <!-- Modal Body -->
                    <div class="modal-body p-4">

                        <!-- Section 1: Personal Details -->
                        <h6 class="text-uppercase text-muted fw-bold mb-3 small">1. Personal Information</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label for="editfirstname" class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" id="editfirstname" name="fname" oninput="this.value = this.value.toUpperCase()" placeholder="Enter First Name" required>
                            </div>

                            <div class="col-md-3">
                                <label for="editmiddlename" class="form-label fw-semibold">Middle Name</label>
                                <input type="text" class="form-control form-control-sm" id="editmiddlename" name="mname" oninput="this.value = this.value.toUpperCase()" placeholder="Enter Middle Name">
                            </div>

                            <div class="col-md-3">
                                <label for="editlastname" class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" id="editlastname" name="lname" oninput="this.value = this.value.toUpperCase()" placeholder="Enter Last Name" required>
                            </div>

                            <div class="col-md-3">
                                <label for="editextension" class="form-label fw-semibold">Extension Name</label>
                                <select name="ext" class="form-control form-control-sm" id="editextension">
                                    <option value="">--- Select Extension ---</option>
                                    <option value="Jr.">Jr.</option>
                                    <option value="Sr.">Sr.</option>
                                    <option value="II">II</option>
                                    <option value="III">III</option>
                                    <option value="IV">IV</option>
                                    <option value="V">V</option>
                                    <option value="VI">VI</option>
                                </select>
                            </div>
                        </div>

                        <hr class="my-3 text-muted opacity-25">

                        <!-- Section 2: Account Details -->
                        <h6 class="text-uppercase text-muted fw-bold mb-3 small">2. Account Credentials & Demographics</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label for="edituseremail" class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control form-control-sm" id="edituseremail" name="email" placeholder="Enter Email" required>
                            </div>

                            <div class="col-md-4">
                                <label for="editoffice" class="form-label fw-semibold">Office <span class="text-danger">*</span></label>
                                <select name="office_id" id="editoffice" class="form-control form-control-sm select2" required>
                                    <option value="" disabled>--- Select Office ---</option>
                                    @foreach ($offices as $dataoff)
                                        <option value="{{ $dataoff->id }}">{{ $dataoff->office_abbr }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="editgender" class="form-label fw-semibold">Gender <span class="text-danger">*</span></label>
                                <select name="gender" class="form-control form-control-sm" id="editgender" required>
                                    <option value="" disabled>--- Select Gender ---</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>

                        <hr class="my-3 text-muted opacity-25">

                        <!-- Section 3: Role & Access Control -->
                        <h6 class="text-uppercase text-muted fw-bold mb-3 small">3. Role, Campus & Access Control</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="editrole" class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                                <select name="role" class="form-control form-control-sm" id="editrole" required>
                                    <option value="">--- Select Role ---</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->rolename }}">
                                            {{ $role->rolename }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="editcampus" class="form-label fw-semibold">Campus <span class="text-danger">*</span></label>
                                <select name="campus_id" class="form-control form-control-sm" id="editcampus">
                                    <option value="" disabled>--- Select Campus ---</option>
                                    <option value="MC">Main</option>
                                    <option value="VC">Victorias</option>
                                    <option value="SCC">San Carlos</option>
                                    <option value="HC">Hinigaran</option>
                                    <option value="MP">Moises Padilla</option>
                                    <option value="IC">Ilog</option>
                                    <option value="CA">Candoni</option>
                                    <option value="CC">Cauayan</option>
                                    <option value="SC">Sipalay</option>
                                    <option value="HinC">Hinobaan</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="editpermission" class="form-label fw-semibold">System Permission <span class="text-danger">*</span></label>
                                <select name="isAllowed" class="form-control form-control-sm" id="editpermission" required>
                                    <option value="Yes">Yes Allowed</option>
                                    <option value="No">Not Allowed</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer with Justified Action Buttons -->
                    <div class="modal-footer d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Close
                        </button>
                        <button type="submit" class="btn btn-success text-white">
                            <i class="fas fa-save me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editPasswordModal" tabindex="-1" role="dialog" aria-labelledby="editPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPasswordModalLabel"><i class="ti ti-lock"></i> Edit Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editPasswordForm">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="editPasswordId">
                        <div class="form-group">
                            <label for="editPasswordName" class="form-label fw-semibold">Enter New Password: <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editPasswordName" name="password" minlength="5" required>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success text-light">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="assignTaskCategoryModal" tabindex="-1" role="dialog" aria-labelledby="assignTaskCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignTaskCategoryModalLabel"><i class="ti ti-server"></i> Assign Task Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="assignTaskCategoryForm">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="assignTaskCategoryId">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="assignTaskCategoryName" class="form-label fw-semibold">Name: <span class="text-danger">*</span></label>
                                <input type="text" class="form-control fw-semibold" value="{{ Auth::user()->fname }} {{ Auth::user()->mname ?? '' }} {{ Auth::user()->lname }}" name="user" readonly>
                            </div>
                            <div class="col-md-12">
                                <label for="assignTaskCategoryName" class="form-label fw-semibold">Select & asssign Task Category: <span class="text-danger">*</span></label>
                                <select class="form-control select2bs4" id="assignTaskCategoryName" name="taskassigned[]" multiple="multiple">
                                    @foreach ($cat as $category)
                                        <option value="{{ $category->id }}">
                                            {{ $category->ticketcatname }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success text-light">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editUstatusModal" tabindex="-1" role="dialog" aria-labelledby="editUstatusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUstatusModalLabel"><i class="ti ti-toggle-left me-2"></i> Edit User Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editUstatusForm">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="editUstatusId">
                        <div class="form-group">
                            <label for="editUstatusName">Select User Status: <span class="text-danger">*</span></label>
                            <select name="ustatus" id="editUstatusName" class="form-control">
                                <option disabled selected> --Select-- </option>
                                <option value="1">Enabled</option>
                                <option value="2">Disabled</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success text-light">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        var userReadRoute = "{{ route('user.show') }}";
        var userCreateRoute = "{{ route('user.create') }}";
        var userUpdateRoute = "{{ route('user.update', ['id' => ':id']) }}";
        var userPassUpdateRoute = "{{ route('userUpdatePassword', ['id' => ':id']) }}";
        var userStatusUpdateRoute = "{{ route('userUpdateStatus', ['id' => ':id']) }}";

        var isAdmin = '{{ Auth::guard("web")->user()->role == "Administrator" ? true : false }}';
        var isChecker = '{{ Auth::guard("web")->user()->role == "Checker" ? true : false }}';
    </script>
@endsection
