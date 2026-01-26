<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\Project;
use App\Models\Apartment;
use App\Models\Payment;
use App\Models\Building;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;

class ShopController extends Controller
{
     /* ===================== PDF ===================== */
     public function invoicePrint(Shop $shop)
{
    $shop->load('building.project');

    $building = $shop->building;
    $project  = $building->project;

    /* ===== العنوان ===== */
    $title = "بيان المحل رقم {$shop->number} – عمارة {$building->number} – إقامة {$project->name}";

    /* ===== الدفوعات ===== */
    $payments = Payment::where('context', 'shop')
        ->where('shop_id', $shop->id)
        ->orderBy('paid_at')
        ->get();

    $paidTotal = $payments->sum('amount');
    $remaining = max($shop->total_price - $paidTotal, 0);

    /* ===== MEZZANINE ===== */
    $mezzanineArea  = $shop->mezzanine_area ?? 0;
    $mezzanineTotal = $shop->mezzanine_total_price ?? 0;

    /* ===== PRINT VIEW (بدون PDF) ===== */
    return view('shops.invoice', compact(
        'shop',
        'project',
        'title',
        'payments',
        'paidTotal',
        'remaining',
        'mezzanineArea',
        'mezzanineTotal'
    ));
}


    /* ===================== CREATE ===================== */
    public function create()
    {
        return Inertia::render('Shops/Create', [
            // 🔥 فقط المشاريع من نوع عمارة
            'projects' => Project::where('type', 'building')->get(),
        ]);
    }

    /* ===================== STORE ===================== */
public function store(Request $request)
{
    $validated = $request->validate([
        'project_id'      => 'required|exists:projects,id',
        'building_number' => 'required|string|max:50',
        'number'          => 'required|string|max:50',
        'tranche_number'  => 'nullable|string|max:50',

        'area'            => 'required|numeric|min:1',
        'price_per_m2'    => 'required|numeric|min:0',
        'discount'        => 'nullable|numeric|min:0',

        /* 🪜 Mezzanine */
        'mezzanine_area'         => 'nullable|numeric|min:0',
        'mezzanine_price_per_m2' => 'nullable|numeric|min:0',

        'status'          => 'required|in:متاح,مباع,محجوز',

        /* 👤 Customer */
        'customer_name'   => 'nullable|string|max:255',
        'customer_id'     => 'nullable|string|max:255',
        'customer_phone'  => 'nullable|string|max:20',

        /* 🖼 Image */
        'image'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
    ]);

    /* ================= BUILDING ================= */
    $building = Building::firstOrCreate([
        'project_id' => $validated['project_id'],
        'name'       => $validated['building_number'],
    ]);

    /* ================= UNIQUE ================= */
    $exists = Shop::where('building_id', $building->id)
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
            'number' => 'رقم المحل موجود مسبقًا في نفس العمارة ونفس الشطر',
        ]);
    }

    /* ================= CUSTOMER ================= */
    $customer = null;

    if (!empty($validated['customer_id'])) {

        $customer = Customer::where('national_id', $validated['customer_id'])->first();

        if (!$customer) {
            $customer = Customer::create([
                'national_id' => $validated['customer_id'],
                'name'        => $validated['customer_name'] ?? 'غير محدد',
                'phone'       => $validated['customer_phone'] ?? null,
                'address'     => null,
            ]);
        } else {
            $updates = [];

            if (!empty($validated['customer_name']) && empty($customer->name)) {
                $updates['name'] = $validated['customer_name'];
            }

            if (!empty($validated['customer_phone']) && empty($customer->phone)) {
                $updates['phone'] = $validated['customer_phone'];
            }

            if (!empty($updates)) {
                $customer->update($updates);
            }
        }
    }

    /* ================= IMAGE ================= */
    $imagePath = null;

    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('shops', 'public');
    }

    /* ================= CALCULATIONS ================= */
    $area     = (float) $validated['area'];
    $priceM2 = (float) $validated['price_per_m2'];
    $discount = (float) ($validated['discount'] ?? 0);

    /* 🪜 Mezzanine (مثل terrasse) */
    $hasMezzanine = !empty($validated['mezzanine_area']);

    $mezzanineArea = $hasMezzanine
        ? (float) $validated['mezzanine_area']
        : 0;

    $mezzaninePriceM2 = $hasMezzanine
        ? (float) $validated['mezzanine_price_per_m2']
        : 0;

    $mezzanineTotalPrice = $hasMezzanine
        ? ($mezzanineArea * $mezzaninePriceM2)
        : 0;

    /* 💰 TOTAL */
    $baseTotal = $area * $priceM2;

    $totalPrice = max(
        $baseTotal + $mezzanineTotalPrice - $discount,
        0
    );

    /* ================= CREATE ================= */
    $shop = Shop::create([
        'building_id'   => $building->id,
        'number'        => $validated['number'],
        'tranche_number'=> $validated['tranche_number'] ?? null,

        'area'         => $area,
        'price_per_m2' => $priceM2,

        /* 🪜 Mezzanine */
        'has_mezzanine'           => $hasMezzanine,
        'mezzanine_area'          => $hasMezzanine ? $mezzanineArea : null,
        'mezzanine_price_per_m2'  => $hasMezzanine ? $mezzaninePriceM2 : null,
        'mezzanine_total_price'   => $mezzanineTotalPrice,

        'discount'    => $discount,
        'total_price' => $totalPrice,
        'status'      => $validated['status'],

        /* 🖼 Image */
        'image' => $imagePath,

        /* 👤 Customer */
        'customer_ref_id' => $customer?->id,
        'customer_name'   => $validated['customer_name'] ?? null,
        'customer_id'     => $validated['customer_id'] ?? null,
        'customer_phone'  => $validated['customer_phone'] ?? null,
    ]);

    return redirect()->to(
        "/projects/{$validated['project_id']}/apartments"
        . "?building={$building->name}"
        . ($validated['tranche_number'] ? "&tranche={$validated['tranche_number']}" : "")
        . "&focus-shop={$shop->id}"
    );
}


    /* ===================== EDIT ===================== */
public function edit(Shop $shop)
{
    $shop->load('building.project');

    return inertia('Shops/Edit', [
        'shop' => [
            ...$shop->toArray(),

            'project_id'      => $shop->building->project->id,
            'building_number' => $shop->building->name,

            // ✅ هذا هو الحل
            'image_url' => $shop->image
                ? Storage::url($shop->image)
                : null,
        ],

        'projects' => Project::where('type', 'building')->get(),
    ]);
}

    /* ===================== UPDATE ===================== */
public function update(Request $request, Shop $shop)
{
    $validated = $request->validate([
        'project_id'      => 'required|exists:projects,id',
        'building_number' => 'required|string|max:50',
        'number'          => 'required|string|max:50',
        'tranche_number'  => 'nullable|string|max:50',

        'area'            => 'required|numeric|min:1',
        'price_per_m2'    => 'required|numeric|min:0',
        'discount'        => 'nullable|numeric|min:0',

        /* 🪜 Mezzanine */
        'mezzanine_area'         => 'nullable|numeric|min:0',
        'mezzanine_price_per_m2' => 'nullable|numeric|min:0',

        'status'          => 'required|in:متاح,مباع,محجوز',

        /* 👤 Customer */
        'customer_name'   => 'nullable|string|max:255',
        'customer_id'     => 'nullable|string|max:255',
        'customer_phone'  => 'nullable|string|max:20',

        /* 🖼 Image */
        'image'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
    ]);

    /* ================= BUILDING ================= */
    $building = Building::firstOrCreate([
        'project_id' => $validated['project_id'],
        'name'       => $validated['building_number'],
    ]);

    /* ================= UNIQUE ================= */
    $exists = Shop::where('building_id', $building->id)
        ->where('number', $validated['number'])
        ->where(function ($q) use ($validated) {
            if (!empty($validated['tranche_number'])) {
                $q->where('tranche_number', $validated['tranche_number']);
            } else {
                $q->whereNull('tranche_number');
            }
        })
        ->where('id', '!=', $shop->id)
        ->exists();

    if ($exists) {
        throw ValidationException::withMessages([
            'number' => 'رقم المحل موجود مسبقًا في نفس العمارة ونفس الشطر',
        ]);
    }

    /* ================= CUSTOMER ================= */
    $customer = null;

    if (!empty($validated['customer_id'])) {

        $customer = Customer::where('national_id', $validated['customer_id'])->first();

        if (!$customer) {
            $customer = Customer::create([
                'national_id' => $validated['customer_id'],
                'name'        => $validated['customer_name'] ?? 'غير محدد',
                'phone'       => $validated['customer_phone'] ?? null,
                'address'     => null,
            ]);
        } else {
            $updates = [];

            if (!empty($validated['customer_name']) && empty($customer->name)) {
                $updates['name'] = $validated['customer_name'];
            }

            if (!empty($validated['customer_phone']) && empty($customer->phone)) {
                $updates['phone'] = $validated['customer_phone'];
            }

            if (!empty($updates)) {
                $customer->update($updates);
            }
        }
    }

    /* ================= IMAGE ================= */
    $imagePath = $shop->image;

    if ($request->hasFile('image')) {

        if ($shop->image && Storage::disk('public')->exists($shop->image)) {
            Storage::disk('public')->delete($shop->image);
        }

        $imagePath = $request->file('image')->store('shops', 'public');
    }

    /* ================= CALCULATIONS ================= */
    $area     = (float) $validated['area'];
    $priceM2 = (float) $validated['price_per_m2'];
    $discount = (float) ($validated['discount'] ?? 0);

    /* 🪜 Mezzanine (مثل terrasse) */
    $hasMezzanine = !empty($validated['mezzanine_area']);

    $mezzanineArea = $hasMezzanine
        ? (float) $validated['mezzanine_area']
        : 0;

    $mezzaninePriceM2 = $hasMezzanine
        ? (float) $validated['mezzanine_price_per_m2']
        : 0;

    $mezzanineTotalPrice = $hasMezzanine
        ? ($mezzanineArea * $mezzaninePriceM2)
        : 0;

    /* 💰 TOTAL */
    $baseTotal = $area * $priceM2;

    $totalPrice = max(
        $baseTotal + $mezzanineTotalPrice - $discount,
        0
    );

    /* ================= UPDATE ================= */
    $shop->update([
        'building_id'   => $building->id,
        'number'        => $validated['number'],
        'tranche_number'=> $validated['tranche_number'] ?? null,

        'area'         => $area,
        'price_per_m2' => $priceM2,

        /* 🪜 Mezzanine */
        'has_mezzanine'           => $hasMezzanine,
        'mezzanine_area'          => $hasMezzanine ? $mezzanineArea : null,
        'mezzanine_price_per_m2'  => $hasMezzanine ? $mezzaninePriceM2 : null,
        'mezzanine_total_price'   => $mezzanineTotalPrice,

        'discount'    => $discount,
        'total_price' => $totalPrice,
        'status'      => $validated['status'],

        /* 🖼 Image */
        'image' => $imagePath,

        /* 👤 Customer */
        'customer_ref_id' => $customer?->id,
        'customer_name'   => $validated['customer_name'] ?? null,
        'customer_id'     => $validated['customer_id'] ?? null,
        'customer_phone'  => $validated['customer_phone'] ?? null,
    ]);

    /* ================= REDIRECT ================= */
    return redirect()->to(
        "/projects/{$validated['project_id']}/apartments"
        . "?building={$building->name}"
        . ($validated['tranche_number'] ? "&tranche={$validated['tranche_number']}" : "")
        . "&focus-shop={$shop->id}"
    );
}
 
        /* =====================================================
       SHOW
    ===================================================== */
public function show(Shop $shop)
{
    // تحميل العلاقات
    $shop->load([
    'building.project',
    'transfers.fromCustomer:id,name',
    'transfers.toCustomer:id,name',
    ]);


    /* ================= الدفوعات ================= */
    $payments = Payment::where('context', 'shop')
        ->where('shop_id', $shop->id)
        ->orderBy('paid_at')
        ->get();

    $paid = $payments->sum('amount');

   /* ================= سجل الملكية الصحيح ================= */
   $ownershipHistory = collect();

   // 1️⃣ المالك الأصلي = من أول تنازل
   $firstTransfer = $shop->transfers->sortBy('transfer_number')->first();

   if ($firstTransfer && $firstTransfer->fromCustomer) {
    $ownershipHistory->push([
        'name' => $firstTransfer->fromCustomer->name,
        'transfer_number' => '-',
        'date' => '-',
    ]);
   }

   // 2️⃣ الملاك بعد التنازلات
   foreach ($shop->transfers->sortBy('transfer_number') as $t) {
    if ($t->toCustomer) {
        $ownershipHistory->push([
            'name' => $t->toCustomer->name,
            'transfer_number' => $t->transfer_number,
            'date' => $t->transfer_date,
        ]);
    }
   }

  // 3️⃣ إزالة التكرار (احتياط)
   $ownershipHistory = $ownershipHistory->unique('name')->values();

   // 4️⃣ عكس الترتيب → المالك الحالي في الأعلى
      $ownershipHistory = $ownershipHistory->reverse()->values();


    return Inertia::render('Shops/Show', [
        'shop' => $shop,

        'project' => [
            'id'   => $shop->building->project->id,
            'name' => $shop->building->project->name,
        ],

        'building_number' => $shop->building->name,

        /* ================= الملخص المالي ================= */
        'summary' => [
            'total'     => $shop->total_price,
            'paid'      => $paid,
            'remaining' => max($shop->total_price - $paid, 0),
        ],

        /* ================= الدفوعات ================= */
        'payments' => $payments->map(function ($p) use ($shop) {
            return [
                'id'         => $p->id,
                'amount'     => $p->amount,
                'paid_at'    => $p->paid_at?->format('Y-m-d'),
                'method'     => $p->payment_method,
                'percentage' => $shop->total_price > 0
                    ? round(($p->amount / $shop->total_price) * 100, 1)
                    : 0,
            ];
        }),

        /* ================= سجل التنازلات (جاهز للعرض) ================= */
        'ownership_history' => $ownershipHistory,
    ]);
}

    /* =====================================================
       DELETE
    ===================================================== */
    public function destroy(Shop $shop)
    {




        $shop->load('building.project');

        $project  = $shop->building->project;
        $building = $shop->building;
        $tranche  = $shop->tranche_number;
        $id       = $shop->id;

         Payment::where('context', 'shop')
        ->where('shop_id', $shop->id)
        ->delete();

        $shop->delete();

        return redirect()->to(
            "/projects/{$project->id}/apartments"
            . "?building={$building->name}"
            . ($tranche ? "&tranche={$tranche}" : "")
            . "&focus-deleted={$id}"
        );
    }
}
