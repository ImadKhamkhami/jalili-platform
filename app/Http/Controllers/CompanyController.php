<?php
namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    public function show(Company $company)
    {
      return inertia('Companies/Show', [
        'company' => $company->load('projects'),
      ]);
    }

public function index(Request $request)
{
    $search = $request->search;
    $type   = $request->type;

    $companies = Company::query()
        ->withCount([
            // جميع المشاريع
            'projects',

            // عدد مشاريع العمارات
            'projects as buildings_count' => function ($q) {
                $q->where('type', 'building');
            },

            // عدد مشاريع التجزئات
            'projects as lots_count' => function ($q) {
                $q->where('type', 'lot');
            },
        ])

        // 🔍 البحث بالاسم
        ->when($search, function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%");
        })

        // 🏗️ فلترة حسب نوع المشروع
        ->when($type, function ($q) use ($type) {

            if ($type === 'building') {
                $q->whereHas('projects', fn ($p) => $p->where('type', 'building'))
                  ->whereDoesntHave('projects', fn ($p) => $p->where('type', 'lot'));
            }

            if ($type === 'lot') {
                $q->whereHas('projects', fn ($p) => $p->where('type', 'lot'))
                  ->whereDoesntHave('projects', fn ($p) => $p->where('type', 'building'));
            }

            if ($type === 'mixed') {
                $q->whereHas('projects', fn ($p) => $p->where('type', 'building'))
                  ->whereHas('projects', fn ($p) => $p->where('type', 'lot'));
            }
        })

        ->latest()
        ->get();

    return inertia('Companies/Index', [
        'companies' => $companies,
        'filters' => [
            'search' => $search,
            'type'   => $type,
            'focus'  => $request->get('focus-company'),
        ],
    ]);
}


    public function create()
    {
        return Inertia::render('Companies/Create');
    }
public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
    ]);

    $company = Company::create($validated);

    return Inertia::location(
        route('companies.index', [
            'focus-company' => $company->id
        ])
    );
}

    public function edit(Company $company) 
    {
        return Inertia::render('Companies/Edit', [
            'company' => $company,
        ]);
    }

public function update(Request $request, Company $company)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
    ]);

    $company->update($validated);

    return Inertia::location(
        route('companies.index', [
            'focus-company' => $company->id
        ])
    );
}

    public function destroy(Company $company)
    {


        $company->delete();

        return back();
    }
}
