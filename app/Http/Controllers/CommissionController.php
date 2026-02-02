<?php

namespace App\Http\Controllers;
use App\Models\Commission;
use App\Models\Project;
use App\Models\LandPlot;
use Inertia\Inertia;

use Illuminate\Http\Request;

class CommissionController extends Controller
{

public function index(Request $request)
{
    $commissions = Commission::query()
        ->with([
            'land.project',
            'apartment.building.project',
            'shop.building.project',
        ])

        // فلترة حسب المشروع
        ->when($request->project_id, function ($q) use ($request) {
            $q->where(function ($qq) use ($request) {
                $qq->whereHas('land', fn ($l) =>
                        $l->where('project_id', $request->project_id)
                    )
                    ->orWhereHas('apartment.building.project', fn ($p) =>
                        $p->where('id', $request->project_id)
                    )
                    ->orWhereHas('shop.building.project', fn ($p) =>
                        $p->where('id', $request->project_id)
                    );
            });
        })

        // فلترة حسب النوع
        ->when($request->context, fn ($q) =>
            $q->where('context', $request->context)
        )

        // فلترة حسب رقم الوحدة
        ->when($request->unit_number, function ($q) use ($request) {
            $q->where(function ($qq) use ($request) {
                $qq->whereHas('land', fn ($l) =>
                        $l->where('land_number', $request->unit_number)
                    )
                    ->orWhereHas('apartment', fn ($a) =>
                        $a->where('number', $request->unit_number)
                    )
                    ->orWhereHas('shop', fn ($s) =>
                        $s->where('number', $request->unit_number)
                    );
            });
        })

        ->latest()
        ->paginate(10)
        ->withQueryString();

    return inertia('Commissions/Index', [
        'commissions' => $commissions,
        'projects'    => Project::select('id', 'name')->get(),
        'filters'     => $request->only([
            'project_id',
            'context',
            'unit_number',
        ]),
    ]);
}




public function store(Request $request)
{
    $data = $request->validate([
        'context' => 'required|in:land,apartment,shop',
        'unit_id' => 'required|integer',

        'amount' => 'required|numeric|min:0',
        'commission_date' => 'required|date',
        'broker_name' => 'nullable|string|max:255',
        'notes' => 'nullable|string',
    ]);

    $payload = [
        'context'         => $data['context'],
        'amount'          => $data['amount'],
        'commission_date' => $data['commission_date'],
        'broker_name'     => $data['broker_name'],
        'notes'           => $data['notes'],
    ];

    match ($data['context']) {
        'land'      => $payload['land_id']      = $data['unit_id'],
        'apartment' => $payload['apartment_id'] = $data['unit_id'],
        'shop'      => $payload['shop_id']      = $data['unit_id'],
    };

    Commission::create($payload);

     return redirect('/commissions')
        ->with('success', 'تم اضافة السمسرة بنجاح');
}


public function create(string $context, int $unit)
{
    if ($context === 'land') {
        $unitModel = LandPlot::with('project')->findOrFail($unit);
        $project = $unitModel->project;

        $unitModel->label = 'قطعة رقم ' . $unitModel->land_number;
    }

    return inertia('Commissions/Create', [
        'context' => $context,
        'unit'    => $unitModel,
        'project' => $project,
    ]);
}

public function edit(Commission $commission)
{
    $unit = null;
    $label = null;

    if ($commission->context === 'land') {
        $unit = $commission->land;
        $label = 'قطعة رقم ' . $unit->land_number;
    } elseif ($commission->context === 'apartment') {
        $unit = $commission->apartment;
        $label = 'شقة رقم ' . $unit->number;
    } elseif ($commission->context === 'shop') {
        $unit = $commission->shop;
        $label = 'محل رقم ' . $unit->number;
    }

    return inertia('Commissions/Edit', [
        'commission' => $commission,
        'context'    => $commission->context,
        'unit'       => $unit,
        'unitLabel'  => $label,
        'project'    => $commission->project,
    ]);
}

public function print(Commission $commission)
{
    return view('pdf.commission', compact('commission'));
}




public function update(Request $request, Commission $commission)
{
    $data = $request->validate([
        'amount' => 'required|numeric',
        'commission_date' => 'required|date',
        'broker_name' => 'nullable|string',
        'notes' => 'nullable|string',
    ]);

    $commission->update($data);

    return redirect('/commissions')
        ->with('success', 'تم تعديل السمسرة بنجاح');
}


public function destroy(Commission $commission)
{
    $commission->delete();

    return redirect()
        ->back()
        ->with('success', 'تم حذف السمسرة بنجاح');
}



}
