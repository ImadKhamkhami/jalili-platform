<?php

namespace App\Http\Controllers;

use App\Models\LandPlot;
use App\Models\Project;
use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Customer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;


class LandPlotController extends Controller
{

     /* -------------------------------------------------------
        طباعة بيان القطعة
    ------------------------------------------------------- */

public function invoicePrint(LandPlot $land)
{
    $land->load('project');

    /* ===== العنوان ===== */
    $title = "بيان القطعة رقم {$land->land_number} – {$land->project->name}";

    /* ===== الدفوعات ===== */
    $payments = Payment::where('context', 'land')
        ->where('land_id', $land->id)
        ->orderBy('paid_at')
        ->get();

    $paidTotal = $payments->sum('amount');
    $remaining = max($land->total_price - $paidTotal, 0);
    $progress  = $land->total_price > 0
        ? round(($paidTotal / $land->total_price) * 100, 1)
        : 0;

    /* ===== PRINT VIEW (بدون PDF) ===== */
    return view('lands.invoice', compact(
        'land',
        'title',
        'payments',
        'paidTotal',
        'remaining',
        'progress'
    ));
}

    /* -------------------------------------------------------
        طباعة المخطط
    ------------------------------------------------------- */
public function printPlan(Project $project)
{
    $lands = LandPlot::where('project_id', $project->id)
        ->orderByRaw('CAST(land_number AS UNSIGNED) ASC')
        ->get();

    return view('pdf.lands-plan', [
        'project' => $project,
        'lands'   => $lands,
    ]);
}


private function getLandsWithPayments(Project $project)
{
    return LandPlot::where('project_id', $project->id)
        ->with('payments')
        ->orderByRaw("LPAD(land_number, 10, '0') ASC")
        ->get()
        ->map(function ($land) {

            $paid = $land->payments->sum('amount');
            $total = $land->total_price;

            return [
                ...$land->toArray(),

                'paid_amount' => $paid,
                'remaining_amount' => max($total - $paid, 0),

                'payment_percentage' => $total > 0
                    ? round(($paid / $total) * 100, 1)
                    : 0,
            ];
        });
}


    /* -------------------------------------------------------
        INDEX – عرض كل القطع
    ------------------------------------------------------- */
public function index()
{
    $projects = Project::where('type', 'lot')
    ->orderByDesc('id')
    ->get()
    ->values();

    $currentProject = $projects->first();

    $lands = $currentProject
        ? $this->getLandsWithPayments($currentProject)
        : collect();

    return Inertia::render('Lands/Index', [
        'projects'        => $projects,
        'current_project' => $currentProject,
        'lands'           => $lands,
    ]);
}




    /* -------------------------------------------------------
        BY PROJECT – عرض القطع حسب المشروع (مثل الشقق)
        /projects/{project}/lands
    ------------------------------------------------------- */
public function byProject(Project $project)
{
    return Inertia::render('Lands/Index', [
        'projects' => Project::where('type', 'lot')
            ->orderByDesc('id') // ✅ نفس الترتيب
            ->get()
            ->values(),

        'current_project' => $project,
        'lands'           => $this->getLandsWithPayments($project),
    ]);
}


    /* -------------------------------------------------------
        CREATE
    ------------------------------------------------------- */
    public function create()
    {
        return Inertia::render('Lands/Create', [
            'projects' => Project::where('type', 'lot')
                ->select('id', 'name')
                ->get(),
        ]);
    }

    /* -------------------------------------------------------
        STORE
    ------------------------------------------------------- */

    public function store(Request $request)
{
    $validated = $request->validate(
        [
            'project_id' => 'required|exists:projects,id',

            'land_number' => [
                'required',
                Rule::unique('land_plots', 'land_number')
                    ->where(fn ($q) => $q->where('project_id', $request->project_id)),
            ],

            'area'         => 'required|numeric|min:1',
            'road_type'    => 'required|string|max:100',
            'view_type'    => 'required|in:1-FACADE,2-FACADE',
            'status'       => 'required|in:متاحة,محجوزة,مباعة',

            'price_per_m2' => 'required|numeric|min:0',
            'discount'     => 'nullable|numeric|min:0',

            /* 👤 Customer */
            'customer_name'  => 'nullable|string|max:255',
            'customer_id'    => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',

            /* 🖼 Image */
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ],
        [
            'land_number.unique'    => 'رقم القطعة موجود مسبقًا داخل هذا المشروع',
            'land_number.required'  => 'حقل رقم القطعة إجباري',
            'price_per_m2.required' => 'حقل ثمن المتر إجباري',
            'road_type.required'    => 'حقل نوع الطريق إجباري',
            'view_type.required'    => 'حقل نوع الواجهة إجباري',
            'area.required'         => 'حقل المساحة إجباري',
            'project_id.required'   => 'حقل المشروع إجباري',
        ]
    );

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
        $imagePath = $request->file('image')->store('land_plots', 'public');
    }

    /* ================= CALCULATIONS ================= */
    $area      = (float) $validated['area'];
    $priceM2  = (float) $validated['price_per_m2'];
    $discount = (float) ($validated['discount'] ?? 0);

    $baseTotal  = $area * $priceM2;
    $totalPrice = max($baseTotal - $discount, 0);

    /* ================= CREATE ================= */
    $land = LandPlot::create([
        'project_id'   => $validated['project_id'],
        'land_number'  => $validated['land_number'],
        'area'         => $area,
        'road_type'    => $validated['road_type'],
        'view_type'    => $validated['view_type'],
        'status'       => $validated['status'],
        'price_per_m2' => $priceM2,

        'discount'    => $discount,
        'total_price' => $totalPrice,
        'image'       => $imagePath,

        /* 👤 Customer */
        'customer_ref_id' => $customer?->id,
        'customer_name'   => $validated['customer_name'] ?? null,
        'customer_id'     => $validated['customer_id'] ?? null,
        'customer_phone'  => $validated['customer_phone'] ?? null,
    ]);

    return Inertia::location(
        "/projects/{$validated['project_id']}/lands?focus-land={$land->id}"
    );
}

    /* -------------------------------------------------------
        SHOW (اختياري – نوسّعه لاحقًا)
    ------------------------------------------------------- */
public function show(LandPlot $land)
{
    // تحميل العلاقات
    $land->load([
        'project',
        'transfers.fromCustomer:id,name',
        'transfers.toCustomer:id,name',
    ]);

    /* ================= الدفوعات ================= */
    $payments = Payment::where('context', 'land')
        ->where('land_id', $land->id)
        ->orderBy('paid_at')
        ->get();

    $paid  = $payments->sum('amount');
    $total = $land->total_price;

    /* ================= سجل الملكية (نفس المنهج) ================= */
    $ownershipHistory = collect();

    // 1️⃣ المالك الأصلي = from أول تنازل
    $firstTransfer = $land->transfers
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
    foreach ($land->transfers->sortBy('transfer_number') as $t) {
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

    return Inertia::render('Lands/Show', [
        'land' => $land,

        'project' => [
            'id'   => $land->project->id,
            'name' => $land->project->name,
        ],

        'summary' => [
            'total'     => $total,
            'paid'      => $paid,
            'remaining' => max($total - $paid, 0),
        ],

        /* ================= الدفوعات ================= */
        'payments' => $payments->map(function ($p) use ($total) {
            return [
                'id'         => $p->id,
                'amount'     => $p->amount,
                'paid_at'    => $p->paid_at?->format('Y-m-d'),
                'method'     => $p->payment_method,
                'percentage' => $total > 0
                    ? round(($p->amount / $total) * 100, 1)
                    : 0,
            ];
        }),

        /* ✅ المهم */
        'ownership_history' => $ownershipHistory,
    ]);
}

    /* -------------------------------------------------------
        EDIT
    ------------------------------------------------------- */
public function edit(LandPlot $land)
{
    return Inertia::render('Lands/Edit', [
        'land' => [
            'id'            => $land->id,
            'project_id'    => (int) $land->project_id,
            'land_number'   => $land->land_number,
            'road_type'     => $land->road_type,
            'view_type'     => $land->view_type,
            'area'          => $land->area,
            'price_per_m2'  => $land->price_per_m2,
            'discount'      => $land->discount ?? 0,
            'status'        => $land->status,
            'image'         => $land->image,

            'customer_name'  => $land->customer_name,
            'customer_id'    => $land->customer_id,
            'customer_phone' => $land->customer_phone,
        ],

        // ✅ نفس create تمامًا
        'projects' => Project::where('type', 'lot')
            ->select('id', 'name')
            ->get(),
    ]);
}

    /* -------------------------------------------------------
        UPDATE
    ------------------------------------------------------- */
public function update(Request $request, LandPlot $land)
{
    

    $validated = $request->validate(
        [
            'project_id' => 'required|exists:projects,id',

            'land_number' => [
                'required',
                Rule::unique('land_plots', 'land_number')
                    ->where(fn ($q) => $q->where('project_id', $request->project_id))
                    ->ignore($land->id), // ✅ الفرق المهم
            ],

            'area'         => 'required|numeric|min:1',
            'road_type'    => 'required|string|max:100',
            'view_type'    => 'required|in:1-FACADE,2-FACADE',
            'status'       => 'required|in:متاحة,محجوزة,مباعة',

            'price_per_m2' => 'required|numeric|min:0',
            'discount'     => 'nullable|numeric|min:0',

            /* 👤 Customer */
            'customer_name'  => 'nullable|string|max:255',
            'customer_id'    => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',

            /* 🖼 Image */
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]
    );

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
            $customer->update([
                'name'  => $validated['customer_name']  ?? $customer->name,
                'phone' => $validated['customer_phone'] ?? $customer->phone,
            ]);
        }
    }

    /* ================= IMAGE ================= */
    $imagePath = $land->image; // ✅ نحتفظ بالقديمة

    if ($request->hasFile('image')) {
        if ($land->image && Storage::disk('public')->exists($land->image)) {
            Storage::disk('public')->delete($land->image);
        }

        $imagePath = $request->file('image')->store('land_plots', 'public');
    }

    /* ================= CALCULATIONS ================= */
    $area      = (float) $validated['area'];
    $priceM2   = (float) $validated['price_per_m2'];
    $discount  = (float) ($validated['discount'] ?? 0);

    $baseTotal  = $area * $priceM2;
    $totalPrice = max($baseTotal - $discount, 0);

    /* ================= UPDATE ================= */
    $land->update([
        'project_id'   => $validated['project_id'],
        'land_number'  => $validated['land_number'],
        'area'         => $area,
        'road_type'    => $validated['road_type'],
        'view_type'    => $validated['view_type'],
        'status'       => $validated['status'],
        'price_per_m2' => $priceM2,

        'discount'    => $discount,
        'total_price' => $totalPrice,
        'image'       => $imagePath,

        /* 👤 Customer */
        'customer_ref_id' => $customer?->id,
        'customer_name'   => $validated['customer_name'] ?? null,
        'customer_id'     => $validated['customer_id'] ?? null,
        'customer_phone'  => $validated['customer_phone'] ?? null,
    ]);

    return Inertia::location(
        "/projects/{$validated['project_id']}/lands?focus-land={$land->id}"
    );
}

    /* -------------------------------------------------------
        DELETE
    ------------------------------------------------------- */
public function destroy(LandPlot $land)
{
    DB::transaction(function () use ($land) {

        // 🧾 حذف التنازلات المرتبطة بالقطعة
        \App\Models\Transfer::where('context', 'land')
            ->where('unit_id', $land->id)
            ->delete();

        // 💰 حذف دفوعات القطعة
        Payment::where('context', 'land')
            ->where('land_id', $land->id)
            ->delete();

        // 🌍 حذف القطعة
        $land->delete();
    });

    $projectId = $land->project_id;
    $id = $land->id;

    return redirect(
        "/projects/{$projectId}/lands?focus-deleted={$id}"
    )->with('success', 'تم حذف القطعة ودفوعاتها وتنازلاتها بنجاح');
}

}
