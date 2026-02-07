<?php

namespace App\Http\Controllers;

use App\Models\Transfer;
use App\Models\Apartment;
use App\Models\Shop;
use App\Models\LandPlot;
use App\Models\Customer;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    /* =========================================================
     | عرض قائمة التنازلات
     ========================================================= */
public function index(Request $request)
{
    $query = Transfer::with([
        'fromCustomer:id,name',
        'toCustomer:id,name',
        'apartment.building.project:id,name',
        'shop.building.project:id,name',
        'land.project:id,name',
    ]);

    /* ================= الفلاتر ================= */

    if ($request->filled('project_id')) {
        $query->where(function ($q) use ($request) {
            $q->whereHas('apartment.building.project', fn ($qq) =>
                $qq->where('id', $request->project_id)
            )
            ->orWhereHas('shop.building.project', fn ($qq) =>
                $qq->where('id', $request->project_id)
            )
            ->orWhereHas('land.project', fn ($qq) =>
                $qq->where('id', $request->project_id)
            );
        });
    }

    if ($request->filled('context')) {
        $query->where('context', $request->context);
    }

    if ($request->filled('date_from')) {
        $query->whereDate('transfer_date', '>=', $request->date_from);
    }

    if ($request->filled('date_to')) {
        $query->whereDate('transfer_date', '<=', $request->date_to);
    }

    if ($request->filled('unit_number')) {
        $query->where(function ($q) use ($request) {
            $q->whereHas('apartment', fn ($qq) =>
                $qq->where('number', $request->unit_number)
            )
            ->orWhereHas('shop', fn ($qq) =>
                $qq->where('number', $request->unit_number)
            )
            ->orWhereHas('land', fn ($qq) =>
                $qq->where('land_number', $request->unit_number)
            );
        });
    }

    /* ================= Pagination ================= */

    $transfers = $query
        ->latest()
        ->paginate(10)
        ->withQueryString();

    /* ================= Transform (المفتاح) ================= */

    $transfers->getCollection()->transform(function ($t) {
        return [
            'id' => $t->id,
            'context' => $t->context,
            'transfer_number' => $t->transfer_number,
            'transfer_date' => $t->transfer_date,

            'from_customer' => $t->fromCustomer
                ? ['name' => $t->fromCustomer->name]
                : null,

            'to_customer' => $t->toCustomer
                ? ['name' => $t->toCustomer->name]
                : null,

            // ✅ المشروع
            'project' => match ($t->context) {
                'apartment' => $t->apartment?->building?->project
                    ? ['name' => $t->apartment->building->project->name]
                    : null,

                'shop' => $t->shop?->building?->project
                    ? ['name' => $t->shop->building->project->name]
                    : null,

                'land' => $t->land?->project
                    ? ['name' => $t->land->project->name]
                    : null,

                default => null,
            },

            // ✅ الوحدة
            'unit_label' => match ($t->context) {
                'apartment' => $t->apartment
                    ? 'شقة ' . $t->apartment->number
                    : '-',

                'shop' => $t->shop
                    ? 'محل ' . $t->shop->number
                    : '-',

                'land' => $t->land
                    ? 'قطعة ' . $t->land->land_number
                    : '-',

                default => '-',
            },
        ];
    });

    return Inertia::render('Transfers/Index', [
        'transfers' => $transfers,
        'projects'  => Project::select('id', 'name')->get(),
        'filters'   => $request->all(),
    ]);
}


    /* =========================================================
     | صفحة إنشاء تنازل
     ========================================================= */
        public function create(string $context, int $unit)
    {
        $model = match ($context) {
            'apartment' => Apartment::with('building.project')->findOrFail($unit),
            'shop'      => Shop::with('building.project')->findOrFail($unit),
            'land'      => LandPlot::with('project')->findOrFail($unit),
            default     => abort(404),
        };

        /* المالك الحالي */
        $fromCustomer = $model->customer_ref_id
            ? Customer::find($model->customer_ref_id)
            : null;

        if (!$fromCustomer) {
            return Inertia::render('Transfers/Create', [
                'context' => $context,
                'unit' => [
                    'id'    => $model->id,
                    'label' => match ($context) {
                        'apartment' => "شقة رقم {$model->number}",
                        'shop'      => "محل رقم {$model->number}",
                        'land'      => "قطعة رقم {$model->land_number}",
                    },
                ],
                'project' => match ($context) {
                    'land' => $model->project,
                    default => $model->building->project,
                },
                'transfer_error' =>
                    'لا يمكن تسجيل تنازل لأن هذه الوحدة لا تملك مالكًا حاليًا',
            ]);
        }

        $nextTransferNumber = Transfer::where('context', $context)
            ->where('unit_id', $unit)
            ->max('transfer_number');

        return Inertia::render('Transfers/Create', [
            'context' => $context,
            'unit' => [
                'id'    => $model->id,
                'label' => match ($context) {
                    'apartment' => "شقة رقم {$model->number}",
                    'shop'      => "محل رقم {$model->number}",
                    'land'      => "قطعة رقم {$model->land_number}",
                },
            ],
            'project' => match ($context) {
                'land' => $model->project,
                default => $model->building->project,
            },
            'from_customer' => [
                'id'   => $fromCustomer->id,
                'name' => $fromCustomer->name,
            ],
            'transfer_number' => ($nextTransferNumber ?? 0) + 1,
        ]);
        }
    

    /* =========================================================
     | تخزين التنازل
     ========================================================= */

public function store(Request $request)
{
    $data = $request->validate([
        'context'          => 'required|in:apartment,shop,land',
        'unit_id'          => 'required|integer',
        'from_customer_id' => 'required|exists:customers,id',

        'to_name'        => 'required|string|max:255',
        'to_national_id' => 'required|string|max:50',
        'to_phone'       => 'nullable|string|max:50',

        'transfer_date' => 'required|date',
        'notes'         => 'nullable|string',
    ]);

    DB::transaction(function () use ($data) {

        $unit = match ($data['context']) {
            'apartment' => Apartment::lockForUpdate()->findOrFail($data['unit_id']),
            'shop'      => Shop::lockForUpdate()->findOrFail($data['unit_id']),
            'land'      => LandPlot::lockForUpdate()->findOrFail($data['unit_id']),
        };

        if ($unit->customer_ref_id != $data['from_customer_id']) {
            abort(422, 'الزبون المتنازل ليس هو المالك الحالي للوحدة');
        }

        $toCustomer = Customer::firstOrCreate(
            ['national_id' => $data['to_national_id']],
            [
                'name'  => $data['to_name'],
                'phone' => $data['to_phone'],
            ]
        );

        if ($toCustomer->id == $data['from_customer_id']) {
            abort(422, 'لا يمكن التنازل لنفس الزبون');
        }

        $transferNumber = Transfer::where('context', $data['context'])
            ->where('unit_id', $data['unit_id'])
            ->max('transfer_number');

        Transfer::create([
            'context'          => $data['context'],
            'unit_id'          => $data['unit_id'],
            'from_customer_id' => $data['from_customer_id'],
            'to_customer_id'   => $toCustomer->id,
            'to_national_id'   => $data['to_national_id'],// خزن رقم البطاقة مباشرة في التنازل
            'transfer_number'  => ($transferNumber ?? 0) + 1,
            'transfer_date'    => $data['transfer_date'],
            'notes'            => $data['notes'],
        ]);

        // ✅ تحديث المالك الحالي فقط
        $unit->update([
            'customer_id' => $data['to_national_id'],
            'customer_ref_id' => $toCustomer->id,
            'customer_name'   => $toCustomer->name,
            'customer_phone'  => $toCustomer->phone,
        ]);
    });

    return Inertia::location(route('transfers.index'));
}

    /* =========================================================
     | تعديل تنازل
     ========================================================= */
    public function edit(Transfer $transfer)
    {
        $transfer->load([
            'fromCustomer:id,name',
            'toCustomer:id,name,national_id,phone',
            'apartment.building.project',
            'shop.building.project',
            'land.project',
        ]);

        $unit = match ($transfer->context) {
            'apartment' => $transfer->apartment,
            'shop'      => $transfer->shop,
            'land'      => $transfer->land,
        };

        $project = match ($transfer->context) {
            'land'    => $unit->project,
            default   => $unit->building->project,
        };

        return Inertia::render('Transfers/Edit', [
            'transfer' => [
                'id'              => $transfer->id,
                'context'         => $transfer->context,
                'unit_id'         => $transfer->unit_id,
                'transfer_number' => $transfer->transfer_number,
                'transfer_date'   => $transfer->transfer_date,
                'notes'           => $transfer->notes,
            ],
            'unit' => [
                'id'    => $unit->id,
                'label' => match ($transfer->context) {
                    'apartment' => "شقة رقم {$unit->number}",
                    'shop'      => "محل رقم {$unit->number}",
                    'land'      => "قطعة رقم {$unit->land_number}",
                },
            ],
            'project' => [
                'id'   => $project->id,
                'name' => $project->name,
            ],
            'from_customer' => $transfer->fromCustomer,
            'to_customer'   => $transfer->toCustomer,
        ]);
    }

    public function update(Request $request, Transfer $transfer)
    {
        $data = $request->validate([
            'to_name'        => 'required|string|max:255',
            'to_national_id' => 'required|string|max:50',
            'to_phone'       => 'nullable|string|max:50',
            'transfer_date'  => 'required|date',
            'notes'          => 'nullable|string',
        ]);

        $toCustomer = Customer::firstOrCreate(
            ['national_id' => $data['to_national_id']],
            [
                'name'  => $data['to_name'],
                'phone' => $data['to_phone'],
            ]
        );

        $transfer->update([
            'to_customer_id' => $toCustomer->id,
            'transfer_date'  => $data['transfer_date'],
            'notes'          => $data['notes'],
        ]);

        return Inertia::location(route('transfers.index'));
    }

    /* =========================================================
     | إرجاع الملكية
     ========================================================= */
public function restoreOwnership(Transfer $transfer)
{
    $unit = match ($transfer->context) {
        'apartment' => Apartment::findOrFail($transfer->unit_id),
        'shop'      => Shop::findOrFail($transfer->unit_id),
        'land'      => LandPlot::findOrFail($transfer->unit_id),
    };

    $previousTransfer = Transfer::where('context', $transfer->context)
        ->where('unit_id', $transfer->unit_id)
        ->where('id', '<', $transfer->id)
        ->latest('id')
        ->first();

    $previousCustomerId = $previousTransfer
        ? $previousTransfer->to_customer_id
        : $transfer->from_customer_id;

    $unit->update([
        'customer_ref_id' => $previousCustomerId,
    ]);

    return back()->with('success', 'تمت إعادة ملكية الوحدة بنجاح');
}


public function print(Transfer $transfer)
{
    $transfer->load([
        'fromCustomer',
        'toCustomer',
        'apartment.building.project.company',
        'shop.building.project.company',
        'land.project.company',
    ]);

    // ===============================
    // تحديد المشروع والشركة حسب context
    // ===============================
    if ($transfer->context === 'apartment') {
        $project = $transfer->apartment->building->project;
    } elseif ($transfer->context === 'shop') {
        $project = $transfer->shop->building->project;
    } else { // land
        $project = $transfer->land->project;
    }

    $company = $project->company;

    return view('pdf.transfer', [
        'transfer' => $transfer,
        'project'  => $project,
        'company'  => $company,
    ]);
}


    /* =========================================================
     | حذف تنازل
     ========================================================= */
    public function destroy(Transfer $transfer)
    {
        $transfer->delete();

        return redirect()
            ->route('transfers.index')
            ->with('success', 'تم حذف التنازل بنجاح');
    }
}