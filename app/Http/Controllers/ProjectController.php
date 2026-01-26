<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;

class ProjectController extends Controller
{



public function statement(Project $project)
{
    $project->load([
        'buildings.shops.payments',
        'buildings.apartments.payments',
        'landPlots.payments',
    ]);

    $rows = [];

    // ================= العمارات =================
    foreach ($project->buildings as $building) {

        $buildingNumber = $building->name;

        // ---------- المحلات ----------
        foreach ($building->shops as $shop) {
            $paid = $shop->payments->sum('amount');

            $rows[] = [
                'type'      => 'shop',
                'group_key' => "building:$buildingNumber:tranche:" . ($shop->tranche_number ?? 'none'),

                'building'    => $buildingNumber,
                'tranche'     => $shop->tranche_number,
                'number'      => $shop->number,
                'area'        => $shop->area,
                'total_price' => $shop->total_price,
                'paid'        => $paid,
                'remaining'   => $shop->total_price - $paid,
                'payments'    => $shop->payments->map(fn ($p) => [
                    'amount' => $p->amount,
                    'date'   => optional($p->paid_at)->format('d/m/Y'),
                ])->toArray(),
            ];
        }

        // ---------- الشقق ----------
        foreach ($building->apartments as $apartment) {
            $paid = $apartment->payments->sum('amount');

            $rows[] = [
                'type'      => 'apartment',
                'group_key' => "building:$buildingNumber:tranche:" . ($apartment->tranche_number ?? 'none'),

                'building'    => $buildingNumber,
                'tranche'     => $apartment->tranche_number,
                'number'      => $apartment->number,
                'area'        => $apartment->area,
                'terrace'     => $apartment->terrace_area,
                'total_price' => $apartment->total_price,
                'paid'        => $paid,
                'remaining'   => $apartment->total_price - $paid,
                'payments'    => $apartment->payments->map(fn ($p) => [
                    'amount' => $p->amount,
                    'date'   => optional($p->paid_at)->format('d/m/Y'),
                ])->toArray(),
            ];
        }
    }

    // ================= القطع الأرضية =================
    foreach ($project->landPlots as $land) {
        $paid = $land->payments->sum('amount');

        $rows[] = [
            'type'      => 'land',
            'group_key' => 'lands',

            'building'    => null,
            'tranche'     => null,
            'number'      => $land->land_number,
            'area'        => $land->area,
            'total_price' => $land->total_price,
            'paid'        => $paid,
            'remaining'   => $land->total_price - $paid,
            'payments'    => $land->payments->map(fn ($p) => [
                'amount' => $p->amount,
                'date'   => optional($p->paid_at)->format('d/m/Y'),
            ])->toArray(),
        ];
    }

    // ✅ PRINT VIEW (بدون PDF)
    return view('pdf.project-statement', [
        'project' => $project,
        'rows'    => $rows,
    ]);
}




public function index(Request $request)
{
    $search = $request->search;
    $type   = $request->type; // فقط لإرسالها للـ Vue

    $projects = Project::with('company')
        ->withCount([
            'shops',        // ✅ عدد المحلات التجارية الحقيقي
            'apartments',   // عدد الشقق
            'landPlots',    // عدد القطع
        ])
        ->when($search, fn ($q) =>
            $q->where('name', 'LIKE', "%{$search}%")
        )
        ->latest()
        ->get();

    return inertia('Projects/Index', [
        'projects' => $projects,
        'filters'  => [
            'search' => $search,
            'type'   => $type,
        ],
    ]);
}


    public function create()
    {
        return Inertia::render('Projects/Create', [
            'companies' => Company::select('id', 'name')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name'       => 'required|string|max:255',
            'type'       => 'required|in:building,lot',
            'titre_foncier' => 'nullable|string|max:255',
        ]);

        $project = Project::create($validated);

return Inertia::location(
    route('projects.index', [
        'focus' => $project->id,
        'type'  => $project->type, // building / lot
    ])
);

    }

    public function edit(Project $project)
    {
        return Inertia::render('Projects/Edit', [
            'project'   => $project,
            'companies' => Company::all()
        ]);
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name'       => 'required|string|max:255',
            'type'       => 'required|in:building,lot',
            'titre_foncier' => 'nullable|string|max:255', 
        ]);

        $project->update($validated);

return Inertia::location(
    route('projects.index', [
        'focus' => $project->id,
        'type'  => $project->type, // مهم باش يفتح tab الصحيح
    ])
);

    }

    public function destroy(Project $project)
    {

        $project->delete();
        return back();
    }

    public function apartments(Project $project)
{
    $project->load(['buildings.apartments']);

    return Inertia::render('Apartments/Index', [
        'project'     => $project,
        'apartments'  => $project->buildings->flatMap->apartments,
        'buildings'   => $project->buildings,
    ]);
}

}