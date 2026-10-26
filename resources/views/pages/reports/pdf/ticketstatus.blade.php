<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket Status Report</title>
    <style>
        @page {
            margin: 24px 26px 30px 26px;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 7.5pt;
            color: #222;
        }

        .header {
            text-align: center;
        }

        .header img {
            width: 70%;
            height: 23%;
        }

        h1 {
            font-size: 15pt;
            font-family: Arial, sans-serif;
            font-weight: bold;
            text-align: center;
            margin: 10px 0 3px 0;
        }

        .period {
            text-align: center;
            font-family: Arial, sans-serif;
            font-size: 9pt;
            margin: 0 0 10px 0;
        }

        .scope {
            font-size: 8pt;
            color: #555;
            margin: 0 0 10px 0;
        }

        .summary {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .summary td {
            border: 1px solid #bbbbbb;
            text-align: center;
            padding: 6px 4px;
            width: 20%;
        }

        .summary .summary-label {
            font-size: 7pt;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .summary .summary-value {
            font-size: 15pt;
            font-weight: bold;
        }

        .status-pending {
            background-color: #fff3cd;
        }

        .status-progress {
            background-color: #cff4fc;
        }

        .status-resolved {
            background-color: #d1e7dd;
        }

        .status-cancelled {
            background-color: #f8d7da;
        }

        .status-total {
            background-color: #e9ecef;
        }

        table.detail {
            width: 100%;
            border-collapse: collapse;
            font-size: 6.8pt;
        }

        table.detail th {
            background-color: #04401f;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 4px 3px;
            border: 1px solid #9aa39d;
        }

        table.detail td {
            border: 1px solid #bbbbbb;
            padding: 3px;
            vertical-align: top;
            word-wrap: break-word;
        }

        table.detail tr:nth-child(even) td {
            background-color: #f8f9fa;
        }

        .status-pill {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 6.5pt;
        }

        .empty {
            text-align: center;
            padding: 12px;
            color: #777;
        }

        .footer {
            position: absolute;
            left: 0;
            right: 0;
            font-size: 6.5pt;
            color: #777;
            text-align: center;
        }
        .footer-logo-container {
            position: absolute;
            left: 0;
            right: 0;
            bottom: -10px;
            width: 100%;
            text-align: center;
        }

        .footer-logo-container img {
            width: 70%;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('uilibs/images/headerLogoNew1.png') }}" alt="Header Logo">
    </div>

    <h1>Ticket Status Report</h1>
    <p class="period">{{ $periodLabel }}</p>

    <p class="scope">
        <strong>Support Office:</strong> {{ $officeLabel }}
        &nbsp;|&nbsp;
        <strong>Detailed list:</strong> {{ $statusFilter ?: 'All Statuses' }}
        &nbsp;|&nbsp;
        <strong>Total tickets:</strong> {{ $totalTickets }}
    </p>

    {{-- Summary of every status in DailyTicketRequest --}}
    <table class="summary">
        <tr>
            <td class="status-pending">
                <div class="summary-label">Pending</div>
                <div class="summary-value">{{ $summary['Pending'] ?? 0 }}</div>
            </td>
            <td class="status-progress">
                <div class="summary-label">In Progress</div>
                <div class="summary-value">{{ $summary['In Progress'] ?? 0 }}</div>
            </td>
            <td class="status-resolved">
                <div class="summary-label">Resolved</div>
                <div class="summary-value">{{ $summary['Resolved'] ?? 0 }}</div>
            </td>
            <td class="status-cancelled">
                <div class="summary-label">Cancelled</div>
                <div class="summary-value">{{ $summary['Cancelled'] ?? 0 }}</div>
            </td>
            <td class="status-total">
                <div class="summary-label">Total</div>
                <div class="summary-value">{{ $totalTickets }}</div>
            </td>
        </tr>
    </table>

    @php
        $statusStyles = [
            'Pending'     => 'background-color:#fff3cd;color:#664d03;',
            'In Progress' => 'background-color:#cff4fc;color:#055160;',
            'Resolved'    => 'background-color:#d1e7dd;color:#0f5132;',
            'Cancelled'   => 'background-color:#f8d7da;color:#842029;',
        ];
    @endphp

    <table class="detail">
        <thead>
            <tr>
                <th width="3%">No.</th>
                <th width="10%">Ticket No.</th>
                <th width="10%">Requester</th>
                <th width="6%">Req. Office</th>
                <th width="10%">Category</th>
                <th width="10%">Sub Category</th>
                <th width="6%">Priority</th>
                <th width="7%">Status</th>
                <th width="9%">Assigned To</th>
                <th width="9%">Resolved By</th>
                <th width="10%">Date Created</th>
                <th width="10%">Date Resolved</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($tickets as $index => $ticket)
                @php
                    $requester = $ticket->requester;
                    $resolver = $ticket->resolved_by ? $ticket->resolver : null;
                    $assigned = $ticket->assigned_to ? $ticket->assignedStaff : null;

                    $requesterName = $requester
                        ? trim(($requester->fname ?? '') . ' ' . ($requester->lname ?? ''))
                        : 'Unknown';

                    $resolverName = $resolver
                        ? trim(($resolver->fname ?? '') . ' ' . ($resolver->lname ?? ''))
                        : '-';

                    $assignedName = $assigned
                        ? trim(($assigned->fname ?? '') . ' ' . ($assigned->lname ?? ''))
                        : '-';
                @endphp
                <tr>
                    <td style="text-align:center;">{{ $index + 1 }}</td>
                    <td><strong>{{ $ticket->ticket_number }}</strong></td>
                    <td>{{ $requesterName }}</td>
                    <td>{{ $ticket->requesteroffice->office_abbr ?? '-' }}</td>
                    <td>{{ $ticket->category->ticketcatname ?? '-' }}</td>
                    <td>{{ $ticket->subcategory->ticketsubcatname ?? '-' }}</td>
                    <td>{{ $ticket->priority }}</td>
                    <td>
                        <span class="status-pill" style="{{ $statusStyles[$ticket->status] ?? '' }}">
                            {{ $ticket->status }}
                        </span>
                    </td>
                    <td>{{ $assignedName }}</td>
                    <td>{{ $resolverName }}</td>
                    <td>{{ $ticket->created_at?->format('M d, Y h:i A') ?? '-' }}</td>
                    <td>{{ $ticket->resolved_at?->format('M d, Y h:i A') ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="empty">
                        No tickets found for the selected month, office, and status.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ $generatedAt }} by {{ trim((auth()->user()->fname ?? '') . ' ' . (auth()->user()->lname ?? '')) ?: 'System' }}
    </div>

    <div class="footer-logo-container">
        <img src="{{ public_path('uilibs/images/footerLogoNew.png') }}">
    </div>
</body>
</html>
