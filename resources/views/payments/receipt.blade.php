<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>توصيل دفعة</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        body {
            font-family: 'Tajawal', sans-serif;
            font-size: 15px;
            direction: rtl;
        }
        .receipt-title {
            font-size: 20px;
            font-weight: bold;
            margin-top: 8px;
            margin-bottom: 25px; /* ⬅️ المسافة المطلوبة */
            text-align: center;
        }

        .half {
            height: 130mm; /* ✅ نصف الصفحة فعليًا */
            border: 1px solid #000;
            padding: 18px;
            box-sizing: border-box;
        }

        .separator {
            border-top: 1px dashed #000;
            margin: 6mm 0;
        }

        .logo {
            text-align: center;
            margin-bottom: 8px;
        }

        .logo img {
            height: 90px; /* تكبير الشعار */
        }

        .title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 16px;
        }

        table.info {
            width: 90%;
            margin: auto;
            border-collapse: collapse;
        }

        table.info td {
            padding: 6px;
            text-align: center;
            vertical-align: middle;
        }

        .label {
            color: #666;
            font-size: 12px;
        }

        .value {
            font-weight: bold;
        }

        .amount {
            color: #000; /* ✅ أسود */
            font-weight: bold;
            font-size: 14px;
        }

        table.signatures {
            width: 100%;
            margin-top: 20mm;
        }

        table.signatures td {
            width: 50%;
            text-align: center;
        }
    </style>
</head>

<body>

@php
function unitDescription($payment) {
    if ($payment->context === 'apartment') {
        return 'شقة رقم ' . $payment->apartment->number;
    }
    if ($payment->context === 'shop') {
        return 'محل رقم ' . $payment->shop->number;
    }
    if ($payment->context === 'land') {
        return 'قطعة رقم ' . $payment->land->land_number;
    }
    return '—';
}
@endphp

{{-- ================= نسخة الزبون ================= --}}
<div class="half">

    <div class="logo">
        <img src="{{ asset('images/jalili-logo.png') }}">
    </div>

    <div class="receipt-title">توصيل</div>

    <table class="info">
        <tr>
            <td class="label">المشروع</td>
            <td class="value">{{ $payment->project->name }}</td>
            <td class="label">الوحدة</td>
            <td class="value">{{ unitDescription($payment) }}</td>
        </tr>

        <tr>
            <td class="label">المبلغ</td>
            <td class="amount">{{ number_format($payment->amount, 2, ',', '.') }} درهم</td>
            <td class="label">طريقة الدفع</td>
            <td class="value">{{ $payment->payment_method }}</td>
        </tr>

        <tr>
            <td class="label">التاريخ</td>
            <td class="value">{{ \Carbon\Carbon::parse($payment->paid_at)->format('Y-m-d') }}</td>
            <td class="label">رقم التوصيل</td>
            <td class="value">{{ $payment->receipt_number }}</td>
        </tr>
    </table>

    <table class="signatures">
        <tr>
            <td>توقيع الزبون<br><br>------------------------</td>
            <td>توقيع الإدارة<br><br>------------------------</td>
        </tr>
    </table>
</div>

<div class="separator"></div>

{{-- ================= نسخة الإدارة ================= --}}
<div class="half">

    <div class="logo">
        <img src="{{ asset('images/jalili-logo.png') }}">
    </div>

    <div class="receipt-title">توصيل</div>

    <table class="info">
        <tr>
            <td class="label">المشروع</td>
            <td class="value">{{ $payment->project->name }}</td>
            <td class="label">الوحدة</td>
            <td class="value">{{ unitDescription($payment) }}</td>
        </tr>

        <tr>
            <td class="label">المبلغ</td>
            <td class="amount">{{ number_format($payment->amount, 2, ',', '.') }} درهم</td>
            <td class="label">طريقة الدفع</td>
            <td class="value">{{ $payment->payment_method }}</td>
        </tr>

        <tr>
            <td class="label">التاريخ</td>
            <td class="value">{{ \Carbon\Carbon::parse($payment->paid_at)->format('Y-m-d') }}</td>
            <td class="label">رقم التوصيل</td>
            <td class="value">{{ $payment->receipt_number }}</td>
        </tr>
    </table>

    <table class="signatures">
        <tr>
            <td>توقيع الزبون<br><br>------------------------</td>
            <td>توقيع الإدارة<br><br>------------------------</td>
        </tr>
    </table>
</div>

<!-- 🖨️ طباعة تلقائية -->
<script>
    window.onload = () => window.print();
</script>

</body>
</html>
