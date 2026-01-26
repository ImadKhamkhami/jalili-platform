<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>

    <style>
        body {
            font-family: 'Tajawal', sans-serif;
            font-size: 13px;
            color: #000;
        }
        .logo {
            text-align: center;
            margin-bottom: 8px;
        }

        .logo img {
            height: 90px; /* تكبير الشعار */
        }

        h2 {
            text-align: center;
            font-weight: bold;
            margin-bottom: 25px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: center;
        }

        th {
            background: #f3f3f3;
            font-weight: bold;
        }

        tfoot td {
            font-weight: bold;
            background: #fafafa;
        }
    </style>
</head>
<body>
    {{-- العنوان (قادِم جاهز من Controller) --}}
    <h2>
        {{ $title }}
    </h2>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>المشروع</th>
                <th>النوع</th>
                <th>الوحدة</th>
                <th>العمارة</th>
                <th>الشطر</th>
                <th>المبلغ</th>
                <th>طريقة الدفع</th>
                <th>التاريخ</th>
            </tr>
        </thead>

        <tbody>
            @foreach($payments as $p)
                <tr>
                    <td>{{ $loop->iteration }}</td>

                    <td>{{ $p->project->name ?? '-' }}</td>

                    <td>
                        @switch($p->context)
                            @case('apartment') الشقة @break
                            @case('shop') المحل @break
                            @case('land') القطعة @break
                            @default -
                        @endswitch
                    </td>

                    <td>
                        {{
                            $p->apartment->number
                            ?? $p->shop->number
                            ?? $p->land->land_number
                            ?? '-'
                        }}
                    </td>

                    <td>{{ $p->building_number ?? '-' }}</td>
                    <td>{{ $p->tranche_number ?? '-' }}</td>

                    <td>{{ number_format($p->amount, 2) }}</td>

                    <td>{{ $p->payment_method }}</td>

                    <td>
                        {{ \Carbon\Carbon::parse($p->paid_at)->format('Y-m-d') }}
                    </td>
                </tr>
            @endforeach
        </tbody>

        <tfoot>
            <tr>
                <td colspan="6" style="text-align:center;">
                    المجموع
                </td>
                <td colspan="3">
                    {{ number_format($totalAmount, 2) }}
                </td>
            </tr>
        </tfoot>
    </table>

    <script>
        window.print();
    </script>

</body>
</html>
