<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>فسخ البيع والتنازل</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 25mm 30mm;
        }
        /*         وهو مبلغ قدره:
        <span class="highlight price">
            {{ number_format($unitPrice, 2, ',', ' ') }} درهم
        </span>،*/

        body {
            font-family: "Tajawal", sans-serif;
            font-size: 15px;
            line-height: 2.2;
            color: #000;
        }

        h1 {
            text-align: center;
            font-size: 22px;
            margin-bottom: 35px;
            text-decoration: underline;
        }

        .price {
            direction: ltr;
            unicode-bidi: isolate;
            display: inline-block;
            font-weight: bold;
        }

        .section {
            margin-bottom: 22px;
            text-align: justify;
            page-break-inside: avoid;
        }

        .highlight {
            font-weight: bold;
        }

        .footer {
            margin-top: 50px;
            page-break-inside: avoid;
        }

        .signature {
            margin-top: 70px;
            display: flex;
            justify-content: space-between;
        }

        .signature div {
            width: 45%;
            text-align: center;
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
    $unitLabel = match($transfer->context) {
        'land'      => 'القطعة رقم ' . ($transfer->land->land_number ?? '-'),
        'apartment' => 'الشقة رقم ' . ($transfer->apartment->number ?? '-'),
        'shop'      => 'المحل رقم ' . ($transfer->shop->number ?? '-'),
    };

    $unitArea = match($transfer->context) {
        'land'      => $transfer->land->area ?? null,
        'apartment' => $transfer->apartment->area ?? null,
        'shop'      => $transfer->shop->area ?? null,
    };

    $unitPrice = match($transfer->context) {
        'land'      => $transfer->land->total_price ?? 0,
        'apartment' => $transfer->apartment->total_price ?? 0,
        'shop'      => $transfer->shop->total_price ?? 0,
    };

    $project = match($transfer->context) {
        'land'      => $transfer->land->project ?? null,
        'apartment' => $transfer->apartment->building->project ?? null,
        'shop'      => $transfer->shop->building->project ?? null,
    };

    $company = $project?->company;

    $projectTypeLabel = $project?->type === 'building'
        ? 'إقامة'
        : 'تجزئة';
@endphp

<h1>
    فسخ البيع والتنازل عن
    {{ $transfer->context === 'land' ? 'قطعة أرضية' : ($transfer->context === 'apartment' ? 'شقة' : 'محل تجاري') }}
</h1>

<div class="section">
    <p>
        أنا الموقع أسفله، السيد:
        <span class="highlight">{{ $transfer->fromCustomer->name }}</span>،
        الحامل لبطاقة التعريف الوطنية رقم
        <span class="highlight">{{ $transfer->fromCustomer->national_id ?? '---' }}</span>.
    </p>
</div>

<div class="section">
    <p>
        أشهد وأصرّح، تحت كافة الضمانات العقلية والقانونية، أنني أتنازل تنازلاً
        تامًا لا رجعة فيه لفائدة شركة:
        <span class="highlight">{{ $company->name ?? '---' }}</span>،
        الكائن مقرها الاجتماعي شارع مولاي إسماعيل، إقامة وليلي، عمارة B رقم 43، الطابق الأول، طنجة،
        الممثلة في شخص ممثلها القانوني،
        وذلك عن جميع حقوقي المتعلقة بـ
        <span class="highlight">{{ $unitLabel }}</span>
        @if($unitArea)
            ذات مساحة
            <span class="highlight">{{ $unitArea }} م²</span>
        @endif
        المستخرجة من
        <span class="highlight">
            {{ $projectTypeLabel }} {{ $project->name ?? '---' }}
        </span>
        ذات الرسم العقاري عدد
        <span class="highlight">{{ $project->titre_foncier ?? '---' }}</span>.
    </p>
</div>

<div class="section">
    <p>
        كما أصرّح بأنني توصلت بكامل المبلغ المؤدى لفائدة الشركة المذكورة،
        إبراءً تامًا ونهائيًا لا رجعة فيه.
    </p>
</div>

<div class="section">
    <p>
        واعتبارًا من تاريخ التوقيع على هذا العقد، تصبح الشركة المالكة
        الوحيدة والشرعية للوحدة المذكورة، ولها كامل الصلاحية في
        التصرف فيها تصرف المالك في ملكه.
    </p>
</div>

<div class="section">
    <p>
        وبهذا أوقع على هذا التنازل وأنا في كامل قواي العقلية،
        للإدلاء به عند الاقتضاء.
    </p>
</div>

<div class="footer">
    <p class="highlight">
        حرر بطنجة بتاريخ {{ \Carbon\Carbon::parse($transfer->transfer_date)->format('Y/m/d') }}
    </p>

    <div class="signature">
        <div>
            الاسم الكامل:<br>
            <strong>{{ $transfer->fromCustomer->name }}</strong>
        </div>

        <div>
            التوقيع:<br><br>
            ------------------------
        </div>
    </div>
</div>

<script>
    window.onload = () => window.print();
</script>

</body>
</html>

