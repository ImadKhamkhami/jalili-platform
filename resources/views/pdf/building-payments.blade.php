{{-- resources/views/pdf/building-payments.blade.php --}}

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans; direction: rtl; font-size: 12px }
        h2 { margin: 20px 0 10px }
        table { width: 100%; border-collapse: collapse; margin-bottom: 25px }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: center }
        th { background: #f3f4f6 }
        .remaining-zero { color: red; font-weight: bold }
        .payments { text-align: right; font-size: 11px }
    </style>
</head>
<body>

<h2>
    دفوعات العمارة: {{ $building->name ?? ('عمارة #' . $building->id) }}
    — الشطر {{ $tranche }}
</h2>

{{-- ================== المحلات ================== --}}
@if($shops->count())
<h3>المحلات التجارية</h3>
<table>
    <thead>
    <tr>
        <th>الرقم</th>
        <th>الزبون</th>
        <th>المساحة</th>
        <th>المبلغ الإجمالي</th>
        <th>الدفوعات</th>
        <th>المدفوع</th>
        <th>المتبقي</th>
    </tr>
    </thead>
    <tbody>
    @foreach($shops as $shop)
        <tr>
            <td>{{ $shop['number'] }}</td>
            <td>{{ $shop['customer'] ?? '—' }}</td>
            <td>{{ $shop['area'] }}</td>
            <td>{{ number_format($shop['total'], 2) }}</td>
            <td class="payments">
                @foreach($shop['payments'] as $p)
                    {{ number_format($p->amount, 2) }} — {{ \Carbon\Carbon::parse($p->paid_at)->format('Y-m-d') }}<br>
                @endforeach
            </td>
            <td>{{ number_format($shop['paid'], 2) }}</td>
            <td class="{{ $shop['remaining'] == 0 ? 'remaining-zero' : '' }}">
                {{ number_format($shop['remaining'], 2) }}
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
@endif

{{-- ================== الشقق ================== --}}
@if($apartments->count())
<h3>الشقق</h3>
<table>
    <thead>
    <tr>
        <th>الرقم</th>
        <th>الطابق</th>
        <th>الزبون</th>
        <th>المساحة</th>
        <th>المبلغ الإجمالي</th>
        <th>الدفوعات</th>
        <th>المدفوع</th>
        <th>المتبقي</th>
    </tr>
    </thead>
    <tbody>
    @foreach($apartments as $ap)
        <tr>
            <td>{{ $ap['number'] }}</td>
            <td>{{ $ap['floor'] }}</td>
            <td>{{ $ap['customer'] ?? '—' }}</td>
            <td>{{ $ap['area'] }}</td>
            <td>{{ number_format($ap['total'], 2) }}</td>
            <td class="payments">
                @foreach($ap['payments'] as $p)
                    {{ number_format($p->amount, 2) }} — {{ \Carbon\Carbon::parse($p->paid_at)->format('Y-m-d') }}<br>
                @endforeach
            </td>
            <td>{{ number_format($ap['paid'], 2) }}</td>
            <td class="{{ $ap['remaining'] == 0 ? 'remaining-zero' : '' }}">
                {{ number_format($ap['remaining'], 2) }}
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
@endif

</body>
</html>
