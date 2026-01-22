<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>بيان الدفوعات</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 12mm;
        }

        body {
            font-family: 'Tajawal', sans-serif;
            font-size: 12px;
            direction: rtl;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        h2 {
            text-align: center;
            margin: 18px 0 10px 0;
        }

        h3 {
            margin: 14px 0 6px 0;
            border-bottom: 1px solid #000;
            padding-bottom: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
        }

        th {
            background: #f2f2f2;
        }

        .payments {
            text-align: right;
            font-size: 11px;
            line-height: 1.6;
        }

        .page-break {
            page-break-before: always;
        }

        .meta {
            text-align: center;
            margin-bottom: 10px;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body>

{{-- ===== العنوان الرئيسي (مرة واحدة فقط) ===== --}}
@php
    $hasLandsOnly = collect($rows)->every(fn($r) => $r['type'] === 'land');
@endphp

<h1 style="text-align:center; margin-bottom:20px;">
    @if($hasLandsOnly)
        بيان دفوعات تجزئة {{ $project->name }}
    @else
        بيان دفوعات إقامة {{ $project->name }}
    @endif
</h1>

@php
    $groups = collect($rows)->groupBy('group_key');
@endphp

@foreach($groups as $groupKey => $items)

    @php
    $isLandGroup = $groupKey === 'lands';

    if (!$isLandGroup) {
        $firstItem = $items->first();
        $buildingNumber = $firstItem['building'];
        $tranche = $firstItem['tranche'];
    }

        $shops      = $items->where('type', 'shop');
        $apartments = $items->where('type', 'apartment');
        $lands      = $items->where('type', 'land');
    @endphp

    @if(!$loop->first)
        <div class="page-break"></div>
    @endif

    {{-- ===== عنوان العمارة ===== --}}
    @unless($isLandGroup)
        <div class="meta">
            عمارة {{ $buildingNumber }}
            @if($tranche)
                — شطر {{ $tranche }}
            @endif
        </div>
    @endunless

    {{-- ================= المحلات التجارية ================= --}}
    @if($shops->count())
        <h3>المحلات التجارية</h3>
        <table>
            <thead>
                <tr>
                    <th>الرقم</th>
                    <th>المساحة</th>
                    <th>الثمن الإجمالي</th>
                    <th>الدفعات</th>
                    <th>المدفوع</th>
                    <th>المتبقي</th>
                </tr>
            </thead>
            <tbody>
            @foreach($shops as $row)
                <tr>
                    <td>{{ $row['number'] }}</td>
                    <td>{{ $row['area'] }} م²</td>
                    <td>{{ number_format($row['total_price'],2,',','.') }}</td>
                    <td class="payments">
                        @forelse($row['payments'] as $p)
                            {{ number_format($p['amount'],2,',','.') }} — {{ $p['date'] }}<br>
                        @empty —
                        @endforelse
                    </td>
                    <td>{{ number_format($row['paid'],2,',','.') }}</td>
                    <td>{{ number_format($row['remaining'],2,',','.') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    {{-- ================= الشقق ================= --}}
    @if($apartments->count())
        <h3>الشقق</h3>
        <table>
            <thead>
                <tr>
                    <th>الرقم</th>
                    <th>المساحة</th>
                    <th>الثمن الإجمالي</th>
                    <th>الدفعات</th>
                    <th>المدفوع</th>
                    <th>المتبقي</th>
                </tr>
            </thead>
            <tbody>
            @foreach($apartments as $row)
                <tr>
                    <td>{{ $row['number'] }}</td>
                    <td>
                        {{ $row['area'] }} م²
                        @if(!empty($row['terrace']))
                            + {{ $row['terrace'] }} م²
                        @endif
                    </td>
                    <td>{{ number_format($row['total_price'],2,',','.') }}</td>
                    <td class="payments">
                        @forelse($row['payments'] as $p)
                            {{ number_format($p['amount'],2,',','.') }} — {{ $p['date'] }}<br>
                        @empty —
                        @endforelse
                    </td>
                    <td>{{ number_format($row['paid'],2,',','.') }}</td>
                    <td>{{ number_format($row['remaining'],2,',','.') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    {{-- ================= القطع الأرضية ================= --}}
    @if($lands->count())
        <table>
            <thead>
                <tr>
                    <th>رقم القطعة</th>
                    <th>المساحة</th>
                    <th>الثمن الإجمالي</th>
                    <th>الدفعات</th>
                    <th>المدفوع</th>
                    <th>المتبقي</th>
                </tr>
            </thead>
            <tbody>
            @foreach($lands as $row)
                <tr>
                    <td>{{ $row['number'] }}</td>
                    <td>{{ $row['area'] }} م²</td>
                    <td>{{ number_format($row['total_price'],2,',','.') }}</td>
                    <td class="payments">
                        @forelse($row['payments'] as $p)
                            {{ number_format($p['amount'],2,',','.') }} — {{ $p['date'] }}<br>
                        @empty —
                        @endforelse
                    </td>
                    <td>{{ number_format($row['paid'],2,',','.') }}</td>
                    <td>{{ number_format($row['remaining'],2,',','.') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

@endforeach

</body>
</html>
