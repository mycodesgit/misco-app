@extends('layouts.app')

@section('title')
    {{ auth()->user()->pageTitle('Office Calendar') }}
@endsection

@section('body')
    <div class="row">
        <div class="col-12">
            <div class="mb-4">

                <!-- Page Header -->
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-4 pb-2 border-bottom">
                    <div>
                        <h1 class="h4 fw-bold mb-1">{{ auth()->user()->pageTitle('Office Calendar') }}</h1>
                        <p class="text-muted small mb-0">
                            {{ $office->office_name ?? 'Your office' }}'s shared Google Calendar &mdash; everyone in your office sees the same events.
                        </p>
                    </div>

                    <!-- Calendar Actions -->
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        @if ($calendarId)
                            <button type="button" class="btn btn-success btn-sm text-white" id="googleSyncBtn" title="Sign in with your CPSU Google account to see the events">
                                <i class="ti ti-brand-google me-1"></i> Sync with Google
                            </button>
                            <button type="button" class="btn btn-light btn-sm" id="calendarRefreshBtn" title="Reload the calendar">
                                <i class="ti ti-refresh"></i>
                            </button>
                            <a href="https://calendar.google.com/calendar/r" target="_blank" rel="noopener" class="btn btn-primary btn-sm" title="Open Google Calendar to add or edit events">
                                <i class="ti ti-external-link me-1"></i> Open in Google Calendar
                            </a>
                            <a href="https://calendar.google.com/calendar/u/0/r?cid={{ urlencode($calendarId) }}" target="_blank" rel="noopener" class="btn btn-light btn-sm" title="Show this calendar in your own Google Calendar (and on your phone)">
                                <i class="ti ti-calendar-plus me-1"></i> Add to my Google Calendar
                            </a>
                        @endif
                        @if ($canManage)
                            <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#calendarSettingsModal" title="Calendar settings">
                                <i class="ti ti-settings"></i>
                            </button>
                        @endif
                    </div>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-12">
                        @if ($calendarId)
                            <!-- Sign-in tip -->
                            <div class="alert alert-info small d-flex align-items-start gap-2 card-animate">
                                <i class="ti ti-info-circle fs-5"></i>
                                <div>
                                    Seeing <strong>"You need permission"</strong> or no events? Click <strong>Sync with Google</strong> and choose your
                                    <strong>CPSU Google account</strong> &mdash; the calendar reloads when you're done.
                                    To add or edit events, use <strong>Open in Google Calendar</strong>; changes show here after a refresh.
                                </div>
                            </div>

                            <div class="card card-animate">
                                <div class="card-header pt-3">
                                    <h6 class="card-title">
                                        <i class="ti ti-calendar-event"></i> {{ $office->office_abbr ?? '' }} Calendar
                                    </h6>
                                </div>
                                <div class="card-body p-2">
                                    <iframe id="googleCalendarFrame"
                                            src="{{ $embedUrl }}"
                                            title="{{ $office->office_name ?? 'Office' }} Google Calendar"
                                            style="border: 0; width: 100%; height: 72vh; min-height: 520px;"
                                            frameborder="0" scrolling="no"></iframe>
                                </div>
                            </div>
                        @else
                            <!-- Not set up yet -->
                            <div class="card card-animate">
                                <div class="card-body text-center text-muted py-5">
                                    <i class="ti ti-calendar-off fs-1 d-block mb-2"></i>
                                    <div class="fw-semibold">Your office has no Google Calendar connected yet</div>
                                    @if ($canManage)
                                        <div class="small mb-3">Connect your office's shared Google Workspace calendar so everyone in your office can see its events here.</div>
                                        <button type="button" class="btn btn-success btn-sm text-white" data-bs-toggle="modal" data-bs-target="#calendarSettingsModal">
                                            <i class="ti ti-link me-1"></i> Connect Google Calendar
                                        </button>
                                    @else
                                        <div class="small">Ask your office's support staff to connect it.</div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($canManage)
        <!-- Calendar Settings Modal -->
        <div class="modal fade" id="calendarSettingsModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="calendarSettingsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="calendarSettingsModalLabel">
                            <i class="ti ti-calendar-cog me-2"></i> {{ $office->office_abbr ?? '' }} Google Calendar
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <form action="#" method="POST" id="calendarSettingsForm">
                        @csrf

                        <div class="modal-body">
                            <h6 class="text-uppercase text-muted fw-bold mb-3 small">1. Prepare the calendar in Google</h6>
                            <ol class="small text-muted mb-4">
                                <li>Open <strong>Google Calendar</strong> with your CPSU account and hover your office calendar &rarr; <strong>&#8942;</strong> &rarr; <strong>Settings and sharing</strong>.</li>
                                <li>Under <strong>Access permissions</strong>, make it available to your organization with <strong>See all event details</strong> &mdash; so every employee can see the events.</li>
                                <li>Under <strong>Share with specific people</strong>, add the staff who post events with <strong>Make changes to events</strong>.</li>
                                <li>Under <strong>Integrate calendar</strong>, copy the <strong>Calendar ID</strong> (or the embed code).</li>
                            </ol>

                            <h6 class="text-uppercase text-muted fw-bold mb-3 small">2. Paste it here</h6>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label for="google_calendar_id" class="form-label fw-semibold">Calendar ID</label>
                                    <input type="text" class="form-control form-control-sm" id="google_calendar_id" name="google_calendar_id"
                                           value="{{ $calendarId }}" placeholder="e.g. c_abc123@group.calendar.google.com">
                                    <div class="form-text">You can also paste the embed URL or the whole &lt;iframe&gt; code. Leave empty to disconnect.</div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                <i class="ti ti-cancel me-1"></i> Close
                            </button>
                            <button type="submit" class="btn btn-success text-white">
                                <i class="ti ti-device-floppy me-1"></i> Save Calendar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <script>
        var calendarUpdateRoute = "{{ route('calendar.update') }}";
    </script>
@endsection
