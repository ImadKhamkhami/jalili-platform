<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>فسخ البيع والتنازل</title>

    <style>
        body {
            font-family: "Tajawal", sans-serif;
            font-size: 15px;
            line-height: 2.2;
            margin: 40px;
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
        }

        .highlight {
            font-weight: bold;
        }

        .box {
            border: 1px solid #000;
            padding: 14px 18px;
            margin: 25px 0;
        }

        .footer {
            margin-top: 45px;
        }

        .signature {
            margin-top: 80px;
            display: flex;
            justify-content: space-between;
        }

        .signature div {
            width: 45%;
        }

        .text-right { text-align: right; }
        .text-left { text-align: left; }
    </style>
</head>

<body>

@php
    /* =========================
       تحديد الوحدة
    ========================= */
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

    /* =========================
       المشروع + الشركة
    ========================= */
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

<!-- ================== هوية المتنازل ================== -->
<div class="section">
    <p>
        أنا الموقع أسفله، السيد:
        <span class="highlight">{{ $transfer->fromCustomer->name }}</span>،
        الحامل لبطاقة التعريف الوطنية رقم
        <span class="highlight">{{ $transfer->fromCustomer->national_id ?? '---' }}</span>.
    </p>
</div>

<!-- ================== موضوع التنازل ================== -->
<div class="section">
    <p>
        أشهد وأصرّح، تحت كافة الضمانات العقلية والقانونية، أنني أتنازل تنازلاً
        تامًا لا رجعة فيه لفائدة شركة:
        <span class="highlight">{{ $company->name ?? '---' }}</span>،
         الكائن مقرها الاجتماعي شارع مولاي اسماعيل ,اقامة وليلي ,عمارة ب رقم 43 الطلبق الاول طنجة ,الممثلة في شخص ممثلها القانوني
       ودالك عن جميع حقوقي المتعلقة بـ
        <span class="highlight">{{ $unitLabel }}</span>
        @if($unitArea)
            ذات مساحة 
            <span class="highlight">{{ $unitArea }}m² متر مربع</span>
        @endif
        المستخرجة من
        <span class="highlight">
            {{ $projectTypeLabel }} {{ $project->name ?? '---' }}
        </span>
        ذات الرسم العقاري عدد
        <span class="highlight">{{ $project->titre_foncier ?? '---' }}</span>.
    </p>
</div>

<!-- ================== الثمن ================== -->
<div >
    <p>
        كما أصرّح بأنني توصلت بكامل المبلغ المؤدى لفائدة الشركة المذكورة،
        وهو مبلغ قدره:
        <span class="highlight price">
            {{ number_format($unitPrice, 2, ',', ' ') }} درهم
        </span>،
        إبراءً تامًا ونهائيًا لا رجعة فيه.
    </p>
</div>

<!-- ================== نقل الملكية ================== -->
<div class="section">
    <p>
        واعتبارًا من تاريخ التوقيع على هذا العقد، تصبح الشركة المالكة
        الوحيدة والشرعية للوحدة المذكورة، ولها كامل الصلاحية في
        التصرف فيها تصرف المالك في ملكه.
    </p>
</div>

<!-- ================== الخاتمة ================== -->
<div class="section">
    <p>
        وبهذا أوقع على هذا التنازل وأنا في كامل قواي العقلية،
        للإدلاء به عند الاقتضاء.
    </p>
</div>

<!-- ==================  التاريخ والمكان و التوقيع ================== -->
<div class="footer text-right">
    <p>
       
        <span class="highlight">
             حرر بـطنجة بتاريخ {{ \Carbon\Carbon::parse($transfer->transfer_date)->format('Y/m/d') }}
        </span>
        <br>
        <p class="highlight"> الاسم الكامل :{{ $transfer->fromCustomer->name }}</p>
        <p class="highlight">التوقيع:</p>
    </p>
</div>

</body>
</html>

