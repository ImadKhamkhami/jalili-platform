<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">

    <style>
        body {
            font-family: 'Tajawal', sans-serif;
            direction: rtl;
            margin: 20px;
            color: #000;
        }

        h2.title {
            text-align: center;
            color: #0a6b6b;
            margin-bottom: 10px;
            font-size: 22px;
            font-weight: bold;
        }

        /* 🔴 القطع المحجوزة */
        .reserved-underline {
            display: inline-block;
            padding-bottom: 2px;
            border-bottom: 3px solid #c62828;
        }

        /* 🟡 خانة قطعة مباعة */
        .land-sold {
            background-color: #ffe082;
            border-color: #f9a825;
        }

        .current-owner {
            color: #000;
            font-weight: bold;
            margin-bottom: 4px;
            display: block;
        }

        .previous-owner {
            color: #8a2525;
            font-size: 11px;
            display: block;
        }

        /* ===================== الإحصائيات ===================== */
        .stats-row {
            text-align: center;
            margin-bottom: 22px;
            font-size: 15px;
            font-weight: bold;
        }

        .stats-row span {
            display: inline-block;
            margin: 0 8px;
            padding: 6px 16px;
            border-radius: 10px;
            background: #f3f3f3;
        }

        /* ===================== مخطط القطع ===================== */
        .plan-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
            table-layout: fixed;
        }

        .plan-table tr,
        .plan-table td {
            page-break-inside: avoid;
        }

        .plan-table td {
            border: 2px solid #000;
            height: 118px;
            padding: 10px 8px;
            vertical-align: top;
            text-align: center;
            width: 25%;
            overflow: hidden;
        }

        .land-title {
            font-size: 17px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .info {
            font-size: 13.5px;
            margin: 4px 0;
            line-height: 1.6;
        }

        .owner {
            margin-top: 10px;
            font-size: 15px;
            font-weight: bold;
            color: #c62828;
        }

        /* ===================== فاصل صفحة ===================== */
        .page-break {
            page-break-before: always;
        }

        /* ===================== جدول الملخص ===================== */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .summary-table th,
        .summary-table td {
            border: 2px solid #000;
            padding: 8px 10px;
            text-align: center;
            vertical-align: middle;
            font-size: 14px;
        }

        .summary-table th {
            background: #f3f3f3;
            font-weight: bold;
        }

        .summary-owner {
            color: #c62828;
            font-weight: bold;
        }

        .bolddd {
            font-weight: bold;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>

@php
    $totalLands = $lands->count();
    $soldLands  = $lands->where('status', 'مباعة')->count();
    $vtLands    = $lands->where('status', 'متاحة')->count();
@endphp

<h2 class="title">تجزئة {{ $project->name }}</h2>

<div class="stats-row">
    <span>إجمالي القطع : {{ $totalLands }}</span>
    <span>القطع المباعة : {{ $soldLands }}</span>
    <span>الباقي : {{ $vtLands }}</span>
</div>

<table class="plan-table">
@foreach($lands->chunk(4) as $row)
<tr>
@foreach($row as $land)
@php
    $facadeLabel = $land->view_type === '2-FACADE' ? '2F' : '1F';
@endphp

<td class="{{ $land->status === 'مباعة' ? 'land-sold' : '' }}">
    <div class="land-title">
        @if($land->status === 'محجوزة')
            <span class="reserved-underline">قطعة {{ $land->land_number }}</span>
        @else
            قطعة {{ $land->land_number }}
        @endif
    </div>

    <div class="info">
        <strong>{{ $land->area }}</strong> م² —
        <strong>{{ $land->road_type }}</strong> م —
        <strong>{{ $facadeLabel }}</strong>
    </div>

    @if($land->owners_history->count())
        <div class="owner">
            @foreach($land->owners_history as $owner)
                @if($owner['current'])
                    <span class="current-owner">{{ $owner['name'] }}</span>
                @else
                    <span class="previous-owner">{{ $owner['name'] }}</span>
                @endif
            @endforeach
        </div>
    @endif
</td>

@endforeach
</tr>
@endforeach
</table>

<div class="page-break"></div>

<h2 class="title">تجزئة {{ $project->name }} — توزيع القطع حسب الزبناء</h2>

@php
    $grouped = $lands->filter(fn($l) => !empty($l->customer_name))
        ->groupBy('customer_name')
        ->sortByDesc(fn($items) => $items->count());
@endphp

<table class="summary-table">
<thead>
<tr>
    <th>اسم الزبون</th>
    <th>عدد القطع</th>
    <th>أرقام القطع</th>
</tr>
</thead>
<tbody>
@foreach($grouped as $customer => $items)
<tr>
    <td class="summary-owner">{{ $customer }}</td>
    <td class="bolddd">{{ $items->count() }}</td>
    <td class="bolddd">{{ $items->pluck('land_number')->sort()->implode(' ، ') }}</td>
</tr>
@endforeach
</tbody>
</table>

<div class="page-break"></div>

<h2 class="title">بيان التنازلات — تجزئة {{ $project->name }}</h2>

<table class="summary-table">
<thead>
<tr>
    <th> القطعة</th>
    <th>عدد التنازلات</th>
    <th>تفاصيل التنازلات</th>
</tr>
</thead>
<tbody>
@forelse($transfers as $rows)
<tr>
    <td class="bolddd">{{ optional($rows->first()->land)->land_number }}</td>
    <td class="bolddd">{{ $rows->count() }}</td>
    <td style="text-align:right; line-height:1.9">
        @foreach($rows as $i => $t)
            <div>
                <strong>{{ $i + 1 }}.</strong>
                من <span style="color:#8a2525" class="bolddd">{{ optional($t->fromCustomer)->name }}</span>
                إلى <span style="color:#0a6b6b" class="bolddd">{{ optional($t->toCustomer)->name }}</span>
                — بتاريخ <strong>{{ \Carbon\Carbon::parse($t->transfer_date)->format('d/m/Y') }}</strong>
            </div>
        @endforeach
    </td>
</tr>
@empty
<tr>
    <td colspan="3">لا توجد تنازلات مسجلة</td>
</tr>
@endforelse
</tbody>
</table>


<div class="page-break"></div>

<h2 class="title">بيان السمسرة — تجزئة {{ $project->name }}</h2>

@php
    $totalCommission = $commissions->sum('amount');
@endphp


<table class="summary-table">
<thead>
<tr>
    <th> القطعة</th>
    <th>المبلغ </th>
    <th>التاريخ </th>
    <th>اسم السمسار</th>
</tr>
</thead>

<tbody>
@forelse($commissions as $c)
<tr>
    <td class="bolddd">
         {{ optional($c->land)->land_number }}
    </td>

    <td class="bolddd">
        {{ number_format($c->amount, 2, ',', '.') }}
    </td>

    <td class="bolddd">
        {{ \Carbon\Carbon::parse($c->commission_date)->format('d/m/Y') }}
    </td>

    <td class="bolddd">
        {{ $c->broker_name ?: '—' }}
    </td>
</tr>
@empty
<tr>
    <td colspan="4">لا توجد سمسرات مسجلة</td>
</tr>
@endforelse


{{-- ✅ سطر المجموع المستقل --}}
@if($commissions->count())
<tfoot>
<tr style="background:#f3f3f3">
    <td colspan="3" class="bolddd" style="text-align:center">
        مجموع السمسرة
    </td>
    <td class="bolddd" style="color:#0a6b6b">
        {{ number_format($totalCommission, 2, ',', '.') }}
    </td>
</tr>
</tfoot>
@endif
</tbody>
</table>


<script>
    window.onload = () => window.print();
</script>

</body>
</html>
