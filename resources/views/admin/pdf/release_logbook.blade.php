<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Release Logbook</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 10px; margin-bottom: 20px; }
        .title { font-size: 16px; font-weight: bold; text-transform: uppercase; color: #0f172a; }
        .subtitle { font-size: 11px; color: #64748b; margin-top: 4px; }
        table { border-collapse: collapse; width: 100%; margin-top: 10px; }
        th, td { border: 1px solid #cbd5e1; padding: 8px; text-align: left; }
        th { background-color: #f8fafc; font-weight: bold; text-transform: uppercase; font-size: 9px; }
        .signature-box { width: 120px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Barangay Doña Lucia Services (BDLS)</div>
        <div class="subtitle">OFFICIAL DOCUMENT RELEASE LOGBOOK</div>
        <div class="subtitle">Generated on: {{ \Carbon\Carbon::now()->format('F d, Y h:i A') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Queue #</th>
                <th>Pangalan ng Residente</th>
                <th>Dokumento</th>
                <th>Date Released</th>
                <th>Pirma ng Kumuha</th>
            </tr>
        </thead>
        <tbody>
            @forelse($receivedRequests as $req)
            <tr>
                <td style="font-weight: bold; color: #0f172a;">{{ $req->queue_number }}</td>
                <td style="text-transform: uppercase;">{{ $req->user->last_name }}, {{ $req->user->first_name }}</td>
                <td>{{ $req->documentType->name ?? 'N/A' }}</td>
                <td>{{ $req->released_at ? \Carbon\Carbon::parse($req->released_at)->format('M d, Y h:i A') : 'N/A' }}</td>
                <td class="signature-box"></td> <!-- BLANK ITO PARA MAY MAPIRMAHAN GAMIT ANG BALLPEN -->
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center;">Walang record ng released documents.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>