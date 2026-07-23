<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Registered Accounts</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            color: #0f172a;
        }
        .subtitle {
            font-size: 11px;
            color: #64748b;
            margin-top: 4px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 10px;
        }
        th,
        td {
            border: 1px solid #cbd5e1;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f8fafc;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Barangay Doña Lucia Services (BDLS)</div>
        <div class="subtitle">REGISTERED ACCOUNTS REPORT</div>
        <div class="subtitle">Generated on: {{ \Carbon\Carbon::now()->format('F d, Y h:i A') }}</div>
        <div class="subtitle">Total Accounts: {{ count($registeredAccounts) }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Pangalan</th>
                <th>Contact Number</th>
                <th>Email Address</th>
                <th>Status</th>
                <th>Date Registered</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($registeredAccounts as $acc)
                <tr>
                    <td style="font-weight: bold; text-transform: uppercase; color: #0f172a;">
                        {{ $acc->last_name }}, {{ $acc->first_name }} {{ $acc->suffix }}
                    </td>
                    <td>{{ $acc->contact_number }}</td>
                    <td>{{ $acc->email }}</td>
                    <td>{{ $acc->is_verified ? 'Verified' : 'Unverified' }}</td>
                    <td>{{ \Carbon\Carbon::parse($acc->created_at)->format('M d, Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center">Walang nakarehistrong account.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
