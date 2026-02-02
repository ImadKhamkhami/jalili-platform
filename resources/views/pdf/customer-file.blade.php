<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm;
        }

        body {
            font-family: Tajawal, sans-serif;
            font-size: 12px;
            direction: rtl;
        }

        h1 {
            text-align: center;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        th, td {
            border: 1px solid #000;
            padding: 6px;
            font-weight: bold;
            text-align: center;
        }

        th {
            background: #f2f2f2;
        }

        h2 {
            margin: 18px 0 8px;
            border-bottom: 1px solid #000;
            padding-bottom: 4px;
        }

        tfoot td {
            font-weight: bold;
            background: #f9f9f9;
        }
    </style>
</head>
<body>

<h1>
    بيان السيد  {{ $customer->name }}
</h1>


{{-- ===== 
معلومات الزبون     
<table style="font-size:13px;">
    <thead>
        <tr>
            <th>الاسم</th>
            <th>رقم البطاقة</th>
            <th>الهاتف</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ $customer->name }}</td>
            <td>{{ $customer->national_id ?? '-' }}</td>
            <td>{{ $customer->phone ?? '-' }}</td>
        </tr>
    </tbody>
</table>
===== --}}


{{-- ================== الشقق ================== --}}
@php
    $apartments = $units->where('type','apartment');
@endphp

@if($apartments->count())
<h2>الشقق</h2>
<table>
    <thead>
        <tr>
            <th>المشروع</th>
            <th>رقم</th>
            <th>العمارة</th>
            <th>الشطر</th>
            <th>الثمن</th>
            <th>المدفوع</th>
            <th>المتبقي</th>
        </tr>
    </thead>
    <tbody>
        @foreach($apartments as $u)
        <tr>
            <td>{{ $u['project_name'] }}</td>
            <td>{{ $u['number'] }}</td>
            <td>{{ $u['building_number'] }}</td>
            <td>{{ $u['tranche_number'] ?? '-' }}</td>
            <td>{{ number_format($u['total_price'],2,',','.') }}</td>
            <td>{{ number_format($u['total_paid'],2,',','.') }}</td>
            <td>{{ number_format($u['remaining'],2,',','.') }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4">الإجمالي</td>
            <td>{{ number_format($apartments->sum('total_price'),2,',','.') }}</td>
            <td>{{ number_format($apartments->sum('total_paid'),2,',','.') }}</td>
            <td>{{ number_format($apartments->sum('remaining'),2,',','.') }}</td>
        </tr>
    </tfoot>
</table>
@endif

{{-- ================== المحلات ================== --}}
@php
    $shops = $units->where('type','shop');
@endphp

@if($shops->count())
<h2>المحلات التجارية</h2>
<table>
    <thead>
        <tr>
            <th>المشروع</th>
            <th>العمارة</th>
            <th>رقم</th>
            <th>الثمن</th>
            <th>المدفوع</th>
            <th>المتبقي</th>
        </tr>
    </thead>
    <tbody>
        @foreach($shops as $u)
        <tr>
            <td>{{ $u['project_name'] }}</td>
            <td>{{ $u['building_number'] }}</td>
            <td>{{ $u['number'] }}</td>
            <td>{{ number_format($u['total_price'],2,',','.') }}</td>
            <td>{{ number_format($u['total_paid'],2,',','.') }}</td>
            <td>{{ number_format($u['remaining'],2,',','.') }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3">الإجمالي</td>
            <td>{{ number_format($shops->sum('total_price'),2,',','.') }}</td>
            <td>{{ number_format($shops->sum('total_paid'),2,',','.') }}</td>
            <td>{{ number_format($shops->sum('remaining'),2,',','.') }}</td>
        </tr>
    </tfoot>
</table>
@endif

{{-- ================== القطع الأرضية ================== --}}
@php
    $lands = $units->where('type','land');
@endphp

@if($lands->count())
<h2>القطع الأرضية</h2>
<table>
    <thead>
        <tr>
            <th>المشروع</th>
            <th> القطعة</th>
            <th>المساحة</th>
            <th>الطريق</th>
            <th>الواجهة</th>
            <th>الثمن</th>
            <th>المدفوع</th>
            <th>المتبقي</th>
            <th>نسبة الاداء</th>
        </tr>
    </thead>
    <tbody>
        @foreach($lands as $u)
        <tr>
            <td>{{ $u['project_name'] }}</td>
            <td>{{ $u['number'] }}</td>
            <td>{{ $u['area'] }} م²</td>
            <td>{{ $u['road_type'] }} م</td>
            <td>{{ $u['view_type'] }}</td>
            <td>{{ number_format($u['total_price'],2,',','.') }}</td>
            <td>{{ number_format($u['total_paid'],2,',','.') }}</td>
            <td>{{ number_format($u['remaining'],2,',','.') }}</td>
            <td>{{ $u['payment_percent'] > 0 ? $u['payment_percent'].' %' : '-' }}</td>

        </tr>
        @endforeach
    </tbody>
  <tfoot>
    <tr>
        <!-- الأعمدة الوصفية -->
        <td colspan="5" style="text-align:center; font-weight:bold;">
            الإجمالي
        </td>

        <!-- الثمن -->
        <td style="font-weight:bold;">
            {{ number_format($lands->sum('total_price'),2,',','.') }}
        </td>

        <!-- المدفوع -->
        <td style="font-weight:bold;">
            {{ number_format($lands->sum('total_paid'),2,',','.') }}
        </td>

        <!-- المتبقي -->
        <td style="font-weight:bold;">
            {{ number_format($lands->sum('remaining'),2,',','.') }}
        </td>
    </tr>
    </tfoot>
</table>
@endif

<!-- 🖨️ طباعة تلقائية -->
<script>
    window.onload = () => window.print();
</script>

</body>
</html>
