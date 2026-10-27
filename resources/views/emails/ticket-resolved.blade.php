@php
    $recipientName = trim($recipient->fname ?? '') ?: 'there';
    $resolverName = trim(($ticket->resolver->fname ?? '') . ' ' . ($ticket->resolver->lname ?? '')) ?: 'N/A';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #{{ $ticket->ticket_number }} Resolved</title>
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
                            <div style="font-size:22px; font-weight:bold; margin-top:4px;">Your Ticket has been Resolved</div>
                        </td>
                    </tr>

                    <!-- Intro -->
                    <tr>
                        <td style="padding:24px 28px 8px;">
                            <p style="margin:0 0 12px; font-size:15px;">Hi {{ $recipientName }},</p>
                            <p style="margin:0; font-size:14px; line-height:1.6; color:#525252;">
                                Good news! Your ticket request has been <strong>resolved</strong> by our support team. Please see the resolution details below.
                            </p>
                        </td>
                    </tr>

                    <!-- Ticket number + status -->
                    <tr>
                        <td style="padding:16px 28px 0;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#ccf4dc; border-radius:8px;">
                                <tr>
                                    <td style="padding:14px 16px;">
                                        <div style="font-size:12px; color:#005020;">Ticket Number</div>
                                        <div style="font-size:18px; font-weight:bold; color:#005020;">{{ $ticket->ticket_number }}</div>
                                    </td>
                                    <td align="right" style="padding:14px 16px;">
                                        <span style="display:inline-block; padding:5px 12px; border-radius:20px; font-size:12px; font-weight:bold; background-color:#00c951; color:#ffffff;">
                                            &#10003; {{ $ticket->status }}
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
                                    <td style="padding:9px 0; color:#737373; width:38%; border-bottom:1px solid #f0f0f0;">Category</td>
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
                                    <td style="padding:9px 0; color:#737373; border-bottom:1px solid #f0f0f0;">Date Resolved</td>
                                    <td style="padding:9px 0; font-weight:600; border-bottom:1px solid #f0f0f0;">{{ optional($ticket->resolved_at)->format('F d, Y h:i A') }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:9px 0; color:#737373; border-bottom:1px solid #f0f0f0;">Resolved By</td>
                                    <td style="padding:9px 0; font-weight:600; border-bottom:1px solid #f0f0f0;">{{ $resolverName }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:9px 0; color:#737373;">Resolution Time</td>
                                    <td style="padding:9px 0; font-weight:600;">{{ $ticket->duration ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Issue description -->
                    <tr>
                        <td style="padding:20px 28px 0;">
                            <div style="font-size:13px; font-weight:bold; color:#525252; margin-bottom:6px;">Your Issue</div>
                            <div style="background-color:#fafafa; border-left:4px solid #a3a3a3; border-radius:4px; padding:12px 14px; font-size:14px; line-height:1.6;">
                                {!! nl2br(e($ticket->issue_description)) !!}
                            </div>
                        </td>
                    </tr>

                    <!-- Resolution remarks -->
                    <tr>
                        <td style="padding:16px 28px 0;">
                            <div style="font-size:13px; font-weight:bold; color:#525252; margin-bottom:6px;">Resolution Remarks</div>
                            <div style="background-color:#f0faf4; border-left:4px solid #65ab85; border-radius:4px; padding:12px 14px; font-size:14px; line-height:1.6;">
                                {!! nl2br(e($ticket->resolution_remarks ?: 'No remarks provided.')) !!}
                            </div>
                        </td>
                    </tr>

                    <!-- Feedback note -->
                    <tr>
                        <td style="padding:20px 28px 0;">
                            <p style="margin:0; font-size:14px; line-height:1.6; color:#525252;">
                                If the issue still persists, you may reopen the ticket from your ticket list. We'd also appreciate it if you could take a moment to <strong>rate your experience</strong> &mdash; your feedback helps us improve our service.
                            </p>
                        </td>
                    </tr>

                    <!-- Button -->
                    <tr>
                        <td align="center" style="padding:28px 28px 8px;">
                            <a href="{{ route('ticketsrequester.index') }}" style="display:inline-block; background-color:#65ab85; color:#ffffff; text-decoration:none; font-weight:bold; font-size:14px; padding:12px 28px; border-radius:6px;">
                                View My Tickets &amp; Give Feedback
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
