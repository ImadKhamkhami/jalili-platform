<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\Building;
use App\Models\Project;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\Shop;
use Illuminate\Support\Facades\DB;
use App\Models\Transfer;
use Inertia\Inertia;
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

class ApartmentController extends Controller
{
    /* =====================================================
        PDF
    ===================================================== */


public function invoicePdf(Apartment $apartment)
{
    $building = $apartment->building;
    $project  = $building->project;
    $tranche  = $apartment->tranche_number;

    /* ===== العنوان ===== */
    $title = "بيان الشقة {$apartment->number} – عمارة {$building->name}";
    if ($tranche) $title .= " – الشطر {$tranche}";
    $title .= " – إقامة {$project->name}";

    /* ===== اسم الملف ===== */
    $file = "بيان الشقة {$apartment->number} - عمارة {$building->name}";
    if ($tranche) $file .= " - الشطر {$tranche}";
    $file .= ".pdf";

    /* ===== الدفوعات ===== */
    $payments = Payment::where('context', 'apartment')
        ->where('apartment_id', $apartment->id)
        ->orderBy('paid_at')
        ->get();

    $paidTotal = $payments->sum('amount');
    $remaining = max($apartment->total_price - $paidTotal, 0);

    /* ===== إعداد الخطوط ===== */
    $defaultConfig = (new ConfigVariables())->getDefaults();
    $fontDirs = $defaultConfig['fontDir'];

    $defaultFontConfig = (new FontVariables())->getDefaults();
    $fontData = $defaultFontConfig['fontdata'];

    /* ===== إنشاء mPDF ===== */
    $mpdf = new Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'orientation' => 'P',
        'directionality' => 'rtl',
        'default_font' => 'tajawal',
        'fontDir' => array_merge($fontDirs, [
            storage_path('fonts'),
        ]),
        'fontdata' => $fontData + [
            'tajawal' => [
                'R' => 'Tajawal-Regular.ttf',
                'B' => 'Tajawal-Bold.ttf',
            ],
        ],
    ]);

    /* ===== HTML ===== */
    $html = view('apartments.invoice', compact(
        'apartment',
        'building',
        'project',
        'title',
        'payments',
        'paidTotal',
        'remaining'
    ))->render();

    $mpdf->WriteHTML($html);

    /* ===== عرض في المتصفح ===== */
    return response($mpdf->Output($file, 'S'), 200, [
        'Content-Type'        => 'application/pdf; charset=utf-8',
        'Content-Disposition' => "inline; filename*=UTF-8''" . rawurlencode($file),
    ]);
}

    /* =====================================================
        INDEX (كل المشاريع)
    ===================================================== */
public function index()
{
    $apartments = Apartment::with('building.project')
        ->get()
        ->map(function ($apartment) {

            $paid = Payment::where('context', 'apartment')
                ->where('apartment_id', $apartment->id)
                ->sum('amount');

            $total = $apartment->total_price ?? 0;

            return [
                ...$apartment->toArray(),
                'payment_percentage' => $total > 0
                    ? round(($paid / $total) * 100, 1)
                    : 0,
            ];
        });

    $shops = Shop::with('building.project')
        ->get()
        ->map(function ($shop) {

            $paid = Payment::where('context', 'shop')
                ->where('shop_id', $shop->id)
                ->sum('amount');

            $total = $shop->total_price ?? 0;

            return [
                ...$shop->toArray(),
                'payment_percentage' => $total > 0
                    ? round(($paid / $total) * 100, 1)
                    : 0,
            ];
        });

    return inertia('Apartments/Index', [
        'current_project' => null,
        'projects' => Project::where('type', 'building')->get(),
        'apartments' => $apartments,
        'shops' => $shops,
    ]);
}

    /* =====================================================
        BY PROJECT
    ===================================================== */
    public function byProject(Project $project)
{
    $apartments = Apartment::with('building.project')
        ->whereHas('building', fn ($q) =>
            $q->where('project_id', $project->id)
        )
        ->get()
        ->map(function ($apartment) {

            $paid = Payment::where('context', 'apartment')
                ->where('apartment_id', $apartment->id)
                ->sum('amount');

            $total = $apartment->total_price ?? 0;

            return [
                ...$apartment->toArray(),
                'paid_amount' => $paid,
                'remaining_amount' => max($total - $paid, 0),
                'payment_percentage' => $total > 0
                    ? round(($paid / $total) * 100, 1)
                    : 0,
            ];
        });

    $shops = Shop::with('building.project')
        ->whereHas('building', fn ($q) =>
            $q->where('project_id', $project->id)
        )
        ->get()
        ->map(function ($shop) {

            $paid = Payment::where('context', 'shop')
                ->where('shop_id', $shop->id)
                ->sum('amount');

            $total = $shop->total_price ?? 0;

            return [
                ...$shop->toArray(),
                'paid_amount' => $paid,
                'remaining_amount' => max($total - $paid, 0),
                'payment_percentage' => $total > 0
                    ? round(($paid / $total) * 100, 1)
                    : 0,
            ];
        });

    return inertia('Apartments/Index', [
        'current_project' => $project,
        'projects' => Project::where('type', 'building')->get(),
        'apartments' => $apartments,
        'shops' => $shops,
    ]);
}



    /* =====================================================
        SHOW
    ===================================================== */
public function show(Apartment $apartment)
{
    $apartment->load([
        'building.project',
        'transfers.fromCustomer:id,name',
        'transfers.toCustomer:id,name',
    ]);

    /* ================= الدفوعات ================= */
    $payments = Payment::where('context', 'apartment')
        ->where('apartment_id', $apartment->id)
        ->orderBy('paid_at')
        ->get();

    $paid  = $payments->sum('amount');
    $total = $apartment->total_price;

    /* ================= سجل الملكية الصحيح ================= */
    $ownershipHistory = collect();

    // 1️⃣ المالك الأصلي = من أول تنازل
    $firstTransfer = $apartment->transfers
        ->sortBy('transfer_number')
        ->first();

    if ($firstTransfer && $firstTransfer->fromCustomer) {
        $ownershipHistory->push([
            'name' => $firstTransfer->fromCustomer->name,
            'transfer_number' => '-',
            'date' => '-',
        ]);
    }

    // 2️⃣ الملاك بعد التنازلات
    foreach ($apartment->transfers->sortBy('transfer_number') as $t) {
        if ($t->toCustomer) {
            $ownershipHistory->push([
                'name' => $t->toCustomer->name,
                'transfer_number' => $t->transfer_number,
                'date' => $t->transfer_date,
            ]);
        }
    }

    // 3️⃣ إزالة التكرار (احتياط)
    $ownershipHistory = $ownershipHistory
        ->unique('name')
        ->values();

    // 4️⃣ عكس الترتيب → المالك الحالي في الأعلى
    $ownershipHistory = $ownershipHistory
        ->reverse()
        ->values();

    return inertia('Apartments/Show', [
        'apartment' => $apartment,

        'project' => [
            'id'   => $apartment->building->project->id,
            'name' => $apartment->building->project->name,
        ],

        'building_number' => $apartment->building->name,

        'summary' => [
            'total'     => $total,
            'paid'      => $paid,
            'remaining' => max($total - $paid, 0),
        ],

        'payments' => $payments->map(function ($p) use ($total) {
            return [
                'id'         => $p->id,
                'amount'     => $p->amount,
                'paid_at'    => $p->paid_at,
                'method'     => $p->payment_method,
                'percentage' => $total > 0
                    ? round(($p->amount / $total) * 100, 1)
                    : 0,
            ];
        }),

        /* ✅ هذا هو المهم */
        'ownership_history' => $ownershipHistory,
    ]);
}


    /* =====================================================
        CREATE
    ===================================================== */
    public function create()
    {
        return inertia('Apartments/Create', [
            'projects' => Project::where('type', 'building')
                ->select('id', 'name')
                ->get(),
        ]);
    }

    /* =====================================================
        STORE
    ===================================================== */
public function store(Request $request)
{
    $validated = $request->validate([
        'project_id'      => 'required|exists:projects,id',
        'building_number' => 'required|string|max:255',

        'number'          => 'required|string|max:50',
        'floor'           => 'required|numeric',
        'tranche_number'  => 'nullable|string|max:50',

        'area'            => 'required|numeric|min:1',
        'price_per_m2'    => 'required|numeric|min:0',
        'rooms'           => 'required|numeric|min:1',

        /* 👤 Customer */
        'customer_name'   => 'nullable|string|max:255',
        'customer_id'     => 'nullable|string|max:255',
        'customer_phone'  => 'nullable|string|max:20',

        /* 🚗 Parking */
        'parking_number'  => 'nullable|string|max:50',
        'parking_price'   => 'nullable|numeric|min:0',

        /* 🌿 Terrasse */
        'terrace_area'    => 'nullable|numeric|min:0',
        'terrace_type'    => 'nullable|in:terrasse,coeur',

        /* 💸 Discount */
        'discount'        => 'nullable|numeric|min:0',

        /* 🖼 Image */
        'image'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

        'status'          => 'required|in:متاحة,محجوزة,مباعة',
    ]);

    /* ================= BUILDING ================= */
    // رقم العمارة لا يُخزن في الشقة
    // بل يُنشئ / يُستعمل Building
    $building = Building::firstOrCreate([
        'project_id' => $validated['project_id'],
        'name'       => $validated['building_number'],
    ]);

    /* ================= UNIQUE APARTMENT ================= */
    $exists = Apartment::where('building_id', $building->id)
        ->where('number', $validated['number'])
        ->where(function ($q) use ($validated) {
            if (!empty($validated['tranche_number'])) {
                $q->where('tranche_number', $validated['tranche_number']);
            } else {
                $q->whereNull('tranche_number');
            }
        })
        ->exists();

    if ($exists) {
        throw ValidationException::withMessages([
            'number' => 'رقم الشقة موجود مسبقًا داخل هذه العمارة ونفس الشطر',
        ]);
    }

    /* ================= CUSTOMER ================= */
    $customer = null;

    if (!empty($validated['customer_id'])) {
        $customer = Customer::firstOrCreate(
            ['national_id' => $validated['customer_id']],
            [
                'name'    => $validated['customer_name'] ?? 'غير محدد',
                'phone'   => $validated['customer_phone'] ?? null,
                'address' => null,
            ]
        );

        if (!empty($validated['customer_phone']) && empty($customer->phone)) {
            $customer->update([
                'phone' => $validated['customer_phone'],
            ]);
        }
    }

    /* ================= IMAGE ================= */
    $imagePath = null;

    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('apartments', 'public');
    }

    /* ================= CALCULATIONS ================= */
    $area    = (float) $validated['area'];
    $priceM2 = (float) $validated['price_per_m2'];

    /* 🚗 Parking */
    $hasParking =
        !empty($validated['parking_number']) ||
        !empty($validated['parking_price']);

    $parkingPrice = $hasParking
        ? (float) ($validated['parking_price'] ?? 0)
        : 0;

    /* 🌿 Terrasse */
    $hasTerrace = !empty($validated['terrace_area']);

    $terraceArea = $hasTerrace
        ? (float) $validated['terrace_area']
        : 0;

    $terraceTotalPrice = $hasTerrace
        ? ($terraceArea * ($priceM2 / 2))
        : 0;

    /* 💸 Discount */
    $discount = (float) ($validated['discount'] ?? 0);

    /* 💰 TOTAL */
    $totalPrice =
        ($area * $priceM2)
        + $parkingPrice
        + $terraceTotalPrice
        - $discount;

    $totalPrice = max($totalPrice, 0);

    /* ================= CREATE ================= */
    $apartment = Apartment::create([
        'building_id' => $building->id,

        'number'         => $validated['number'],
        'floor'          => $validated['floor'],
        'tranche_number' => $validated['tranche_number'] ?? null,

        'area'         => $area,
        'price_per_m2' => $priceM2,
        'rooms'        => $validated['rooms'],

        /* 👤 Customer */
        'customer_ref_id' => $customer?->id,
        'customer_name'   => $validated['customer_name'] ?? null,
        'customer_id'     => $validated['customer_id'] ?? null,
        'customer_phone'  => $validated['customer_phone'] ?? null,

        /* 🚗 Parking */
        'has_parking'    => $hasParking,
        'parking_number' => $hasParking ? ($validated['parking_number'] ?? null) : null,
        'parking_price'  => $hasParking ? $parkingPrice : null,

        /* 🌿 Terrasse */
        'has_terrace'         => $hasTerrace,
        'terrace_area'        => $hasTerrace ? $terraceArea : null,
        'terrace_type'        => $hasTerrace ? ($validated['terrace_type'] ?? null) : null,
        'terrace_total_price' => $terraceTotalPrice,

        /* 💸 Discount */
        'discount' => $discount,

        /* 🖼 Image */
        'image' => $imagePath,

        'status'      => $validated['status'],
        'total_price' => $totalPrice,
    ]);

  return Inertia::location(
    "/projects/{$validated['project_id']}/apartments?focus={$apartment->id}"
  );

}
    /* =====================================================
        EDIT
    ===================================================== */
public function edit(Apartment $apartment)
{
    return inertia('Apartments/Edit', [
        'apartment' => [
            'id' => $apartment->id,

            /* 🏗 Project / Building */
            'project_id'      => $apartment->building->project_id,
            'building_id'     => $apartment->building_id,
            'building_number' => $apartment->building->name, // ✅ مهم جدًا

            /* 🏠 Apartment */
            'number'         => $apartment->number,
            'floor'          => $apartment->floor,
            'tranche_number' => $apartment->tranche_number,
            'rooms'          => $apartment->rooms,
            'area'           => $apartment->area,
            'price_per_m2'   => $apartment->price_per_m2,
            'status'         => $apartment->status,

            /* 👤 Customer */
            'customer_name'  => $apartment->customer_name,
            'customer_id'    => $apartment->customer_id,
            'customer_phone' => $apartment->customer_phone,

            /* 🚗 Parking */
            'parking_number' => $apartment->parking_number,
            'parking_price'  => $apartment->parking_price,

            /* 🌿 Terrasse */
            'terrace_type' => $apartment->terrace_type,
            'terrace_area' => $apartment->terrace_area,

            /* 💸 Discount */
            'discount' => $apartment->discount,

            /* 🖼 Image */
            'image_url' => $apartment->image
                ? Storage::url($apartment->image)
                : null,
        ],

        'projects' => Project::where('type', 'building')
            ->select('id', 'name')
            ->get(),
    ]);
}
    /* =====================================================
        UPDATE
    ===================================================== */
public function update(Request $request, Apartment $apartment)
{
    /* ================= VALIDATION ================= */
    $validated = $request->validate([
        'project_id'      => 'required|exists:projects,id',
        'building_number' => 'required|string|max:255',

        'number'          => 'required|string|max:50',
        'floor'           => 'required|numeric',
        'tranche_number'  => 'nullable|string|max:50',

        'area'            => 'required|numeric|min:1',
        'price_per_m2'    => 'required|numeric|min:0',
        'rooms'           => 'required|numeric|min:1',

        /* 👤 Customer */
        'customer_name'   => 'nullable|string|max:255',
        'customer_id'     => 'nullable|string|max:255',
        'customer_phone'  => 'nullable|string|max:20',

        /* 🚗 Parking */
        'parking_number'  => 'nullable|string|max:50',
        'parking_price'   => 'nullable|numeric|min:0',

        /* 🌿 Terrasse */
        'terrace_area'    => 'nullable|numeric|min:0',
        'terrace_type'    => 'nullable|in:terrasse,coeur',

        /* 💸 Discount */
        'discount'        => 'nullable|numeric|min:0',

        /* 🖼 Image */
        'image'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

        'status'          => 'required|in:متاحة,محجوزة,مباعة',
    ]);

    /* ================= BUILDING ================= */
    $building = Building::firstOrCreate([
        'project_id' => $validated['project_id'],
        'name'       => $validated['building_number'],
    ]);

    /* ================= UNIQUE CHECK ================= */
    $exists = Apartment::where('building_id', $building->id)
        ->where('number', $validated['number'])
        ->where(function ($q) use ($validated) {
            if (!empty($validated['tranche_number'])) {
                $q->where('tranche_number', $validated['tranche_number']);
            } else {
                $q->whereNull('tranche_number');
            }
        })
        ->where('id', '!=', $apartment->id)
        ->exists();

    if ($exists) {
        throw ValidationException::withMessages([
            'number' => 'رقم الشقة موجود مسبقًا داخل هذه العمارة ونفس الشطر',
        ]);
    }

    /* ================= CUSTOMER ================= */
    $customer = null;

    if (!empty($validated['customer_id'])) {
        $customer = Customer::firstOrCreate(
            ['national_id' => $validated['customer_id']],
            [
                'name'    => $validated['customer_name'] ?? 'غير محدد',
                'phone'   => $validated['customer_phone'] ?? null,
                'address' => null,
            ]
        );

        // تحديث البيانات إذا تغيّرت
        $customer->update([
            'name'  => $validated['customer_name'] ?? $customer->name,
            'phone' => $validated['customer_phone'] ?? $customer->phone,
        ]);
    }

    /* ================= IMAGE ================= */
    $imagePath = $apartment->image;

    if ($request->hasFile('image')) {
        if ($imagePath && Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }
        $imagePath = $request->file('image')->store('apartments', 'public');
    }

    /* ================= CALCULATIONS ================= */
    $area    = (float) $validated['area'];
    $priceM2 = (float) $validated['price_per_m2'];

    $hasParking =
        !empty($validated['parking_number']) ||
        !empty($validated['parking_price']);

    $parkingPrice = $hasParking
        ? (float) ($validated['parking_price'] ?? 0)
        : 0;

    $hasTerrace = !empty($validated['terrace_area']);
    $terraceArea = $hasTerrace ? (float) $validated['terrace_area'] : 0;
    $terraceTotalPrice = $hasTerrace
        ? ($terraceArea * ($priceM2 / 2))
        : 0;

    $discount = (float) ($validated['discount'] ?? 0);

    $totalPrice = max(
        ($area * $priceM2) + $parkingPrice + $terraceTotalPrice - $discount,
        0
    );

    /* ================= UPDATE ================= */
    $apartment->update([
        'building_id' => $building->id,

        'number'         => $validated['number'],
        'floor'          => $validated['floor'],
        'tranche_number' => $validated['tranche_number'] ?? null,

        'area'         => $area,
        'price_per_m2' => $priceM2,
        'rooms'        => $validated['rooms'],

        /* 👤 Customer */
        'customer_ref_id' => $customer?->id,
        'customer_name'   => $validated['customer_name'] ?? null,
        'customer_id'     => $validated['customer_id'] ?? null,
        'customer_phone'  => $validated['customer_phone'] ?? null,

        /* 🚗 Parking */
        'has_parking'    => $hasParking,
        'parking_number' => $hasParking ? ($validated['parking_number'] ?? null) : null,
        'parking_price'  => $hasParking ? $parkingPrice : null,

        /* 🌿 Terrasse */
        'has_terrace'         => $hasTerrace,
        'terrace_area'        => $hasTerrace ? $terraceArea : null,
        'terrace_type'        => $hasTerrace ? ($validated['terrace_type'] ?? null) : null,
        'terrace_total_price' => $terraceTotalPrice,

        'discount' => $discount,
        'image'    => $imagePath,

        'status'      => $validated['status'],
        'total_price' => $totalPrice,
    ]);

    return Inertia::location(
    route('apartments.show', $apartment->id)
);

}

    /* =====================================================
        DELETE
    
    ===================================================== */
    public function destroy(Apartment $apartment)
{



    DB::transaction(function () use ($apartment) {

        // حذف دفوعات الشقة
        Payment::where('context', 'apartment')
            ->where('apartment_id', $apartment->id)
            ->delete();

        // حذف الشقة
        $apartment->delete();
    });

    $projectId = $apartment->building->project_id;
    $building  = $apartment->building->name;
    $id        = $apartment->id;
    
    session()->flash('success', 'تم حذف الشقة ودفوعاتها بنجاح');
    return redirect(
        "/projects/{$projectId}/apartments?building={$building}&focus-deleted={$id}"
    )->with('success', 'تم حذف الشقة ودفوعاتها بنجاح');
}

}
