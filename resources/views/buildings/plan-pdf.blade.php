<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">

    <style>
        @page {
            size: A4 portrait;
            margin: 12mm;
        }

        body {
            font-family: 'Tajawal', sans-serif;
            direction: rtl;
            margin: 20px;
            color: #000;
        }

        h2.title {
            text-align: center;
            color: #0a6b6b;
            margin-bottom: 25px;
            font-size: 22px;
            font-weight: bold;
        }

        .section-title {
            font-size: 20px;
            font-weight: bold;
            color: #0a6b6b;
            margin: 25px 0 10px;
            text-align: right;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed; /* ✅ مهم للطباعة */
            margin-bottom: 30px;
        }

        td {
            border: 2px solid #000;
            width: 20%;                 /* 5 خانات ثابتة */
            height: 140px;              /* ارتفاع موحّد */
            padding: 28px 8px 10px;     /* فراغ علوي للشارات */
            vertical-align: top;
            text-align: center;
            position: relative;
        }

        /* شارة موقف السيارة */
        .parking-badge {
            position: absolute;
            top: 6px;
            right: 6px;
            padding: 2px 7px;
            border-radius: 10px;
            font-size: 11.5px;
            font-weight: bold;
            border: 1px solid #1e88e5;
            color: #1e88e5;
            background: #fff;
        }

        .box-title {
            font-size: 17px;
            font-weight: bold;
            margin: 4px 0 6px;
        }

        .info {
            font-size: 13.5px;
            margin: 4px 0;
            line-height: 1.7;
            white-space: nowrap;
        }

        .owner {
            margin-top: 8px;
            font-size: 14.5px;
            font-weight: bold;
            color: #c62828;
        }

        /* شارة مباع */
        .sold-badge {
            background: #ffe082;
            border: 1px solid #f9a825;
            padding: 2px 8px;
            border-radius: 6px;
            display: inline-block;
        }

        /* ألوان الطباعة */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>

<h2 class="title">
    إقامة {{ $project->name }} — العمارة {{ $building->name }}
    @if($tranche) — الشطر {{ $tranche }} @endif
</h2>

{{-- ====================== المحلات التجارية ====================== --}}
@if($shops->count())

<div class="section-title">المحلات التجارية</div>

<table>
@foreach($shops->chunk(5) as $row)
<tr>
    @foreach($row as $shop)
        <td>

            <div class="box-title">
                @if($shop->status === 'مباع')
                    <span class="sold-badge">محل {{ $shop->number }}</span>
                @else
                    محل {{ $shop->number }}
                @endif
            </div>

            <div class="info">
                <strong>{{ $shop->area }}</strong> م²
            </div>

            @if($shop->customer_name)
                <div class="owner">
                    {{ $shop->customer_name }}
                </div>
            @endif

        </td>
    @endforeach
</tr>
@endforeach
</table>

@endif

{{-- ====================== الشقق ====================== --}}
@foreach($apartments as $floor => $items)

<div class="section-title">
    الطابق {{ $floor == 0 ? 'الأرضي' : $floor }}
</div>

<table>
@foreach($items->chunk(5) as $row)
<tr>
    @foreach($row as $ap)
        <td>

            {{-- موقف السيارة --}}
            @if($ap->parking_number)
                <span class="parking-badge">
                    P {{ $ap->parking_number }}
                </span>
            @endif

            <div class="box-title">
                @if($ap->status === 'مباعة')
                    <span class="sold-badge">شقة {{ $ap->number }}</span>
                @else
                    شقة {{ $ap->number }}
                @endif
            </div>

            <div class="info">
                @if($ap->has_terrace)
                    <strong>{{ $ap->area }} م² + {{ $ap->terrace_area }}</strong> م²
                @else
                    <strong>{{ $ap->area }}</strong> م²
                @endif
                &nbsp;—&nbsp;
                <strong>{{ $ap->rooms }}</strong> غرف
            </div>

            @if($ap->customer_name)
                <div class="owner">
                    {{ $ap->customer_name }}
                </div>
            @endif

        </td>
    @endforeach
</tr>
@endforeach
</table>

@endforeach

<!-- 🖨️ طباعة تلقائية -->
<script>
    window.onload = () => window.print();
</script>

</body>
</html>
