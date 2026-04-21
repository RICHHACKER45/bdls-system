<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Ulat ng System Analytics</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #b91c1c; padding-bottom: 10px; margin-bottom: 20px; }
        .title { font-size: 18px; font-weight: bold; text-transform: uppercase; color: #0f172a; }
        .subtitle { font-size: 12px; color: #64748b; }
        .section-title { font-size: 14px; font-weight: bold; background-color: #f1f5f9; padding: 8px; margin-top: 20px; }
        table { w-full; border-collapse: collapse; margin-top: 10px; width: 100%; }
        th, td { border: 1px solid #cbd5e1; padding: 8px; text-align: left; }
        th { background-color: #f8fafc; font-weight: bold; text-transform: uppercase; font-size: 10px; }
        .highlight { color: #b91c1c; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Barangay Doña Lucia Services (BDLS)</div>
        <div class="subtitle">{{ $reportTitle }}</div>
        <div class="subtitle">Petsa ng Ulat: {{ \Carbon\Carbon::now()->format('F d, Y h:i A') }}</div>
    </div>

    <div class="section-title">1. KABUUANG DAMI NG REQUEST</div>
    <table>
        <tr>
            <th>Kabuuang Request</th>
            <th>Walk-in Channel</th>
            <th>Online Channel</th>
        </tr>
        <tr>
            <td class="highlight">{{ $totalRequests }}</td>
            <td>{{ $walkinCount }}</td>
            <td>{{ $onlineCount }}</td>
        </tr>
    </table>

    <div class="section-title">2. KAHUSAYAN SA OPERASYON (Status)</div>
    <table>
        <tr>
            <th>Pending</th>
            <th>Pinoproseso</th>
            <th>Para sa Interview</th>
            <th>Na-release / Tinanggap</th>
        </tr>
        <tr>
            <td>{{ $pendingCount }}</td>
            <td>{{ $processingCount }}</td>
            <td>{{ $interviewCount }}</td>
            <td>{{ $releasedCount }}</td>
        </tr>
    </table>

    <div class="section-title">3. PAGGAMIT NG NOTIPIKASYON AT API</div>
    <table>
        <tr>
            <th>Kabuuang SMS na Naipadala</th>
            <th>Kabuuang Email na Naipadala</th>
            <th>Bigo ang Pagpapadala</th>
        </tr>
        <tr>
            <td class="highlight">{{ $smsCount }}</td>
            <td>{{ $emailCount }}</td>
            <td>{{ $failedCount }}</td>
        </tr>
    </table>
</body>
</html>
