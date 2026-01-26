<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Apartment;
use App\Models\Shop;
use App\Models\LandPlot;
use App\Models\Project;
use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;

class PaymentController extends Controller
{
    /* =====================================================
     * طباعة خاصة بالفلتر
     * ===================================================== */
public function print(Request $request)
{
    $filters = $request->only([
        'project_id',
        'context',
        'unit_number',
        'building_number',
        'tranche_number',
        'date_from',
        'date_to',
    ]);

    $query = Payment::with(['project', 'apartment', 'shop', 'land']);

    // =======================
    // الفلاتر
    // =======================
    if ($request->filled('project_id')) {
        $query->where('project_id', $request->project_id);
    }

    if ($request->filled('context')) {
        $query->where('context', $request->context);
    }

    if ($request->filled('building_number')) {
        $query->where('building_number', $request->building_number);
    }

    if ($request->filled('tranche_number')) {
        $query->where('tranche_number', $request->tranche_number);
    }

    if ($request->filled('unit_number')) {
        $query->where(function ($q) use ($request) {
            $q->whereHas('apartment', fn ($a) =>
                $a->where('number', $request->unit_number)
            )
            ->orWhereHas('shop', fn ($s) =>
                $s->where('number', $request->unit_number)
            )
            ->orWhereHas('land', fn ($l) =>
                $l->where('land_number', $request->unit_number)
            );
        });
    }

    if ($request->filled('date_from')) {
        $query->whereDate('paid_at', '>=', $request->date_from);
    }

    if ($request->filled('date_to')) {
        $query->whereDate('paid_at', '<=', $request->date_to);
    }

    $payments = $query->orderBy('paid_at')->get();
    $totalAmount = $payments->sum('amount');

  // =======================
  // 🧠 بناء العنوان (نهائي)
  // =======================

 $contextLabels = [
    'apartment' => 'الشقة',
    'shop'      => 'المحل',
    'land'      => 'القطعة',
 ];

 $title = ' الدفوعات';

 // الفلاتر
 $context        = $request->context;
 $unitNumber     = $request->unit_number;
 $buildingNumber = $request->building_number;
 $trancheNumber  = $request->tranche_number;

 // المشروع (إن وُجد)
 $project = null;
 if ($payments->pluck('project_id')->unique()->count() === 1) {
    $project = $payments->first()->project;
 }

 // تسمية المشروع الصحيحة
 $projectLabel = null;
 if ($project) {
    $projectLabel = ($project->type === 'building')
        ? 'إقامة ' . $project->name
        : 'تجزئة ' . $project->name;
 }

 // =======================
 // 1️⃣ وحدة محددة (شقة / محل / قطعة)
 // =======================
 if ($context && $unitNumber) {

    $title = 'بيان دفوعات '
        . ($contextLabels[$context] ?? 'الوحدة')
        . ' رقم ' . $unitNumber;

    if ($buildingNumber) {
        $title .= ' عمارة ' . $buildingNumber;
    }

    if ($trancheNumber) {
        $title .= ' شطر ' . $trancheNumber;
    }

    if ($projectLabel) {
        $title .= ' ' . $projectLabel;
    }
 }

 // =======================
 // 2️⃣ مشروع فقط
 // =======================
 elseif ($projectLabel) {
    $title = 'بيان دفوعات ' . $projectLabel;
 }

 // =======================
 // 3️⃣ إضافة فترة التاريخ
 // =======================
 if ($request->filled('date_from') || $request->filled('date_to')) {

    $period = [];

    if ($request->filled('date_from')) {
        $period[] = 'من ' . Carbon::parse($request->date_from)->format('d/m/Y');
    }

    if ($request->filled('date_to')) {
        $period[] = 'إلى ' . Carbon::parse($request->date_to)->format('d/m/Y');
    }

    $title .= ' (' . implode(' ', $period) . ')';
 }


    return view('payments.print', compact(
        'payments',
        'filters',
        'title',
        'totalAmount'
    ));
}

    /* =====================================================
     * طباعة التوصيل
     * ===================================================== */
   public function receipt(Payment $payment)
  {
        $payment->load(['project', 'apartment', 'shop', 'land']);

        return PDF::loadView('payments.receipt', [
    'payment' => $payment,
     ])
      ->setPaper('a4', 'portrait')
      ->setOption('encoding', 'UTF-8')
      ->setOption('margin-top', 10)
      ->setOption('margin-bottom', 10)
      ->setOption('margin-left', 10)
      ->setOption('margin-right', 10)
      ->inline('receipt.pdf');
  }
    /* =====================================================
     * INDEX
     * ===================================================== */
public function index(Request $request)
{
    $query = Payment::with(['project', 'apartment', 'shop', 'land']);

    /* ================== Filters ================== */

    // 🔹 المشروع
    if ($request->filled('project_id')) {
        $query->where('project_id', $request->project_id);
    }

    // 🔹 العمارة
    if ($request->filled('building_number')) {
        $query->where('building_number', $request->building_number);
    }

    // 🔹 الشطر
    if ($request->filled('tranche_number')) {
        $query->where('tranche_number', $request->tranche_number);
    }

    // 🔹 النوع
    if ($request->filled('context')) {
        $query->where('context', $request->context);
    }

    // 🔹 رقم الوحدة
    if ($request->filled('unit_number')) {
        $query->where(function ($q) use ($request) {

            $q->when(
                $request->context === 'apartment' || !$request->context,
                fn ($qq) =>
                    $qq->orWhereHas('apartment', fn ($a) =>
                        $a->where('number', $request->unit_number)
                    )
            );

            $q->when(
                $request->context === 'shop' || !$request->context,
                fn ($qq) =>
                    $qq->orWhereHas('shop', fn ($s) =>
                        $s->where('number', $request->unit_number)
                    )
            );

            $q->when(
                $request->context === 'land' || !$request->context,
                fn ($qq) =>
                    $qq->orWhereHas('land', fn ($l) =>
                        $l->where('land_number', $request->unit_number)
                    )
            );
        });
    }

    // 🔹 التاريخ
    if ($request->filled('date_from')) {
        $query->whereDate('paid_at', '>=', $request->date_from);
    }

    if ($request->filled('date_to')) {
        $query->whereDate('paid_at', '<=', $request->date_to);
    }

    /* ================== Pagination ================== */
    $payments = $query
        ->latest()
        ->paginate(10)          // 👈 عدد الصفوف في الصفحة
        ->withQueryString();    // 👈 الحفاظ على الفلاتر

    return inertia('Payments/Index', [
        'payments' => $payments,

        'filters' => $request->only([
            'project_id',
            'building_number',
            'tranche_number',
            'context',
            'unit_number',
            'date_from',
            'date_to',
        ]),

        'projects' => Project::select('id', 'name')->get(),
    ]);
}

    /* =====================================================
     * STORE
     * ===================================================== */
public function store(Request $request)
{
    $validated = $request->validate([
        'context'        => 'required|in:apartment,shop,land',
        'project_id'     => 'required|exists:projects,id',

        'apartment_id'   => 'nullable|exists:apartments,id',
        'shop_id'        => 'nullable|exists:shops,id',
        'land_id'        => 'nullable|exists:land_plots,id',

        'payment_method' => 'required|in:cash,check,transfer,bill',
        'amount'         => 'required|numeric|min:0',
        'paid_at'        => 'required|date',
    ]);

    $buildingNumber = null;
    $trancheNumber  = null;

    // 🏢 شقة
    if ($validated['context'] === 'apartment' && $validated['apartment_id']) {
        $apartment = \App\Models\Apartment::with('building')->find($validated['apartment_id']);

        $buildingNumber = $apartment?->building?->name; // ✅ هنا التصحيح
        $trancheNumber  = $apartment?->tranche_number;
    }

    // 🏪 محل
    if ($validated['context'] === 'shop' && $validated['shop_id']) {
        $shop = \App\Models\Shop::with('building')->find($validated['shop_id']);

        $buildingNumber = $shop?->building?->name; // ✅ هنا التصحيح
        $trancheNumber  = $shop?->tranche_number;
    }

    // 🌍 قطعة
    if ($validated['context'] === 'land' && $validated['land_id']) {
        $land = \App\Models\LandPlot::find($validated['land_id']);

        $trancheNumber = $land?->tranche_number;
    }

    Payment::create([
        'context'         => $validated['context'],
        'project_id'      => $validated['project_id'],

        'apartment_id'    => $validated['apartment_id'] ?? null,
        'shop_id'         => $validated['shop_id'] ?? null,
        'land_id'         => $validated['land_id'] ?? null,

        'building_number' => $buildingNumber,
        'tranche_number'  => $trancheNumber,

        'payment_method'  => $validated['payment_method'],
        'amount'          => $validated['amount'],
        'paid_at'         => $validated['paid_at'],
    ]);

    return Inertia::location(route('payments.index'));
}

    /* =====================================================
     * CREATE FROM APARTMENT
     * ===================================================== */
public function createFromApartment($apartmentId)
{
    $apartment = Apartment::with('building.project')->findOrFail($apartmentId);
    $project = $apartment->building->project;

    $paid = Payment::where('context', 'apartment')
        ->where('apartment_id', $apartment->id)
        ->sum('amount');

    return inertia('Payments/Create', [
        'context' => 'apartment',

        'project' => [
            'id'   => $project->id,
            'name' => $project->name,
        ],

        'unit' => [
            'id'               => $apartment->id,
            'number'           => $apartment->number,
            'building_number'  => $apartment->building?->name,
            'tranche_number'   => $apartment->tranche_number,

            // ✅ الحل هنا
            'owner_name'       => $apartment->customer_name ?? 'غير محدد',

            'floor'            => $apartment->floor,
            'rooms'            => $apartment->rooms,
            'area'             => $apartment->area,
            'price_per_m2'     => $apartment->price_per_m2,
            'total_price'      => $apartment->total_price,

            'has_parking'      => (bool) $apartment->has_parking,
            'parking_number'   => $apartment->parking_number,
            'parking_price'    => $apartment->parking_price,

            'has_terrace'      => (bool) $apartment->has_terrace,
            'terrace_area'     => $apartment->terrace_area,
            'terrace_total_price' => $apartment->terrace_total_price,
            'terrace_type'     => $apartment->terrace_type,
        ],

        'summary' => [
            'total'     => $apartment->total_price,
            'paid'      => $paid,
            'remaining' => max($apartment->total_price - $paid, 0),
        ],
    ]);
}

    /* =====================================================
     * CREATE FROM SHOP
     * ===================================================== */
public function createFromShop($shopId)
{
    $shop = Shop::with('building.project')->findOrFail($shopId);

    $project = $shop->building->project;

    $paid = Payment::where('context', 'shop')
        ->where('shop_id', $shop->id)
        ->sum('amount');

    return inertia('Payments/Create', [
        'context' => 'shop',

        // ✅ المشروع (عن طريق العمارة)
        'project' => [
            'id'   => $project->id,
            'name' => $project->name,
        ],

        // ✅ بيانات المحل
        'unit' => [
            'id'              => $shop->id,
            'number'          => $shop->number,
            'building_number' => $shop->building_number,
            'tranche_number'  => $shop->tranche_number,
            'owner_name'      => $shop->customer_name,
            'area'            => $shop->area,
            'price_per_m2'    => $shop->price_per_m2,
            'total_price'     => $shop->total_price,
        ],

        'summary' => [
            'total'     => $shop->total_price,
            'paid'      => $paid,
            'remaining' => max($shop->total_price - $paid, 0),
        ],
    ]);
}

    /* =====================================================
     * CREATE FROM LAND
     * ===================================================== */
public function createFromLand(LandPlot $land)
{
    $land->load('project');

    $paid = Payment::where('context', 'land')
        ->where('land_id', $land->id)
        ->sum('amount');

    return inertia('Payments/Create', [
        'context' => 'land',

        'project' => [
            'id'   => $land->project->id,
            'name' => $land->project->name,
        ],

        'unit' => [
            'id'           => $land->id,
            'number'       => $land->land_number,

            // ✅ الحل هنا
            'owner_name'   => $land->customer_name ?? 'غير محدد',

            'area'         => $land->area,
            'price_per_m2' => $land->price_per_m2,
            'total_price'  => $land->total_price,
            'face'         => $land->view_type,
            'road_type'    => $land->road_type,
        ],

        'summary' => [
            'total'     => $land->total_price,
            'paid'      => $paid,
            'remaining' => max($land->total_price - $paid, 0),
        ],
    ]);
}
    /* =====================================================
     EDIT
     * ===================================================== */
public function edit(Payment $payment)
{
    // ⬅️ تحديد السياق
    $context = $payment->context;

    // ⬅️ جلب الوحدة حسب السياق
    if ($context === 'apartment') {
        $unit = Apartment::with('building.project')->findOrFail($payment->apartment_id);
        $project = $unit->building->project;
        $total = $unit->total_price;

        $paid = Payment::where('context', 'apartment')
            ->where('apartment_id', $unit->id)
            ->where('id', '!=', $payment->id)
            ->sum('amount');

    } elseif ($context === 'shop') {
        $unit = Shop::with('project')->findOrFail($payment->shop_id);
        $project = $unit->project;
        $total = $unit->total_price;

        $paid = Payment::where('context', 'shop')
            ->where('shop_id', $unit->id)
            ->where('id', '!=', $payment->id)
            ->sum('amount');

    } else { // land
        $unit = LandPlot::with('project')->findOrFail($payment->land_id);
        $project = $unit->project;
        $total = $unit->total_price;

        $paid = Payment::where('context', 'land')
            ->where('land_id', $unit->id)
            ->where('id', '!=', $payment->id)
            ->sum('amount');
    }

    return inertia('Payments/Edit', [
        'payment' => $payment,

        'context' => $context,
        'project' => optional($project)->only(['id', 'name']),


        'unit' => [
            'id'              => $unit->id,
            'number'          => $unit->number ?? $unit->land_number ?? null,
            'owner_name'      => $unit->customer_name,
            'area'            => $unit->area,
            'price_per_m2'    => $unit->price_per_m2,
            'total_price'     => $total,

            // 🏢 شقق فقط
            'floor'           => $unit->floor ?? null,
            'rooms'           => $unit->rooms ?? null,

            // 🚗 موقف السيارة
            'has_parking'     => $unit->has_parking ?? false,
            'parking_number' => $unit->parking_number ?? null,
            'parking_price'  => $unit->parking_price ?? null,

            // 🌿 التيراس
            'has_terrace'          => $unit->has_terrace ?? false,
            'terrace_area'         => $unit->terrace_area ?? null,
            'terrace_total_price'  => $unit->terrace_total_price ?? null,
            'terrace_type'         => $unit->terrace_type ?? null,
        ],

        'summary' => [
            'total'     => $total,
            'paid'      => $paid,
            'remaining' => max($total - $paid, 0),
        ],
    ]);
}

public function update(Request $request, Payment $payment)
{
    $validated = $request->validate([
        'payment_method' => 'required|in:cash,check,transfer,bill',
        'amount'         => 'required|numeric|min:0',
        'paid_at'        => 'required|date',
    ]);

    $payment->update($validated);

    return Inertia::location(route('payments.index'));

}


    public function destroy(Payment $payment)
    {

      $payment->delete();
      return back();
    }
}
