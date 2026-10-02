<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Accomplishment Report</title>
    <style>
        body {
            font-size: 9pt;
            font-family: 'Times New Roman', serif;
        }
        .title_task {
            font-size: 12pt;
            font-family: Arial, sans-serif;
            font-weight: bold;
            text-align: center;
        }
        .date_task {
            margin-top: -10px;
            font-size: 11pt;
            font-family: Arial, sans-serif;
            text-align: center;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            font-family: Arial, sans-serif;
            font-size: 11px;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border: 1px solid #bbb;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tfoot td {
            font-weight: bold;
        }
        .name_person {
            padding-right: 50px;
            padding-top: 30px;
            font-size: 11pt;
            font-weight: bold;
            font-family: Arial, sans-serif;
            text-align: right;
        }
        .position_person {
            padding-right: 100px;
            margin-top: -30px;
            font-size: 11pt;
            font-family: Arial, sans-serif;
            text-align: right;
        }
        .supervisor_person {
            padding-left: 30px;
            padding-top: 30px;
            font-size: 11pt;
            font-weight: bold;
            font-family: Arial, sans-serif;
            text-align: left;
        }
        .supervisor_position {
            padding-left: 35px;
            margin-top: -30px;
            font-size: 11pt;
            font-family: Arial, sans-serif;
            text-align: left;
        }
        .footer-logo-container {
            position: absolute;
            left: 0;
            bottom: -10px;
            width: 100%;
            text-align: center;
        }
        .footer-logo {
            max-width: 100%;
            max-height: 20%;
            display: inline-block;
            vertical-align: bottom;
        }
    </style>
</head>
<body>
    <div>
        <center>
            <img src="{{ public_path('uilibs/images/headerLogoNew1.png') }}" width="95%" height="14%" style="margin-top: -8px;">
        </center>
    </div>

    <div>
        <p class="title_task">Accomplishment Report</p>
    </div>

    <div>
        @php
            use Carbon\Carbon;

            $startDateInput = request('start_date');
            $endDateInput   = request('end_date');

            if ($startDateInput && $endDateInput) {
                $start = Carbon::parse($startDateInput);
                $end   = Carbon::parse($endDateInput);

                // Same Month & Year -> "October 01 - 15, 2026"
                if ($start->format('Y-m') === $end->format('Y-m')) {
                    $dateRangeText = $start->format('F d') . ' - ' . $end->format('d, Y');
                }
                // Same Year, Different Month -> "October 15 - November 01, 2026"
                elseif ($start->format('Y') === $end->format('Y')) {
                    $dateRangeText = $start->format('F d') . ' - ' . $end->format('F d, Y');
                }
                // Different Year -> "December 20, 2025 - January 05, 2026"
                else {
                    $dateRangeText = $start->format('F d, Y') . ' - ' . $end->format('F d, Y');
                }
            } else {
                $dateRangeText = 'All Tasks';
            }
        @endphp

        <p class="date_task">{{ $dateRangeText }}</p>
    </div>

    <div style="padding-right: 20px; padding-left: 20px;">
        <table>
            <thead>
                <tr>
                    <th width="5%">No.</th>
	                <th>Category</th>
                    <th>Sub Category</th>
	                <th>Task/Accomodation</th>
                </tr>
            </thead>
            <tbody>
                {{-- @if ($groupedAccomplishments->isEmpty())
                    <tr>
                        <td colspan="4" style="text-align: center;">No Accomplishment Report Found.</td>
                    </tr>
                @else
                    @php $no = 1; @endphp
                    @foreach ($groupedAccomplishments as $group)
                        @php
                            // Retrieve category and subcategory from the first item in the group
                            $firstItem = $group->first();
                        @endphp
                        <tr>
                            <td style="text-align: center; vertical-align: top;">{{ $no++ }}</td>
                            <td style="vertical-align: top;">{{ $firstItem->category->ticketcatname ?? '-' }}</td>
                            <td style="vertical-align: top;">{{ $firstItem->subcategory->ticketsubcatname ?? '-' }}</td>
                            <td>
                                <ul style="margin: 0; padding-left: 15px;">
                                    @foreach ($group as $item)
                                        <li>{{ $item->dailytaskdesc }}</li>
                                    @endforeach
                                </ul>
                            </td>
                        </tr>
                    @endforeach
                @endif --}}
                @if ($groupedAccomplishments->isEmpty())
                    <tr>
                        <td colspan="4" style="text-align: center;">No Accomplishment Report Found.</td>
                    </tr>
                @else
                    @php $no = 1; @endphp
                    @foreach ($groupedAccomplishments as $group)
                        @php
                            $firstItem = $group->first();
                            $taskCount = $group->count();
                            $subcatName = $firstItem->subcategory->ticketsubcatname ?? 'item(s)';
                        @endphp
                        <tr>
                            <td style="text-align: center; vertical-align: top;">{{ $no++ }}</td>
                            <td style="vertical-align: top;">{{ $firstItem->category->ticketcatname ?? '-' }}</td>
                            <td style="vertical-align: top;">{{ $subcatName }}</td>
                            <td>
                                <strong>Completed {{ $taskCount }} {{ $subcatName }} request(s):</strong>
                                <ul style="margin: 5px 0 0 0; padding-left: 15px;">
                                    @foreach ($group as $item)
                                        <li>{{ $item->dailytaskdesc }}</li>
                                    @endforeach
                                </ul>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

    <div class="name_person">
        <p>{{ Auth::user()->fname ?? '' }} {{ Auth::user()->mname ? Auth::user()->mname . '.' : '' }} {{ Auth::user()->lname ?? '' }}</p>
    </div>

    <div class="position_person">
        <p>MIS Staff</p>
    </div>

    <div class="supervisor_person">
        <p>&nbsp;&nbsp;JOHN DOE</p>
    </div>

    <div class="supervisor_position">
        <p>Immediate Supervisor</p>
    </div>

    <div class="footer-logo-container">
        <img src="{{ public_path('uilibs/images/footerLogoNew.png') }}" class="footer-logo" width="100%">
    </div>

</body>
</html>
