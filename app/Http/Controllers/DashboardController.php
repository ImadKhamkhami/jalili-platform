<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Company;
use App\Models\Project;
use App\Models\Customer;
use App\Models\Payment;
use Carbon\Carbon;

class DashboardController extends Controller
{


public function __invoke()
{
    $stats = [
        'companies' => Company::count(),
        'projects'  => Project::count(),
        'customers' => Customer::count(),
        'payments_sum' => Payment::whereDate('created_at', Carbon::today())->sum('amount'),
    ];

    $lastPayments = Payment::with([
            'apartment.building.project',
            'apartment.customer',
            'shop.building.project',
            'shop.customer',
            'land.project',
            'land.customer',
        ])
        ->latest()
        ->take(5)
        ->get()
        ->map(function ($p) {

            $customer = '-';
            $project  = '-';
            $number   = '-';

            if ($p->context === 'apartment' && $p->apartment) {
                $customer = $p->apartment->customer?->name ?? '-';
                $project  = $p->apartment->building?->project?->name ?? '-';
                $number   = $p->apartment->number ?? '-';
            }

            elseif ($p->context === 'shop' && $p->shop) {
                $customer = $p->shop->customer?->name ?? '-';
                $project  = $p->shop->building?->project?->name ?? '-';
                $number   = $p->shop->number ?? '-';
            }

            elseif ($p->context === 'land' && $p->land) {
                $customer = $p->land->customer?->name ?? '-';
                $project  = $p->land->project?->name ?? '-';
                $number   = $p->land->land_number ?? '-';
            }

            return [
                'id'              => $p->id,
                'date'            => $p->created_at->format('d/m/Y'),
                'customer'        => $customer,
                'project'         => $project,
                'context'         => $p->context,

                // ✅ هذه كانت ناقصة
                'number'          => $number,
                'building_number' => $p->building_number,
                'tranche_number'  => $p->tranche_number,
                'payment_method'  => $p->payment_method,

                'amount'          => $p->amount,
            ];
        });

    return Inertia::render('Dashboard', [
        'stats'        => $stats,
        'lastPayments' => $lastPayments,
    ]);
}



}
