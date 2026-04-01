<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate of Grades</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #000; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; font-size: 16px; }
        .header h3 { margin: 0; font-size: 13px; font-weight: normal; }
        .document-title { text-align: center; font-size: 14px; font-weight: bold; margin: 20px 0 10px; text-transform: uppercase; border-bottom: 2px solid #000; padding-bottom: 6px; }
        .info-grid { width: 100%; margin-bottom: 16px; }
        .info-grid td { padding: 3px 6px; }
        .label { font-weight: bold; width: 140px; }
        table.grades { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.grades th, table.grades td { border: 1px solid #000; padding: 6px 8px; text-align: left; }
        table.grades th { background: #f0f0f0; font-weight: bold; }
        .gwa-row td { font-weight: bold; background: #f9f9f9; }
        .footer { margin-top: 40px; }
        .signature-line { border-top: 1px solid #000; width: 200px; margin-top: 40px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Eastern Samar State University - Guiuan Campus</h2>
        <h3>Office of the Registrar</h3>
    </div>

    <div class="document-title">Certificate of Grades</div>

    <table class="info-grid">
        <tr>
            <td class="label">Student Name:</td>
            <td>{{ $student->getFullName() }}</td>
            <td class="label">Document No.:</td>
            <td>{{ $cog->document_number }}</td>
        </tr>
        <tr>
            <td class="label">Student No.:</td>
            <td>{{ $student->student_number }}</td>
            <td class="label">Date Generated:</td>
            <td>{{ now()->format('F d, Y') }}</td>
        </tr>
        <tr>
            <td class="label">Course:</td>
            <td>{{ $student->course->name ?? 'N/A' }}</td>
            <td class="label">Semester:</td>
            <td>{{ $semester->semester_name }}</td>
        </tr>
    </table>

    <table class="grades">
        <thead>
            <tr>
                <th>Subject Code</th>
                <th>Subject Name</th>
                <th>Units</th>
                <th>Grade</th>
            </tr>
        </thead>
        <tbody>
            @foreach($gradeData as $row)
            <tr>
                <td>{{ $row['subject_code'] }}</td>
                <td>{{ $row['subject_name'] }}</td>
                <td>{{ $row['units'] }}</td>
                <td>{{ number_format($row['grade'], 1) }}</td>
            </tr>
            @endforeach
            <tr class="gwa-row">
                <td colspan="3" style="text-align:right;">Semester GWA:</td>
                <td>{{ $semesterGwa ? number_format($semesterGwa, 1) : 'N/A' }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>This is to certify that the above grades are true and correct records on file in this office.</p>
        <div class="signature-line"></div>
        <p><strong>Registrar</strong></p>
    </div>
</body>
</html>
