<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>{{ $title }}</title>

@php
    function money($v) {
        return number_format($v ?? 0, 2, ',', '.');
    }
@endphp

<style>
/* PAGE */
@page {
    margin: 15mm 12mm;
}

body {
    font-family: Tajawal, Arial, sans-serif;
    font-size: 14px;
    color: #333;
    margin: 0;
}

.page {
    width: 92%;
    margin: 30px auto;
}

/* HEADER */
.header {
    text-align: center;
    margin-bottom: 30px;
}

.header img {
    width: 130px;
}

.header h1 {
    margin-top: 10px;
    font-size: 26px;
    color: #1b7d3c;
}

/* INFO GRID (TABLE SAFE FOR PRINT) */
.info-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 30px;
}

.info-table td {
    vertical-align: top;
    padding-left: 20px;
}

/* IMAGE */
.image-box img {
    width: 300px;
    max-width: 100%;
    height: auto;
    border-radius: 12px;
}

/* LABELS */
.label {
    font-weight: bold;
    color: #1b7d3c;
    margin-bottom: 4px;
}

.value {
    margin-bottom: 12px;
}

/* TOTAL PRICE */
.total-box {
    text-align: center;
    margin: 40px 0;
    border: 1px solid #000;
    padding: 22px;
    border-radius: 10px;
}

.total-box .final {
    font-size: 32px;
    font-weight: bold;
    color: #1b7d3c;
}

/* TABLES */
.payments-table,
.summary-table {
    width: 100%;
    border-collapse: collapse;
}

.payments-table th,
.payments-table td,
.summary-table th,
.summary-table td {
    border: 1px solid #000;
    padding: 10px;
    text-align: center;
}

.payments-table th,
.summary-table th {
    background: #f4fbf6;
}

/* TITLES */
.payments-title,
.summary-title {
    text-align: center;
    font-weight: bold;
    color: #1b7d3c;
    margin: 35px 0 12px;
}
</style>
</head>
<script>
    window.onload = function () {
        window.print();
    };
</script>


<body>
<div class="page">

<!-- HEADER -->
<div class="header">
    <h1>{{ $title }}</h1>
</div>

<!-- INFO -->
<table class="info-table">
    <tr>
        <td width="60%">
            <div class="label">رقم القطعة</div>
            <div class="value">{{ $land->land_number }}</div>

            <div class="label">المساحة</div>
            <div class="value">{{ $land->area }} م²</div>

            <div class="label">نوع الطريق</div>
            <div class="value">{{ $land->road_type }}</div>

            <div class="label">الواجهة</div>
            <div class="value">{{ $land->view_type }}</div>

            <div class="label">حالة القطعة</div>
            <div class="value">{{ $land->status }}</div>

            <div class="label">صاحب القطعة</div>
            <div class="value">{{ $land->customer_name ?? '—' }}</div>
        </td>

        @if($land->image)
        <td width="40%" align="center">
            <div class="image-box">
                <img src="{{ asset('storage/'.$land->image) }}" alt="land">
            </div>
        </td>
        @endif
    </tr>
</table>

<!-- TOTAL PRICE -->
<div class="total-box">
    <div class="final">
        الثمن الإجمالي: {{ money($land->total_price) }} درهم
    </div>
</div>

<!-- PAYMENTS -->
@if($payments->count())
<div class="payments-title"> الدفوعات</div>

<table class="payments-table">
    <thead>
        <tr>
            <th>التاريخ</th>
            <th>المبلغ</th>
            <th>النسبة</th>
            <th>الطريقة</th>
        </tr>
    </thead>
    <tbody>
        @foreach($payments as $p)
        <tr>
            <td>{{ $p->paid_at->format('d/m/Y') }}</td>
            <td>{{ money($p->amount) }}</td>
            <td>
                {{ $land->total_price > 0
                    ? round(($p->amount / $land->total_price) * 100, 1)
                    : 0 }} %
            </td>
            <td>{{ $p->payment_method }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

<!-- SUMMARY -->
@if($payments->count())
<div class="summary-title">الملخص المالي</div>

<table class="summary-table">
    <tr>
        <th>مجموع الدفوعات</th>
        <th>المتبقي</th>
        <th>نسبة الأداء</th>
    </tr>
    <tr>
        <td>{{ money($paidTotal) }} درهم</td>
        <td>{{ money($remaining) }} درهم</td>
        <td>{{ $progress }} %</td>
    </tr>
</table>
@endif

</div>
</body>
</html>
