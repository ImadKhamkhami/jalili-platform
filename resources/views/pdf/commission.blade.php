<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>توصيل سمسرة</title>

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
            margin-bottom: 25px;
            text-align: center;
            color: #0d47a1; /* 🔵 أزرق */
        }

        .half {
            height: 130mm;
            border: 2px solid #1565c0; /* 🔵 إطار أزرق */
            padding: 18px;
            box-sizing: border-box;
        }

        .separator {
            border-top: 1px dashed #1565c0;
            margin: 6mm 0;
        }

        .logo {
            text-align: center;
            margin-bottom: 8px;
        }

        .logo img {
            height: 90px;
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
            color: #555;
            font-size: 12px;
        }

        .value {
            font-weight: bold;
        }

        .amount {
            color: #0d47a1; /* 🔵 مبلغ أزرق */
            font-weight: bold;
            font-size: 15px;
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
function unitDescription($commission) {
    if ($commission->context === 'land') {
        return 'قطعة ' . optional($commission->land)->land_number;
    }
    if ($commission->context === 'apartment') {
        return 'شقة ' . optional($commission->apartment)->number;
    }
    if ($commission->context === 'shop') {
        return 'محل ' . optional($commission->shop)->number;
    }
    return '—';
}
@endphp

{{-- ================= نسخة الزبون ================= --}}
<div class="half">

    <div class="logo">
        <img src="{{ asset('images/jalili-logo.png') }}">
    </div>

    <div class="receipt-title">توصيل سمسرة</div>

    <table class="info">
        <tr>
            <td class="label">المشروع</td>
            <td class="value">
                {{ $commission->project->name ?? '-' }}
            </td>
            <td class="label">الوحدة</td>
            <td class="value">
                {{ unitDescription($commission) }}
            </td>
        </tr>

        <tr>
            <td class="label">مبلغ السمسرة</td>
            <td class="amount">
                {{ number_format($commission->amount, 2, ',', '.') }} درهم
            </td>
            <td class="label">اسم السمسار</td>
            <td class="value">
                {{ $commission->broker_name ?? '-' }}
            </td>
        </tr>

        <tr>
            <td class="label">التاريخ</td>
            <td class="value">
                {{ \Carbon\Carbon::parse($commission->commission_date)->format('Y-m-d') }}
            </td>
        </tr>
    </table>

    <table class="signatures">
        <tr>
            <td>توقيع السمسار<br><br>------------------------</td>
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

    <div class="receipt-title">توصيل سمسرة</div>

    <table class="info">
        <tr>
            <td class="label">المشروع</td>
            <td class="value">
                {{ $commission->project->name ?? '-' }}
            </td>
            <td class="label">الوحدة</td>
            <td class="value">
                {{ unitDescription($commission) }}
            </td>
        </tr>

        <tr>
            <td class="label">مبلغ السمسرة</td>
            <td class="amount">
                {{ number_format($commission->amount, 2, ',', '.') }} درهم
            </td>
            <td class="label">اسم السمسار</td>
            <td class="value">
                {{ $commission->broker_name ?? '-' }}
            </td>
        </tr>

        <tr>
            <td class="label">التاريخ</td>
            <td class="value">
                {{ \Carbon\Carbon::parse($commission->commission_date)->format('Y-m-d') }}
            </td>
        </tr>
    </table>

    <table class="signatures">
        <tr>
            <td>توقيع السمسار<br><br>------------------------</td>
            <td>توقيع الإدارة<br><br>------------------------</td>
        </tr>
    </table>
</div>

<script>
    window.onload = () => window.print();
</script>

</body>
</html>
