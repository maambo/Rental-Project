<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #1f2328; }
        h1 { font-size: 18px; margin-bottom: 2px; }
        .subtitle { color: #656d76; font-size: 11px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 6px 8px; border-bottom: 1px solid #d0d7de; text-align: left; }
        th { background: #f6f8fa; font-size: 10px; text-transform: uppercase; letter-spacing: 0.04em; }
        td.amount, th.amount { text-align: right; }
        .total-row td { font-weight: bold; border-top: 2px solid #1f2328; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p class="subtitle">{{ $from->format('d M Y') }} — {{ $to->format('d M Y') }}</p>

    <table>
        <thead>
            <tr>
                <th>Period</th>
                <th class="amount">Total (K)</th>
                <th class="amount">Count</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row['period'] }}</td>
                    <td class="amount">{{ number_format($row['total'], 2) }}</td>
                    <td class="amount">{{ $row['count'] }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td>Total</td>
                <td class="amount">{{ number_format($total, 2) }}</td>
                <td class="amount"></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
