<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>{{ $title }}</title>

@php
    $fontRegular = public_path('fonts/Tajawal-Regular.ttf');
    $fontBold    = public_path('fonts/Tajawal-Bold.ttf');

    function money($v) {
        return number_format($v ?? 0, 2, ',', '.');
    }

    $paidTotal = $payments->sum('amount');
    $remaining = max($apartment->total_price - $paidTotal, 0);
    $progress  = $apartment->total_price > 0
        ? round(($paidTotal / $apartment->total_price) * 100, 1)
        : 0;
@endphp

<style>
@font-face {
    font-family: 'Tajawal';
    src: url("file://{{ $fontRegular }}");
}
@font-face {
    font-family: 'Tajawal';
    src: url("file://{{ $fontBold }}");
    font-weight: bold;
}

body {
    font-family: 'Tajawal', sans-serif;
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

.total-box {
    text-align: center;
    margin: 40px 0;
    border: 1px solid #000;   /* ✅ بوردر أسود رقيق */
    padding: 22px;
    border-radius: 10px;
}
.header img {
    width: 130px;
}

.header h1 {
    margin-top: 10px;
    font-size: 26px;
    color: #1b7d3c;
}

/* INFO */
.info-grid {
    display: table;
    width: 100%;
    margin-bottom: 30px;
}

.info-text {
    display: table-cell;
    width: 60%;
    vertical-align: top;
    padding-left: 20px;
}

.info-image {
    display: table-cell;
    width: 40%;
    vertical-align: top;
}

/* ❌ بدون أي Border للصورة */
.image-box {
    padding: 6px;
}

.image-box img {
    width: 100%;
    height: 320px;
    object-fit: cover;
    border-radius: 14px;
}

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
}

.total-box .final {
    font-size: 34px;
    font-weight: bold;
    color: #1b7d3c;
}

.total-box .line {
    margin-bottom: 6px;
}

/* FINANCIAL SUMMARY */
.summary-box {
    margin: 45px 0;
}

.summary-title {
    text-align: center;
    font-weight: bold;
    color: #1b7d3c;
    margin-bottom: 12px;
}

/* ✅ الجداول فقط بحدود سوداء */
.summary-table,
.payments-table {
    width: 100%;
    border-collapse: collapse;
}

.summary-table th,
.summary-table td,
.payments-table th,
.payments-table td {
    border: 1px solid #000;
    padding: 10px;
    text-align: center;
}

.summary-table th,
.payments-table th {
    background: #f4fbf6;
}

/* PAYMENTS */
.payments-title {
    text-align: center;
    font-weight: bold;
    color: #1b7d3c;
    margin: 35px 0 12px;
}
</style>
</head>

<body>
<div class="page">

<!-- HEADER -->
<div class="header">
    <img src="{{ public_path('images/logo-jalili.jpg') }}">
    <h1>{{ $title }}</h1>
</div>

<!-- INFO -->
<div class="info-grid">

    <div class="info-text">
        <div class="label">رقم الشقة</div>
        <div class="value">{{ $apartment->number }}</div>

        <div class="label">الطابق</div>
        <div class="value">
            {{ $apartment->floor == 0 ? 'الطابق الأرضي' : 'الطابق '.$apartment->floor }}
        </div>

        <div class="label">عدد الغرف</div>
        <div class="value">{{ $apartment->rooms }} غرف</div>

        <div class="label">المساحة</div>
        <div class="value">
            {{ $apartment->area }} م²
            @if($apartment->has_terrace)
                | {{ $apartment->terrace_type }} {{ $apartment->terrace_area }} م² 
                – {{ money($apartment->terrace_total_price) }} درهم
            @endif
        </div>

        @if($apartment->parking_number)
        <div class="label">موقف السيارة</div>
        <div class="value">
            رقم {{ $apartment->parking_number }} – {{ money($apartment->parking_price) }} درهم
        </div>
        @endif

        <div class="label">صاحب الشقة</div>
        <div class="value">{{ $apartment->customer_name ?? '—' }}</div>
    </div>

    @if($apartment->image)
    <div class="info-image">
        <div class="image-box">
            <img src="{{ public_path('storage/'.$apartment->image) }}">
        </div>
    </div>
    @endif

</div>

<!-- TOTAL PRICE -->
<div class="total-box">
    @if($apartment->discount > 0)
        <div class="line">
            الثمن قبل التخفيض:
            {{ money($apartment->total_price + $apartment->discount) }} درهم
        </div>
        <div class="line" style="color:#c0392b">
            التخفيض: - {{ money($apartment->discount) }} درهم
        </div>
    @endif

    <div class="final">
        الثمن الإجمالي: {{ money($apartment->total_price) }} درهم
    </div>
</div>

<!-- PAYMENTS -->
@if($payments->count())
<div class="payments-title">تفاصيل الدفوعات</div>


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
                {{ $apartment->total_price > 0
                    ? round(($p->amount / $apartment->total_price) * 100, 1)
                    : 0 }} %
            </td>
            <td>{{ $p->payment_method }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@else


@endif

<!-- FINANCIAL SUMMARY (في الأخير كما طلبت) -->
@if($payments->count())
<div class="summary-box">
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
</div>
@endif

</div>
</body>
</html>
