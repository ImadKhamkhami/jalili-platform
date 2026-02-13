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
            color: #000;
        }

        .remaining-zero {
    background-color: #ffe082; /* أصفر واضح */
}

.solded {  /* أخضر فاتح */
    background-color: #ffe082;
    color: #0b0c0c;
    font-weight: bold;
}
.boooold{
font-size: 15px;
font-weight: bold;
}


        .discount {
    color: #c62828; /* أحمر */
    font-weight: bold;
}

.payment-amount {
    color: #145217; /* أخضر */
    font-weight: bold;
    font-size: 14px;
}

.payment-date {
    color: #000;
    font-size: 13px;
    font-weight: bold;
}

        .header {
            text-align: center;
            margin-bottom: 18px;
        }

        .header h1 {
            font-size: 18px;
            margin: 0;
        }

        .meta {
            text-align: center;
            margin: 8px 0 14px 0;
            font-size: 13px;
            font-weight: bold;
        }

        h3 {
            margin: 14px 0 6px 0;
            border-bottom: 1px solid #000;
            padding-bottom: 4px;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th, td {
            border: 1px solid #000;
            padding: 6px;
            font-weight: bold;
            font-size: 14px;
            text-align: center;
            vertical-align: middle;
        }

        th {
            background: #f2f2f2;
            font-weight: bold;
        }

        .payments {
            text-align: right;
            font-size: 11px;
            line-height: 1.6;
        }

        .total-project {
            margin-top: 30px;
            font-weight: bold;
            background: #f9f9f9;
        }

        .page-break {
            page-break-before: always;
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
    $hasLandsOnly = collect($rows)->every(fn($r) => $r['type'] === 'land');

    // ===== مجموع المشروع كامل =====
    $projectTotalPrice = collect($rows)->sum('total_price');
    $projectPaid       = collect($rows)->sum('paid');
    $projectRemaining  = collect($rows)->sum('remaining');
@endphp

<!-- ===== HEADER ===== -->
<div class="header">
    <h2>
        @if($hasLandsOnly)
            بيان دفوعات تجزئة {{ $project->name }}
        @else
            بيان دفوعات إقامة {{ $project->name }}
        @endif
    </h2>
</div>

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

    @unless($isLandGroup)
        <div class="meta">
            عمارة {{ $buildingNumber }}
            @if($tranche)
                — شطر {{ $tranche }}
            @endif
        </div>
    @endunless

    {{-- ===== المحلات ===== --}}
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
                    <td class="font-bold">{{ $row['number'] }}</td>
                    <td class="font-bold">{{ $row['area'] }} م²</td>
                    <td class="font-bold">{{ number_format($row['total_price'],2,',','.') }}</td>
                    <td class="payments font-bold">
                        @forelse($row['payments'] as $p)
                            {{ number_format($p['amount'],2,',','.') }} — {{ $p['date'] }}<br>
                        @empty — @endforelse
                    </td>
                    <td class="font-bold">{{ number_format($row['paid'],2,',','.') }}</td>
                    <td class="font-bold">{{ number_format($row['remaining'],2,',','.') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    {{-- ===== الشقق ===== --}}
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
                    <td class="font-bold">{{ $row['number'] }}</td>
                    <td class="font-bold">
                        {{ $row['area'] }} م²
                        @if(!empty($row['terrace']))
                            + {{ $row['terrace'] }} م²
                        @endif
                    </td>
                    <td class="font-bold">{{ number_format($row['total_price'],2,',','.') }}</td>
                    <td class="payments font-bold">
                        @forelse($row['payments'] as $p)
                            {{ number_format($p['amount'],2,',','.') }} — {{ $p['date'] }}<br>
                        @empty — @endforelse
                    </td>
                    <td class="font-bold">{{ number_format($row['paid'],2,',','.') }}</td>
                    <td class="font-bold">{{ number_format($row['remaining'],2,',','.') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    {{-- ===== القطع الأرضية ===== --}}
    @if($lands->count())
        <table>
            <thead>
                <tr>
                    <th>N°</th>
                    <th>مساحة</th>
                    <th>التخفيض</th>
                    <th>ثمن إجمالي </th>
                    <th>الدفعات</th>
                    <th> % </th>
                    <th>المدفوع</th>
                    <th>المتبقي</th>
                </tr>
            </thead>
            <tbody>
            @foreach($lands as $row)
                <tr>
                    <td class="font-bold">{{ $row['number'] }}</td>
                    <td class="font-bold">{{ $row['area'] }} م²</td>
                    <td class="discount">
                    @if(($row['discount'] ?? 0) > 0)
                    {{ number_format($row['discount'],2,',','.') }}
                      @else
                     -
                     @endif
                    </td>
                    <td class="font-bold">{{ number_format($row['total_price'],2,',','.') }}</td>
                    <td class="payments font-bold">
                        @forelse($row['payments'] as $p)
                            <span class="payment-amount">
                          {{ number_format($p['amount'],2,',','.') }}
                        </span>
                        <span class="payment-date">
                         — {{ $p['date'] }}
                        </span>
                         <br>
                        @empty — @endforelse
                    </td>
      <td>
  @if(($row['payment_percent'] ?? 0) > 0)
      {{ round($row['payment_percent']) }} %
  @else
      -
  @endif
</td>

                     <td class="font-bold">
                       {{ $row['paid'] > 0 ? number_format($row['paid'],2,',','.') : '-' }}
                    </td>
<td class="font-bold {{ ($row['paid'] > 0 && $row['remaining'] == 0) ? 'solded' : '' }}">
    @if($row['paid'] > 0 && $row['remaining'] == 0)
    خالص
    @elseif($row['paid'] > 0)
        {{ number_format($row['remaining'],2,',','.') }}
    @else
        -
    @endif
</td>



                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

@endforeach

{{-- ===== المجموع النهائي للمشروع ===== --}}
<table class="total-project">
    <tr>
        <th>مجموع  دفوعات  {{ $project->name }}</th>
        <th class="boooold">{{ number_format($projectPaid,2,',','.') }}</th>
    </tr>
</table>

<script>
    window.onload = () => window.print();
</script>

</body>
</html>
