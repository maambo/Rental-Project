<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #1f2328; }
        h1 { font-size: 18px; margin-bottom: 2px; }
        h2 { font-size: 13px; margin: 18px 0 6px; }
        .subtitle { color: #656d76; font-size: 11px; margin-bottom: 16px; }
        .summary { display: table; width: 100%; margin-bottom: 12px; }
        .summary .cell { display: table-cell; padding: 8px 10px; border: 1px solid #d0d7de; }
        .summary .label { color: #656d76; font-size: 10px; text-transform: uppercase; }
        .summary .value { font-size: 15px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        th, td { padding: 5px 8px; border-bottom: 1px solid #d0d7de; text-align: left; }
        th { background: #f6f8fa; font-size: 10px; text-transform: uppercase; letter-spacing: 0.04em; }
        td.amount, th.amount { text-align: right; }
    </style>
</head>
<body>
    <h1>Property Analytics Report</h1>
    <p class="subtitle">
        Generated {{ now()->format('d M Y') }}
        @if (array_filter($filters))
            — filtered: {{ implode(', ', array_map(fn ($k, $v) => "$k=$v", array_keys(array_filter($filters)), array_filter($filters))) }}
        @else
            — all properties
        @endif
    </p>

    <div class="summary">
        <div class="cell"><div class="label">Total Properties</div><div class="value">{{ $summary['total_properties'] }}</div></div>
        <div class="cell"><div class="label">Avg Price (K)</div><div class="value">{{ number_format($summary['avg_price'], 2) }}</div></div>
        <div class="cell"><div class="label">For Rent</div><div class="value">{{ $summary['for_rent'] }}</div></div>
        <div class="cell"><div class="label">For Sale</div><div class="value">{{ $summary['for_sale'] }}</div></div>
        <div class="cell"><div class="label">Total Views</div><div class="value">{{ $summary['total_views'] }}</div></div>
    </div>

    <h2>By Province</h2>
    <table>
        <thead><tr><th>Province</th><th class="amount">Properties</th><th class="amount">Avg Price (K)</th></tr></thead>
        <tbody>
            @forelse ($byProvince as $row)
                <tr><td>{{ $row->label }}</td><td class="amount">{{ $row->total }}</td><td class="amount">{{ number_format($row->avg_price, 2) }}</td></tr>
            @empty
                <tr><td colspan="3">No data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>By District (top 50)</h2>
    <table>
        <thead><tr><th>District</th><th class="amount">Properties</th><th class="amount">Avg Price (K)</th></tr></thead>
        <tbody>
            @forelse ($byDistrict as $row)
                <tr><td>{{ $row->label }}</td><td class="amount">{{ $row->total }}</td><td class="amount">{{ number_format($row->avg_price, 2) }}</td></tr>
            @empty
                <tr><td colspan="3">No data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>By Property Type</h2>
    <table>
        <thead><tr><th>Type</th><th>Subtype</th><th class="amount">Properties</th><th class="amount">Avg Price (K)</th></tr></thead>
        <tbody>
            @forelse ($byType as $row)
                <tr><td>{{ ucfirst($row->property_type) }}</td><td>{{ ucfirst(str_replace('_', ' ', $row->property_subtype ?? '—')) }}</td><td class="amount">{{ $row->total }}</td><td class="amount">{{ number_format($row->avg_price, 2) }}</td></tr>
            @empty
                <tr><td colspan="4">No data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>By Listing Type</h2>
    <table>
        <thead><tr><th>Listing</th><th class="amount">Properties</th><th class="amount">Avg Price (K)</th></tr></thead>
        <tbody>
            @forelse ($byListing as $row)
                <tr><td>{{ ucfirst($row->listing_type) }}</td><td class="amount">{{ $row->total }}</td><td class="amount">{{ number_format($row->avg_price, 2) }}</td></tr>
            @empty
                <tr><td colspan="3">No data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>By Utility</h2>
    <table>
        <thead><tr><th>Utility</th><th class="amount">Properties</th></tr></thead>
        <tbody>
            @forelse ($byUtility as $row)
                <tr><td>{{ $row->label }}</td><td class="amount">{{ $row->total }}</td></tr>
            @empty
                <tr><td colspan="2">No data.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
