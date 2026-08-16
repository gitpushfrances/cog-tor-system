    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Transcript of Records</title>
        <style>
            @page { margin: 0.75in 0.7in 0.75in 0.7in; }
            body { font-family: Arial, sans-serif; font-size: 10px; color: #000; line-height: 1.35; }

            .page-break { page-break-after: always; }

            .header-logo { text-align: center; margin-bottom: 6px; }
            .header-logo img { height: 55px; }

            .doc-title { text-align: center; font-size: 13px; font-weight: bold; margin: 6px 0 14px; }

            .info-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 9px; }
            .info-table td { padding: 3px 4px; vertical-align: bottom; }
            .info-label { white-space: nowrap; width: 90px; }
            .info-value { border-bottom: 1px solid #000; font-weight: bold; font-size: 10px; }

            .prelim-title { font-size: 10px; margin: 8px 0 4px; }

            .collegiate-title { text-align: center; font-size: 12px; font-weight: bold; margin: 14px 0 6px; }

            table.record { width: 100%; table-layout: fixed; border-collapse: separate; border-spacing: 0; margin-bottom: 0; }
            table.record th, table.record td { padding: 2.25pt 3.75pt; font-size: 10px; box-sizing: border-box; }
            table.record thead th { border-top: 1.5pt solid #000; border-bottom: 1.5pt solid #000; text-align: center; font-weight: normal; }
            table.record .col-course { width: 23.5%; border-left: 1.5pt solid #000; border-right: 1.5pt solid #000; }
            table.record .col-title { width: 42%; border-right: 1.5pt solid #000; }
            table.record .col-final { width: 10.5%; text-align: center; border-right: 1.5pt solid #000; }
            table.record .col-reexam { width: 11%; text-align: center; border-right: 1.5pt solid #000; }
            table.record .col-credit { width: 13%; text-align: center; border-right: 1.5pt solid #000; }

            .year-label { font-weight: bold; padding: 3pt 3.75pt 1.5pt; text-transform: uppercase; text-decoration: underline; border-left: 1.5pt solid #000; border-right: 1.5pt solid #000; }
            .sem-label { font-weight: bold; padding: 1.5pt 3.75pt 3pt; text-decoration: underline; border-left: 1.5pt solid #000; border-right: 1.5pt solid #000; }

            .subject-row td { padding: 1.5pt 3.75pt; }
            .continuation-row td { border-top: 1.5pt solid #000; border-bottom: 1.5pt solid #000; border-left: 1.5pt solid #000; border-right: 1.5pt solid #000; padding: 3pt 3.75pt; }
            .continuation-row .over-text { text-align: right; font-style: italic; }

            .legend-row { width: 100%; border-collapse: collapse; margin-top: 0; }
            .legend-row td { border-top: 2px solid #000; padding: 5px; font-size: 9px; vertical-align: top; }
            .legend-row .legend-label { font-weight: bold; font-size: 10px; width: 90px; white-space: nowrap; }
            .legend-row .legend-text { }

            .remarks-table { width: 100%; border-collapse: collapse; margin-top: 4px; }
            .remarks-table td { border-top: 2px solid #000; border-bottom: 2px solid #000; padding: 6px 5px; }
            .remarks-label { font-weight: bold; font-style: italic; font-size: 10px; width: 90px; vertical-align: top; }
            .remarks-text { font-size: 10px; }

            .not-valid { font-weight: bold; font-size: 10px; margin: 8px 0 20px; }

            .signature-row { width: 100%; margin-top: 6px; }
            .signature-row td { width: 50%; vertical-align: bottom; padding: 0 5px; }
            .sig-label { font-size: 9px; }
            .sig-name-prepared { font-weight: bold; font-size: 10px; text-align: left; padding-top: 2px; }
            .sig-title-prepared { font-size: 9px; font-style: italic; text-align: left; }
            .sig-label-checked { font-size: 9px; text-align: right; }
            .sig-name-checked { font-weight: bold; font-size: 10px; text-align: center; padding-top: 2px; }
            .sig-title-checked { font-size: 9px; font-style: italic; text-align: center; }

            .campus-admin { text-align: center; margin-top: 18px; }
            .campus-admin-name { font-weight: bold; font-size: 10px; }
            .campus-admin-title { font-size: 9px; font-style: italic; }

            .grad-statement { margin-top: 24px; font-size: 10px; text-align: justify; }
        </style>
    </head>
    <body>

    @foreach($pages as $pageIndex => $page)

        <div class="header-logo">
            @if(file_exists(public_path('images/logo/essu-horizontal.png')))
                <img src="{{ $forPdf ? public_path('images/logo/essu-horizontal.png') : asset('images/logo/essu-horizontal.png') }}">
            @endif
        </div>

        <div class="doc-title">OFFICIAL TRANSCRIPT OF RECORDS</div>

        <table class="info-table">
            <tr>
                <td class="info-label">NAME</td>
                <td class="info-value" style="width: 42%;">{{ strtoupper($student->getFullName()) }}</td>
                <td class="info-label" style="width: 60px;">DATE</td>
                <td class="info-value">{{ $tor->issued_date ? $tor->issued_date->format('F d, Y') : now()->format('F d, Y') }}</td>
            </tr>
            <tr>
                <td class="info-label">ADDRESS</td>
                <td class="info-value">{{ $student->address ?? '' }}</td>
                <td class="info-label">PROGRAM</td>
                <td class="info-value">{{ $student->course->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="info-label">DATE OF BIRTH</td>
                <td class="info-value">{{ $student->date_of_birth ? $student->date_of_birth->format('F d, Y') : '' }}</td>
                <td class="info-label">PLACE OF BIRTH</td>
                <td class="info-value">{{ $student->place_of_birth ?? '' }}</td>
            </tr>
        </table>

        <div class="prelim-title"><strong>PRELIMINARY EDUCATION</strong></div>
        <table class="info-table">
            <tr>
                <td class="info-label">Elementary</td>
                <td class="info-value" style="width: 42%;">{{ $student->elementary_school ?? '' }}</td>
                <td class="info-label" style="width: 110px;">Year of Graduation</td>
                <td class="info-value">{{ $student->elementary_graduation_year ?? '' }}</td>
            </tr>
            <tr>
                <td class="info-label">High School</td>
                <td class="info-value">{{ $student->high_school ?? '' }}</td>
                <td class="info-label">Year of Graduation</td>
                <td class="info-value">{{ $student->high_school_graduation_year ?? '' }}</td>
            </tr>
        </table>

        <div class="collegiate-title">COLLEGIATE RECORD</div>

        <table class="record">
            <colgroup>
                <col style="width: 23.5%;">
                <col style="width: 42%;">
                <col style="width: 10.5%;">
                <col style="width: 11%;">
                <col style="width: 13%;">
            </colgroup>
            <thead>
                <tr class="top">
                    <th class="col-course" rowspan="2">Course Number</th>
                    <th class="col-title" rowspan="2">Descriptive Title</th>
                    <th colspan="2">Grades</th>
                    <th class="col-credit" rowspan="2">Credit</th>
                </tr>
                <tr class="sub">
                    <th class="col-final">Final</th>
                    <th class="col-reexam">Re-Ex.</th>
                </tr>
            </thead>
            <tbody>
                @foreach($page['entries'] as $entry)
                    @if($entry['type'] === 'year')
                        <tr><td class="year-label" colspan="5">{{ $entry['label'] }}</td></tr>
                    @elseif($entry['type'] === 'semester')
                        <tr><td class="sem-label" colspan="5">{{ $entry['label'] }}</td></tr>
                    @elseif($entry['type'] === 'subject')
                        <tr class="subject-row">
                            <td class="col-course">{{ $entry['course_code'] }}</td>
                            <td class="col-title">{{ $entry['subject_name'] }}</td>
                            <td class="col-final">{{ $entry['grade'] !== null ? number_format($entry['grade'], 2) : '' }}</td>
                            <td class="col-reexam">{{ $entry['re_exam'] !== null ? number_format($entry['re_exam'], 2) : '' }}</td>
                            <td class="col-credit">{{ number_format($entry['units'], 1) }}</td>
                        </tr>
                    @endif
                @endforeach

                @if(!$page['isLast'])
                    <tr class="continuation-row">
                        <td colspan="5" class="over-text">Over</td>
                    </tr>
                @else
                    <tr class="continuation-row"><td colspan="5"></td></tr>
                @endif
            </tbody>
        </table>

        <table class="legend-row">
            <tr>
                <td class="legend-label" style="border-right: none;">Grading System:</td>
                <td class="legend-text" style="width: 47%;">
                    1.0-Outstanding; 1.1-1.5-Excellent; 1.6-2.0-Very Good; 2.1-2.5-Good; 2.6-3.0-Fair; 3.1-3.5-Conditional; 3.6-5.0-Failed; INC-Incomplete; Dr-Dropped; WP-Withdrawal with Permission; IP-In Progress
                </td>
                <td class="legend-label" style="border-right: none;">Credits:</td>
                <td class="legend-text">
                    One unit of credit is one hour lecture or recitation of 3 hours of lab. work each week per semester.
                </td>
            </tr>
        </table>

        <table class="remarks-table">
            <tr>
                <td class="remarks-label">REMARKS:</td>
                <td class="remarks-text">{{ $tor->remarks ?? '' }}</td>
            </tr>
        </table>

        <div class="not-valid">NOT VALID WITHOUT SEAL</div>

        <table class="signature-row">
            <tr>
                <td>
                    <div class="sig-label">Prepared by:</div>
                    <div class="sig-name-prepared">{{ strtoupper($tor->prepared_by_name ?? '') }}</div>
                    <div class="sig-title-prepared">{{ $tor->prepared_by_title ?? '' }}</div>
                </td>
                <td>
                    <div class="sig-label-checked">Checked by:</div>
                    <div class="sig-name-checked">{{ strtoupper($tor->checked_by_name ?? '') }}@if($tor->checked_by_credentials), {{ $tor->checked_by_credentials }}@endif</div>
                    <div class="sig-title-checked">{{ $tor->checked_by_title ?? '' }}</div>
                </td>
            </tr>
        </table>

        <div class="campus-admin">
            <div class="campus-admin-name">{{ strtoupper($tor->campus_admin_name ?? '') }}</div>
            <div class="campus-admin-title">{{ $tor->campus_admin_title ?? '' }}</div>
        </div>

        @if($page['isLast'] && $student->status === 'graduated' && $tor->degree_awarded)
            <div class="grad-statement">
                GRADUATED: The degree of <strong>{{ $tor->degree_awarded }}</strong>
                @if($tor->major_at_graduation) major in <strong>{{ $tor->major_at_graduation }}</strong> @endif
                on {{ $tor->graduation_date ? $tor->graduation_date->format('F d, Y') : '' }},
                in accordance with Board Resolution No. <strong>{{ $tor->board_resolution_no }}</strong>,
                approved by the Board of Regents on {{ $tor->board_regents_approval_date ? $tor->board_regents_approval_date->format('F d, Y') : '' }},
                with NSTP Serial Number <strong>{{ $tor->nstp_serial_number }}</strong>.
            </div>
        @endif

        @if(!$loop->last)
            <div class="page-break"></div>
        @endif

    @endforeach

    </body>
    </html>
