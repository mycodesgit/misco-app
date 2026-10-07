@extends('layouts.app')

@section('title')
    {{ auth()->user()->pageTitle('My Account') }}
@endsection

@section('body')
    <div class="row">
        <div class="col-12">
            <div class="mb-4">

                <!-- Dashboard Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <div>
                        <h1 class="h4 fw-bold mb-1">{{ auth()->user()->pageTitle('My Account') }}</h1>
                        <p class="text-muted small mb-0">View your personal information and update your password.</p>
                    </div>
                </div>

                <!-- Top Row: Metric Cards -->
                <div class="row g-3 mb-5">
                    <div class="col-md-4">
                        <div class="card card-animate">
                            <div class="card-header pt-3">
                                <h6 class="card-title">
                                    <i class="ti ti-user"></i> Personal Information
                                </h6>
                            </div>
                            <div class="card-body text-center">
                                @php
                                    $fname = $user->fname ?? '';
                                    $lname = $user->lname ?? '';
                                    $initials = strtoupper(substr($fname, 0, 1) . substr($lname, 0, 1));
                                @endphp
                                <div class="avatar-circle-lg bg-primary bg-opacity-10 text-primary fw-bold mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle" style="width: 72px; height: 72px; font-size: 1.5rem;">
                                    {{ $initials ?: 'U' }}
                                </div>
                                <h5 class="fw-bold mb-0">{{ $fname }} {{ $user->mname ?? '' }} {{ $lname }} {{ $user->ext ?? '' }}</h5>
                                <p class="text-muted small mb-3">{{ $user->email }}</p>

                                <ul class="list-group list-group-flush text-start">
                                    <li class="list-group-item d-flex justify-content-between px-0">
                                        <span class="text-muted small">Office</span>
                                        <span class="fw-semibold small">{{ $user->office->office_abbr ?? 'N/A' }}</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between px-0">
                                        <span class="text-muted small">Role</span>
                                        <span class="fw-semibold small">{{ $user->role }}</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between px-0">
                                        <span class="text-muted small">Gender</span>
                                        <span class="fw-semibold small">{{ $user->gender ?? 'N/A' }}</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between px-0">
                                        <span class="text-muted small">Status</span>
                                        <span class="badge {{ ($user->ustatus ?? 1) == 1 ? 'bg-success' : 'bg-secondary' }}">{{ ($user->ustatus ?? 1) == 1 ? 'Active' : 'Inactive' }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="card card-animate">
                            <div class="card-header pt-3">
                                <h6 class="card-title">
                                    <i class="ti ti-lock"></i> Update Password
                                </h6>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="#" id="updatePasswordForm">
                                    @csrf

                                    <div class="form-group">
                                        <div class="row g-3">
                                            <div class="col-md-12">
                                                <label class="form-label fw-semibold">Current Password: <span class="text-danger">*</span></label>
                                                <input type="password" name="current_password" class="form-control form-control-sm" placeholder="Enter current password" required autocomplete="current-password">
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label fw-semibold">New Password: <span class="text-danger">*</span></label>
                                                <input type="password" name="password" class="form-control form-control-sm" placeholder="Minimum 8 characters" required autocomplete="new-password">
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label fw-semibold">Confirm New Password: <span class="text-danger">*</span></label>
                                                <input type="password" name="password_confirmation" class="form-control form-control-sm" placeholder="Re-type new password" required autocomplete="new-password">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mt-3">
                                        <div class="row">
                                            <div class="col-md-12 d-flex justify-content-between">
                                                <button type="reset" class="btn btn-light"><i class="ti ti-restore"></i> Clear</button>
                                                <button type="submit" class="btn btn-success text-white"><i class="ti ti-device-floppy"></i> Update Password</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        var accountPasswordRoute = "{{ route('account.password') }}";
    </script>
@endsection
