@php
    $priorityColors = [
        'Low'    => ['bg' => '#ccf4dc', 'text' => '#005020'],
        'Medium' => ['bg' => '#ccf1f8', 'text' => '#004a58'],
        'High'   => ['bg' => '#fcefcc', 'text' => '#604700'],
        'Urgent' => ['bg' => '#fed5d7', 'text' => '#641216'],
    ];
    $priority = $priorityColors[$ticket->priority] ?? ['bg' => '#f5f5f5', 'text' => '#404040'];

    $requesterName = trim(($ticket->requester->fname ?? '') . ' ' . ($ticket->requester->lname ?? '')) ?: 'N/A';
    $recipientName = trim($recipient->fname ?? '') ?: 'there';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Ticket Request #{{ $ticket->ticket_number }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f5f5f5; font-family:'Poppins', Arial, Helvetica, sans-serif; color:#171717;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f5f5f5; padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px; background-color:#ffffff; border-radius:10px; overflow:hidden; border:1px solid #e5e5e5;">

                    <!-- Header -->
                    <tr>
                        <td style="background-color:#65ab85; padding:24px 28px; color:#ffffff;">
                            <div style="font-size:13px; letter-spacing:1px; text-transform:uppercase; opacity:0.9;">ServiceNow Ticketing System</div>
                            <div style="font-size:22px; font-weight:bold; margin-top:4px;">New Ticket Request</div>
                        </td>
                    </tr>

                    <!-- Intro -->
                    <tr>
                        <td style="padding:24px 28px 8px;">
                            <p style="margin:0 0 12px; font-size:15px;">Hi {{ $recipientName }},</p>
                            <p style="margin:0; font-size:14px; line-height:1.6; color:#525252;">
                                A new ticket has been submitted and <strong>assigned to you</strong>. Please review the details below and take action as soon as possible.
                            </p>
                        </td>
                    </tr>

                    <!-- Ticket number + priority -->
                    <tr>
                        <td style="padding:16px 28px 0;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#fae0d7; border-radius:8px;">
                                <tr>
                                    <td style="padding:14px 16px;">
                                        <div style="font-size:12px; color:#5c2717;">Ticket Number</div>
                                        <div style="font-size:18px; font-weight:bold; color:#5c2717;">{{ $ticket->ticket_number }}</div>
                                    </td>
                                    <td align="right" style="padding:14px 16px;">
                                        <span style="display:inline-block; padding:5px 12px; border-radius:20px; font-size:12px; font-weight:bold; background-color:{{ $priority['bg'] }}; color:{{ $priority['text'] }};">
                                            {{ $ticket->priority }} Priority
                                        </span>
                                        <span style="display:inline-block; padding:5px 12px; border-radius:20px; font-size:12px; font-weight:bold; background-color:#fcefcc; color:#604700; margin-left:4px;">
                                            {{ $ticket->status }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Details -->
                    <tr>
                        <td style="padding:20px 28px 0;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px; border-collapse:collapse;">
                                <tr>
                                    <td style="padding:9px 0; color:#737373; width:38%; border-bottom:1px solid #f0f0f0;">Requested By</td>
                                    <td style="padding:9px 0; font-weight:600; border-bottom:1px solid #f0f0f0;">{{ $requesterName }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:9px 0; color:#737373; border-bottom:1px solid #f0f0f0;">Office</td>
                                    <td style="padding:9px 0; font-weight:600; border-bottom:1px solid #f0f0f0;">{{ $ticket->requesteroffice->office_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:9px 0; color:#737373; border-bottom:1px solid #f0f0f0;">Contact No.</td>
                                    <td style="padding:9px 0; font-weight:600; border-bottom:1px solid #f0f0f0;">{{ $ticket->contactno ?: 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:9px 0; color:#737373; border-bottom:1px solid #f0f0f0;">Category</td>
                                    <td style="padding:9px 0; font-weight:600; border-bottom:1px solid #f0f0f0;">{{ $ticket->category->ticketcatname ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:9px 0; color:#737373; border-bottom:1px solid #f0f0f0;">Subcategory</td>
                                    <td style="padding:9px 0; font-weight:600; border-bottom:1px solid #f0f0f0;">{{ $ticket->subcategory->ticketsubcatname ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:9px 0; color:#737373; border-bottom:1px solid #f0f0f0;">Date Submitted</td>
                                    <td style="padding:9px 0; font-weight:600; border-bottom:1px solid #f0f0f0;">{{ optional($ticket->created_at)->format('F d, Y h:i A') }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:9px 0; color:#737373;">Attachment</td>
                                    <td style="padding:9px 0; font-weight:600;">{{ $ticket->attachment ? 'Yes (view in the system)' : 'None' }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Issue description -->
                    <tr>
                        <td style="padding:20px 28px 0;">
                            <div style="font-size:13px; font-weight:bold; color:#525252; margin-bottom:6px;">Issue Description</div>
                            <div style="background-color:#fafafa; border-left:4px solid #65ab85; border-radius:4px; padding:12px 14px; font-size:14px; line-height:1.6;">
                                {!! nl2br(e($ticket->issue_description)) !!}
                            </div>
                        </td>
                    </tr>

                    @if ($ticket->remarks)
                        <tr>
                            <td style="padding:16px 28px 0;">
                                <div style="font-size:13px; font-weight:bold; color:#525252; margin-bottom:6px;">Remarks / Notes</div>
                                <div style="background-color:#fafafa; border-left:4px solid #a3a3a3; border-radius:4px; padding:12px 14px; font-size:14px; line-height:1.6;">
                                    {!! nl2br(e($ticket->remarks)) !!}
                                </div>
                            </td>
                        </tr>
                    @endif

                    <!-- Button -->
                    <tr>
                        <td align="center" style="padding:28px 28px 8px;">
                            <a href="{{ route('tickets.index') }}" style="display:inline-block; background-color:#65ab85; color:#ffffff; text-decoration:none; font-weight:bold; font-size:14px; padding:12px 28px; border-radius:6px;">
                                View Ticket in System
                            </a>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding:20px 28px 24px; font-size:12px; color:#a3a3a3; text-align:center; line-height:1.6;">
                            This is an automated message from the ServiceNow Ticketing System. Please do not reply to this email.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
