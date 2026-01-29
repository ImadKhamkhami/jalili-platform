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

.current-owner {
    color: #000000;
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
        }

        .stats-total {
            background: #f3f3f3;
        }

        /* ===================================================
           مخطط القطع
        =================================================== */
.plan-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 40px;
    table-layout: fixed;
}

        .plan-table tr {
        page-break-inside: avoid;
        }

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

        /* 🟡 القطع المباعة */
        .sold-badge {
            background: #ffe082;
            border: 1px solid #f9a825;
            padding: 2px 8px;
            border-radius: 6px;
            display: inline-block;
        }

        /* ===================== فاصل صفحة ===================== */
        .page-break {
            page-break-before: always;
        }

        /* ===================================================
           جدول الملخص حسب الزبناء
        =================================================== */
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

        /* ===== إعدادات الطباعة ===== */
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

{{-- ===================== العنوان ===================== --}}
<h2 class="title">
    تجزئة {{ $project->name }}
</h2>

{{-- ===================== الإحصائيات ===================== --}}
<div class="stats-row">
    <span class="stats-total">إجمالي القطع : {{ $totalLands }}</span>
    <span class="stats-total">القطع المباعة : {{ $soldLands }}</span>
    <span class="stats-total">الباقي : {{ $vtLands }}</span>
</div>

{{-- ===================== مخطط القطع ===================== --}}
<table class="plan-table">
@foreach($lands->chunk(4) as $row)
    <tr>
        @foreach($row as $land)

            @php
                $facadeLabel = $land->view_type === '2-FACADE' ? '2F' : '1F';
            @endphp

            <td>
<div class="land-title">
    @if($land->status === 'مباعة')
        <span class="sold-badge">قطعة {{ $land->land_number }}</span>

    @elseif($land->status === 'محجوزة')
        <span class="reserved-underline">
            قطعة {{ $land->land_number }}
        </span>

    @else
        قطعة {{ $land->land_number }}
    @endif
</div>
                <div class="info">
                    <strong>{{ $land->area }}</strong> م²
                    &nbsp;—&nbsp;
                    <strong>{{ $land->road_type }}</strong> م
                    &nbsp;—&nbsp;
                    <strong>{{ $facadeLabel }}</strong>
                </div>

@if($land->owners_history->count())
    <div class="owner">
        @foreach($land->owners_history as $owner)
            @if($owner['current'])
                <span class="current-owner">
                    {{ $owner['name'] }}
                </span>
            @else
                <span class="previous-owner">
                    {{ $owner['name'] }}
                </span>
            @endif
        @endforeach
    </div>
@endif



            </td>

        @endforeach
    </tr>
@endforeach
</table>

{{-- ===================== صفحة جديدة ===================== --}}
<div class="page-break"></div>

{{-- ===================== ملخص القطع حسب الزبناء ===================== --}}
<h2 class="title">
    تجزئة {{ $project->name }} — توزيع القطع حسب الزبناء
</h2>

@php
    $grouped = $lands
        ->filter(fn($l) => !empty($l->customer_name))
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
                <td class="bolddd">
                    {{ $items->pluck('land_number')->sort()->implode(' ، ') }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<!-- 🖨️ طباعة تلقائية -->
<script>
    window.onload = () => window.print();
</script>

</body>
</html>