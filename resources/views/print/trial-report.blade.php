<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Trial Report - {{ $report->part_no ?? 'PDF' }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            font-family: Tahoma, Arial, sans-serif;
            font-size: 10pt;
            color: #000;
            background: #fff;
        }
        .sheet-container {
            width: 285mm;
            margin: 0 auto;
            background: #fff;
            padding: 4mm 6mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .top-banner {
            display: grid;
            grid-template-columns: 220px 1fr 220px;
            align-items: center;
            margin-bottom: 6px;
        }
        .logo-box .logo-text-main {
            font-family: Arial, sans-serif;
            font-size: 34px;
            font-weight: 900;
            color: #0d6832;
            letter-spacing: 2px;
            line-height: 1;
        }
        .logo-box .logo-text-sub {
            font-family: Arial, sans-serif;
            font-size: 7.5px;
            font-weight: bold;
            color: #000;
            letter-spacing: 0.3px;
            margin-top: 2px;
        }
        .report-title {
            font-family: "Times New Roman", Times, serif;
            font-size: 24pt;
            font-weight: bold;
            text-align: center;
            letter-spacing: 1px;
        }
        .meta-grid {
            display: grid;
            grid-template-columns: 35% 1.5% 9% 1.5% 53%;
            align-items: start;
            margin-bottom: 8px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-table td {
            border: 1px solid #000;
            height: 22px;
            padding: 2px 6px;
            font-size: 9.5pt;
            vertical-align: middle;
        }
        .meta-table .label-col {
            width: 32%;
            font-weight: bold;
            white-space: nowrap;
        }
        .trial-date-table {
            width: 100%;
            border-collapse: collapse;
            font-family: "Times New Roman", Times, serif;
        }
        .trial-date-table td {
            border: 1px solid #000;
            text-align: center;
            vertical-align: middle;
        }
        .trial-date-table .label-col {
            height: 24px;
            font-weight: bold;
            font-size: 8.5pt;
            background: #fafafa;
            white-space: nowrap;
        }
        .trial-date-table .val-col {
            height: 48px;
            font-size: 9.5pt;
            font-weight: bold;
            white-space: nowrap;
        }
        .approval-table {
            width: 100%;
            border-collapse: collapse;
            font-family: "Times New Roman", Times, serif;
            font-size: 9.5pt;
            text-align: center;
            table-layout: fixed;
        }
        .approval-table th, .approval-table td {
            border: 1px solid #000;
            width: 20%;
        }
        .approval-table th {
            font-weight: bold;
            height: 24px;
            font-size: 9.5pt;
            background: #fafafa;
        }
        .approval-table .sign-space td { height: 80px; }
        .approval-table .sign-name td { height: 24px; font-size: 9pt; }

        .main-table {
            width: 100%;
            border-collapse: collapse;
            font-family: "Times New Roman", Times, serif;
            table-layout: fixed;
        }
        .main-table th {
            border: 1px solid #000;
            font-weight: bold;
            font-size: 8.5pt;
            text-align: center;
            vertical-align: middle;
            padding: 2px 2px;
            letter-spacing: 0.2px;
            white-space: normal;
            word-break: normal;
        }
        .main-table td {
            border-left: 1px solid #000;
            border-right: 1px solid #000;
            border-top: none;
            padding: 3px 5px;
            font-family: Tahoma, Arial, sans-serif;
            font-size: 8.5pt;
            vertical-align: top;
            line-height: 1.3;
            word-wrap: break-word;
            word-break: break-word;
            overflow-wrap: break-word;
        }
        .main-table td.text-center {
            text-align: center;
            white-space: nowrap;
        }
        .main-table td.text-right {
            text-align: right;
            white-space: nowrap;
        }
        .main-table td.wrap-text {
            white-space: normal;
        }
        .main-table tr.has-data td {
            border-bottom: 1px solid #000;
        }
        .main-table tr.empty-row td {
            border-bottom: none;
            height: 20px;
        }
        .main-table tbody tr:last-child td {
            border-bottom: 1px solid #000;
        }
        .doc-footer {
            margin-top: 6px;
            font-family: "Times New Roman", Times, serif;
            font-size: 9.5pt;
        }

        @page {
            size: A4 landscape;
            margin: 6mm;
        }
        @media print {
            html, body {
                width: 100%;
                height: 100%;
                margin: 0;
                padding: 0;
            }
            .sheet-container {
                width: 100% !important;
                max-width: 100% !important;
                min-height: auto !important;
                padding: 0 !important;
                margin: 0 !important;
            }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="sheet-container">
        <div>
            <!-- Header Logo & Judul -->
            <div class="top-banner">
                <div class="logo-box">
                    <div class="logo-text-main">BMI</div>
                    <div class="logo-text-sub">PT BINTANG MATRIX INDONESIA</div>
                </div>
                <div class="report-title">TRIAL REPORT</div>
                <div></div>
            </div>

            <!-- Metadata Grid -->
            <div class="meta-grid">
                <!-- Data Part -->
                <table class="meta-table">
                    <tr><td class="label-col">Customer</td><td class="val-col">{{ $report->customer ?? '' }}</td></tr>
                    <tr><td class="label-col">Part No.</td><td class="val-col">{{ $report->part_no ?? '' }}</td></tr>
                    <tr><td class="label-col">Part Name</td><td class="val-col">{{ $report->part_name ?? '' }}</td></tr>
                    <tr><td class="label-col">Model</td><td class="val-col">{{ $report->model ?? '' }}</td></tr>
                    <tr><td class="label-col">Material</td><td class="val-col">{{ $report->material ?? '' }}</td></tr>
                    <tr><td class="label-col">Qty Material</td><td class="val-col">{{ $report->qty_material ?? '' }}</td></tr>
                </table>

                <div></div>

                <!-- Trial Date (2 baris: label dan date di bawah) -->
                <table class="meta-table trial-date-table">
                    <tr>
                        <td class="text-center label-col">Trial Date</td>
                    </tr>
                    <tr>
                        <td class="text-center val-col">
                            {{ $report->trial_date ?? '' }}
                        </td>
                    </tr>
                </table>

                <div></div>

                <!-- Approval Box (Kotak proporsional dan luas untuk tanda tangan basah) -->
                <table class="approval-table">
                    <colgroup>
                        <col style="width: 20%;">
                        <col style="width: 20%;">
                        <col style="width: 20%;">
                        <col style="width: 20%;">
                        <col style="width: 20%;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Prepared</th>
                            <th colspan="3">Checked</th>
                            <th>Approved</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="sign-space">
                            <td></td><td></td><td></td><td></td><td></td>
                        </tr>
                        <tr class="sign-name">
                            <td></td><td></td><td></td><td></td><td></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Tabel Utama Proses (Minimal 21 baris) -->
            <table class="main-table">
                <thead>
                    <tr style="height: 24px;">
                        <th rowspan="2" style="width: 3.5%;">NO.</th>
                        <th rowspan="2" style="width: 9.5%;">PROSES</th>
                        <th colspan="3">MESIN</th>
                        <th colspan="2">OUTPUT</th>
                        <th rowspan="2" style="width: 19%;">PROBLEM</th>
                        <th rowspan="2" style="width: 19.5%;">CORRECTIVE ACTION</th>
                        <th rowspan="2" style="width: 6%;">PIC</th>
                        <th rowspan="2" style="width: 7.5%;">DATE</th>
                        <th rowspan="2" style="width: 12.5%;">KETERANGAN</th>
                    </tr>
                    <tr style="height: 26px;">
                        <th style="width: 4.5%; font-size: 8pt; font-weight: normal;">M/C No.</th>
                        <th style="width: 4.5%; font-size: 8pt; font-weight: normal;">Tonage</th>
                        <th style="width: 4%; font-size: 8pt; font-weight: normal;">DH</th>
                        <th style="width: 5%; font-size: 8pt; font-weight: normal;">Qty OK</th>
                        <th style="width: 5%; font-size: 8pt; font-weight: normal;">Qty NG</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $items = $report->items ?? [];
                        $totalRows = max(21, count($items));
                    @endphp
                    @for ($i = 0; $i < $totalRows; $i++)
                        @php
                            $row = $items[$i] ?? null;
                            $hasData = !empty($row) && (
                                !empty($row['proses']) ||
                                !empty($row['problem']) ||
                                !empty($row['corrective_action']) ||
                                !empty($row['qty_ok']) ||
                                !empty($row['qty_ng']) ||
                                !empty($row['mc_no']) ||
                                !empty($row['tonage']) ||
                                !empty($row['keterangan'])
                            );
                        @endphp
                        <tr class="{{ $hasData ? 'has-data' : 'empty-row' }}">
                            <td class="text-center">{{ $hasData ? ($row['no'] ?? ($i + 1)) : '' }}</td>
                            <td class="wrap-text">{{ $row['proses'] ?? '' }}</td>
                            <td class="text-center">{{ $row['mc_no'] ?? '' }}</td>
                            <td class="text-center">{{ $row['tonage'] ?? '' }}</td>
                            <td class="text-center">{{ $row['dh'] ?? '' }}</td>
                            <td class="text-right">{{ $row['qty_ok'] ?? '' }}</td>
                            <td class="text-right">{{ $row['qty_ng'] ?? '' }}</td>
                            <td class="wrap-text">{{ $row['problem'] ?? '' }}</td>
                            <td class="wrap-text">{{ $row['corrective_action'] ?? '' }}</td>
                            <td class="text-center">{{ $row['pic'] ?? '' }}</td>
                            <td class="text-center">{{ $row['date'] ?? '' }}</td>
                            <td class="wrap-text">{{ $row['keterangan'] ?? '' }}</td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        <div class="doc-footer">
            BMI-FM-EN-034 Rev.02
        </div>
    </div>

</body>
</html>