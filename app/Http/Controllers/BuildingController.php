<?php

namespace App\Http\Controllers;
use App\Models\Apartment;

use App\Models\Building;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
//use Barryvdh\Snappy\Facades\SnappyPdf as PDF;

class BuildingController extends Controller
{
public function plan(Building $building)
{
    $building->load('project');

    // جلب الشقق
    $apartments = $building->apartments()
        ->orderBy('floor', 'desc')
        ->orderBy('number', 'asc')
        ->get();

    // تجميع حسب الطابق وإرجاعه كـ Array
    $grouped = $apartments->groupBy('floor')->map(function ($group) {
        return $group->values(); // ← تحويل Collection إلى Array نظيف
    })->toArray(); // ← مهم جداً!

    return Inertia::render('Buildings/Plan', [
        'building'   => $building,
        'project'    => $building->project,
        'apartments' => $grouped, // الآن Array حقيقي
    ]);
}
 
public function planPrint(Building $building, Request $request)
{
    $tranche = $request->get('tranche');

    // المشروع
    $project = $building->project;

    /* ===================== الشقق ===================== */
    $apartments = $building->apartments()
        ->when($tranche, fn ($q) => $q->where('tranche_number', $tranche))
        ->orderBy('floor')
        ->orderByRaw('CAST(number AS UNSIGNED)')
        ->get()
        ->groupBy('floor');

    /* ===================== المحلات التجارية ===================== */
    $shops = \App\Models\Shop::where('building_id', $building->id)
        ->when($tranche, fn ($q) => $q->where('tranche_number', $tranche))
        ->orderByRaw('CAST(number AS UNSIGNED)')
        ->get();

    /* ===================== عنوان الصفحة ===================== */
    $title = "مخطط العمارة {$building->name}";
    if ($tranche) {
        $title .= " – الشطر {$tranche}";
    }
    $title .= " – إقامة {$project->name}";

    /* ===================== PRINT VIEW ===================== */
    return view('buildings.plan-pdf', [
        'building'   => $building,
        'project'    => $project,
        'apartments' => $apartments,
        'shops'      => $shops,
        'tranche'    => $tranche,
        'title'      => $title,
    ]);
}




public function paymentsPdf(Building $building, int $tranche)
{
    // =========================
    // المحلات التجارية
    // =========================
    $shops = $building->shops()
        ->where('tranche_number', $tranche)
        ->with(['customer', 'payments' => function ($q) {
            $q->orderBy('paid_at');
        }])
        ->get()
        ->map(function ($shop) {
            $total = $shop->area * $shop->price_per_meter;
            $paid  = $shop->payments->sum('amount');

            return [
                'number'      => $shop->number,
                'customer'    => optional($shop->customer)->name,
                'area'        => $shop->area,
                'total'       => $total,
                'payments'    => $shop->payments,
                'paid'        => $paid,
                'remaining'   => $total - $paid,
            ];
        });

    // =========================
    // الشقق
    // =========================
    $apartments = $building->apartments()
        ->where('tranche_number', $tranche)
        ->with(['customer', 'payments' => function ($q) {
            $q->orderBy('paid_at');
        }])
        ->get()
        ->map(function ($apartment) {
            $total = $apartment->area * $apartment->price_per_meter;
            $paid  = $apartment->payments->sum('amount');

            return [
                'number'      => $apartment->number,
                'floor'       => $apartment->floor,
                'customer'    => optional($apartment->customer)->name,
                'area'        => $apartment->area,
                'total'       => $total,
                'payments'    => $apartment->payments,
                'paid'        => $paid,
                'remaining'   => $total - $paid,
            ];
        });

    $pdf = Pdf::loadView('pdf.building-payments', [
        'building'   => $building,
        'tranche'    => $tranche,
        'shops'      => $shops,
        'apartments' => $apartments,
    ])->setPaper('a4', 'portrait');

    return $pdf->download(
        "payments-building-{$building->id}-tranche-{$tranche}.pdf"
    );
}


}
