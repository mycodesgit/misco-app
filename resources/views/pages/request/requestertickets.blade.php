@extends('layouts.app')

@section('title')
    {{ auth()->user()->pageTitle('Ticketing Requests') }}
@endsection

@section('body')
    <div class="row">
        <div class="col-12">
            <div class="mb-4">

                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <div>
                        <h1 class="h4 fw-bold mb-1">{{ auth()->user()->pageTitle('Tickets') }}</h1>
                        <p class="text-muted small mb-0">View your submitted issues, track ongoing requests, and review closed tickets.</p>
                    </div>
                    <button class="btn btn-success text-white" data-bs-toggle="modal" data-bs-target="#createNewTicketModal">
                        <i class="ti ti-plus me-1"></i> Submit New Ticket
                    </button>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-12">
                        <ul class="nav nav-pills bg-light p-2 card-animate rounded-2 d-inline-flex mb-2" id="pills-tab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="pills-one-tab" data-bs-toggle="pill"
                                    data-bs-target="#pills-one" type="button" role="tab"
                                    aria-controls="pills-one" aria-selected="true">
                                    Pending Tickets
                                </button>
                            </li>
                            &nbsp;
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="pills-two-tab" data-bs-toggle="pill"
                                    data-bs-target="#pills-two" type="button" role="tab"
                                    aria-controls="pills-two" aria-selected="false" tabindex="-1">
                                    In Progress Tickets
                                </button>
                            </li>
                            &nbsp;
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="pills-three-tab" data-bs-toggle="pill"
                                    data-bs-target="#pills-three" type="button" role="tab"
                                    aria-controls="pills-three" aria-selected="false" tabindex="-1">
                                    Resolved Tickets
                                </button>
                            </li>
                            &nbsp;
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="pills-four-tab" data-bs-toggle="pill"
                                    data-bs-target="#pills-four" type="button" role="tab"
                                    aria-controls="pills-four" aria-selected="false" tabindex="-1">
                                    Closed Tickets
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-12">
                        <div class="tab-content" id="pills-tabContent">
                            <div class="tab-pane fade show active" id="pills-one" role="tabpanel" aria-labelledby="pills-one-tab" tabindex="0">
                                <div class="card card-animate">
                                    <div class="card-header pt-3">
                                        <h6 class="card-title">
                                            <i class="fas fa-server"></i> List of all Pending Ticket Section
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive p-2">
                                            <table id="ticketpendingTable" class="table table-hover" style="width: 100%">
                                                <thead>
                                                    <tr>
                                                        <th>Ticket No.</th>
                                                        <th>Requester</th>
                                                        <th>Subject</th>
                                                        <th>Category</th>
                                                        <th>Sub-Category</th>
                                                        <th>Priority</th>
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
                            <div class="tab-pane fade" id="pills-two" role="tabpanel" aria-labelledby="pills-two-tab" tabindex="0">
                                <div class="card card-animate">
                                    <div class="card-header pt-3">
                                        <h6 class="card-title">
                                            <i class="fas fa-server"></i> List of all In-progress Ticket Section
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive p-2">
                                            <table id="ticketprogressTable" class="table table-hover" style="width: 100%">
                                                <thead>
                                                    <tr>
                                                        <th>Ticket No.</th>
                                                        <th>Requester</th>
                                                        <th>Subject</th>
                                                        <th>Category</th>
                                                        <th>Sub-Category</th>
                                                        <th>Priority</th>
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
                            <div class="tab-pane fade" id="pills-three" role="tabpanel" aria-labelledby="pills-three-tab" tabindex="0">
                                <div class="card card-animate">
                                    <div class="card-header pt-3">
                                        <h6 class="card-title">
                                            <i class="fas fa-server"></i> List of all Resolved Ticket Section
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive p-2">
                                            <table id="ticketresolvedTable" class="table table-hover" style="width: 100%">
                                                <thead>
                                                    <tr>
                                                        <th>Ticket No.</th>
                                                        <th>Requester</th>
                                                        <th>Subject</th>
                                                        <th>Category</th>
                                                        <th>Sub-Category</th>
                                                        <th>Priority</th>
                                                        <th>Status</th>
                                                        <th>Resolved By</th>
                                                        <th>Date Created</th>
                                                        <th>Date Resolved</th>
                                                        <th>Duration</th>
                                                        <th width="20%">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="pills-four" role="tabpanel" aria-labelledby="pills-four-tab" tabindex="0">
                                <div class="card card-animate">
                                    <div class="card-header pt-3">
                                        <h6 class="card-title">
                                            <i class="fas fa-server"></i> List of all Closed Ticket Section
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive p-2">
                                            <table id="ticketclosedTable" class="table table-hover" style="width: 100%">
                                                <thead>
                                                    <tr>
                                                        <th>Ticket No.</th>
                                                        <th>Requester</th>
                                                        <th>Subject</th>
                                                        <th>Category</th>
                                                        <th>Sub-Category</th>
                                                        <th>Priority</th>
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

    @include('pages.request.modals.addticketmodal')
    @include('pages.request.modals.editticketmodal')

    <div class="modal fade" id="submitfeedTicketModal" tabindex="-1" aria-labelledby="submitfeedTicketModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 p-2">
                <div class="bg-white px-4 pt-4 pb-3" style="border-radius: 16px;">
                    <!-- Modal Header -->
                    <div class="d-flex justify-content-between align-items-start border-0 p-0 mb-1">
                        <div class="w-100 text-center">
                            <h5 class="fw-bold mb-0" id="submitfeedTicketModalLabel" style="font-size: 1.1rem; color: #111;">
                                How was your experience?
                            </h5>
                            <small class="text-muted" id="feedbackTicketNo">Ticket #</small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <form action="#" method="POST" id="submitFeedbackForm">
                        @csrf
                        <input type="hidden" name="ticket_id" id="feedback_ticket_id">
                        <input type="hidden" name="rating" id="feedback_rating" value="">

                        <!-- Emoji rating -->
                        <div class="d-flex justify-content-center align-items-start gap-2 my-3" id="emojiRatingGroup">
                            <button type="button" class="emoji-btn" data-value="1" title="Very Poor">
                                <span class="emoji-icon">😣</span>
                                <span class="emoji-label">Very Poor</span>
                            </button>
                            <button type="button" class="emoji-btn" data-value="2" title="Poor">
                                <span class="emoji-icon">😟</span>
                                <span class="emoji-label">Poor</span>
                            </button>
                            <button type="button" class="emoji-btn" data-value="3" title="Average">
                                <span class="emoji-icon">😊</span>
                                <span class="emoji-label">Average</span>
                            </button>
                            <button type="button" class="emoji-btn" data-value="4" title="Good">
                                <span class="emoji-icon">😄</span>
                                <span class="emoji-label">Good</span>
                            </button>
                            <button type="button" class="emoji-btn" data-value="5" title="Excellent">
                                <span class="emoji-icon">🤩</span>
                                <span class="emoji-label">Excellent</span>
                            </button>
                        </div>
                        <p class="text-center small mb-3">Choose your experience</p>

                        <!-- Suggestion -->
                        <div class="mb-3">
                            <textarea class="form-control border-0" id="feedback_text" name="feedback" rows="3" placeholder="Suggest anything we can improve.." style="background: #f3f6ff; border-radius: 12px; resize: none;"></textarea>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Your input helps us enhance our service. Tap the emoji that best represents your experience and share any comments.</small>
                        </div>

                        <!-- Submit -->
                        <button type="submit" class="btn btn-success w-100 text-white fw-semibold" style="border-radius: 999px; padding: 10px 0;">
                            Send Feedback
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        #emojiRatingGroup .emoji-btn {
            cursor: pointer;
            opacity: .55;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
            padding: 0 2px;
            background: none;
            border: none;
            transition: transform .15s;
        }
        #emojiRatingGroup .emoji-icon {
            font-size: 2.75rem;
            line-height: 1;
            filter: grayscale(0);
        }
        #emojiRatingGroup .emoji-label {
            font-size: .72rem;
            font-weight: 600;
            line-height: 1.2;
            white-space: nowrap;
            color: #6c757d;
        }
        #emojiRatingGroup .emoji-btn:hover {
            transform: scale(1.15);
            opacity: 1;
        }
        #emojiRatingGroup .emoji-btn:hover .emoji-label {
            color: #111;
        }
        #emojiRatingGroup .emoji-btn.active {
            transform: scale(1.25);
            opacity: 1;
            filter: drop-shadow(0 3px 6px rgba(0,0,0,.25)) !important;
        }
        #emojiRatingGroup .emoji-btn.active .emoji-label {
            color: #111;
        }
    </style>

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
        var ticketPendingReadRoute = "{{ route('tickets.showreqpending') }}";
        var ticketProgressRoute = "{{ route('tickets.showreqprogress') }}";
        var ticketResolvedRoute = "{{ route('tickets.showreqresolved') }}";
        var ticketClosedRoute = "{{ route('tickets.showreqclosed') }}";
        var ticketCreateRoute = "{{ route('ticketsrequester.create') }}";
        var ticketFeedbackShowBase = "{{ url('/tickets/requester/tickets/feedback') }}";
        var ticketFeedbackSubmitRoute = "{{ route('ticketsrequester.feedback.submit') }}";
        var ticketEditBase = "{{ url('/tickets/requester/tickets/edit') }}";
        var ticketUpdateRoute = "{{ route('ticketsrequester.update') }}";
        var ticketReopenRoute = "{{ route('ticketsrequester.reopen') }}";

        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function(){
                var output = document.getElementById('image-preview');
                output.src = reader.result;
                document.getElementById('preview-container').classList.remove('d-none');
            };
            if(event.target.files[0]) {
                reader.readAsDataURL(event.target.files[0]);
            }
        }
    </script>
@endsection
