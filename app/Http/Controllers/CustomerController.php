<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Apartment;
use App\Models\Shop;
use App\Models\LandPlot;
use App\Models\Project;
use App\Models\Payment;
use Illuminate\Http\Request;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Inertia\Inertia;

class CustomerController extends Controller
{
public function print(Request $request, Customer $customer)
{
    // ================= قراءة الفلاتر =================
    $projectId  = $request->project_id;
    $context    = $request->context;
    $unitNumber = $request->unit_number;
    $building   = $request->building_number;
    $tranche    = $request->tranche_number;

    $units = collect();

    /* ================= الشقق ================= */
    if (!$context || $context === 'apartment') {

        $apartments = Apartment::with('building.project')
            ->where('customer_ref_id', $customer->id)
            ->when($projectId, fn ($q) =>
                $q->whereHas('building.project', fn ($qq) =>
                    $qq->where('id', $projectId)
                )
            )
            ->when($unitNumber, fn ($q) => $q->where('number', $unitNumber))
            ->when($tranche, fn ($q) => $q->where('tranche_number', $tranche))
            ->when($building, fn ($q) =>
                $q->whereHas('building', fn ($qq) =>
                    $qq->where('name', $building)
                )
            )
            ->get()
            ->map(function ($a) {

                $paid = Payment::where('context', 'apartment')
                    ->where('apartment_id', $a->id)
                    ->sum('amount');

                $total = $a->total_price;

                return [
                    'type'            => 'apartment',
                    'project_name'    => $a->building?->project?->name ?? '-',
                    'building_number' => $a->building?->name ?? '-',
                    'tranche_number'  => $a->tranche_number,
                    'number'          => $a->number,

                    'total_price'     => $total,
                    'total_paid'      => $paid,
                    'remaining'       => $total - $paid,

                    // ✅ نسبة الأداء
                    'payment_percent' => $total > 0
                        ? round(($paid / $total) * 100, 2)
                        : 0,
                ];
            });

        $units = $units->merge($apartments);
    }

    /* ================= المحلات ================= */
    if (!$context || $context === 'shop') {

        $shops = Shop::with('building.project')
            ->where('customer_ref_id', $customer->id)
            ->when($projectId, fn ($q) =>
                $q->whereHas('building.project', fn ($qq) =>
                    $qq->where('id', $projectId)
                )
            )
            ->when($unitNumber, fn ($q) => $q->where('number', $unitNumber))
            ->when($building, fn ($q) =>
                $q->whereHas('building', fn ($qq) =>
                    $qq->where('name', $building)
                )
            )
            ->get()
            ->map(function ($s) {

                $paid  = Payment::where('context', 'shop')
                    ->where('shop_id', $s->id)
                    ->sum('amount');

                $total = $s->total_price;

                return [
                    'type'            => 'shop',
                    'project_name'    => $s->building?->project?->name ?? '-',
                    'building_number' => $s->building?->name ?? '-',
                    'number'          => $s->number,

                    'total_price'     => $total,
                    'total_paid'      => $paid,
                    'remaining'       => $total - $paid,

                    // ✅ نسبة الأداء
                    'payment_percent' => $total > 0
                        ? round(($paid / $total) * 100, 2)
                        : 0,
                ];
            });

        $units = $units->merge($shops);
    }

    /* ================= القطع الأرضية ================= */
    if (!$context || $context === 'land') {

        $lands = LandPlot::with('project')
            ->where('customer_ref_id', $customer->id)
            ->when($projectId, fn ($q) => $q->where('project_id', $projectId))
            ->when($unitNumber, fn ($q) => $q->where('land_number', $unitNumber))
            ->get()
            ->map(function ($l) {

                $paid  = Payment::where('context', 'land')
                    ->where('land_id', $l->id)
                    ->sum('amount');

                $total = $l->total_price;

                return [
                    'type'         => 'land',
                    'project_name' => $l->project?->name ?? '-',
                    'number'       => $l->land_number,
                    'area'         => $l->area,
                    'road_type'    => $l->road_type,
                    'view_type'    => $l->view_type === '2-FACADE' ? '2F' : '1F',

                    'total_price'  => $total,
                    'total_paid'   => $paid,
                    'remaining'    => $total - $paid,

                    // ✅ نسبة الأداء
                    'payment_percent' => $total > 0
                        ? round(($paid / $total) * 100, 2)
                        : 0,
                ];
            });

        $units = $units->merge($lands);
    }

    /* ================= PRINT VIEW ================= */
    return view('pdf.customer-file', [
        'customer' => $customer,
        'units'    => $units,
    ]);
}



public function index(Request $request)
{
    $search = $request->input('search');

    $customers = Customer::query()
        ->when($search, function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('national_id', 'like', "%{$search}%");
        })
        ->orderBy('name')
        ->get();

    return Inertia::render('Customers/Index', [
        'customers' => $customers,
        'filters' => [
            'search' => $search,
        ],
    ]);
}



public function show(Request $request, Customer $customer)
{
    // ================= قراءة الفلاتر =================
    $projectId  = $request->project_id;
    $unitNumber = $request->unit_number;
    $building   = $request->building_number;
    $tranche    = $request->tranche_number;

    $units = collect();

    /* ================= الشقق ================= */
    $apartments = Apartment::with('building.project')
        ->where('customer_ref_id', $customer->id)
        ->when($projectId, fn ($q) =>
            $q->whereHas('building.project', fn ($qq) =>
                $qq->where('id', $projectId)
            )
        )
        ->when($unitNumber, fn ($q) => $q->where('number', $unitNumber))
        ->when($tranche, fn ($q) => $q->where('tranche_number', $tranche))
        ->when($building, fn ($q) =>
            $q->whereHas('building', fn ($qq) =>
                $qq->where('name', $building)
            )
        )
        ->get()
        ->map(function ($a) {
            $paid = Payment::where('context', 'apartment')
                ->where('apartment_id', $a->id)
                ->sum('amount');

            return [
                'id'              => $a->id,
                'context'         => 'apartment',
                'project_id'      => $a->building?->project?->id,
                'project_name'    => $a->building?->project?->name ?? '-',
                'building_number' => $a->building?->name ?? '-',
                'tranche_number'  => $a->tranche_number,
                'number'          => $a->number,
                'total_price'     => $a->total_price,
                'total_paid'      => $paid,
            ];
        });

    /* ================= المحلات ================= */
    $shops = Shop::with('building.project')
        ->where('customer_ref_id', $customer->id)
        ->when($projectId, fn ($q) =>
            $q->whereHas('building.project', fn ($qq) =>
                $qq->where('id', $projectId)
            )
        )
        ->when($unitNumber, fn ($q) => $q->where('number', $unitNumber))
        ->when($building, fn ($q) =>
            $q->whereHas('building', fn ($qq) =>
                $qq->where('name', $building)
            )
        )
        ->get()
        ->map(function ($s) {
            $paid = Payment::where('context', 'shop')
                ->where('shop_id', $s->id)
                ->sum('amount');

            return [
                'id'              => $s->id,
                'context'         => 'shop',
                'project_id'      => $s->building?->project?->id,
                'project_name'    => $s->building?->project?->name ?? '-',
                'building_number' => $s->building?->name ?? '-',
                'number'          => $s->number,
                'total_price'     => $s->total_price,
                'total_paid'      => $paid,
            ];
        });

    /* ================= القطع الأرضية ================= */
    $lands = LandPlot::with('project')
        ->where('customer_ref_id', $customer->id)
        ->when($projectId, fn ($q) => $q->where('project_id', $projectId))
        ->when($unitNumber, fn ($q) => $q->where('land_number', $unitNumber))
        ->get()
        ->map(function ($l) {
            $paid = Payment::where('context', 'land')
                ->where('land_id', $l->id)
                ->sum('amount');

            return [
                'id'           => $l->id,
                'context'      => 'land',
                'project_id'   => $l->project?->id,
                'project_name' => $l->project?->name ?? '-',
                'land_number'  => $l->land_number,
                'road_view'    => "{$l->road_type}م — {$l->view_type}",
                'total_price'  => $l->total_price,
                'total_paid'   => $paid,
            ];
        });

    $units = collect()
        ->merge($apartments)
        ->merge($shops)
        ->merge($lands);

    /* ================= المشاريع ================= */
    $projects = Project::whereIn(
        'id',
        $units->pluck('project_id')->filter()->unique()
    )->get();

    return inertia('Customers/Show', [
        'customer' => $customer,
        'units'    => $units,
        'projects' => $projects,
        'filters'  => $request->only([
            'project_id',
            'context',
            'unit_number',
            'building_number',
            'tranche_number',
        ]),
    ]);
}



    public function search(Request $request)
    {
       $q = $request->q;

      return Customer::where('name', 'like', "%$q%")
        ->orWhere('national_id', 'like', "%$q%")
        ->limit(10)
        ->get(['id', 'name', 'national_id']);
    }
}