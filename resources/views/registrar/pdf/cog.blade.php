<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certification of Grades</title>
    <style>
        @page { margin: 1in 1in 1in 1.5in; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #000; line-height: 1.5; }
        .header-table { width: 100%; margin-bottom: 10px; }
        .header-table td { vertical-align: middle; text-align: center; }
        .header-table .uni-name { font-size: 20px; font-weight: bold; color: #14532d; margin: 0; }
        .header-table .uni-tagline { font-size: 10px; margin: 2px 0 0; }
        .office-title { text-align: center; font-size: 15px; font-weight: bold; margin-top: 10px; }
        .cert-title { text-align: center; font-size: 13px; font-weight: bold; letter-spacing: 1px; margin: 8px 0 14px; }
        .to-whom { font-weight: bold; margin-bottom: 10px; }
        .body-text { text-align: justify; margin-bottom: 14px; text-indent: 36pt; }
        .sem-label { font-weight: bold; margin-bottom: 4px; }
        table.grades { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.grades th, table.grades td { border: 1px solid #000; padding: 5px 8px; text-align: left; font-size: 11px; vertical-align: bottom; }
        table.grades th { background: #f0f0f0; font-weight: bold; text-align: center; vertical-align: middle; }
        table.grades td.center { text-align: center; }
        table.grades tr.gwa-row td { font-weight: bold; text-align: center; }
        .purpose-text { text-align: justify; margin: 6px 0 30px; text-indent: 36pt; }
        .issued-text { margin-bottom: 50px; margin-left: 36pt; }
        .signature-block { text-align: center; margin-left: 45%; }
        .signature-name { font-weight: bold; margin: 0; }
        .signature-title { margin: 2px 0 0; }
        .footer-notes { font-size: 10px; margin-top: 50px; }
        .doc-footer { position: fixed; bottom: -30px; left: 0; right: 0; font-size: 9px; color: #444; border-top: 1px solid #ccc; padding-top: 4px; }
        .doc-footer table { width: 100%; }
        .doc-footer td { vertical-align: top; }
        .doc-footer td.right { text-align: right; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td style="width: 78%; text-align: left;">
                @if(file_exists(public_path('images/logo/essu-horizontal.png')))
                    <img src="{{ $forPdf ? public_path('images/logo/essu-horizontal.png') : asset('images/logo/essu-horizontal.png') }}" style="height: 75px;">
                @endif
            </td>
            <td style="width: 22%; text-align: center;">
                @if(file_exists(public_path('images/logo/bagong-pilipinas-logo.png')))
                    <img src="{{ $forPdf ? public_path('images/logo/bagong-pilipinas-logo.png') : asset('images/logo/bagong-pilipinas-logo.png') }}" style="height: 70px;">
                @endif
            </td>
        </tr>
    </table>

    <div class="office-title">OFFICE OF THE REGISTRAR</div>
    <div class="cert-title">C E R T I F I C A T I O N</div>

    <div class="to-whom">TO WHOM THIS MAY CONCERN:</div>

    <div class="body-text">
        THIS IS TO CERTIFY that <strong>{{ strtoupper($student->getFormalName()) }},</strong>
        a {{ $student->getYearLevelWord() }} year student of
        <strong>{{ strtoupper($student->course->name ?? 'N/A') }} ({{ $student->course->code ?? 'N/A' }})</strong>
        has enrolled the following subjects, to wit:
    </div>

    <div class="sem-label">
        {{ $semester->semester_name }}, S.Y {{ $semester->schoolYear->year_code ?? 'N/A' }}
    </div>

    <table class="grades">
        <thead>
            <tr>
                <th style="width: 15%;">Course</th>
                <th style="width: 11%;">No.</th>
                <th style="width: 49%;">Descriptive Title</th>
                <th style="width: 12%;">Grades</th>
                <th style="width: 13%;">Units</th>
            </tr>
        </thead>
        <tbody>
            @foreach($gradeData as $row)
            @php
                $codeParts = explode(' ', $row['subject_code'], 2);
                $courseCode = $codeParts[0] ?? $row['subject_code'];
                $courseNo = $codeParts[1] ?? '';
            @endphp
            <tr>
                <td class="center">{{ $courseCode }}</td>
                <td class="center">{{ $courseNo }}</td>
                <td>{{ $row['subject_name'] }}</td>
                <td class="center">{{ number_format($row['grade'], 1) }}</td>
                <td class="center">{{ $row['units'] }}</td>
            </tr>
            @endforeach
            <tr class="gwa-row">
                <td colspan="3">GWA</td>
                <td>{{ $semesterGwa ? number_format($semesterGwa, 2) : 'N/A' }}</td>
                <td>{{ number_format(collect($gradeData)->sum('units'), 1) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="purpose-text">
        This certification is issued upon request of <strong>{{ strtoupper($student->getFormalName()) }}</strong>
        for <strong>{{ $cog->purpose }}</strong> purposes only.
    </div>

    <div class="issued-text">
        Issued this {{ $cog->getIssuedDateFormatted() }}.
    </div>

    <div class="signature-block">
        <p class="signature-name">{{ strtoupper($cog->signatory_name) }}@if($cog->signatory_credentials), {{ $cog->signatory_credentials }}@endif</p>
        <p class="signature-title">{{ $cog->signatory_title }}</p>
    </div>

    <div class="footer-notes">
        Not valid w/o school seal<br>
        O.R. No. {{ $cog->or_number }}<br>
        {{ $cog->issued_date ? $cog->issued_date->format('F d, Y') : '' }}
    </div>

    <div class="doc-footer">
        <table>
            <tr>
                <td>ESSU-ACAD-210 | Version 5<br>Effectivity Date: March 15, 2024</td>
                <td class="right" id="page-num-holder"></td>
            </tr>
        </table>
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->getFont("Arial", "normal");
            $size = 9;
            $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
            $width = $fontMetrics->getTextWidth($text, $font, $size);
            $x = $pdf->get_width() - 72 - $width;
            $y = $pdf->get_height() - 65;
            $pdf->page_text($x, $y, $text, $font, $size);
        }
    </script>

</body>
</html>
