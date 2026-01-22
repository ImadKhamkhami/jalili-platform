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
    $remaining = max($shop->total_price - $paidTotal, 0);
    $progress  = $shop->total_price > 0
        ? round(($paidTotal / $shop->total_price) * 100, 1)
        : 0;

    $baseShopPrice = $shop->area * $shop->price_per_m2;
    $mezzaninePrice = $shop->mezzanine_total_price ?? 0;
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
    text-align: center;
    margin: 25px 0;
}

.image-box img {
    width: 340px;
    max-width: 100%;
    height: auto;
    object-fit: contain;
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

.total-box {
    text-align: center;
    margin: 40px 0;
    border: 1px solid #000;
    padding: 22px;
    border-radius: 10px;
}

.total-box .final {
    font-size: 34px;
    font-weight: bold;
    color: #1b7d3c;
}

.total-box .line {
    margin-bottom: 6px;
}

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

.payments-title,
.summary-title {
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

        <div class="label">رقم المحل</div>
        <div class="value">{{ $shop->number }}</div>

        <div class="label">العمارة</div>
        <div class="value">{{ $shop->building_number }}</div>

        <div class="label">المساحة</div>
        <div class="value">{{ $shop->area }} م²</div>

        <div class="label">ثمن المتر</div>
        <div class="value">{{ money($shop->price_per_m2) }} درهم</div>

        @if($shop->mezzanine_area && $shop->mezzanine_area > 0)
            <div class="label">MEZZANINE</div>
            <div class="value">
                {{ $shop->mezzanine_area }} م² —
                {{ money($shop->mezzanine_total_price) }} درهم
            </div>
        @endif

        <div class="label">حالة المحل</div>
        <div class="value">{{ $shop->status }}</div>

        <div class="label">صاحب المحل</div>
        <div class="value">{{ $shop->customer_name ?? '—' }}</div>
    </div>

    @if($shop->image)
    <div class="info-image">
        <div class="image-box">
            <img src="{{ public_path('storage/'.$shop->image) }}">
        </div>
    </div>
    @endif
</div>

<!-- TOTAL PRICE -->
<div class="total-box">

    @if($mezzaninePrice > 0)
        <div class="line">
            ثمن المحل: {{ money($baseShopPrice) }} درهم
        </div>
        <div class="line">
           MEZZANINE ثمن : {{ money($mezzaninePrice) }} درهم
        </div>
    @endif

    @if($shop->discount > 0)
        <div class="line" style="color:#c0392b">
            التخفيض: - {{ money($shop->discount) }} درهم
        </div>
    @endif

    <div class="final">
        الثمن الإجمالي: {{ money($shop->total_price) }} درهم
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
                {{ $shop->total_price > 0
                    ? round(($p->amount / $shop->total_price) * 100, 1)
                    : 0 }} %
            </td>
            <td>{{ $p->payment_method }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

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
