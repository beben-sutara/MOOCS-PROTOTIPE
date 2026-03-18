<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Sertifikat - {{ $certificate->certificate_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            background: #fff;
            width: 297mm;
            height: 210mm;
            color: #1c1d1f;
        }

        .page {
            width: 297mm;
            height: 210mm;
            position: relative;
            overflow: hidden;
        }

        /* Decorative border */
        .border-outer {
            position: absolute;
            top: 8mm;
            left: 8mm;
            right: 8mm;
            bottom: 8mm;
            border: 3px solid #a435f0;
            border-radius: 4px;
        }

        .border-inner {
            position: absolute;
            top: 11mm;
            left: 11mm;
            right: 11mm;
            bottom: 11mm;
            border: 1px solid #d1a4f5;
            border-radius: 3px;
        }

        /* Purple accent bar at top */
        .accent-bar {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 28mm;
            background: linear-gradient(135deg, #a435f0, #5624d0);
        }

        /* Bottom bar */
        .bottom-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 12mm;
            background: linear-gradient(135deg, #5624d0, #a435f0);
        }

        .content {
            position: absolute;
            top: 30mm;
            left: 20mm;
            right: 20mm;
            bottom: 14mm;
            text-align: center;
        }

        .platform-name {
            font-size: 13pt;
            font-weight: bold;
            color: #fff;
            position: absolute;
            top: 8mm;
            left: 0;
            right: 0;
            text-align: center;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .cert-title {
            font-size: 10pt;
            color: #8710d8;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 6mm;
        }

        .presented-to {
            font-size: 10pt;
            color: #6a6f73;
            margin-bottom: 3mm;
        }

        .recipient-name {
            font-size: 28pt;
            font-weight: bold;
            color: #a435f0;
            margin-bottom: 5mm;
            border-bottom: 2px solid #a435f0;
            display: inline-block;
            padding-bottom: 2mm;
        }

        .completion-text {
            font-size: 10pt;
            color: #6a6f73;
            margin-bottom: 3mm;
        }

        .course-title {
            font-size: 18pt;
            font-weight: bold;
            color: #1c1d1f;
            margin-bottom: 6mm;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
            margin-top: 6mm;
        }

        .meta-item {
            text-align: center;
            width: 30%;
        }

        .meta-label {
            font-size: 8pt;
            color: #6a6f73;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 1mm;
        }

        .meta-value {
            font-size: 10pt;
            font-weight: bold;
            color: #1c1d1f;
        }

        .cert-number {
            font-size: 8pt;
            color: #8710d8;
        }

        .divider {
            border-top: 1px solid #e0d0f8;
            margin: 5mm 0;
        }
    </style>
</head>
<body>
<div class="page">
    <div class="accent-bar"></div>
    <div class="bottom-bar"></div>
    <div class="border-outer"></div>
    <div class="border-inner"></div>

    <div class="platform-name">MoocsPangarti</div>

    <div class="content">
        <div class="cert-title">Certificate of Completion</div>

        <div class="presented-to">Diberikan kepada</div>
        <div class="recipient-name">{{ $user->name }}</div>

        <div class="completion-text" style="margin-top: 4mm;">yang telah berhasil menyelesaikan course</div>
        <div class="course-title">{{ $course->title }}</div>

        <div class="divider"></div>

        <div class="meta-row">
            <div class="meta-item">
                <div class="meta-label">Instructor</div>
                <div class="meta-value">{{ $instructor->name }}</div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Tanggal Selesai</div>
                <div class="meta-value">{{ $certificate->issued_at->format('d F Y') }}</div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Nomor Sertifikat</div>
                <div class="cert-number">{{ $certificate->certificate_number }}</div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
